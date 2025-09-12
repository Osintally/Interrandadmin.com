<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;

class EssentialsAttendanceSummary extends Model
{
    protected $table = 'essentials_attendance_summary';

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'total_hours' => 'decimal:2',
        'is_holiday' => 'boolean',
        'is_weekend' => 'boolean',
    ];

    /**
     * Get the employee for this attendance record.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if the employee was present.
     */
    public function isPresent()
    {
        return $this->status === 'present';
    }

    /**
     * Check if the employee was late.
     */
    public function isLate()
    {
        return $this->status === 'late' || $this->late_minutes > 0;
    }

    /**
     * Check if the employee was absent.
     */
    public function isAbsent()
    {
        return $this->status === 'absent';
    }

    /**
     * Check if the employee left early.
     */
    public function hasEarlyLeave()
    {
        return $this->early_leave_minutes > 0;
    }

    /**
     * Get status with color coding.
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'present':
                return 'success';
            case 'late':
                return 'warning';
            case 'absent':
                return 'danger';
            case 'partial_day':
                return 'info';
            case 'holiday':
                return 'secondary';
            default:
                return 'secondary';
        }
    }

    /**
     * Get formatted status text.
     */
    public function getStatusTextAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    /**
     * Get the attendance percentage for a user over a period.
     */
    public static function getAttendancePercentage($userId, $businessId, $startDate = null, $endDate = null)
    {
        $query = static::where('user_id', $userId)
            ->where('business_id', $businessId)
            ->where('is_weekend', false)
            ->where('is_holiday', false);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $totalDays = $query->count();
        $presentDays = $query->whereIn('status', ['present', 'late', 'partial_day'])->count();

        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;
    }

    /**
     * Get punctuality percentage (not late) for a user.
     */
    public static function getPunctualityPercentage($userId, $businessId, $startDate = null, $endDate = null)
    {
        $query = static::where('user_id', $userId)
            ->where('business_id', $businessId)
            ->whereIn('status', ['present', 'late'])
            ->where('is_weekend', false)
            ->where('is_holiday', false);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $totalPresentDays = $query->count();
        $punctualDays = $query->where('late_minutes', 0)->count();

        return $totalPresentDays > 0 ? round(($punctualDays / $totalPresentDays) * 100, 2) : 0;
    }

    /**
     * Scope for present days.
     */
    public function scopePresent($query)
    {
        return $query->whereIn('status', ['present', 'late', 'partial_day']);
    }

    /**
     * Scope for absent days.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope for late arrivals.
     */
    public function scopeLate($query)
    {
        return $query->where('late_minutes', '>', 0);
    }

    /**
     * Scope for working days (exclude weekends and holidays).
     */
    public function scopeWorkingDays($query)
    {
        return $query->where('is_weekend', false)->where('is_holiday', false);
    }

    /**
     * Scope for a specific business.
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope for a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
