<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PayrollRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'salary_id',
        'payroll_date',
        'period_start',
        'period_end',
        'total_working_days',
        'days_present',
        'days_absent',
        'days_late',
        'days_low_late',
        'days_very_late',
        'days_early_leave',
        'total_hours_worked',
        'overtime_hours',
        'basic_salary',
        'allowances_total',
        'overtime_amount',
        'gross_amount',
        'attendance_deductions',
        'tax_deductions',
        'other_deductions',
        'total_deductions',
        'net_amount',
        'status',
        'calculated_by',
        'calculated_at',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'payment_method',
        'payment_reference',
        'notes',
        // Approval workflow fields
        'approval_status',
        'submitted_by',
        'reviewed_by',
        'rejected_by',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'rejected_at'
    ];

    protected $casts = [
        'payroll_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'total_hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'allowances_total' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'attendance_deductions' => 'decimal:2',
        'tax_deductions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        // Approval workflow timestamps
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    /**
     * Get the business that owns the payroll record.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    /**
     * Get the user that owns the payroll record.
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * Get the user who generated this payroll record.
     */
    public function generator()
    {
        return $this->belongsTo(\App\User::class, 'generated_by');
    }

    /**
     * Get the user who approved this payroll record.
     */
    public function approver()
    {
        return $this->belongsTo(\App\User::class, 'approved_by');
    }

    /**
     * Get the user who submitted this payroll record for review.
     */
    public function submitter()
    {
        return $this->belongsTo(\App\User::class, 'submitted_by');
    }

    /**
     * Get the user who reviewed this payroll record.
     */
    public function reviewer()
    {
        return $this->belongsTo(\App\User::class, 'reviewed_by');
    }

    /**
     * Get the user who rejected this payroll record.
     */
    public function rejector()
    {
        return $this->belongsTo(\App\User::class, 'rejected_by');
    }

    /**
     * Get the approval history for this payroll record.
     */
    public function approvalHistory()
    {
        return $this->hasMany(PayrollApprovalHistory::class);
    }

    /**
     * Get the salary setting used for this payroll.
     */
    public function salarySetting()
    {
        return $this->belongsTo(SalarySetting::class, 'user_id', 'user_id')
            ->where('business_id', $this->business_id)
            ->current($this->period_start);
    }

    /**
     * Get the attendance logs for this payroll period.
     */
    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'user_id', 'user_id')
            ->where('business_id', $this->business_id)
            ->whereBetween('attendance_date', [$this->period_start, $this->period_end]);
    }

    /**
     * Scope a query to only include records for a specific business.
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope a query to only include draft records.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include approved records.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include paid records.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query for a specific payroll period.
     */
    public function scopeForPeriod($query, $period)
    {
        return $query->where('payroll_period', $period);
    }

    /**
     * Generate payroll record for a user.
     */
    public static function generatePayroll($businessId, $userId, $periodStart, $periodEnd, $attendanceSettings = null)
    {
        $period = Carbon::parse($periodStart)->format('Y-m');
        
        // Check if payroll already exists
        $existing = self::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->where('payroll_period', $period)
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'Payroll already exists for this period'];
        }

        // Get salary setting
        $salarySetting = SalarySetting::getCurrentSalary($businessId, $userId, $periodStart);
        if (!$salarySetting) {
            return ['success' => false, 'message' => 'No salary setting found for user'];
        }

        // Get attendance summary
        $attendanceSummary = AttendanceLog::getUserSummary($businessId, $userId, $periodStart, $periodEnd);
        
        // Get attendance settings
        if (!$attendanceSettings) {
            $attendanceSettings = AttendanceSetting::where('business_id', $businessId)->first();
        }

        // Calculate working days
        $workingDays = self::calculateWorkingDays($periodStart, $periodEnd, $attendanceSettings->working_days ?? [1,2,3,4,5]);

        // Calculate deductions
        $absentDeduction = ($attendanceSummary->absent_days ?? 0) * ($salarySetting->daily_rate * ($attendanceSettings->absent_deduction_percent ?? 5) / 100);
        $lateDeduction = (($attendanceSummary->late_days ?? 0) * ($salarySetting->daily_rate * ($attendanceSettings->late_deduction_percent ?? 1) / 100)) +
                        (($attendanceSummary->low_late_days ?? 0) * ($salarySetting->daily_rate * ($attendanceSettings->low_late_deduction_percent ?? 0.5) / 100));

        $totalDeductions = $absentDeduction + $lateDeduction + $salarySetting->deductions;
        $netPay = $salarySetting->gross_salary + $salarySetting->allowances - $totalDeductions;

        // Create payroll record
        $payrollData = [
            'business_id' => $businessId,
            'user_id' => $userId,
            'payroll_period' => $period,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'gross_pay' => $salarySetting->gross_salary,
            'basic_pay' => $salarySetting->basic_salary,
            'allowances' => $salarySetting->allowances,
            'attendance_deduction' => $absentDeduction,
            'late_deduction' => $lateDeduction,
            'other_deductions' => $salarySetting->deductions,
            'net_pay' => $netPay,
            'total_working_days' => $workingDays,
            'total_present_days' => $attendanceSummary->present_days ?? 0,
            'total_absent_days' => $attendanceSummary->absent_days ?? 0,
            'total_late_days' => $attendanceSummary->late_days ?? 0,
            'total_low_late_days' => $attendanceSummary->low_late_days ?? 0,
            'total_hours_worked' => $attendanceSummary->total_hours_worked ?? 0,
            'generated_by' => auth()->id(),
            'status' => 'draft'
        ];

        $payroll = self::create($payrollData);

        return ['success' => true, 'data' => $payroll];
    }

    /**
     * Generate payroll for multiple users.
     */
    public static function generateBulkPayroll($businessId, $userIds, $periodStart, $periodEnd)
    {
        $results = [];
        $attendanceSettings = AttendanceSetting::where('business_id', $businessId)->first();

        foreach ($userIds as $userId) {
            $result = self::generatePayroll($businessId, $userId, $periodStart, $periodEnd, $attendanceSettings);
            $results[$userId] = $result;
        }

        return $results;
    }

    /**
     * Calculate working days in a period.
     */
    private static function calculateWorkingDays($startDate, $endDate, $workingDays)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $count = 0;

        while ($start->lte($end)) {
            if (in_array($start->dayOfWeek, $workingDays)) {
                $count++;
            }
            $start->addDay();
        }

        return $count;
    }

    /**
     * Approve payroll record.
     */
    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return $this;
    }

    /**
     * Mark payroll as paid.
     */
    public function markAsPaid($remarks = null)
    {
        $this->update([
            'status' => 'paid',
            'remarks' => $remarks
        ]);

        return $this;
    }

    /**
     * Get formatted net pay with currency.
     */
    public function getFormattedNetPayAttribute()
    {
        $salarySetting = $this->salarySetting;
        $symbol = $salarySetting ? $salarySetting->getCurrencySymbol() : '₦';
        return $symbol . number_format($this->net_pay, 2);
    }

    /**
     * Get attendance rate percentage.
     */
    public function getAttendanceRateAttribute()
    {
        if ($this->total_working_days == 0) {
            return 0;
        }

        return round(($this->days_present / $this->total_working_days) * 100, 2);
    }

    /**
     * Get punctuality rate percentage.
     */
    public function getPunctualityRateAttribute()
    {
        if ($this->days_present == 0) {
            return 0;
        }

        $onTimeDays = $this->days_present - $this->days_late - $this->days_low_late;
        return round(($onTimeDays / $this->days_present) * 100, 2);
    }

    /**
     * Get payroll summary for business.
     */
    public static function getBusinessSummary($businessId, $period = null)
    {
        $query = self::where('business_id', $businessId);
        
        if ($period) {
            $query->where('payroll_period', $period);
        }

        return $query->selectRaw('
            COUNT(*) as total_employees,
            SUM(gross_amount) as total_gross_pay,
            SUM(net_amount) as total_net_pay,
            SUM(attendance_deductions + tax_deductions + other_deductions) as total_deductions,
            AVG((days_present / total_working_days) * 100) as avg_attendance_rate
        ')->first();
    }

    /**
     * Approval Workflow Helper Methods
     */

    /**
     * Submit payroll for review.
     */
    public function submitForReview($userId, $comments = null)
    {
        $this->update([
            'approval_status' => 'submitted_for_review',
            'submitted_by' => $userId,
            'submitted_at' => now()
        ]);

        // Log the action
        $this->logApprovalAction('submitted', $userId, $comments);

        return $this;
    }

    /**
     * Review payroll record.
     */
    public function review($userId, $comments = null)
    {
        $this->update([
            'approval_status' => 'pending_approval',
            'reviewed_by' => $userId,
            'reviewed_at' => now()
        ]);

        // Log the action
        $this->logApprovalAction('reviewed', $userId, $comments);

        return $this;
    }

    /**
     * Approve payroll record.
     */
    public function approve($userId, $comments = null)
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now()
        ]);

        // Log the action
        $this->logApprovalAction('approved', $userId, $comments);

        return $this;
    }

    /**
     * Reject payroll record.
     */
    public function reject($userId, $reason, $comments = null)
    {
        $this->update([
            'approval_status' => 'rejected',
            'rejected_by' => $userId,
            'rejection_reason' => $reason,
            'rejected_at' => now()
        ]);

        // Log the action
        $this->logApprovalAction('rejected', $userId, $comments);

        return $this;
    }

    /**
     * Log approval action in history.
     */
    public function logApprovalAction($action, $userId, $comments = null)
    {
        PayrollApprovalHistory::create([
            'payroll_record_id' => $this->id,
            'user_id' => $userId,
            'action' => $action,
            'comments' => $comments
        ]);
    }

    /**
     * Check if payroll can be edited.
     */
    public function canBeEdited()
    {
        return in_array($this->approval_status, ['draft', 'rejected']);
    }

    /**
     * Check if payroll can be deleted.
     */
    public function canBeDeleted()
    {
        return in_array($this->approval_status, ['draft', 'rejected']);
    }

    /**
     * Check if payroll can be submitted for review.
     */
    public function canBeSubmitted()
    {
        return $this->approval_status === 'draft';
    }

    /**
     * Check if payroll can be reviewed.
     */
    public function canBeReviewed()
    {
        return $this->approval_status === 'submitted_for_review';
    }

    /**
     * Check if payroll can be approved.
     */
    public function canBeApproved()
    {
        return $this->approval_status === 'pending_approval';
    }

    /**
     * Check if payroll can be rejected.
     */
    public function canBeRejected()
    {
        return in_array($this->approval_status, ['submitted_for_review', 'pending_approval']);
    }

    /**
     * Check if payroll can be exported.
     */
    public function canBeExported()
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Get approval status badge class.
     */
    public function getApprovalStatusBadgeClassAttribute()
    {
        return match($this->approval_status) {
            'draft' => 'badge-secondary',
            'submitted_for_review' => 'badge-warning',
            'pending_approval' => 'badge-info',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Get approval status display text.
     */
    public function getApprovalStatusDisplayAttribute()
    {
        return match($this->approval_status) {
            'draft' => 'Draft',
            'submitted_for_review' => 'Under Review',
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown'
        };
    }
}