<?php

namespace App\Models\Performance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Essentials\Entities\EssentialsPerformanceTemplate;

class TemplateKPIMapping extends Model
{
    use HasFactory;

    protected $table = 'template_kpi_mapping';

    protected $fillable = [
        'template_id',
        'field_name',
        'kpi_id',
        'weight',
        'scoring_formula',
    ];

    protected $casts = [
        'weight' => 'decimal:1',
    ];

    // Relationships
    public function template(): BelongsTo
    {
        return $this->belongsTo(EssentialsPerformanceTemplate::class, 'template_id');
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(PerformanceKPI::class, 'kpi_id');
    }

    // Scopes
    public function scopeForTemplate($query, $templateId)
    {
        return $query->where('template_id', $templateId);
    }

    public function scopeForField($query, $fieldName)
    {
        return $query->where('field_name', $fieldName);
    }

    // Methods
    public function calculateScore($fieldValue): float
    {
        // Use custom formula if provided, otherwise use KPI's calculation method
        if ($this->scoring_formula) {
            return $this->evaluateCustomFormula($this->scoring_formula, $fieldValue);
        }

        return $this->kpi->calculateScore($fieldValue);
    }

    private function evaluateCustomFormula(string $formula, $value): float
    {
        if (empty($formula)) {
            return (float) $value;
        }

        // Safe formula evaluation
        $formula = str_replace('value', $value, $formula);
        $formula = str_replace('target', $this->kpi->target_value ?? 0, $formula);
        $formula = preg_replace('/[^0-9+\-*\/().\s]/', '', $formula);
        
        try {
            $result = eval("return $formula;");
            return is_numeric($result) ? (float) $result : 0;
        } catch (\Exception $e) {
            \Log::error("Template KPI Formula evaluation error: " . $e->getMessage());
            return 0;
        }
    }

    public function getWeightedScore($fieldValue): float
    {
        $score = $this->calculateScore($fieldValue);
        return $score * $this->weight;
    }

    public function getScoreThreshold(float $score): string
    {
        return $this->kpi->getScoreThreshold($score);
    }

    public function getScoreColor(string $threshold): string
    {
        return $this->kpi->getScoreColor($threshold);
    }

    public function getScoreLabel(string $threshold): string
    {
        return $this->kpi->getScoreLabel($threshold);
    }

    public function getFieldConfig(): array
    {
        $template = $this->template;
        $fields = $template->fields ?? [];
        
        foreach ($fields as $field) {
            if ($field['id'] === $this->field_name) {
                return $field;
            }
        }
        
        return [];
    }

    public function isFieldRequired(): bool
    {
        $fieldConfig = $this->getFieldConfig();
        return $fieldConfig['required'] ?? false;
    }

    public function getFieldType(): string
    {
        $fieldConfig = $this->getFieldConfig();
        return $fieldConfig['type'] ?? 'text';
    }

    public function getFieldOptions(): array
    {
        $fieldConfig = $this->getFieldConfig();
        return $fieldConfig['options'] ?? [];
    }

    public function validateFieldValue($value): bool
    {
        $fieldConfig = $this->getFieldConfig();
        $type = $this->getFieldType();
        
        switch ($type) {
            case 'number':
            case 'rating':
                return is_numeric($value) && $value >= 0;
                
            case 'select':
            case 'radio':
                $options = $this->getFieldOptions();
                return in_array($value, array_keys($options));
                
            case 'checkbox':
                if (is_array($value)) {
                    $options = $this->getFieldOptions();
                    return count(array_diff($value, array_keys($options))) === 0;
                }
                return false;
                
            case 'date':
                return strtotime($value) !== false;
                
            case 'text':
            case 'textarea':
                return is_string($value) && !empty(trim($value));
                
            default:
                return true;
        }
    }
}
