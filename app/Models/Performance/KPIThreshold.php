<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KPIThreshold extends Model
{
    use HasFactory;

    protected $table = 'kpi_thresholds';

    protected $fillable = [
        'kpi_id',
        'excellent_min',
        'good_min',
        'needs_improvement_min',
    ];

    protected $casts = [
        'excellent_min' => 'decimal:2',
        'good_min' => 'decimal:2',
        'needs_improvement_min' => 'decimal:2',
    ];

    // Relationships
    public function kpi(): BelongsTo
    {
        return $this->belongsTo(PerformanceKPI::class, 'kpi_id');
    }

    // Methods
    public function getThresholdForScore(float $score): string
    {
        if ($score >= $this->excellent_min) {
            return 'excellent';
        }
        
        if ($score >= $this->good_min) {
            return 'good';
        }
        
        if ($score >= $this->needs_improvement_min) {
            return 'needs_improvement';
        }
        
        return 'poor';
    }

    public function getThresholdColor(string $threshold): string
    {
        return match($threshold) {
            'excellent' => 'success',
            'good' => 'info',
            'needs_improvement' => 'warning',
            'poor' => 'danger',
            default => 'secondary'
        };
    }

    public function getThresholdLabel(string $threshold): string
    {
        return match($threshold) {
            'excellent' => 'Excellent',
            'good' => 'Good',
            'needs_improvement' => 'Needs Improvement',
            'poor' => 'Poor',
            default => 'Unknown'
        };
    }

    public function getThresholdRange(string $threshold): array
    {
        return match($threshold) {
            'excellent' => ['min' => $this->excellent_min, 'max' => 10.0],
            'good' => ['min' => $this->good_min, 'max' => $this->excellent_min - 0.01],
            'needs_improvement' => ['min' => $this->needs_improvement_min, 'max' => $this->good_min - 0.01],
            'poor' => ['min' => 0, 'max' => $this->needs_improvement_min - 0.01],
            default => ['min' => 0, 'max' => 10.0]
        };
    }

    public function isScoreInRange(float $score, string $threshold): bool
    {
        $range = $this->getThresholdRange($threshold);
        return $score >= $range['min'] && $score <= $range['max'];
    }

    public function getScoreProgress(float $score): array
    {
        $thresholds = [
            'poor' => $this->needs_improvement_min,
            'needs_improvement' => $this->good_min,
            'good' => $this->excellent_min,
            'excellent' => 10.0
        ];

        $currentThreshold = $this->getThresholdForScore($score);
        $nextThreshold = null;
        $progress = 0;

        foreach ($thresholds as $threshold => $minScore) {
            if ($score < $minScore) {
                $nextThreshold = $threshold;
                $prevThreshold = array_search($minScore, $thresholds);
                $prevScore = $prevThreshold ? $thresholds[$prevThreshold] : 0;
                $progress = (($score - $prevScore) / ($minScore - $prevScore)) * 100;
                break;
            }
        }

        return [
            'current_threshold' => $currentThreshold,
            'next_threshold' => $nextThreshold,
            'progress' => min(100, max(0, $progress))
        ];
    }
}
