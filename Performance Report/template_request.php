<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $templateId = $this->route('template') ? $this->route('template')->id : null;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('templates', 'name')->ignore($templateId)
            ],
            'description' => 'nullable|string|max:1000',
            'department_id' => 'nullable|exists:departments,id',
            'fields' => 'required|array|min:1',
            'fields.*.type' => 'required|in:text,rating,select,file,number,date',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.required' => 'boolean',
            'fields.*.weight' => 'nullable|numeric|min:0|max:10',
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string|max:255',
            'scoring_criteria' => 'nullable|array',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Template name is required.',
            'name.unique' => 'A template with this name already exists.',
            'name.max' => 'Template name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'department_id.exists' => 'Selected department does not exist.',
            'fields.required' => 'At least one field is required.',
            'fields.array' => 'Fields must be provided as an array.',
            'fields.min' => 'Template must have at least one field.',
            'fields.*.type.required' => 'Field type is required.',
            'fields.*.type.in' => 'Invalid field type. Allowed types: text, rating, select, file, number, date.',
            'fields.*.label.required' => 'Field label is required.',
            'fields.*.label.max' => 'Field label cannot exceed 255 characters.',
            'fields.*.weight.numeric' => 'Field weight must be a number.',
            'fields.*.weight.min' => 'Field weight cannot be negative.',
            'fields.*.weight.max' => 'Field weight cannot exceed 10.',
            'fields.*.options.*.max' => 'Option text cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'department_id' => 'department',
            'is_active' => 'status',
            'scoring_criteria' => 'scoring criteria'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate field-specific requirements
            if ($this->has('fields') && is_array($this->fields)) {
                foreach ($this->fields as $index => $field) {
                    // Validate select field options
                    if (isset($field['type']) && $field['type'] === 'select') {
                        if (empty($field['options']) || !is_array($field['options'])) {
                            $validator->errors()->add(
                                "fields.{$index}.options",
                                'Select fields must have at least one option.'
                            );
                        } elseif (count(array_filter($field['options'])) === 0) {
                            $validator->errors()->add(
                                "fields.{$index}.options",
                                'Select fields must have at least one non-empty option.'
                            );
                        }
                    }
                    
                    // Check for duplicate field labels
                    if (isset($field['label'])) {
                        $duplicateCount = collect($this->fields)
                            ->where('label', $field['label'])
                            ->count();
                        
                        if ($duplicateCount > 1) {
                            $validator->errors()->add(
                                "fields.{$index}.label",
                                'Field labels must be unique within the template.'
                            );
                        }
                    }
                    
                    // Validate weight for rating fields
                    if (isset($field['type']) && $field['type'] === 'rating') {
                        if (!isset($field['weight']) || $field['weight'] <= 0) {
                            $validator->errors()->add(
                                "fields.{$index}.weight",
                                'Rating fields must have a positive weight for scoring calculations.'
                            );
                        }
                    }
                }
                
                // Check total weight distribution for rating fields
                $ratingFields = collect($this->fields)->where('type', 'rating');
                if ($ratingFields->count() > 0) {
                    $totalWeight = $ratingFields->sum('weight');
                    if ($totalWeight > 50) {
                        $validator->errors()->add(
                            'fields',
                            'Total weight of rating fields should not exceed 50 to maintain balanced scoring.'
                        );
                    }
                }
                
                // Ensure at least one rating field exists
                if ($ratingFields->count() === 0) {
                    $validator->errors()->add(
                        'fields',
                        'Template must include at least one rating field for performance scoring.'
                    );
                }
                
                // Limit total number of fields
                if (count($this->fields) > 20) {
                    $validator->errors()->add(
                        'fields',
                        'Template cannot have more than 20 fields.'
                    );
                }
            }
            
            // Validate scoring criteria if provided
            if ($this->has('scoring_criteria') && is_array($this->scoring_criteria)) {
                $validLevels = ['excellent', 'good', 'satisfactory', 'needs_improvement'];
                foreach ($this->scoring_criteria as $level => $criteria) {
                    if (!in_array($level, $validLevels)) {
                        $validator->errors()->add(
                            "scoring_criteria.{$level}",
                            'Invalid scoring level. Allowed levels: ' . implode(', ', $validLevels)
                        );
                    }
                    
                    if (isset($criteria['min']) && (!is_numeric($criteria['min']) || $criteria['min'] < 0 || $criteria['min'] > 100)) {
                        $validator->errors()->add(
                            "scoring_criteria.{$level}.min",
                            'Minimum score must be between 0 and 100.'
                        );
                    }
                }
            }
            
            // Department-specific validation
            if ($this->filled('department_id')) {
                $department = \App\Models\Department::find($this->department_id);
                if ($department && !$department->is_active) {
                    $validator->errors()->add(
                        'department_id',
                        'Cannot assign template to an inactive department.'
                    );
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Clean up and normalize fields array
        if ($this->has('fields') && is_array($this->fields)) {
            $cleanedFields = [];
            
            foreach ($this->fields as $index => $field) {
                // Skip completely empty fields
                if (empty($field['type']) && empty($field['label'])) {
                    continue;
                }
                
                $cleanedField = [
                    'type' => $field['type'] ?? null,
                    'label' => isset($field['label']) ? trim($field['label']) : null,
                    'required' => (bool) ($field['required'] ?? false),
                    'weight' => isset($field['weight']) ? (float) $field['weight'] : 1.0,
                ];
                
                // Handle options for select fields
                if (isset($field['options']) && is_array($field['options'])) {
                    $cleanedField['options'] = array_values(array_filter(
                        array_map('trim', $field['options']),
                        function ($option) {
                            return !empty($option);
                        }
                    ));
                } else {
                    $cleanedField['options'] = [];
                }
                
                // Add unique ID if not present
                if (!isset($field['id']) || empty($field['id'])) {
                    $cleanedField['id'] = 'field_' . uniqid();
                } else {
                    $cleanedField['id'] = $field['id'];
                }
                
                $cleanedFields[] = $cleanedField;
            }
            
            $this->merge(['fields' => $cleanedFields]);
        }
        
        // Trim text fields
        if ($this->filled('name')) {
            $this->merge(['name' => trim($this->name)]);
        }
        
        if ($this->filled('description')) {
            $this->merge(['description' => trim($this->description)]);
        }
        
        // Set default values
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'department_id' => $this->filled('department_id') ? $this->department_id : null
        ]);
    }

    /**
     * Get the validated data from the request with additional processing.
     *
     * @param  array|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Ensure fields have proper ordering
        if (isset($validated['fields'])) {
            foreach ($validated['fields'] as $index => $field) {
                $validated['fields'][$index]['order'] = $index;
            }
        }
        
        return $validated;
    }
}