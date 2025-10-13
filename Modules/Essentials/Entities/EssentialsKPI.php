<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\User;

class EssentialsKPI extends Model
{
    use HasFactory;

    protected $table = 'essentials_kpis';

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'category',
        'measurement_type',
        'target_type',
        'target_value',
        'weight',
        'frequency',
        'department_id',
        'calculation_method',
        'data_source',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get the business this KPI belongs to
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    /**
     * Get the department this KPI belongs to
     */
    public function department()
    {
        return $this->belongsTo(\App\Taxonomy::class, 'department_id');
    }

    /**
     * Get the user who created this KPI
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get performance records for this KPI
     */
    public function performances()
    {
        return $this->hasMany(EssentialsKPIPerformance::class, 'kpi_id');
    }

    /**
     * Scope to get KPIs for a specific business
     */
    public function scopeForBusiness($query, $business_id)
    {
        return $query->where('business_id', $business_id);
    }

    /**
     * Scope to get active KPIs only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get KPIs for a specific department
     */
    public function scopeForDepartment($query, $department_id)
    {
        return $query->where(function($q) use ($department_id) {
            $q->where('department_id', $department_id)
              ->orWhereNull('department_id'); // Include global KPIs
        });
    }

    /**
     * Scope to get KPIs by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if this KPI can be deleted
     */
    public function canBeDeleted()
    {
        // Check if there are any performance records associated with this KPI
        return $this->performances()->count() === 0;
    }

    /**
     * Get the current performance for this KPI
     */
    public function getCurrentPerformance($period = 'current_month')
    {
        $query = $this->performances();
        
        switch ($period) {
            case 'current_month':
                $query->whereMonth('period_start', now()->month)
                      ->whereYear('period_start', now()->year);
                break;
            case 'current_quarter':
                $quarter = now()->quarter;
                $year = now()->year;
                $query->whereRaw('QUARTER(period_start) = ? AND YEAR(period_start) = ?', [$quarter, $year]);
                break;
            case 'current_year':
                $query->whereYear('period_start', now()->year);
                break;
        }
        
        return $query->latest('period_start')->first();
    }

    /**
     * Calculate achievement percentage
     */
    public function calculateAchievement($actual_value)
    {
        if ($this->target_value == 0) {
            return 0;
        }
        
        return round(($actual_value / $this->target_value) * 100, 2);
    }

    /**
     * Get performance trend for this KPI
     */
    public function getPerformanceTrend($months = 12)
    {
        return $this->performances()
            ->where('period_start', '>=', now()->subMonths($months))
            ->orderBy('period_start')
            ->get()
            ->map(function ($performance) {
                return [
                    'period' => $performance->period_start->format('M Y'),
                    'actual_value' => $performance->actual_value,
                    'target_value' => $this->target_value,
                    'achievement' => $this->calculateAchievement($performance->actual_value),
                    'status' => $performance->getStatusAttribute()
                ];
            });
    }

    /**
     * Get average performance over a period
     */
    public function getAveragePerformance($period = 'current_year')
    {
        $query = $this->performances();
        
        switch ($period) {
            case 'current_year':
                $query->whereYear('period_start', now()->year);
                break;
            case 'last_12_months':
                $query->where('period_start', '>=', now()->subMonths(12));
                break;
        }
        
        $avg = $query->avg('actual_value');
        
        return [
            'average_actual' => round($avg ?: 0, 2),
            'target' => $this->target_value,
            'achievement' => $this->calculateAchievement($avg ?: 0)
        ];
    }

    /**
     * Get KPI category display name
     */
    public function getCategoryDisplayName()
    {
        $categories = [
            'productivity' => 'Productivity',
            'quality' => 'Quality',
            'efficiency' => 'Efficiency',
            'customer_satisfaction' => 'Customer Satisfaction',
            'financial' => 'Financial',
            'innovation' => 'Innovation',
            'learning_development' => 'Learning & Development',
            'teamwork' => 'Teamwork',
            'leadership' => 'Leadership',
            'communication' => 'Communication',
            'attendance' => 'Attendance',
            'safety' => 'Safety',
            'compliance' => 'Compliance',
            'other' => 'Other'
        ];

        return $categories[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Get measurement type display name
     */
    public function getMeasurementTypeDisplayName()
    {
        return $this->measurement_type === 'quantitative' ? 'Quantitative' : 'Qualitative';
    }

    /**
     * Get frequency display name
     */
    public function getFrequencyDisplayName()
    {
        return ucfirst($this->frequency);
    }

    /**
     * Get formatted target value
     */
    public function getFormattedTargetValue()
    {
        if ($this->target_type === 'percentage') {
            return $this->target_value . '%';
        }
        
        return number_format($this->target_value, 2);
    }

    /**
     * Get next evaluation date based on frequency
     */
    public function getNextEvaluationDate()
    {
        $lastPerformance = $this->performances()->latest('period_start')->first();
        
        if (!$lastPerformance) {
            return now();
        }
        
        switch ($this->frequency) {
            case 'monthly':
                return $lastPerformance->period_start->addMonth();
            case 'quarterly':
                return $lastPerformance->period_start->addMonths(3);
            case 'yearly':
                return $lastPerformance->period_start->addYear();
            default:
                return now();
        }
    }

    /**
     * Check if KPI is due for evaluation
     */
    public function isDueForEvaluation()
    {
        return $this->getNextEvaluationDate()->isPast();
    }

    /**
     * Get KPI status based on recent performance
     */
    public function getStatusAttribute()
    {
        $recentPerformance = $this->getCurrentPerformance();
        
        if (!$recentPerformance) {
            return 'not_evaluated';
        }
        
        $achievement = $this->calculateAchievement($recentPerformance->actual_value);
        
        if ($achievement >= 100) {
            return 'achieved';
        } elseif ($achievement >= 80) {
            return 'near_target';
        } else {
            return 'below_target';
        }
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStatistics($period = 'current_year')
    {
        $performances = $this->performances();
        
        switch ($period) {
            case 'current_year':
                $performances->whereYear('period_start', now()->year);
                break;
            case 'last_12_months':
                $performances->where('period_start', '>=', now()->subMonths(12));
                break;
        }
        
        $data = $performances->get();
        
        if ($data->isEmpty()) {
            return [
                'total_evaluations' => 0,
                'average_performance' => 0,
                'best_performance' => 0,
                'worst_performance' => 0,
                'achievement_rate' => 0,
                'trend' => 'stable'
            ];
        }
        
        $actualValues = $data->pluck('actual_value');
        $achievements = $actualValues->map(function($value) {
            return $this->calculateAchievement($value);
        });
        
        // Calculate trend (comparing first half vs second half of data)
        $trend = 'stable';
        if ($data->count() > 1) {
            $half = intval($data->count() / 2);
            $firstHalf = $achievements->slice(0, $half)->average();
            $secondHalf = $achievements->slice($half)->average();
            
            if ($secondHalf > $firstHalf + 5) {
                $trend = 'improving';
            } elseif ($secondHalf < $firstHalf - 5) {
                $trend = 'declining';
            }
        }
        
        return [
            'total_evaluations' => $data->count(),
            'average_performance' => round($actualValues->average(), 2),
            'best_performance' => round($actualValues->max(), 2),
            'worst_performance' => round($actualValues->min(), 2),
            'achievement_rate' => round($achievements->average(), 1),
            'trend' => $trend
        ];
    }
}