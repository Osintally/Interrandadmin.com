<?php

namespace App\Services\Performance;

use Modules\Essentials\Entities\EssentialsPerformanceTemplate;
use App\Models\Performance\TemplateKPIMapping;
use App\Models\Performance\PerformanceKPI;
use App\Category;
use Illuminate\Support\Facades\DB;

class TemplateBuilderService
{
    /**
     * Get templates accessible to user based on permissions
     */
    public function getTemplatesForUser($user): \Illuminate\Database\Eloquent\Collection
    {
        $query = EssentialsPerformanceTemplate::with(['department', 'creator', 'kpiMappings.kpi']);

        if ($user->hasPermission('performance.template.view_all')) {
            // Can view all templates
        } elseif ($user->hasPermission('performance.template.view_department')) {
            // Can view department templates
            $query->where('department_id', $user->essentials_department_id);
        } elseif ($user->hasPermission('performance.template.view_own')) {
            // Can only view own templates
            $query->where('created_by', $user->id);
        } else {
            // No permission
            return collect();
        }

        return $query->get();
    }

    /**
     * Create a new performance template
     */
    public function createTemplate(array $data): EssentialsPerformanceTemplate
    {
        return DB::transaction(function () use ($data) {
            $template = EssentialsPerformanceTemplate::create([
                'business_id' => session('user.business_id'),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'department_id' => $data['department_id'],
                'fields' => $data['template_data']['fields'] ?? [],
                'kpi_config' => $data['kpi_config'] ?? [],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Create KPI mappings if provided
            if (isset($data['kpi_config']) && is_array($data['kpi_config'])) {
                $this->createKPIMappings($template, $data['kpi_config']);
            }

            return $template;
        });
    }

    /**
     * Update an existing performance template
     */
    public function updateTemplate(EssentialsPerformanceTemplate $template, array $data): EssentialsPerformanceTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            $template->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? $template->description,
                'department_id' => $data['department_id'],
                'fields' => $data['template_data']['fields'] ?? $template->fields,
                'kpi_config' => $data['kpi_config'] ?? $template->kpi_config,
            ]);

            // Update KPI mappings
            if (isset($data['kpi_config']) && is_array($data['kpi_config'])) {
                $this->updateKPIMappings($template, $data['kpi_config']);
            }

            return $template->fresh();
        });
    }

    /**
     * Create KPI mappings for template
     */
    private function createKPIMappings(EssentialsPerformanceTemplate $template, array $kpiConfig): void
    {
        foreach ($kpiConfig as $config) {
            if (isset($config['fieldId']) && isset($config['kpiConfig']['kpiId'])) {
                TemplateKPIMapping::create([
                    'template_id' => $template->id,
                    'field_name' => $config['fieldId'],
                    'kpi_id' => $config['kpiConfig']['kpiId'],
                    'weight' => $config['kpiConfig']['weight'] ?? 1.0,
                    'scoring_formula' => $config['kpiConfig']['scoringFormula'] ?? null,
                ]);
            }
        }
    }

    /**
     * Update KPI mappings for template
     */
    private function updateKPIMappings(EssentialsPerformanceTemplate $template, array $kpiConfig): void
    {
        // Delete existing mappings
        $template->kpiMappings()->delete();

        // Create new mappings
        $this->createKPIMappings($template, $kpiConfig);
    }

    /**
     * Get available field types for template builder
     */
    public function getAvailableFieldTypes(): array
    {
        return [
            [
                'type' => 'text',
                'label' => 'Text Input',
                'description' => 'Single line text input',
                'icon' => 'pi pi-align-left',
                'defaultConfig' => [
                    'placeholder' => 'Enter text',
                    'maxLength' => 255,
                ],
                'validation' => [
                    'required' => false,
                    'minLength' => 0,
                    'maxLength' => 255,
                ],
            ],
            [
                'type' => 'textarea',
                'label' => 'Text Area',
                'description' => 'Multi-line text input',
                'icon' => 'pi pi-align-justify',
                'defaultConfig' => [
                    'rows' => 4,
                    'placeholder' => 'Enter detailed text...',
                ],
                'validation' => [
                    'required' => false,
                    'minLength' => 0,
                    'maxLength' => 1000,
                ],
            ],
            [
                'type' => 'number',
                'label' => 'Number',
                'description' => 'Numeric input field',
                'icon' => 'pi pi-hashtag',
                'defaultConfig' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                ],
                'validation' => [
                    'required' => false,
                    'min' => 0,
                    'max' => 100,
                ],
            ],
            [
                'type' => 'rating',
                'label' => 'Rating Scale',
                'description' => 'Star or numeric rating',
                'icon' => 'pi pi-star',
                'defaultConfig' => [
                    'scale' => 10,
                    'showLabels' => true,
                    'labels' => [
                        1 => 'Poor',
                        2 => 'Fair',
                        3 => 'Good',
                        4 => 'Very Good',
                        5 => 'Excellent',
                        6 => 'Outstanding',
                        7 => 'Exceptional',
                        8 => 'Superior',
                        9 => 'Excellent',
                        10 => 'Outstanding',
                    ],
                ],
                'validation' => [
                    'required' => false,
                    'min' => 1,
                    'max' => 10,
                ],
            ],
            [
                'type' => 'select',
                'label' => 'Dropdown',
                'description' => 'Select from options',
                'icon' => 'pi pi-chevron-down',
                'defaultConfig' => [
                    'options' => [
                        'option1' => 'Option 1',
                        'option2' => 'Option 2',
                        'option3' => 'Option 3',
                    ],
                    'placeholder' => 'Select an option',
                ],
                'validation' => [
                    'required' => false,
                ],
            ],
            [
                'type' => 'radio',
                'label' => 'Radio Buttons',
                'description' => 'Single choice selection',
                'icon' => 'pi pi-circle',
                'defaultConfig' => [
                    'options' => [
                        'yes' => 'Yes',
                        'no' => 'No',
                    ],
                ],
                'validation' => [
                    'required' => false,
                ],
            ],
            [
                'type' => 'checkbox',
                'label' => 'Checkboxes',
                'description' => 'Multiple choice selection',
                'icon' => 'pi pi-check-square',
                'defaultConfig' => [
                    'options' => [
                        'option1' => 'Option 1',
                        'option2' => 'Option 2',
                        'option3' => 'Option 3',
                    ],
                ],
                'validation' => [
                    'required' => false,
                ],
            ],
            [
                'type' => 'date',
                'label' => 'Date Picker',
                'description' => 'Date selection',
                'icon' => 'pi pi-calendar',
                'defaultConfig' => [
                    'format' => 'dd/mm/yyyy',
                    'minDate' => null,
                    'maxDate' => null,
                ],
                'validation' => [
                    'required' => false,
                ],
            ],
            [
                'type' => 'file',
                'label' => 'File Upload',
                'description' => 'File attachment',
                'icon' => 'pi pi-upload',
                'defaultConfig' => [
                    'accept' => '.pdf,.doc,.docx,.jpg,.png',
                    'maxSize' => '5MB',
                    'multiple' => false,
                ],
                'validation' => [
                    'required' => false,
                    'maxSize' => 5242880, // 5MB in bytes
                ],
            ],
            [
                'type' => 'percentage',
                'label' => 'Percentage',
                'description' => 'Percentage input field',
                'icon' => 'pi pi-percentage',
                'defaultConfig' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.1,
                ],
                'validation' => [
                    'required' => false,
                    'min' => 0,
                    'max' => 100,
                ],
            ],
            [
                'type' => 'currency',
                'label' => 'Currency',
                'description' => 'Currency input field',
                'icon' => 'pi pi-dollar',
                'defaultConfig' => [
                    'currency' => 'USD',
                    'min' => 0,
                    'max' => 999999.99,
                ],
                'validation' => [
                    'required' => false,
                    'min' => 0,
                ],
            ],
        ];
    }

    /**
     * Generate preview data for template
     */
    public function generatePreviewData(EssentialsPerformanceTemplate $template): array
    {
        $previewData = [];
        $fields = $template->fields ?? [];

        foreach ($fields as $field) {
            $fieldId = $field['id'] ?? uniqid();
            $fieldType = $field['type'] ?? 'text';

            switch ($fieldType) {
                case 'text':
                    $previewData[$fieldId] = 'Sample text input';
                    break;
                case 'textarea':
                    $previewData[$fieldId] = 'This is a sample text area content that demonstrates how the field would look when filled out.';
                    break;
                case 'number':
                    $previewData[$fieldId] = rand(1, 10);
                    break;
                case 'rating':
                    $previewData[$fieldId] = rand(1, 10);
                    break;
                case 'select':
                    $options = $field['options'] ?? [];
                    $previewData[$fieldId] = array_rand($options);
                    break;
                case 'radio':
                    $options = $field['options'] ?? [];
                    $previewData[$fieldId] = array_rand($options);
                    break;
                case 'checkbox':
                    $options = $field['options'] ?? [];
                    $previewData[$fieldId] = array_rand($options, min(2, count($options)));
                    break;
                case 'date':
                    $previewData[$fieldId] = date('Y-m-d');
                    break;
                case 'file':
                    $previewData[$fieldId] = 'sample-document.pdf';
                    break;
                case 'percentage':
                    $previewData[$fieldId] = rand(0, 100);
                    break;
                case 'currency':
                    $previewData[$fieldId] = rand(100, 10000);
                    break;
                default:
                    $previewData[$fieldId] = 'Sample data';
            }
        }

        return $previewData;
    }

    /**
     * Validate template data
     */
    public function validateTemplateData(array $templateData): array
    {
        $errors = [];
        $fields = $templateData['fields'] ?? [];

        if (empty($fields)) {
            $errors[] = 'Template must have at least one field';
        }

        $fieldIds = [];
        foreach ($fields as $index => $field) {
            $fieldId = $field['id'] ?? null;
            $fieldName = $field['name'] ?? null;
            $fieldType = $field['type'] ?? null;

            // Check for duplicate field IDs
            if ($fieldId && in_array($fieldId, $fieldIds)) {
                $errors[] = "Field at position {$index} has duplicate ID: {$fieldId}";
            }
            $fieldIds[] = $fieldId;

            // Validate required fields
            if (!$fieldId) {
                $errors[] = "Field at position {$index} is missing ID";
            }
            if (!$fieldName) {
                $errors[] = "Field at position {$index} is missing name";
            }
            if (!$fieldType) {
                $errors[] = "Field at position {$index} is missing type";
            }

            // Validate field-specific configurations
            if ($fieldType && !$this->isValidFieldType($fieldType)) {
                $errors[] = "Field at position {$index} has invalid type: {$fieldType}";
            }
        }

        return $errors;
    }

    /**
     * Check if field type is valid
     */
    private function isValidFieldType(string $type): bool
    {
        $validTypes = ['text', 'textarea', 'number', 'rating', 'select', 'radio', 'checkbox', 'date', 'file', 'percentage', 'currency'];
        return in_array($type, $validTypes);
    }

    /**
     * Duplicate template
     */
    public function duplicateTemplate(EssentialsPerformanceTemplate $template, string $newName = null): EssentialsPerformanceTemplate
    {
        return DB::transaction(function () use ($template, $newName) {
            $newTemplate = $template->replicate();
            $newTemplate->name = $newName ?? $template->name . ' (Copy)';
            $newTemplate->status = 'draft';
            $newTemplate->created_by = auth()->id();
            $newTemplate->save();

            // Duplicate KPI mappings
            foreach ($template->kpiMappings as $mapping) {
                $newMapping = $mapping->replicate();
                $newMapping->template_id = $newTemplate->id;
                $newMapping->save();
            }

            return $newTemplate;
        });
    }

    /**
     * Publish template
     */
    public function publishTemplate(EssentialsPerformanceTemplate $template): bool
    {
        if ($template->status === 'draft') {
            $template->update(['status' => 'active']);
            return true;
        }
        return false;
    }

    /**
     * Unpublish template
     */
    public function unpublishTemplate(EssentialsPerformanceTemplate $template): bool
    {
        if ($template->status === 'active') {
            $template->update(['status' => 'inactive']);
            return true;
        }
        return false;
    }

    /**
     * Get template statistics
     */
    public function getTemplateStatistics(EssentialsPerformanceTemplate $template): array
    {
        $reviews = $template->performanceReviews();
        
        return [
            'total_usage' => $reviews->count(),
            'completed_reviews' => $reviews->where('status', 'approved')->count(),
            'pending_reviews' => $reviews->whereIn('status', ['draft', 'submitted'])->count(),
            'average_score' => $reviews->where('status', 'approved')->avg('overall_score'),
            'last_used' => $reviews->latest('created_at')->first()?->created_at,
        ];
    }

    /**
     * Get departments for template assignment
     */
    public function getDepartments(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::where('category_type', 'department')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available KPIs for template configuration
     */
    public function getAvailableKPIs($departmentId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = PerformanceKPI::with(['threshold', 'department']);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->orderBy('name')->get();
    }
}
