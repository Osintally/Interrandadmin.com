<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SalarySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'position',
        'gross_salary',
        'basic_salary',
        'allowances',
        'deductions',
        'currency',
        'salary_type',
        'hourly_rate',
        'is_active',
        'effective_from',
        'effective_to',
        'created_by'
    ];

    protected $casts = [
        'gross_salary' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date'
    ];

    /**
     * Get the business that owns the salary setting.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    /**
     * Get the user that owns the salary setting.
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * Get the user who created this salary setting.
     */
    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * Scope a query to only include active salary settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include settings for a specific business.
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope a query to only include current salary settings.
     */
    public function scopeCurrent($query, $date = null)
    {
        $date = $date ?: Carbon::today();
        
        return $query->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            });
    }

    /**
     * Get current salary setting for a user.
     */
    public static function getCurrentSalary($businessId, $userId, $date = null)
    {
        return self::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->active()
            ->current($date)
            ->latest('effective_from')
            ->first();
    }

    /**
     * Calculate daily rate based on salary type.
     */
    public function getDailyRateAttribute()
    {
        switch ($this->salary_type) {
            case 'monthly':
                return $this->gross_salary / 30; // Assuming 30 days in a month
            case 'daily':
                return $this->gross_salary;
            case 'hourly':
                return $this->hourly_rate * 8; // Assuming 8 hours per day
            default:
                return 0;
        }
    }

    /**
     * Calculate hourly rate based on salary type.
     */
    public function getHourlyRateCalculatedAttribute()
    {
        switch ($this->salary_type) {
            case 'monthly':
                return $this->gross_salary / (30 * 8); // 30 days * 8 hours
            case 'daily':
                return $this->gross_salary / 8; // 8 hours per day
            case 'hourly':
                return $this->hourly_rate;
            default:
                return 0;
        }
    }

    /**
     * Calculate net salary after deductions.
     */
    public function getNetSalaryAttribute()
    {
        return $this->gross_salary + $this->allowances - $this->deductions;
    }

    /**
     * Get formatted salary with currency.
     */
    public function getFormattedSalaryAttribute()
    {
        $symbol = $this->getCurrencySymbol();
        return $symbol . number_format($this->gross_salary, 2);
    }

    /**
     * Get currency symbol.
     */
    public function getCurrencySymbol()
    {
        $symbols = [
            'NGN' => '₦',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£'
        ];

        return $symbols[$this->currency] ?? $this->currency;
    }

    /**
     * Create or update salary setting for user.
     */
    public static function createOrUpdate($businessId, $userId, $data)
    {
        // Deactivate previous salary settings
        self::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->update(['is_active' => false, 'effective_to' => Carbon::yesterday()]);

        // Create new salary setting
        $data['business_id'] = $businessId;
        $data['user_id'] = $userId;
        $data['is_active'] = true;
        $data['effective_from'] = $data['effective_from'] ?? Carbon::today();
        $data['created_by'] = auth()->id();

        return self::create($data);
    }

    /**
     * Get salary history for a user.
     */
    public static function getSalaryHistory($businessId, $userId)
    {
        return self::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->with('creator')
            ->orderBy('effective_from', 'desc')
            ->get();
    }

    /**
     * Get all employees with their current salary.
     */
    public static function getAllEmployeesWithSalary($businessId)
    {
        return self::where('business_id', $businessId)
            ->active()
            ->current()
            ->with(['user' => function ($query) {
                $query->select('id', 'surname', 'first_name', 'last_name', 'username', 'email');
            }])
            ->get()
            ->groupBy('user_id')
            ->map(function ($salaries) {
                return $salaries->first(); // Get the latest salary setting
            });
    }
}