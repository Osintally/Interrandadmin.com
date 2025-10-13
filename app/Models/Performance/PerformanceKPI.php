<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Category;

class PerformanceKPI extends Model
{
    use HasFactory;

    protected $table = 'performance_kpis';

    protected $fillable = [
        'name',
        'description',
        'department_id',
        'weight',
        'target_value',
        'calculation_method',
        'scoring_formula',
    ];

    protected $casts = [
        'weight' => 'decimal:1',
        'target_value' => 'decimal:2',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'department_id');
    }

    public function threshold(): HasOne
    {
        return $this->hasOne(KPIThreshold::class, 'kpi_id');
    }

    public function templateMappings(): HasMany
    {
        return $this->hasMany(TemplateKPIMapping::class, 'kpi_id');
    }

    public function kpiScores(): HasMany
    {
        return $this->hasMany(KPIScore::class, 'kpi_id');
    }

    public function performanceGoals(): HasMany
    {
        return $this->hasMany(PerformanceGoal::class, 'kpi_id');
    }

    public function performanceAlerts(): HasMany
    {
        return $this->hasMany(PerformanceAlert::class, 'kpi_id');
    }

    // Scopes
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByCalculationMethod($query, $method)
    {
        return $query->where('calculation_method', $method);
    }

    // Methods
    public function calculateScore($fieldValue, $formula = null): float
    {
        $formula = $formula ?? $this->scoring_formula;
        
        switch ($this->calculation_method) {
            case 'direct_score':
                return (float) $fieldValue;
                
            case 'percentage':
                if ($this->target_value && $this->target_value > 0) {
                    return ($fieldValue / $this->target_value) * 10;
                }
                return (float) $fieldValue;
                
            case 'formula':
                return $this->evaluateCustomFormula($formula, $fieldValue);
                
            case 'weighted_average':
                return $this->calculateWeightedAverage($fieldValue);
                
            default:
                return (float) $fieldValue;
        }
    }

    private function evaluateCustomFormula(string $formula, $value): float
    {
        if (empty($formula)) {
            return (float) $value;
        }

        // Safe formula evaluation - replace variables and sanitize
        $formula = str_replace('value', $value, $formula);
        $formula = str_replace('target', $this->target_value ?? 0, $formula);
        $formula = preg_replace('/[^0-9+\-*\/().\s]/', '', $formula);
        
        try {
            $result = eval("return $formula;");
            return is_numeric($result) ? (float) $result : 0;
        } catch (\Exception $e) {
            \Log::error("KPI Formula evaluation error: " . $e->getMessage());
            return 0;
        }
    }

    private function calculateWeightedAverage($fieldValue): float
    {
        // This would need more complex logic based on multiple field values
        return (float) $fieldValue;
    }

    public function getScoreThreshold(float $score): string
    {
        $threshold = $this->threshold;
        
        if (!$threshold) {
            return 'needs_improvement';
        }
        
        if ($score >= $threshold->excellent_min) {
            return 'excellent';
        }
        
        if ($score >= $threshold->good_min) {
            return 'good';
        }
        
        return 'needs_improvement';
    }

    public function getScoreColor(string $threshold): string
    {
        return match($threshold) {
            'excellent' => 'success',
            'good' => 'info',
            'needs_improvement' => 'warning',
            default => 'secondary'
        };
    }

    public function getScoreLabel(string $threshold): string
    {
        return match($threshold) {
            'excellent' => 'Excellent',
            'good' => 'Good',
            'needs_improvement' => 'Needs Improvement',
            default => 'Unknown'
        };
    }

    public function createThreshold(array $thresholdData = []): KPIThreshold
    {
        $defaults = [
            'excellent_min' => 9.0,
            'good_min' => 7.5,
            'needs_improvement_min' => 6.0,
        ];

        return $this->threshold()->create(array_merge($defaults, $thresholdData));
    }

    public function getUsageCount(): int
    {
        return $this->templateMappings()->count() + 
               $this->kpiScores()->count() + 
               $this->performanceGoals()->count();
    }

    public function canBeDeleted(): bool
    {
        return $this->getUsageCount() === 0;
    }

    public static function getDefaultKPIs(): array
    {
        return [
            [
                'name' => 'Job Knowledge',
                'description' => 'Understanding of job responsibilities and technical skills',
                'weight' => 1.0,
                'target_value' => 8.0,
                'calculation_method' => 'direct_score',
                'thresholds' => [
                    'excellent_min' => 9.0,
                    'good_min' => 7.5,
                    'needs_improvement_min' => 6.0,
                ]
            ],
            [
                'name' => 'Quality of Work',
                'description' => 'Accuracy, thoroughness, and reliability of work output',
                'weight' => 1.2,
                'target_value' => 8.0,
                'calculation_method' => 'direct_score',
                'thresholds' => [
                    'excellent_min' => 9.0,
                    'good_min' => 7.5,
                    'needs_improvement_min' => 6.0,
                ]
            ],
            [
                'name' => 'Productivity',
                'description' => 'Volume of work completed within expected timeframes',
                'weight' => 1.0,
                'target_value' => 8.0,
                'calculation_method' => 'direct_score',
                'thresholds' => [
                    'excellent_min' => 9.0,
                    'good_min' => 7.5,
                    'needs_improvement_min' => 6.0,
                ]
            ],
            [
                'name' => 'Communication',
                'description' => 'Verbal and written communication effectiveness',
                'weight' => 0.8,
                'target_value' => 8.0,
                'calculation_method' => 'direct_score',
                'thresholds' => [
                    'excellent_min' => 9.0,
                    'good_min' => 7.5,
                    'needs_improvement_min' => 6.0,
                ]
            ],
            [
                'name' => 'Teamwork',
                'description' => 'Collaboration and cooperation with colleagues',
                'weight' => 0.8,
                'target_value' => 8.0,
                'calculation_method' => 'direct_score',
                'thresholds' => [
                    'excellent_min' => 9.0,
                    'good_min' => 7.5,
                    'needs_improvement_min' => 6.0,
                ]
            ],
            [
                'name' => 'Initiative',
                'description' => 'Proactiveness and self-motivation',
                'weight' => 0.7,
                'target_value' => 8.0,
                'calculation_method' => 'direct_score',
                'thresholds' => [
                    'excellent_min' => 9.0,
                    'good_min' => 7.5,
                    'needs_improvement_min' => 6.0,
                ]
            ],
            [
                'name' => 'Attendance & Punctuality',
                'description' => 'Regularity and timeliness',
                'weight' => 0.6,
                'target_value' => 8.0,
                'calculation_method' => 'direct_score',
                'thresholds' => [
                    'excellent_min' => 9.0,
                    'good_min' => 7.5,
                    'needs_improvement_min' => 6.0,
                ]
            ]
        ];
    }
}
