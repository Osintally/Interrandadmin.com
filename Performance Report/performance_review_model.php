<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'template_id',
        'review_period',
        'review_date',
        'overall_score',
        'scores',
        'responses',
        'strengths',
        'areas_for_improvement',
        'goals',
        'development_plan',
        'comments',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'review_date' => 'date',
        'overall_score' => 'decimal:2',
        'scores' => 'array',
        'responses' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Employee::class, 'reviewer_id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('review_period', $period);
    }

    public function scopeByScore($query, $minScore, $maxScore = null)
    {
        if ($maxScore) {
            return $query->whereBetween('overall_score', [$minScore, $maxScore]);
        }
        return $query->where('overall_score', '>=', $minScore);
    }

    public function scopeRecent($query, $months = 3)
    {
        return $query->where('review_date', '>=', now()->subMonths($months));
    }

    // Methods
    public function calculateOverallScore()
    {
        if (!$this->scores || empty($this->scores)) {
            return 0;
        }

        $totalScore = 0;
        $totalWeight = 0;

        if ($this->template && $this->template->fields) {
            foreach ($this->template->fields as $field) {
                $fieldId = $field['id'] ?? $field['name'];
                $weight = $field['weight'] ?? 1;
                $score = $this->scores[$fieldId] ?? 0;

                $totalScore += $score * $weight;
                $totalWeight += $weight;
            }

            return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
        }

        // Fallback to simple average
        return round(array_sum($this->scores) / count($this->scores), 2);
    }

    public function getPerformanceLevel()
    {
        $score = $this->overall_score;

        if ($score >= 90) return 'excellent';
        if ($score >= 80) return 'good';
        if ($score >= 70) return 'satisfactory';
        return 'needs_improvement';
    }

    public function getPerformanceLevelColor()
    {
        $level = $this->getPerformanceLevel();

        return match($level) {
            'excellent' => '#10b981',
            'good' => '#3b82f6',
            'satisfactory' => '#f59e0b',
            'needs_improvement' => '#ef4444',
            default => '#6b7280'
        };
    }

    public function submit()
    {
        $this->update([
            'status' => 'completed',
            'submitted_at' => now(),
            'overall_score' => $this->calculateOverallScore()
        ]);
    }

    public function approve($approverId)
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approverId
        ]);
    }

    public function reject()
    {
        $this->update([
            'status' => 'rejected'
        ]);
    }

    public function canBeEditedBy($userId)
    {
        return $this->status === 'draft' && 
               ($this->reviewer_id === $userId || 
                Employee::find($userId)->hasPermissionToReview($this->employee_id));
    }

    public function canBeApprovedBy($userId)
    {
        return $this->status === 'completed' &&
               Employee::find($userId)->hasPermissionToReview($this->employee_id);
    }

    public static function generateRecommendations($employeeId = null, $departmentId = null)
    {
        $query = self::where('status', 'approved');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $reviews = $query->with(['employee', 'employee.department'])->get();
        $recommendations = [];

        // Analyze performance patterns
        $lowPerformers = $reviews->where('overall_score', '<', 70);
        $highPerformers = $reviews->where('overall_score', '>=', 90);
        $improvingPerformers = $reviews->groupBy('employee_id')
                                     ->map(function ($employeeReviews) {
                                         return $employeeReviews->sortBy('review_date');
                                     })
                                     ->filter(function ($employeeReviews) {
                                         if ($employeeReviews->count() < 2) return false;
                                         $first = $employeeReviews->first()->overall_score;
                                         $last = $employeeReviews->last()->overall_score;
                                         return $last > $first + 5; // Improved by 5+ points
                                     });

        // Generate training recommendations
        if ($lowPerformers->count() > 0) {
            $recommendations[] = [
                'type' => 'training',
                'title' => 'Skills Development Required',
                'description' => $lowPerformers->count() . ' employees would benefit from additional training and development programs.',
                'priority' => 'high',
                'employees' => $lowPerformers->pluck('employee.full_name')->take(5)->toArray()
            ];
        }

        // Generate promotion recommendations
        if ($highPerformers->count() > 0) {
            $recommendations[] = [
                'type' => 'promotion',
                'title' => 'Promotion Candidates',
                'description' => $highPerformers->count() . ' employees are performing exceptionally and may be ready for advancement.',
                'priority' => 'medium',
                'employees' => $highPerformers->pluck('employee.full_name')->take(3)->toArray()
            ];
        }

        // Generate recognition recommendations
        if ($improvingPerformers->count() > 0) {
            $recommendations[] = [
                'type' => 'recognition',
                'title' => 'Improvement Recognition',
                'description' => 'Several employees have shown significant performance improvements and deserve recognition.',
                'priority' => 'low',
                'employees' => $improvingPerformers->keys()->take(3)->map(function ($employeeId) {
                    return Employee::find($employeeId)->full_name;
                })->toArray()
            ];
        }

        return $recommendations;
    }
}