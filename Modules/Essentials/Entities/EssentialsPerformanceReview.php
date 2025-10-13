<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\User;

class EssentialsPerformanceReview extends Model
{
    use HasFactory;

    protected $table = 'essentials_performance_reviews';

    protected $fillable = [
        'business_id',
        'user_id',
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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function template()
    {
        return $this->belongsTo(EssentialsPerformanceTemplate::class, 'template_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function kpiScores()
    {
        return $this->hasMany(\App\Models\Performance\KPIScore::class, 'report_id');
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

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
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
                User::find($userId)->hasPermissionTo('essentials_performance.edit_all'));
    }

    public function canBeApprovedBy($userId)
    {
        return $this->status === 'completed' &&
               User::find($userId)->hasPermissionTo('essentials_performance.approve');
    }

    public static function generateRecommendations($businessId, $userId = null, $departmentId = null)
    {
        $query = self::where('business_id', $businessId)->where('status', 'approved');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $reviews = $query->with(['user'])->get();
        $recommendations = [];

        // Analyze performance patterns
        $lowPerformers = $reviews->where('overall_score', '<', 70);
        $highPerformers = $reviews->where('overall_score', '>=', 90);
        $improvingPerformers = $reviews->groupBy('user_id')
                                     ->map(function ($userReviews) {
                                         return $userReviews->sortBy('review_date');
                                     })
                                     ->filter(function ($userReviews) {
                                         if ($userReviews->count() < 2) return false;
                                         $first = $userReviews->first()->overall_score;
                                         $last = $userReviews->last()->overall_score;
                                         return $last > $first + 5; // Improved by 5+ points
                                     });

        // Generate training recommendations
        if ($lowPerformers->count() > 0) {
            $recommendations[] = [
                'type' => 'training',
                'title' => 'Skills Development Required',
                'description' => $lowPerformers->count() . ' employees would benefit from additional training and development programs.',
                'priority' => 'high',
                'employees' => $lowPerformers->pluck('user.first_name')->take(5)->toArray()
            ];
        }

        // Generate promotion recommendations
        if ($highPerformers->count() > 0) {
            $recommendations[] = [
                'type' => 'promotion',
                'title' => 'Promotion Candidates',
                'description' => $highPerformers->count() . ' employees are performing exceptionally and may be ready for advancement.',
                'priority' => 'medium',
                'employees' => $highPerformers->pluck('user.first_name')->take(3)->toArray()
            ];
        }

        // Generate recognition recommendations
        if ($improvingPerformers->count() > 0) {
            $recommendations[] = [
                'type' => 'recognition',
                'title' => 'Improvement Recognition',
                'description' => 'Several employees have shown significant performance improvements and deserve recognition.',
                'priority' => 'low',
                'employees' => $improvingPerformers->keys()->take(3)->map(function ($userId) {
                    return User::find($userId)->first_name;
                })->toArray()
            ];
        }

        return $recommendations;
    }
}