<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;
use Carbon\Carbon;

class PerformanceAlert extends Model
{
    use HasFactory;

    protected $table = 'performance_alerts';

    protected $fillable = [
        'type',
        'title',
        'employee_id',
        'kpi_id',
        'goal_id',
        'threshold_type',
        'message',
        'priority',
        'status',
        'is_read',
        'triggered_at',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
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

    public function goal(): BelongsTo
    {
        return $this->belongsTo(PerformanceGoal::class, 'goal_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForKPI($query, $kpiId)
    {
        return $query->where('kpi_id', $kpiId);
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('triggered_at', '>=', Carbon::now()->subDays($days));
    }

    // Methods
    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->save();
    }

    public function markAsUnread(): void
    {
        $this->is_read = false;
        $this->save();
    }

    public function resolve(): void
    {
        $this->resolved_at = Carbon::now();
        $this->save();
    }

    public function unresolve(): void
    {
        $this->resolved_at = null;
        $this->save();
    }

    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }

    public function getAgeInDays(): int
    {
        return Carbon::parse($this->triggered_at)->diffInDays(Carbon::now());
    }

    public function getAgeInHours(): int
    {
        return Carbon::parse($this->triggered_at)->diffInHours(Carbon::now());
    }

    public function getTypeColor(): string
    {
        return match($this->type) {
            'threshold_breach' => 'warning',
            'goal_missed' => 'danger',
            'review_due' => 'info',
            'submission_overdue' => 'danger',
            'performance_decline' => 'warning',
            'kpi_threshold_breach' => 'danger',
            'goal_achieved' => 'success',
            'deadline_approaching' => 'info',
            default => 'secondary'
        };
    }

    public function getTypeIcon(): string
    {
        return match($this->type) {
            'threshold_breach' => 'exclamation-triangle',
            'goal_missed' => 'times-circle',
            'review_due' => 'calendar-alt',
            'submission_overdue' => 'clock',
            'performance_decline' => 'arrow-down',
            'kpi_threshold_breach' => 'exclamation-triangle',
            'goal_achieved' => 'check-circle',
            'deadline_approaching' => 'clock',
            default => 'info-circle'
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'threshold_breach' => 'Threshold Breach',
            'goal_missed' => 'Goal Missed',
            'review_due' => 'Review Due',
            'submission_overdue' => 'Submission Overdue',
            'performance_decline' => 'Performance Decline',
            'kpi_threshold_breach' => 'KPI Threshold Breach',
            'goal_achieved' => 'Goal Achieved',
            'deadline_approaching' => 'Deadline Approaching',
            default => 'Alert'
        };
    }

    public function getThresholdColor(): string
    {
        return match($this->threshold_type) {
            'excellent' => 'success',
            'good' => 'info',
            'needs_improvement' => 'warning',
            default => 'secondary'
        };
    }

    public function getThresholdLabel(): string
    {
        return match($this->threshold_type) {
            'excellent' => 'Excellent',
            'good' => 'Good',
            'needs_improvement' => 'Needs Improvement',
            default => 'Unknown'
        };
    }

    public function getPriority(): string
    {
        if ($this->priority) {
            return $this->priority;
        }
        
        if ($this->type === 'submission_overdue' || $this->type === 'goal_missed') {
            return 'high';
        }
        
        if ($this->type === 'threshold_breach' || $this->type === 'kpi_threshold_breach') {
            return 'medium';
        }
        
        if ($this->type === 'review_due' || $this->type === 'goal_achieved') {
            return 'low';
        }
        
        return 'medium';
    }

    public function getPriorityColor(): string
    {
        return match($this->getPriority()) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            default => 'secondary'
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'active' => 'info',
            'resolved' => 'success',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'resolved' => 'Resolved',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    public function getPriorityLabel(): string
    {
        return match($this->getPriority()) {
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            default => 'Unknown'
        };
    }

    public function getActionRequired(): string
    {
        return match($this->type) {
            'threshold_breach' => 'Review performance metrics and take corrective action',
            'goal_missed' => 'Analyze goal failure and create improvement plan',
            'review_due' => 'Complete performance review by deadline',
            'submission_overdue' => 'Submit overdue performance data immediately',
            'performance_decline' => 'Schedule performance discussion and create improvement plan',
            'kpi_threshold_breach' => 'Review KPI data and adjust targets if needed',
            'goal_achieved' => 'Acknowledge achievement and set new goals',
            'deadline_approaching' => 'Complete pending tasks before deadline',
            default => 'Review alert and take appropriate action'
        };
    }

    public function getSuggestedActions(): array
    {
        return match($this->type) {
            'threshold_breach' => [
                'Review performance data',
                'Schedule one-on-one meeting',
                'Create improvement plan',
                'Set interim targets'
            ],
            'goal_missed' => [
                'Analyze root causes',
                'Adjust goal timeline',
                'Provide additional support',
                'Create action plan'
            ],
            'review_due' => [
                'Schedule review meeting',
                'Prepare performance data',
                'Complete evaluation form',
                'Set next review date'
            ],
            'submission_overdue' => [
                'Send reminder notification',
                'Escalate to manager',
                'Extend deadline if needed',
                'Follow up on progress'
            ],
            'performance_decline' => [
                'Schedule performance discussion',
                'Identify improvement areas',
                'Create development plan',
                'Set regular check-ins'
            ],
            'kpi_threshold_breach' => [
                'Review KPI calculations',
                'Adjust targets if unrealistic',
                'Provide additional training',
                'Monitor progress closely'
            ],
            'goal_achieved' => [
                'Acknowledge achievement',
                'Set new challenging goals',
                'Share success story',
                'Plan next objectives'
            ],
            'deadline_approaching' => [
                'Send deadline reminder',
                'Check progress status',
                'Offer support if needed',
                'Adjust timeline if necessary'
            ],
            default => [
                'Review alert details',
                'Take appropriate action',
                'Update status',
                'Follow up as needed'
            ]
        };
    }


    // Static methods for creating alerts
    public static function createThresholdBreachAlert($employeeId, $kpiId, $thresholdType, $message): self
    {
        return self::create([
            'type' => 'threshold_breach',
            'employee_id' => $employeeId,
            'kpi_id' => $kpiId,
            'threshold_type' => $thresholdType,
            'message' => $message,
            'triggered_at' => Carbon::now(),
        ]);
    }

    public static function createGoalMissedAlert($employeeId, $kpiId, $message): self
    {
        return self::create([
            'type' => 'goal_missed',
            'employee_id' => $employeeId,
            'kpi_id' => $kpiId,
            'message' => $message,
            'triggered_at' => Carbon::now(),
        ]);
    }

    public static function createReviewDueAlert($employeeId, $message): self
    {
        return self::create([
            'type' => 'review_due',
            'employee_id' => $employeeId,
            'message' => $message,
            'triggered_at' => Carbon::now(),
        ]);
    }

    public static function createSubmissionOverdueAlert($employeeId, $message): self
    {
        return self::create([
            'type' => 'submission_overdue',
            'employee_id' => $employeeId,
            'message' => $message,
            'triggered_at' => Carbon::now(),
        ]);
    }
}
