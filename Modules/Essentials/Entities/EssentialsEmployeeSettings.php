<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;

class EssentialsEmployeeSettings extends Model
{
    protected $table = 'essentials_employee_settings';

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'working_days' => 'array',
        'overtime_eligible' => 'boolean',
        'base_salary' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
    ];

    /**
     * Get the employee/user for this setting.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if the employee is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if the employee is inactive.
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Get working days as readable format.
     */
    public function getWorkingDaysTextAttribute()
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $workingDays = $this->working_days ?: [];
        
        return collect($workingDays)->map(function ($day) use ($days) {
            return $days[$day] ?? '';
        })->filter()->implode(', ');
    }

    /**
     * Check if a given day is a working day.
     */
    public function isWorkingDay($dayOfWeek)
    {
        $workingDays = $this->working_days ?: [1, 2, 3, 4, 5]; // Default Mon-Fri
        return in_array($dayOfWeek, $workingDays);
    }

    /**
     * Get the expected work hours per day.
     */
    public function getExpectedWorkHours()
    {
        $startTime = \Carbon\Carbon::parse($this->default_start_time);
        $endTime = \Carbon\Carbon::parse($this->default_end_time);
        
        return $endTime->diffInHours($startTime);
    }

    /**
     * Scope to get active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get inactive employees.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for a specific business.
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }
}
