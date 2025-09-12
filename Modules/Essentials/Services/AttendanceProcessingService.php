<?php

namespace Modules\Essentials\Services;

use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsAttendance;
use Modules\Essentials\Entities\EssentialsAttendanceSummary;
use Modules\Essentials\Entities\EssentialsEmployeeSettings;
use App\User;

class AttendanceProcessingService
{
    /**
     * Process attendance data from uploaded files (similar to your Excel converter)
     */
    public function processAttendanceFromFiles($userFileContent, $attendanceFileContent, $businessId)
    {
        $users = $this->parseUserData($userFileContent);
        $attendanceRecords = $this->parseAttendanceData($attendanceFileContent);
        
        $processedData = [];
        
        foreach ($attendanceRecords as $record) {
            $userId = $this->findUserIdByCode($record['user_code'], $users, $businessId);
            if (!$userId) continue;
            
            $date = Carbon::parse($record['datetime'])->toDateString();
            $time = Carbon::parse($record['datetime']);
            
            // Get or create attendance record for the day
            if (!isset($processedData[$userId][$date])) {
                $processedData[$userId][$date] = [
                    'user_id' => $userId,
                    'business_id' => $businessId,
                    'date' => $date,
                    'clock_in_time' => null,
                    'clock_out_time' => null,
                    'records' => []
                ];
            }
            
            $processedData[$userId][$date]['records'][] = $time;
        }
        
        // Process each day's records
        foreach ($processedData as $userId => $userDates) {
            foreach ($userDates as $date => $dayData) {
                $this->processDayAttendance($dayData, $businessId);
            }
        }
        
        return count($processedData);
    }
    
    /**
     * Parse user data from .dat file (similar to your HTML converter)
     */
    private function parseUserData($content)
    {
        $users = [];
        // Clean control characters and parse
        $cleaned = preg_replace('/[\x00-\x1F]/', ' ', $content);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));
        $tokens = explode(' ', $cleaned);
        
        $names = [];
        $ids = [];
        
        foreach ($tokens as $token) {
            if (preg_match('/^[A-Za-z]+$/', $token)) {
                $names[] = $token;
            } elseif (preg_match('/^\d+$/', $token)) {
                $ids[] = $token;
            }
        }
        
        $count = min(count($names), count($ids));
        for ($i = 0; $i < $count; $i++) {
            $users[$ids[$i]] = $names[$i];
        }
        
        return $users;
    }
    
    /**
     * Parse attendance data from .dat file
     */
    private function parseAttendanceData($content)
    {
        $records = [];
        $lines = explode("\n", trim($content));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 2) {
                $records[] = [
                    'user_code' => trim($parts[0]),
                    'datetime' => trim($parts[1])
                ];
            }
        }
        
        return $records;
    }
    
    /**
     * Find user ID by employee code
     */
    private function findUserIdByCode($code, $users, $businessId)
    {
        // First try to find by employee code
        $employeeSettings = EssentialsEmployeeSettings::where('employee_code', $code)
            ->where('business_id', $businessId)
            ->first();
            
        if ($employeeSettings) {
            return $employeeSettings->user_id;
        }
        
        // If not found, try to match with user data
        if (isset($users[$code])) {
            $userName = $users[$code];
            $user = User::where('username', 'LIKE', "%{$userName}%")
                ->orWhere('first_name', 'LIKE', "%{$userName}%")
                ->first();
                
            if ($user) {
                // Create employee settings if not exists
                EssentialsEmployeeSettings::updateOrCreate([
                    'user_id' => $user->id,
                    'business_id' => $businessId
                ], [
                    'employee_code' => $code,
                    'status' => 'active'
                ]);
                
                return $user->id;
            }
        }
        
        return null;
    }
    
    /**
     * Process attendance for a single day
     */
    private function processDayAttendance($dayData, $businessId)
    {
        $userId = $dayData['user_id'];
        $date = $dayData['date'];
        $records = $dayData['records'];
        
        if (empty($records)) return;
        
        // Sort times
        sort($records);
        $clockIn = $records[0];
        $clockOut = count($records) > 1 ? end($records) : null;
        
        // Get employee settings for late calculation
        $employeeSettings = EssentialsEmployeeSettings::where('user_id', $userId)
            ->where('business_id', $businessId)
            ->first();
            
        $expectedStartTime = $employeeSettings ? $employeeSettings->default_start_time : '08:00:00';
        $graceMinutes = $employeeSettings ? $employeeSettings->grace_minutes : 15;
        
        // Calculate status and late minutes
        $status = 'present';
        $lateMinutes = 0;
        $earlyLeaveMinutes = 0;
        
        $expectedStart = Carbon::parse($date . ' ' . $expectedStartTime);
        $graceTime = $expectedStart->copy()->addMinutes($graceMinutes);
        
        if ($clockIn->gt($graceTime)) {
            $status = 'late';
            $lateMinutes = $clockIn->diffInMinutes($expectedStart);
        }
        
        // Calculate work hours
        $workHours = 0;
        if ($clockOut) {
            $workHours = round($clockIn->diffInHours($clockOut, false), 2);
        }
        
        // Save to attendance table (detailed records)
        EssentialsAttendance::updateOrCreate([
            'user_id' => $userId,
            'business_id' => $businessId,
            'clock_in_time' => $clockIn
        ], [
            'clock_out_time' => $clockOut,
            'attendance_status' => $status,
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'work_hours' => $workHours,
            'is_weekend' => Carbon::parse($date)->isWeekend(),
            'is_holiday' => $this->isHoliday($date, $businessId)
        ]);
        
        // Save to summary table (for performance)
        EssentialsAttendanceSummary::updateOrCreate([
            'user_id' => $userId,
            'business_id' => $businessId,
            'date' => $date
        ], [
            'status' => $status,
            'clock_in_time' => $clockIn,
            'clock_out_time' => $clockOut,
            'total_hours' => $workHours,
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'is_weekend' => Carbon::parse($date)->isWeekend(),
            'is_holiday' => $this->isHoliday($date, $businessId)
        ]);
    }
    
    /**
     * Check if date is a holiday
     */
    private function isHoliday($date, $businessId)
    {
        // You can implement holiday checking logic here
        // For now, return false
        return false;
    }
    
    /**
     * Generate attendance summary for payroll
     */
    public function generateAttendanceSummaryForPayroll($userId, $businessId, $startDate, $endDate)
    {
        $attendanceData = EssentialsAttendanceSummary::where('user_id', $userId)
            ->where('business_id', $businessId)
            ->dateRange($startDate, $endDate)
            ->get();
            
        $summary = [
            'total_days' => $attendanceData->count(),
            'present_days' => $attendanceData->present()->count(),
            'absent_days' => $attendanceData->absent()->count(),
            'late_days' => $attendanceData->late()->count(),
            'total_hours' => $attendanceData->sum('total_hours'),
            'total_late_minutes' => $attendanceData->sum('late_minutes'),
            'attendance_percentage' => 0,
            'punctuality_percentage' => 0
        ];
        
        $workingDays = $attendanceData->workingDays()->count();
        if ($workingDays > 0) {
            $summary['attendance_percentage'] = round(($summary['present_days'] / $workingDays) * 100, 2);
        }
        
        if ($summary['present_days'] > 0) {
            $punctualDays = $attendanceData->where('late_minutes', 0)->count();
            $summary['punctuality_percentage'] = round(($punctualDays / $summary['present_days']) * 100, 2);
        }
        
        return $summary;
    }
    
    /**
     * Generate CSV export of attendance data
     */
    public function exportAttendanceToCSV($businessId, $startDate, $endDate)
    {
        $attendanceData = EssentialsAttendanceSummary::forBusiness($businessId)
            ->dateRange($startDate, $endDate)
            ->with(['employee'])
            ->get();
            
        $csvData = [];
        $csvData[] = [
            'Employee Name',
            'Employee Code', 
            'Date',
            'Clock In',
            'Clock Out',
            'Total Hours',
            'Status',
            'Late Minutes',
            'Early Leave Minutes'
        ];
        
        foreach ($attendanceData as $record) {
            $csvData[] = [
                $record->employee->first_name . ' ' . $record->employee->last_name,
                $record->employee->employeeSettings->employee_code ?? '',
                $record->date->format('Y-m-d'),
                $record->clock_in_time ? $record->clock_in_time->format('H:i:s') : '',
                $record->clock_out_time ? $record->clock_out_time->format('H:i:s') : '',
                $record->total_hours,
                $record->status_text,
                $record->late_minutes,
                $record->early_leave_minutes
            ];
        }
        
        return $csvData;
    }
}
