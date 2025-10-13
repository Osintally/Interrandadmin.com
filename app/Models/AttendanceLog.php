<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $table = 'attendance_logs';

    protected $fillable = [
        'business_id', 'user_id', 'attendance_date', 'clock_in', 'clock_out',
        'total_hours', 'status', 'late_status', 'late_category', 'late_minutes',
        'early_leave', 'early_leave_minutes', 'overtime', 'overtime_minutes',
        'clock_in_ip', 'clock_out_ip', 'clock_in_device', 'clock_out_device',
        'clock_in_lat', 'clock_in_lng', 'clock_out_lat', 'clock_out_lng',
        'notes', 'requires_approval', 'approved', 'approved_by', 'approved_at'
    ];

    protected $dates = [
        'attendance_date', 'clock_in', 'clock_out', 'approved_at', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'early_leave' => 'boolean',
        'overtime' => 'boolean',
        'requires_approval' => 'boolean',
        'approved' => 'boolean',
        'total_hours' => 'decimal:2',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'clock_in_lat' => 'decimal:8',
        'clock_in_lng' => 'decimal:8',
        'clock_out_lat' => 'decimal:8',
        'clock_out_lng' => 'decimal:8'
    ];

    /**
     * Get the business that owns the attendance log.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    /**
     * Get the user that owns the attendance log.
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * Get the user who approved this attendance record.
     */
    public function approver()
    {
        return $this->belongsTo(\App\User::class, 'approved_by');
    }

    /**
     * Scope a query to only include records for a specific business.
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope a query to only include records for a specific date range.
     */
    public function scopeInDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Legacy scope for backward compatibility.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $this->scopeInDateRange($query, $startDate, $endDate);
    }

    /**
     * Scope for date between - alias for compatibility.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $this->scopeInDateRange($query, $startDate, $endDate);
    }

    /**
     * Scope a query to only include present records.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope a query to only include absent records.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope a query to only include late records.
     */
    public function scopeLate($query)
    {
        return $query->whereIn('late_status', ['low_late', 'late']);
    }

    /**
     * Get attendance summary for a user within date range.
     */
    public static function getUserSummary($businessId, $userId, $startDate, $endDate)
    {
        return self::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->dateBetween($startDate, $endDate)
            ->selectRaw('
                COUNT(*) as total_days,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN late_status = "late" THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN late_status = "low_late" THEN 1 ELSE 0 END) as low_late_days,
                SUM(CASE WHEN early_leave = 1 THEN 1 ELSE 0 END) as early_leave_days,
                SUM(total_hours) as total_hours_worked
            ')
            ->first();
    }

    /**
     * Calculate late minutes based on clock_in time and settings.
     */
    public function calculateLateMinutes($startTime, $graceMinutes)
    {
        if (!$this->clock_in) {
            return 0;
        }

        $clockInTime = Carbon::parse($this->clock_in);
        $expectedStartTime = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $startTime);
        $graceEndTime = $expectedStartTime->copy()->addMinutes($graceMinutes);

        if ($clockInTime->gt($graceEndTime)) {
            return $clockInTime->diffInMinutes($graceEndTime);
        }

        return 0;
    }

    /**
     * Determine late status based on clock_in time.
     */
    public function determineLateStatus($startTime, $graceMinutes, $lateThresholdMinutes = 5)
    {
        if (!$this->clock_in || $this->status !== 'present') {
            return [
                'late_status' => 'no',
                'late_category' => null,
                'late_minutes' => 0
            ];
        }

        $startDateTime = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $startTime);
        $graceDateTime = $startDateTime->copy()->addMinutes($graceMinutes);
        $lateThreshold = $graceDateTime->copy()->addMinutes($lateThresholdMinutes);
        $veryLateThreshold = $graceDateTime->copy()->addMinutes(30);

        $lateMinutes = max(0, $this->clock_in->diffInMinutes($startDateTime, false));

        if ($this->clock_in <= $graceDateTime) {
            return [
                'late_status' => 'no',
                'late_category' => null,
                'late_minutes' => 0
            ];
        } elseif ($this->clock_in <= $lateThreshold) {
            return [
                'late_status' => 'low_late',
                'late_category' => 'Minor',
                'late_minutes' => $lateMinutes
            ];
        } elseif ($this->clock_in <= $veryLateThreshold) {
            return [
                'late_status' => 'late',
                'late_category' => 'Major',
                'late_minutes' => $lateMinutes
            ];
        } else {
            return [
                'late_status' => 'very_late',
                'late_category' => 'Severe',
                'late_minutes' => $lateMinutes
            ];
        }
    }

    /**
     * Calculate total hours worked.
     */
    public function calculateTotalHours()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        return round($this->clock_out->diffInMinutes($this->clock_in) / 60, 2);
    }

    /**
     * Check if employee left early.
     */
    public function checkEarlyLeave($endTime)
    {
        if (!$this->clock_out) {
            return false;
        }

        $clockOutTime = Carbon::parse($this->clock_out);
        $expectedEndTime = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $endTime);

        return $clockOutTime->lt($expectedEndTime);
    }

    /**
     * Get attendance statistics for dashboard.
     */
    public static function getBusinessStats($businessId, $startDate = null, $endDate = null)
    {
        $query = self::where('business_id', $businessId);
        
        if ($startDate && $endDate) {
            $query->dateBetween($startDate, $endDate);
        }

        return $query->selectRaw('
            COUNT(DISTINCT user_id) as total_employees,
            COUNT(*) as total_records,
            SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as total_present,
            SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as total_absent,
            SUM(CASE WHEN late_status IN ("late", "low_late") THEN 1 ELSE 0 END) as total_late,
            AVG(total_hours) as avg_hours_worked
        ')->first();
    }

    /**
     * Get most punctual employee.
     */
    public static function getMostPunctualEmployee($businessId, $startDate, $endDate)
    {
        return self::select('user_id')
            ->selectRaw('
                COUNT(*) as total_days,
                SUM(CASE WHEN late_status = "no" AND status = "present" THEN 1 ELSE 0 END) as on_time_days,
                (SUM(CASE WHEN late_status = "no" AND status = "present" THEN 1 ELSE 0 END) / COUNT(*) * 100) as punctuality_rate
            ')
            ->where('business_id', $businessId)
            ->dateBetween($startDate, $endDate)
            ->groupBy('user_id')
            ->having('total_days', '>=', 5) // Minimum 5 days to be considered
            ->orderBy('punctuality_rate', 'desc')
            ->with('user')
            ->first();
    }

    /**
     * Calculate early leave minutes.
     */
    public function calculateEarlyLeaveMinutes($endTime)
    {
        if (!$this->checkEarlyLeave($endTime)) {
            return 0;
        }

        $endDateTime = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $endTime);
        return $this->clock_out->diffInMinutes($endDateTime);
    }

    /**
     * Calculate overtime hours.
     */
    public function calculateOvertimeHours($standardHours = 8)
    {
        $totalHours = $this->calculateTotalHours();
        return max(0, $totalHours - $standardHours);
    }

    /**
     * Get attendance trends for analytics.
     */
    public static function getAttendanceTrends($businessId, $startDate, $endDate, $groupBy = 'day')
    {
        $dateFormat = $groupBy === 'month' ? '%Y-%m' : '%Y-%m-%d';
        
        return self::select([
            DB::raw("DATE_FORMAT(attendance_date, '{$dateFormat}') as period"),
            DB::raw('COUNT(DISTINCT user_id) as unique_employees'),
            DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as total_present'),
            DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as total_absent'),
            DB::raw('SUM(CASE WHEN late_status IN ("low_late", "late", "very_late") THEN 1 ELSE 0 END) as total_late'),
            DB::raw('AVG(CASE WHEN status = "present" THEN total_hours ELSE NULL END) as avg_hours')
        ])
        ->where('business_id', $businessId)
        ->dateBetween($startDate, $endDate)
        ->groupBy(DB::raw("DATE_FORMAT(attendance_date, '{$dateFormat}')"))
        ->orderBy('period')
        ->get();
    }

    /**
     * Get formatted attendance attributes for API response.
     */
    public function getFormattedAttributes()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'username' => $this->user->user_full_name ?? 'Unknown User',
            'date' => $this->attendance_date->format('Y-m-d'),
            'clock_in' => $this->clock_in ? $this->clock_in->format('Y-m-d H:i:s') : null,
            'clock_out' => $this->clock_out ? $this->clock_out->format('Y-m-d H:i:s') : null,
            'total_hours' => $this->total_hours,
            'status' => $this->status,
            'late_status' => $this->late_status,
            'late_category' => $this->late_category,
            'late_minutes' => $this->late_minutes,
            'early_leave' => $this->early_leave,
            'early_leave_minutes' => $this->early_leave_minutes,
            'overtime' => $this->overtime,
            'overtime_minutes' => $this->overtime_minutes,
            'location' => [
                'clock_in' => [
                    'lat' => $this->clock_in_lat,
                    'lng' => $this->clock_in_lng
                ],
                'clock_out' => [
                    'lat' => $this->clock_out_lat,
                    'lng' => $this->clock_out_lng
                ]
            ],
            'approved' => $this->approved,
            'requires_approval' => $this->requires_approval,
            'notes' => $this->notes
        ];
    }
}
