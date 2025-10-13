<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;
use App\Category;
use Carbon\Carbon;

class PerformanceAggregation extends Model
{
    use HasFactory;

    protected $table = 'performance_aggregations';

    protected $fillable = [
        'employee_id',
        'department_id',
        'period_start',
        'period_end',
        'overall_score',
        'kpi_breakdown',
        'ranking_position',
        'percentile',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'overall_score' => 'decimal:2',
        'kpi_breakdown' => 'array',
        'percentile' => 'decimal:2',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'department_id');
    }

    // Scopes
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->where('period_start', '>=', $startDate)
                    ->where('period_end', '<=', $endDate);
    }

    public function scopeCurrentPeriod($query)
    {
        $now = Carbon::now();
        return $query->where('period_start', '<=', $now)
                    ->where('period_end', '>=', $now);
    }

    public function scopeTopPerformers($query, $limit = 10)
    {
        return $query->orderBy('overall_score', 'desc')
                    ->orderBy('percentile', 'desc')
                    ->limit($limit);
    }

    public function scopeBottomPerformers($query, $limit = 10)
    {
        return $query->orderBy('overall_score', 'asc')
                    ->orderBy('percentile', 'asc')
                    ->limit($limit);
    }

    public function scopeByScoreRange($query, $minScore, $maxScore)
    {
        return $query->whereBetween('overall_score', [$minScore, $maxScore]);
    }

    public function scopeByPercentileRange($query, $minPercentile, $maxPercentile)
    {
        return $query->whereBetween('percentile', [$minPercentile, $maxPercentile]);
    }

    // Methods
    public function getPerformanceLevel(): string
    {
        if ($this->overall_score >= 9.0) {
            return 'excellent';
        } elseif ($this->overall_score >= 7.5) {
            return 'good';
        } elseif ($this->overall_score >= 6.0) {
            return 'satisfactory';
        } else {
            return 'needs_improvement';
        }
    }

    public function getPerformanceColor(): string
    {
        return match($this->getPerformanceLevel()) {
            'excellent' => 'success',
            'good' => 'info',
            'satisfactory' => 'warning',
            'needs_improvement' => 'danger',
            default => 'secondary'
        };
    }

    public function getPerformanceLabel(): string
    {
        return match($this->getPerformanceLevel()) {
            'excellent' => 'Excellent',
            'good' => 'Good',
            'satisfactory' => 'Satisfactory',
            'needs_improvement' => 'Needs Improvement',
            default => 'Unknown'
        };
    }

    public function getTopKPIs($limit = 3): array
    {
        if (!$this->kpi_breakdown) {
            return [];
        }

        $kpis = collect($this->kpi_breakdown)
            ->sortByDesc('score')
            ->take($limit)
            ->toArray();

        return array_values($kpis);
    }

    public function getBottomKPIs($limit = 3): array
    {
        if (!$this->kpi_breakdown) {
            return [];
        }

        $kpis = collect($this->kpi_breakdown)
            ->sortBy('score')
            ->take($limit)
            ->toArray();

        return array_values($kpis);
    }

    public function getKPIByName($kpiName): ?array
    {
        if (!$this->kpi_breakdown) {
            return null;
        }

        foreach ($this->kpi_breakdown as $kpi) {
            if ($kpi['name'] === $kpiName) {
                return $kpi;
            }
        }

        return null;
    }

    public function getAverageKPIScore(): float
    {
        if (!$this->kpi_breakdown) {
            return 0;
        }

        $scores = collect($this->kpi_breakdown)->pluck('score');
        return $scores->avg();
    }

    public function getKPICount(): int
    {
        return count($this->kpi_breakdown ?? []);
    }

    public function getScoreTrend(): ?string
    {
        // This would require historical data to calculate trends
        // For now, return null
        return null;
    }

    public function getRankingStatus(): string
    {
        if ($this->percentile >= 90) {
            return 'top_performer';
        } elseif ($this->percentile >= 75) {
            return 'above_average';
        } elseif ($this->percentile >= 50) {
            return 'average';
        } elseif ($this->percentile >= 25) {
            return 'below_average';
        } else {
            return 'bottom_performer';
        }
    }

    public function getRankingColor(): string
    {
        return match($this->getRankingStatus()) {
            'top_performer' => 'success',
            'above_average' => 'info',
            'average' => 'warning',
            'below_average' => 'warning',
            'bottom_performer' => 'danger',
            default => 'secondary'
        };
    }

    public function getRankingLabel(): string
    {
        return match($this->getRankingStatus()) {
            'top_performer' => 'Top Performer',
            'above_average' => 'Above Average',
            'average' => 'Average',
            'below_average' => 'Below Average',
            'bottom_performer' => 'Bottom Performer',
            default => 'Unknown'
        };
    }

    public function getPeriodDuration(): int
    {
        return Carbon::parse($this->period_start)->diffInDays($this->period_end);
    }

    public function isCurrentPeriod(): bool
    {
        $now = Carbon::now();
        return $now->between($this->period_start, $this->period_end);
    }

    public function getDaysRemaining(): int
    {
        if (!$this->isCurrentPeriod()) {
            return 0;
        }

        $now = Carbon::now();
        $endDate = Carbon::parse($this->period_end);
        
        return max(0, $now->diffInDays($endDate, false));
    }

    public function getProgressPercentage(): float
    {
        if (!$this->isCurrentPeriod()) {
            return 100;
        }

        $now = Carbon::now();
        $startDate = Carbon::parse($this->period_start);
        $endDate = Carbon::parse($this->period_end);
        
        $totalDays = $startDate->diffInDays($endDate);
        $elapsedDays = $startDate->diffInDays($now);
        
        if ($totalDays == 0) {
            return 100;
        }
        
        return min(100, max(0, ($elapsedDays / $totalDays) * 100));
    }

    public function getImprovementAreas(): array
    {
        $bottomKPIs = $this->getBottomKPIs(3);
        return array_map(function($kpi) {
            return [
                'name' => $kpi['name'],
                'score' => $kpi['score'],
                'target' => $kpi['target'] ?? 8.0,
                'gap' => ($kpi['target'] ?? 8.0) - $kpi['score']
            ];
        }, $bottomKPIs);
    }

    public function getStrengths(): array
    {
        $topKPIs = $this->getTopKPIs(3);
        return array_map(function($kpi) {
            return [
                'name' => $kpi['name'],
                'score' => $kpi['score'],
                'target' => $kpi['target'] ?? 8.0,
                'excess' => $kpi['score'] - ($kpi['target'] ?? 8.0)
            ];
        }, $topKPIs);
    }

    public function getRecommendations(): array
    {
        $recommendations = [];
        
        if ($this->getPerformanceLevel() === 'excellent') {
            $recommendations[] = 'Consider mentoring other team members';
            $recommendations[] = 'Set higher performance goals for next period';
            $recommendations[] = 'Share best practices with the team';
        } elseif ($this->getPerformanceLevel() === 'good') {
            $recommendations[] = 'Continue current performance level';
            $recommendations[] = 'Focus on areas for further improvement';
            $recommendations[] = 'Seek feedback from supervisors';
        } elseif ($this->getPerformanceLevel() === 'satisfactory') {
            $recommendations[] = 'Identify specific improvement areas';
            $recommendations[] = 'Request additional training or support';
            $recommendations[] = 'Set clear improvement goals';
        } else {
            $recommendations[] = 'Immediate performance improvement required';
            $recommendations[] = 'Schedule regular check-ins with supervisor';
            $recommendations[] = 'Develop detailed improvement plan';
        }

        return $recommendations;
    }

    // Static methods for creating aggregations
    public static function createFromPeriod($employeeId, $departmentId, $startDate, $endDate, $kpiData): self
    {
        $overallScore = collect($kpiData)->avg('score');
        $kpiBreakdown = $kpiData;

        return self::create([
            'employee_id' => $employeeId,
            'department_id' => $departmentId,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'overall_score' => $overallScore,
            'kpi_breakdown' => $kpiBreakdown,
        ]);
    }

    public static function calculateRankings($departmentId = null, $startDate = null, $endDate = null): void
    {
        $query = self::query();
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        if ($startDate && $endDate) {
            $query->byPeriod($startDate, $endDate);
        }

        $aggregations = $query->orderBy('overall_score', 'desc')->get();
        $total = $aggregations->count();

        foreach ($aggregations as $index => $aggregation) {
            $ranking = $index + 1;
            $percentile = $total > 0 ? (($total - $index) / $total) * 100 : 0;
            
            $aggregation->update([
                'ranking_position' => $ranking,
                'percentile' => $percentile
            ]);
        }
    }
}
