<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;
use Carbon\Carbon;

class PerformanceGoal extends Model
{
    use HasFactory;

    protected $table = 'performance_goals';

    protected $fillable = [
        'employee_id',
        'kpi_id',
        'target_score',
        'current_score',
        'achievement_percentage',
        'period_start',
        'period_end',
        'status',
    ];

    protected $casts = [
        'target_score' => 'decimal:2',
        'current_score' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(PerformanceKPI::class, 'kpi_id');
    }

    // Scopes
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForKPI($query, $kpiId)
    {
        return $query->where('kpi_id', $kpiId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAchieved($query)
    {
        return $query->where('status', 'achieved');
    }

    public function scopeMissed($query)
    {
        return $query->where('status', 'missed');
    }

    public function scopeCurrentPeriod($query)
    {
        $now = Carbon::now();
        return $query->where('period_start', '<=', $now)
                    ->where('period_end', '>=', $now);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->where('period_start', '>=', $startDate)
                    ->where('period_end', '<=', $endDate);
    }

    // Methods
    public function updateCurrentScore(float $score): void
    {
        $this->current_score = $score;
        $this->achievement_percentage = $this->calculateAchievementPercentage();
        $this->updateStatus();
        $this->save();
    }

    public function calculateAchievementPercentage(): float
    {
        if ($this->target_score == 0) {
            return 0;
        }
        
        return ($this->current_score / $this->target_score) * 100;
    }

    public function updateStatus(): void
    {
        $now = Carbon::now();
        
        // If period has ended, determine final status
        if ($now->gt($this->period_end)) {
            if ($this->achievement_percentage >= 100) {
                $this->status = 'achieved';
            } else {
                $this->status = 'missed';
            }
        } else {
            // Period is still active
            $this->status = 'active';
        }
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAchieved(): bool
    {
        return $this->status === 'achieved';
    }

    public function isMissed(): bool
    {
        return $this->status === 'missed';
    }

    public function isOverdue(): bool
    {
        return $this->isActive() && Carbon::now()->gt($this->period_end);
    }

    public function getDaysRemaining(): int
    {
        if (!$this->isActive()) {
            return 0;
        }
        
        $now = Carbon::now();
        $endDate = Carbon::parse($this->period_end);
        
        return max(0, $now->diffInDays($endDate, false));
    }

    public function getProgressPercentage(): float
    {
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

    public function getScoreGap(): float
    {
        return $this->target_score - $this->current_score;
    }

    public function getScoreGapPercentage(): float
    {
        if ($this->target_score == 0) {
            return 0;
        }
        
        return (($this->target_score - $this->current_score) / $this->target_score) * 100;
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'active' => 'info',
            'achieved' => 'success',
            'missed' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'achieved' => 'Achieved',
            'missed' => 'Missed',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    public function getAchievementColor(): string
    {
        if ($this->achievement_percentage >= 100) {
            return 'success';
        } elseif ($this->achievement_percentage >= 80) {
            return 'info';
        } elseif ($this->achievement_percentage >= 60) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    public function getAchievementLabel(): string
    {
        if ($this->achievement_percentage >= 100) {
            return 'Target Achieved';
        } elseif ($this->achievement_percentage >= 80) {
            return 'On Track';
        } elseif ($this->achievement_percentage >= 60) {
            return 'Behind Schedule';
        } else {
            return 'At Risk';
        }
    }

    public function getRecommendation(): string
    {
        if ($this->isAchieved()) {
            return 'Goal achieved! Consider setting higher targets for next period.';
        }
        
        if ($this->isMissed()) {
            return 'Goal missed. Review performance and adjust strategies for next period.';
        }
        
        if ($this->achievement_percentage >= 80) {
            return 'Good progress. Continue current efforts to achieve the goal.';
        } elseif ($this->achievement_percentage >= 60) {
            return 'Moderate progress. Consider additional support or resources.';
        } else {
            return 'Limited progress. Immediate action required to meet the goal.';
        }
    }

    public function duplicateForNewPeriod(Carbon $newStartDate, Carbon $newEndDate): self
    {
        $newGoal = $this->replicate();
        $newGoal->current_score = 0;
        $newGoal->achievement_percentage = 0;
        $newGoal->period_start = $newStartDate;
        $newGoal->period_end = $newEndDate;
        $newGoal->status = 'active';
        $newGoal->save();
        
        return $newGoal;
    }

    public function cancel(string $reason = null): void
    {
        $this->status = 'cancelled';
        $this->save();
        
        // Log the cancellation reason if provided
        if ($reason) {
            \Log::info("Performance goal cancelled", [
                'goal_id' => $this->id,
                'employee_id' => $this->employee_id,
                'kpi_id' => $this->kpi_id,
                'reason' => $reason
            ]);
        }
    }
}
