<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsAttendance;
use Modules\Essentials\Entities\EssentialsAttendanceSummary;
use Modules\Essentials\Entities\EssentialsEmployeeSettings;
use Modules\Essentials\Utils\EssentialsUtil;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class EnhancedAttendanceController extends Controller
{
    protected $moduleUtil;
    protected $essentialsUtil;

    public function __construct(ModuleUtil $moduleUtil, EssentialsUtil $essentialsUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->essentialsUtil = $essentialsUtil;
    }

    /**
     * Display the enhanced attendance dashboard.
     */
    public function dashboard()
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        // Get dashboard statistics
        $stats = $this->getDashboardStats($business_id);
        
        return view('essentials::attendance.enhanced_dashboard', compact('stats'));
    }

    /**
     * Get dashboard statistics.
     */
    private function getDashboardStats($businessId)
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $twentyOneDaysAgo = $today->copy()->subDays(21);

        // Total employees
        $totalEmployees = EssentialsEmployeeSettings::forBusiness($businessId)->active()->count();

        // Today's punctual employees (<=8:00 AM)
        $punctualToday = EssentialsAttendanceSummary::forBusiness($businessId)
            ->where('date', $today)
            ->whereTime('clock_in_time', '<=', '08:00:00')
            ->whereIn('status', ['present', 'late'])
            ->count();

        // Today's late employees (>8:15 AM)
        $lateToday = EssentialsAttendanceSummary::forBusiness($businessId)
            ->where('date', $today)
            ->where('late_minutes', '>', 15)
            ->count();

        // Inactive employees (21+ days absent)
        $inactiveEmployees = $this->getInactiveEmployees($businessId, $twentyOneDaysAgo);

        // This month's attendance statistics
        $monthlyStats = $this->getMonthlyStats($businessId, $startOfMonth, $endOfMonth);

        return [
            'total_employees' => $totalEmployees,
            'punctual_today' => $punctualToday,
            'late_today' => $lateToday,
            'inactive_employees' => count($inactiveEmployees),
            'monthly_stats' => $monthlyStats,
            'inactive_employee_list' => $inactiveEmployees
        ];
    }

    /**
     * Get employees with 21+ days absence.
     */
    private function getInactiveEmployees($businessId, $startDate)
    {
        $employees = EssentialsEmployeeSettings::forBusiness($businessId)
            ->active()
            ->with('employee')
            ->get();

        $inactiveEmployees = [];

        foreach ($employees as $employeeSetting) {
            $absentDays = EssentialsAttendanceSummary::where('user_id', $employeeSetting->user_id)
                ->where('business_id', $businessId)
                ->where('date', '>=', $startDate)
                ->where('status', 'absent')
                ->workingDays()
                ->count();

            if ($absentDays >= 21) {
                $inactiveEmployees[] = [
                    'employee' => $employeeSetting->employee,
                    'absent_days' => $absentDays
                ];
            }
        }

        return $inactiveEmployees;
    }

    /**
     * Get monthly statistics.
     */
    private function getMonthlyStats($businessId, $startDate, $endDate)
    {
        return [
            'total_working_days' => $this->getWorkingDaysBetween($startDate, $endDate),
            'average_attendance' => $this->getAverageAttendance($businessId, $startDate, $endDate),
            'total_late_arrivals' => $this->getTotalLateArrivals($businessId, $startDate, $endDate),
            'total_early_leaves' => $this->getTotalEarlyLeaves($businessId, $startDate, $endDate)
        ];
    }

    /**
     * Display employee management interface.
     */
    public function employeeManagement()
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (!(auth()->user()->can('superadmin') || auth()->user()->can('essentials.crud_all_attendance'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $employees = EssentialsEmployeeSettings::forBusiness($business_id)
                ->with(['employee'])
                ->select([
                    'essentials_employee_settings.*',
                    DB::raw("CONCAT(COALESCE(users.surname, ''), ' ', COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as full_name"),
                    'users.username'
                ])
                ->join('users', 'users.id', '=', 'essentials_employee_settings.user_id');

            return DataTables::of($employees)
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group">
                        <button class="btn btn-xs btn-primary edit-employee" data-id="' . $row->id . '">
                            <i class="fa fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-xs btn-danger delete-employee" data-id="' . $row->id . '">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>';
                })
                ->editColumn('status', function ($row) {
                    $badgeClass = $row->status === 'active' ? 'success' : 'danger';
                    return '<span class="label label-' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
                })
                ->editColumn('base_salary', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->base_salary . '</span>';
                })
                ->rawColumns(['action', 'status', 'base_salary'])
                ->make(true);
        }

        return view('essentials::attendance.employee_management');
    }

    /**
     * Show user attendance summary with 21-day calendar.
     */
    public function userAttendanceSummary(Request $request, $userId)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $canViewAll = auth()->user()->can('essentials.crud_all_attendance');
        $canViewOwn = auth()->user()->can('essentials.view_own_attendance');
        
        if (!$canViewAll && !$canViewOwn) {
            abort(403, 'Unauthorized action.');
        }

        if (!$canViewAll && auth()->user()->id != $userId) {
            abort(403, 'Unauthorized action.');
        }

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(20); // 21 days including today

        $employee = User::findOrFail($userId);
        $employeeSettings = EssentialsEmployeeSettings::where('user_id', $userId)
            ->where('business_id', $business_id)
            ->first();

        // Get attendance data for 21 days
        $attendanceData = EssentialsAttendanceSummary::where('user_id', $userId)
            ->where('business_id', $business_id)
            ->dateRange($startDate, $endDate)
            ->get()
            ->keyBy('date');

        // Generate calendar
        $calendar = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->toDateString();
            $attendance = $attendanceData->get($dateStr);
            
            $calendar[] = [
                'date' => $currentDate->copy(),
                'attendance' => $attendance,
                'is_weekend' => $currentDate->isWeekend(),
                'is_today' => $currentDate->isToday()
            ];
            
            $currentDate->addDay();
        }

        // Get statistics
        $stats = [
            'total_present' => $attendanceData->where('status', '!=', 'absent')->count(),
            'total_absent' => $attendanceData->where('status', 'absent')->count(),
            'total_late' => $attendanceData->where('late_minutes', '>', 0)->count(),
            'average_hours' => round($attendanceData->avg('total_hours'), 2),
            'attendance_percentage' => EssentialsAttendanceSummary::getAttendancePercentage($userId, $business_id, $startDate, $endDate),
            'punctuality_percentage' => EssentialsAttendanceSummary::getPunctualityPercentage($userId, $business_id, $startDate, $endDate)
        ];

        return view('essentials::attendance.user_summary', compact(
            'employee', 
            'employeeSettings', 
            'calendar', 
            'stats',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Store or update employee settings.
     */
    public function storeEmployeeSettings(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        if (!(auth()->user()->can('superadmin') || auth()->user()->can('essentials.crud_all_attendance'))) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'employee_code' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,suspended',
            'default_start_time' => 'required|date_format:H:i',
            'default_end_time' => 'required|date_format:H:i|after:default_start_time',
            'grace_minutes' => 'required|integer|min:0|max:120',
            'base_salary' => 'required|numeric|min:0',
            'overtime_eligible' => 'boolean',
            'overtime_rate' => 'required|numeric|min:1',
            'working_days' => 'required|array|min:1'
        ]);

        try {
            EssentialsEmployeeSettings::updateOrCreate(
                [
                    'user_id' => $request->user_id,
                    'business_id' => $business_id
                ],
                [
                    'employee_code' => $request->employee_code,
                    'status' => $request->status,
                    'default_start_time' => $request->default_start_time,
                    'default_end_time' => $request->default_end_time,
                    'grace_minutes' => $request->grace_minutes,
                    'base_salary' => $request->base_salary,
                    'overtime_eligible' => $request->overtime_eligible ?? false,
                    'overtime_rate' => $request->overtime_rate,
                    'working_days' => $request->working_days
                ]
            );

            return response()->json([
                'success' => true,
                'message' => __('lang_v1.updated_success')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.something_went_wrong')
            ]);
        }
    }

    /**
     * Get all users for summary interface.
     */
    public function getAllUsersSummary()
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (request()->ajax()) {
            $users = User::join('essentials_employee_settings', 'users.id', '=', 'essentials_employee_settings.user_id')
                ->where('essentials_employee_settings.business_id', $business_id)
                ->select([
                    'users.id',
                    'users.username',
                    DB::raw("CONCAT(COALESCE(users.surname, ''), ' ', COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as full_name"),
                    'essentials_employee_settings.status',
                    'essentials_employee_settings.employee_code'
                ]);

            return DataTables::of($users)
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-primary btn-sm view-attendance" data-user-id="' . $row->id . '">
                        <i class="fa fa-calendar"></i> View 21-Day Report
                    </button>';
                })
                ->editColumn('status', function ($row) {
                    $badgeClass = $row->status === 'active' ? 'success' : 'danger';
                    return '<span class="label label-' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('essentials::attendance.all_users_summary');
    }

    // Helper methods
    private function getWorkingDaysBetween($startDate, $endDate)
    {
        $count = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            if (!$currentDate->isWeekend()) {
                $count++;
            }
            $currentDate->addDay();
        }
        
        return $count;
    }

    private function getAverageAttendance($businessId, $startDate, $endDate)
    {
        $totalEmployees = EssentialsEmployeeSettings::forBusiness($businessId)->active()->count();
        $totalWorkingDays = $this->getWorkingDaysBetween($startDate, $endDate);
        $totalPossibleAttendance = $totalEmployees * $totalWorkingDays;
        
        if ($totalPossibleAttendance == 0) return 0;
        
        $actualAttendance = EssentialsAttendanceSummary::forBusiness($businessId)
            ->dateRange($startDate, $endDate)
            ->present()
            ->workingDays()
            ->count();
        
        return round(($actualAttendance / $totalPossibleAttendance) * 100, 2);
    }

    private function getTotalLateArrivals($businessId, $startDate, $endDate)
    {
        return EssentialsAttendanceSummary::forBusiness($businessId)
            ->dateRange($startDate, $endDate)
            ->late()
            ->count();
    }

    private function getTotalEarlyLeaves($businessId, $startDate, $endDate)
    {
        return EssentialsAttendanceSummary::forBusiness($businessId)
            ->dateRange($startDate, $endDate)
            ->where('early_leave_minutes', '>', 0)
            ->count();
    }

    /**
     * Get employee settings for editing.
     */
    public function getEmployeeSettings($id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (!(auth()->user()->can('superadmin') || auth()->user()->can('essentials.crud_all_attendance'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $employeeSettings = EssentialsEmployeeSettings::with('employee')
                ->where('id', $id)
                ->where('business_id', $business_id)
                ->first();

            if (!$employeeSettings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee settings not found'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $employeeSettings->id,
                    'user_id' => $employeeSettings->user_id,
                    'employee_code' => $employeeSettings->employee_code,
                    'status' => $employeeSettings->status,
                    'default_start_time' => $employeeSettings->default_start_time,
                    'default_end_time' => $employeeSettings->default_end_time,
                    'grace_minutes' => $employeeSettings->grace_minutes,
                    'base_salary' => $employeeSettings->base_salary,
                    'overtime_eligible' => $employeeSettings->overtime_eligible,
                    'overtime_rate' => $employeeSettings->overtime_rate,
                    'working_days' => $employeeSettings->working_days,
                    'employee_name' => $employeeSettings->employee->first_name . ' ' . $employeeSettings->employee->last_name
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.something_went_wrong')
            ]);
        }
    }

    /**
     * Delete employee settings.
     */
    public function deleteEmployeeSettings($id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (!(auth()->user()->can('superadmin') || auth()->user()->can('essentials.crud_all_attendance'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $deleted = EssentialsEmployeeSettings::where('id', $id)
                ->where('business_id', $business_id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => __('lang_v1.deleted_success')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee settings not found'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.something_went_wrong')
            ]);
        }
    }

    /**
     * Get users for dropdown.
     */
    public function getUsersForDropdown()
    {
        $business_id = request()->session()->get('user.business_id');
        
        $users = User::forDropdown($business_id, false, false);
        
        $dropdownData = [];
        foreach ($users as $id => $name) {
            $dropdownData[] = [
                'id' => $id,
                'text' => $name
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $dropdownData
        ]);
    }
}
