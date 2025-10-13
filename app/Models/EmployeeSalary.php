<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\User;
use App\Business;

class EmployeeSalary extends Model
{

    protected $table = 'employee_salaries';
    
    protected $fillable = [
        'business_id',
        'user_id',
        'employee_id',
        'department',
        'position',
        'basic_salary',
        'house_allowance',
        'transport_allowance',
        'meal_allowance',
        'medical_allowance',
        'other_allowances',
        'gross_salary',
        'tax_deduction',
        'pension_deduction',
        'insurance_deduction',
        'other_deductions',
        'salary_type',
        'hourly_rate',
        'daily_rate',
        'employment_start_date',
        'salary_effective_date',
        'salary_end_date',
        'bank_name',
        'account_number',
        'account_name',
        'active',
        'notes'
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'house_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'pension_deduction' => 'decimal:2',
        'insurance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'employment_start_date' => 'date',
        'salary_effective_date' => 'date',
        'salary_end_date' => 'date',
        'active' => 'boolean'
    ];

    protected $dates = [
        'salary_effective_date',
        'salary_end_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the user that owns the salary record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the business that owns the salary record.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    /**
     * Get payroll records for this salary.
     */
    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class, 'salary_id');
    }

    /**
     * Scope for active salary records.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for specific business.
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Get current salary for a user.
     */
    public static function getCurrentSalary($userId, $businessId = null)
    {
        $query = self::where('user_id', $userId)
            ->where('active', true)
            ->where('salary_effective_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('salary_end_date')
                  ->orWhere('salary_end_date', '>=', now());
            });

        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        return $query->orderBy('salary_effective_date', 'desc')->first();
    }

    /**
     * Calculate gross salary.
     */
    public function getGrossSalaryAttribute()
    {
        return $this->basic_salary + $this->house_allowance + 
               $this->transport_allowance + $this->meal_allowance + 
               $this->medical_allowance + $this->other_allowances;
    }

    /**
     * Calculate total deductions.
     */
    public function getTotalDeductionsAttribute()
    {
        return $this->tax_deduction + $this->pension_deduction + 
               $this->insurance_deduction + $this->other_deductions;
    }

    /**
     * Calculate net salary.
     */
    public function getNetSalaryAttribute()
    {
        return $this->gross_salary - $this->total_deductions;
    }

    /**
     * Get salary breakdown as array.
     */
    public function getSalaryBreakdown()
    {
        return [
            'basic_salary' => $this->basic_salary,
            'allowances' => [
                'house' => $this->house_allowance,
                'transport' => $this->transport_allowance,
                'meal' => $this->meal_allowance,
                'medical' => $this->medical_allowance,
                'other' => $this->other_allowances,
                'total' => $this->house_allowance + $this->transport_allowance + 
                          $this->meal_allowance + $this->medical_allowance + $this->other_allowances
            ],
            'deductions' => [
                'tax' => $this->tax_deduction,
                'pension' => $this->pension_deduction,
                'insurance' => $this->insurance_deduction,
                'other' => $this->other_deductions,
                'total' => $this->total_deductions
            ],
            'gross_salary' => $this->gross_salary,
            'net_salary' => $this->net_salary
        ];
    }

    /**
     * Check if salary is effective for a given date.
     */
    public function isEffectiveOn($date)
    {
        $checkDate = Carbon::parse($date);
        $effectiveFrom = Carbon::parse($this->salary_effective_date);
        $effectiveTo = $this->salary_end_date ? Carbon::parse($this->salary_end_date) : null;

        return $checkDate >= $effectiveFrom && 
               ($effectiveTo === null || $checkDate <= $effectiveTo);
    }

    /**
     * Create or update salary record for a user.
     */
    public static function createOrUpdate($businessId, $userId, $data)
    {
        // Deactivate existing active salary records
        self::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->where('active', true)
            ->update(['active' => false, 'salary_end_date' => Carbon::today()->subDay()]);
            
        // Create new salary record
        $data['business_id'] = $businessId;
        $data['user_id'] = $userId;
        $data['active'] = true;
        $data['salary_effective_date'] = $data['salary_effective_date'] ?? Carbon::today();
        
        return self::create($data);
    }
    
    /**
     * Get salary history for a user.
     */
    public static function getSalaryHistory($businessId, $userId)
    {
        return self::where('business_id', $businessId)
            ->where('user_id', $userId)
            ->with('user')
            ->orderBy('salary_effective_date', 'desc')
            ->get();
    }

    /**
     * Create salary history record when updating and auto-calculate gross salary.
     */
    public static function boot()
    {
        parent::boot();

        // Auto-calculate gross salary before saving
        static::creating(function ($salary) {
            $salary->calculateGrossSalary();
        });

        static::updating(function ($salary) {
            $salary->calculateGrossSalary();
            
            if ($salary->isDirty(['basic_salary', 'house_allowance', 'transport_allowance', 
                                 'meal_allowance', 'medical_allowance', 'other_allowances'])) {
                // Create new salary record with updated values
                $newSalary = $salary->replicate();
                $newSalary->salary_effective_date = now();
                $newSalary->created_at = now();
                $newSalary->updated_at = now();
                
                // End current salary record
                $salary->salary_end_date = now()->subDay();
                $salary->active = false;
                
                // Save new salary record
                $newSalary->save();
            }
        });
    }

    /**
     * Calculate and set gross salary automatically.
     */
    public function calculateGrossSalary()
    {
        $this->gross_salary = $this->basic_salary + 
                             $this->house_allowance + 
                             $this->transport_allowance + 
                             $this->meal_allowance + 
                             $this->medical_allowance + 
                             $this->other_allowances;
    }
}