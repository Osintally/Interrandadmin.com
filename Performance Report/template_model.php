<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'department_id',
        'fields',
        'scoring_criteria',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'fields' => 'array',
        'scoring_criteria' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function templateFields()
    {
        return $this->hasMany(TemplateField::class);
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('department_id');
    }

    // Methods
    public function addField($type, $label, $options = [], $isRequired = false, $weight = 1.0)
    {
        $fields = $this->fields ?? [];
        
        $field = [
            'id' => uniqid('field_'),
            'type' => $type,
            'label' => $label,
            'options' => $options,
            'required' => $isRequired,
            'weight' => $weight,
            'order' => count($fields)
        ];

        $fields[] = $field;
        $this->update(['fields' => $fields]);

        return $field;
    }

    public function removeField($fieldId)
    {
        $fields = $this->fields ?? [];
        $fields = array_filter($fields, function ($field) use ($fieldId) {
            return $field['id'] !== $fieldId;
        });

        // Reorder fields
        $fields = array_values($fields);
        foreach ($fields as $index => $field) {
            $fields[$index]['order'] = $index;
        }

        $this->update(['fields' => $fields]);
    }

    public function updateField($fieldId, $updates)
    {
        $fields = $this->fields ?? [];
        
        foreach ($fields as $index => $field) {
            if ($field['id'] === $fieldId) {
                $fields[$index] = array_merge($field, $updates);
                break;
            }
        }

        $this->update(['fields' => $fields]);
    }

    public function reorderFields($fieldOrder)
    {
        $fields = $this->fields ?? [];
        $orderedFields = [];

        foreach ($fieldOrder as $order => $fieldId) {
            foreach ($fields as $field) {
                if ($field['id'] === $fieldId) {
                    $field['order'] = $order;
                    $orderedFields[] = $field;
                    break;
                }
            }
        }

        $this->update(['fields' => $orderedFields]);
    }

    public function getFieldByType($type)
    {
        $fields = $this->fields ?? [];
        return array_filter($fields, function ($field) use ($type) {
            return $field['type'] === $type;
        });
    }

    public function getTotalWeight()
    {
        $fields = $this->fields ?? [];
        return array_sum(array_column($fields, 'weight'));
    }

    public function clone($newName = null, $departmentId = null, $createdBy = null)
    {
        $newTemplate = $this->replicate();
        $newTemplate->name = $newName ?? ($this->name . ' (Copy)');
        $newTemplate->department_id = $departmentId;
        $newTemplate->created_by = $createdBy ?? $this->created_by;
        $newTemplate->save();

        return $newTemplate;
    }

    public function getUsageStats()
    {
        return [
            'total_reviews' => $this->performanceReviews()->count(),
            'completed_reviews' => $this->performanceReviews()->where('status', 'approved')->count(),
            'average_score' => $this->performanceReviews()->where('status', 'approved')->avg('overall_score') ?? 0,
            'last_used' => $this->performanceReviews()->latest('created_at')->first()?->created_at
        ];
    }

    public function validateStructure()
    {
        $fields = $this->fields ?? [];
        $errors = [];

        if (empty($fields)) {
            $errors[] = 'Template must have at least one field';
        }

        foreach ($fields as $index => $field) {
            if (empty($field['label'])) {
                $errors[] = "Field {$index} must have a label";
            }

            if (empty($field['type'])) {
                $errors[] = "Field {$index} must have a type";
            }

            if (!in_array($field['type'], ['text', 'rating', 'select', 'file', 'number', 'date'])) {
                $errors[] = "Field {$index} has invalid type: {$field['type']}";
            }

            if ($field['type'] === 'select' && empty($field['options'])) {
                $errors[] = "Select field {$index} must have options";
            }
        }

        return $errors;
    }

    public static function getDefaultTemplates()
    {
        return [
            [
                'name' => 'General Performance Review',
                'description' => 'Standard performance review template suitable for all departments',
                'department_id' => null,
                'fields' => [
                    [
                        'id' => 'quality_of_work',
                        'type' => 'rating',
                        'label' => 'Quality of Work',
                        'required' => true,
                        'weight' => 1.5,
                        'order' => 0
                    ],
                    [
                        'id' => 'communication',
                        'type' => 'rating',
                        'label' => 'Communication Skills',
                        'required' => true,
                        'weight' => 1.0,
                        'order' => 1
                    ],
                    [
                        'id' => 'teamwork',
                        'type' => 'rating',
                        'label' => 'Teamwork & Collaboration',
                        'required' => true,
                        'weight' => 1.0,
                        'order' => 2
                    ],
                    [
                        'id' => 'initiative',
                        'type' => 'rating',
                        'label' => 'Initiative & Innovation',
                        'required' => true,
                        'weight' => 0.8,
                        'order' => 3
                    ],
                    [
                        'id' => 'goals_achievement',
                        'type' => 'rating',
                        'label' => 'Goals Achievement',
                        'required' => true,
                        'weight' => 1.5,
                        'order' => 4
                    ],
                    [
                        'id' => 'strengths',
                        'type' => 'text',
                        'label' => 'Key Strengths',
                        'required' => false,
                        'weight' => 0,
                        'order' => 5
                    ],
                    [
                        'id' => 'improvement_areas',
                        'type' => 'text',
                        'label' => 'Areas for Improvement',
                        'required' => false,
                        'weight' => 0,
                        'order' => 6
                    ]
                ]
            ]
        ];
    }
}

class TemplateField extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'field_type',
        'label',
        'options',
        'is_required',
        'order_index',
        'weight'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'weight' => 'decimal:2'
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}