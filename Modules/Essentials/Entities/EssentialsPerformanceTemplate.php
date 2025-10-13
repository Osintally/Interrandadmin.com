<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\User;

class EssentialsPerformanceTemplate extends Model
{
    use HasFactory;

    protected $table = 'essentials_performance_templates';

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'department_id',
        'fields',
        'questions',
        'scoring_criteria',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'fields' => 'array',
        'questions' => 'array',
        'scoring_criteria' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function department()
    {
        return $this->belongsTo(\App\Category::class, 'department_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function performanceReviews()
    {
        return $this->hasMany(EssentialsPerformanceReview::class, 'template_id');
    }

    public function kpiMappings()
    {
        return $this->hasMany(\App\Models\Performance\TemplateKPIMapping::class, 'template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    // Methods
    public function getTotalFields()
    {
        return count($this->fields ?? []);
    }

    public function getTotalWeight()
    {
        if (!$this->fields) return 0;
        
        return collect($this->fields)->sum('weight');
    }

    public function getFieldsByType($type)
    {
        if (!$this->fields) return collect();
        
        return collect($this->fields)->where('type', $type);
    }

    public function duplicate($newName = null)
    {
        $template = $this->replicate();
        $template->name = $newName ?? $this->name . ' (Copy)';
        $template->created_by = auth()->id();
        $template->save();

        return $template;
    }

    public function getUsageCount()
    {
        return $this->performanceReviews()->count();
    }

    public function canBeDeleted()
    {
        return $this->getUsageCount() === 0;
    }

    public static function getDefaultFields()
    {
        return [
            [
                'id' => 'job_knowledge',
                'name' => 'Job Knowledge',
                'type' => 'rating',
                'description' => 'Understanding of job responsibilities and technical skills',
                'weight' => 1.0,
                'options' => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'],
                'required' => true
            ],
            [
                'id' => 'quality_of_work',
                'name' => 'Quality of Work',
                'type' => 'rating',
                'description' => 'Accuracy, thoroughness, and reliability of work output',
                'weight' => 1.2,
                'options' => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'],
                'required' => true
            ],
            [
                'id' => 'productivity',
                'name' => 'Productivity',
                'type' => 'rating',
                'description' => 'Volume of work completed within expected timeframes',
                'weight' => 1.0,
                'options' => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'],
                'required' => true
            ],
            [
                'id' => 'communication',
                'name' => 'Communication',
                'type' => 'rating',
                'description' => 'Verbal and written communication effectiveness',
                'weight' => 0.8,
                'options' => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'],
                'required' => true
            ],
            [
                'id' => 'teamwork',
                'name' => 'Teamwork',
                'type' => 'rating',
                'description' => 'Collaboration and cooperation with colleagues',
                'weight' => 0.8,
                'options' => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'],
                'required' => true
            ],
            [
                'id' => 'initiative',
                'name' => 'Initiative',
                'type' => 'rating',
                'description' => 'Proactiveness and self-motivation',
                'weight' => 0.7,
                'options' => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'],
                'required' => true
            ],
            [
                'id' => 'attendance',
                'name' => 'Attendance & Punctuality',
                'type' => 'rating',
                'description' => 'Regularity and timeliness',
                'weight' => 0.6,
                'options' => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'],
                'required' => true
            ]
        ];
    }

    public static function createDefaultTemplate($businessId, $createdBy)
    {
        return self::create([
            'business_id' => $businessId,
            'name' => 'Default Performance Review Template',
            'description' => 'Standard template for performance evaluations',
            'fields' => self::getDefaultFields(),
            'scoring_criteria' => [
                'excellent' => ['min' => 90, 'max' => 100, 'description' => 'Exceptional performance, exceeds expectations'],
                'good' => ['min' => 80, 'max' => 89, 'description' => 'Strong performance, meets expectations'],
                'satisfactory' => ['min' => 70, 'max' => 79, 'description' => 'Acceptable performance, meets most expectations'],
                'needs_improvement' => ['min' => 0, 'max' => 69, 'description' => 'Performance needs improvement']
            ],
            'is_active' => true,
            'created_by' => $createdBy
        ]);
    }
}