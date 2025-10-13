<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Essentials\Entities\EssentialsPerformanceReview;

class KPIScore extends Model
{
    use HasFactory;

    protected $table = 'kpi_scores';

    protected $fillable = [
        'report_id',
        'kpi_id',
        'field_value',
        'calculated_score',
        'weight_applied',
    ];

    protected $casts = [
        'calculated_score' => 'decimal:2',
        'weight_applied' => 'decimal:1',
    ];

    // Relationships
    public function report(): BelongsTo
    {
        return $this->belongsTo(EssentialsPerformanceReview::class, 'report_id');
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(PerformanceKPI::class, 'kpi_id');
    }

    // Scopes
    public function scopeForReport($query, $reportId)
    {
        return $query->where('report_id', $reportId);
    }

    public function scopeForKPI($query, $kpiId)
    {
        return $query->where('kpi_id', $kpiId);
    }

    public function scopeByThreshold($query, $threshold)
    {
        return $query->whereHas('kpi.threshold', function ($q) use ($threshold) {
            switch ($threshold) {
                case 'excellent':
                    $q->whereRaw('calculated_score >= excellent_min');
                    break;
                case 'good':
                    $q->whereRaw('calculated_score >= good_min AND calculated_score < excellent_min');
                    break;
                case 'needs_improvement':
                    $q->whereRaw('calculated_score >= needs_improvement_min AND calculated_score < good_min');
                    break;
                case 'poor':
                    $q->whereRaw('calculated_score < needs_improvement_min');
                    break;
            }
        });
    }

    // Methods
    public function getThreshold(): string
    {
        return $this->kpi->getScoreThreshold($this->calculated_score);
    }

    public function getThresholdColor(): string
    {
        return $this->kpi->getScoreColor($this->getThreshold());
    }

    public function getThresholdLabel(): string
    {
        return $this->kpi->getScoreLabel($this->getThreshold());
    }

    public function getWeightedScore(): float
    {
        return $this->calculated_score * $this->weight_applied;
    }

    public function getScoreProgress(): array
    {
        $threshold = $this->kpi->threshold;
        
        if (!$threshold) {
            return [
                'current_threshold' => 'unknown',
                'next_threshold' => null,
                'progress' => 0
            ];
        }

        return $threshold->getScoreProgress($this->calculated_score);
    }

    public function getScorePercentage(): float
    {
        return ($this->calculated_score / 10) * 100;
    }

    public function isAboveTarget(): bool
    {
        $target = $this->kpi->target_value;
        return $target && $this->calculated_score >= $target;
    }

    public function getTargetGap(): float
    {
        $target = $this->kpi->target_value;
        return $target ? $this->calculated_score - $target : 0;
    }

    public function getTargetGapPercentage(): float
    {
        $target = $this->kpi->target_value;
        if (!$target || $target == 0) {
            return 0;
        }
        
        return (($this->calculated_score - $target) / $target) * 100;
    }

    public function getFormattedFieldValue(): string
    {
        $fieldType = $this->getFieldType();
        
        switch ($fieldType) {
            case 'rating':
                return $this->field_value . '/10';
                
            case 'percentage':
                return $this->field_value . '%';
                
            case 'currency':
                return '$' . number_format($this->field_value, 2);
                
            case 'number':
                return number_format($this->field_value, 2);
                
            case 'date':
                return date('M d, Y', strtotime($this->field_value));
                
            default:
                return $this->field_value;
        }
    }

    private function getFieldType(): string
    {
        // This would need to be determined from the template field configuration
        // For now, we'll make an educated guess based on the field value
        if (is_numeric($this->field_value)) {
            if (strpos($this->field_value, '.') !== false) {
                return 'decimal';
            }
            return 'number';
        }
        
        if (strtotime($this->field_value) !== false) {
            return 'date';
        }
        
        return 'text';
    }

    public function getScoreTrend(): ?string
    {
        // This would require historical data to calculate trends
        // For now, return null
        return null;
    }

    public function getImprovementSuggestion(): string
    {
        $threshold = $this->getThreshold();
        
        return match($threshold) {
            'excellent' => 'Maintain current performance level and consider mentoring others.',
            'good' => 'Continue current efforts and focus on areas for further improvement.',
            'needs_improvement' => 'Focus on specific areas for improvement and seek additional training.',
            'poor' => 'Immediate attention required. Develop improvement plan with supervisor.',
            default => 'Review performance with supervisor to identify improvement areas.'
        };
    }
}
