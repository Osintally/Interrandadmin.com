<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'start_time', 'end_time', 'grace_minutes', 'late_threshold_minutes',
        'very_late_threshold_minutes', 'early_leave_threshold_minutes',
        'working_days_per_month', 'working_hours_per_day',
        'absent_deduction_percent', 'late_deduction_percent', 'low_late_deduction_percent',
        'very_late_deduction_percent', 'early_leave_deduction_percent',
        'overtime_enabled', 'overtime_multiplier'
    ];

    protected $casts = [
        'working_days' => 'array',
        'grace_minutes' => 'integer',
        'late_threshold_minutes' => 'integer',
        'working_days_per_month' => 'integer',
        'working_hours_per_day' => 'integer',
        'absent_deduction_percent' => 'decimal:2',
        'late_deduction_percent' => 'decimal:2',
        'low_late_deduction_percent' => 'decimal:2',
        'very_late_deduction_percent' => 'decimal:2',
        'early_leave_deduction_percent' => 'decimal:2',
        'overtime_enabled' => 'boolean',
        'overtime_multiplier' => 'decimal:2',
        'overtime_threshold_minutes' => 'integer',
        'notify_late_arrivals' => 'boolean',
        'notify_absences' => 'boolean',
        'notify_early_leaves' => 'boolean',
        'auto_process_files' => 'boolean',
        'location_tracking_enabled' => 'boolean',
        'office_lat' => 'decimal:8',
        'office_lng' => 'decimal:8',
        'location_radius_meters' => 'integer',
        'auto_clock_out' => 'boolean',
        'auto_clock_out_time' => 'datetime',
        'weekend_overtime' => 'boolean',
        'holiday_overtime' => 'boolean'
    ];

    protected $dates = [
        'processing_time', 'created_at', 'updated_at'
    ];

    /**
     * Get the business that owns the attendance setting.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    /**
     * Get or create attendance settings for a business.
     */
    public static function getOrCreateForBusiness($businessId)
    {
        return self::firstOrCreate(['business_id' => $businessId], [
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'grace_minutes' => 15,
            'late_threshold_minutes' => 5,
            'very_late_threshold_minutes' => 30,
            'early_leave_threshold_minutes' => 30,
            'working_days_per_month' => 21,
            'working_hours_per_day' => 8,
            'absent_deduction_percent' => 5.00,
            'late_deduction_percent' => 1.00,
            'low_late_deduction_percent' => 0.50,
            'very_late_deduction_percent' => 2.00,
            'early_leave_deduction_percent' => 1.00,
            'overtime_enabled' => true,
            'overtime_multiplier' => 1.50
        ]);
    }

    /**
     * Get working days as day names.
     */
    public function getWorkingDayNamesAttribute()
    {
        $dayNames = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];

        return collect($this->working_days)->map(function ($day) use ($dayNames) {
            return $dayNames[$day] ?? 'Unknown';
        })->toArray();
    }

    /**
     * Check if a day is a working day.
     */
    public function isWorkingDay($dayOfWeek)
    {
        return in_array($dayOfWeek, $this->working_days);
    }

    /**
     * Get formatted start time.
     */
    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time->format('H:i');
    }

    /**
     * Get formatted end time.
     */
    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time->format('H:i');
    }

    /**
     * Update settings with validation.
     */
    public static function updateSettings($businessId, $data)
    {
        $settings = self::getOrCreateForBusiness($businessId);
        
        // Validate working days
        if (isset($data['working_days'])) {
            $data['working_days'] = array_values(array_filter($data['working_days'], function ($day) {
                return $day >= 0 && $day <= 6;
            }));
        }

        // Validate percentages
        $percentFields = ['absent_deduction_percent', 'late_deduction_percent', 'low_late_deduction_percent'];
        foreach ($percentFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = max(0, min(100, $data[$field]));
            }
        }

        $settings->update($data);
        return $settings;
    }

    /**
     * Format currency amount.
     */
    public function formatCurrency($amount)
    {
        return ($this->currency_symbol ?? '₦') . number_format($amount, 2);
    }

    /**
     * Calculate deduction based on type and count.
     */
    public function calculateDeduction($grossSalary, $deductionType, $count = 1)
    {
        $percentageMap = [
            'absent' => $this->absent_deduction_percent,
            'late' => $this->late_deduction_percent,
            'low_late' => $this->low_late_deduction_percent,
            'very_late' => $this->very_late_deduction_percent ?? 2.00,
            'early_leave' => $this->early_leave_deduction_percent ?? 1.00
        ];

        if (!isset($percentageMap[$deductionType])) {
            return 0;
        }

        $percentage = $percentageMap[$deductionType];
        return ($grossSalary * $percentage / 100) * $count;
    }

    /**
     * Calculate overtime pay.
     */
    public function calculateOvertimePay($hourlyRate, $overtimeHours)
    {
        if (!($this->overtime_enabled ?? true) || $overtimeHours <= 0) {
            return 0;
        }

        $multiplier = $this->overtime_multiplier ?? 1.50;
        return $hourlyRate * $overtimeHours * $multiplier;
    }

    /**
     * Check if location is within office radius.
     */
    public function isWithinOfficeLocation($lat, $lng)
    {
        if (!($this->location_tracking_enabled ?? false) || !$this->office_lat || !$this->office_lng) {
            return true; // Allow if location tracking is disabled
        }

        $distance = $this->calculateDistance(
            $this->office_lat,
            $this->office_lng,
            $lat,
            $lng
        );

        return $distance <= ($this->location_radius_meters ?? 100);
    }

    /**
     * Calculate distance between two coordinates in meters.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Get deduction percentages array.
     */
    public function getDeductionPercentages()
    {
        return [
            'absent' => $this->absent_deduction_percent,
            'late' => $this->late_deduction_percent,
            'low_late' => $this->low_late_deduction_percent,
            'very_late' => $this->very_late_deduction_percent ?? 2.00,
            'early_leave' => $this->early_leave_deduction_percent ?? 1.00
        ];
    }

    /**
     * Convert to formatted array for API responses.
     */
    public function toFormattedArray()
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'start_time' => $this->getFormattedStartTimeAttribute(),
            'end_time' => $this->getFormattedEndTimeAttribute(),
            'grace_minutes' => $this->grace_minutes,
            'late_threshold_minutes' => $this->late_threshold_minutes,
            'working_days' => $this->working_days,
            'working_days_names' => $this->getWorkingDayNamesAttribute(),
            'working_days_per_month' => $this->working_days_per_month,
            'working_hours_per_day' => $this->working_hours_per_day ?? 8,
            'deduction_percentages' => $this->getDeductionPercentages(),
            'overtime_settings' => [
                'enabled' => $this->overtime_enabled ?? true,
                'multiplier' => $this->overtime_multiplier ?? 1.50,
                'threshold_hours' => ($this->overtime_threshold_minutes ?? 480) / 60
            ],
            'notifications' => [
                'late_arrivals' => $this->notify_late_arrivals ?? true,
                'absences' => $this->notify_absences ?? true,
                'early_leaves' => $this->notify_early_leaves ?? true
            ],
            'auto_processing' => [
                'enabled' => $this->auto_process_files ?? false,
                'schedule' => $this->file_processing_schedule ?? 'daily',
                'time' => $this->processing_time ? $this->processing_time->format('H:i') : null
            ],
            'location_tracking' => [
                'enabled' => $this->location_tracking_enabled ?? false,
                'office_location' => [
                    'lat' => $this->office_lat,
                    'lng' => $this->office_lng
                ],
                'radius_meters' => $this->location_radius_meters ?? 100
            ],
            'formatting' => [
                'currency' => $this->currency ?? 'NGN',
                'currency_symbol' => $this->currency_symbol ?? '₦',
                'date_format' => $this->date_format ?? 'Y-m-d',
                'time_format' => $this->time_format ?? 'H:i:s'
            ]
        ];
    }
}
