<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerformanceReviewRequest extends FormRequest
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
        return [
            'employee_id' => 'required|exists:employees,id',
            'reviewer_id' => 'required|exists:employees,id|different:employee_id',
            'template_id' => 'nullable|exists:templates,id',
            'review_period' => 'required|string|max:50',
            'review_date' => 'required|date|before_or_equal:today',
            'scores' => 'nullable|array',
            'scores.*' => 'numeric|min:0|max:5',
            'responses' => 'nullable|array',
            'responses.*' => 'nullable|string|max:2000',
            'strengths' => 'nullable|string|max:2000',
            'areas_for_improvement' => 'nullable|string|max:2000',
            'goals' => 'nullable|string|max:2000',
            'development_plan' => 'nullable|string|max:2000',
            'comments' => 'nullable|string|max:2000',
            'status' => 'nullable|in:draft,completed,approved,rejected'
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
            'employee_id.required' => 'Please select an employee for this review.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'reviewer_id.required' => 'Please select a reviewer.',
            'reviewer_id.exists' => 'Selected reviewer does not exist.',
            'reviewer_id.different' => 'An employee cannot review themselves.',
            'template_id.exists' => 'Selected template does not exist.',
            'review_period.required' => 'Review period is required.',
            'review_date.required' => 'Review date is required.',
            'review_date.before_or_equal' => 'Review date cannot be in the future.',
            'scores.*.numeric' => 'All scores must be valid numbers.',
            'scores.*.min' => 'Scores cannot be less than 0.',
            'scores.*.max' => 'Scores cannot be greater than 5.',
            'responses.*.max' => 'Response text cannot exceed 2000 characters.',
            'strengths.max' => 'Strengths section cannot exceed 2000 characters.',
            'areas_for_improvement.max' => 'Areas for improvement cannot exceed 2000 characters.',
            'goals.max' => 'Goals section cannot exceed 2000 characters.',
            'development_plan.max' => 'Development plan cannot exceed 2000 characters.',
            'comments.max' => 'Comments cannot exceed 2000 characters.'
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
            'employee_id' => 'employee',
            'reviewer_id' => 'reviewer',
            'template_id' => 'template',
            'review_period' => 'review period',
            'review_date' => 'review date',
            'areas_for_improvement' => 'areas for improvement',
            'development_plan' => 'development plan'
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
            // Check if reviewer has permission to review the employee
            if ($this->filled('employee_id') && $this->filled('reviewer_id')) {
                $employee = \App\Models\Employee::find($this->employee_id);
                $reviewer = \App\Models\Employee::find($this->reviewer_id);
                
                if ($employee && $reviewer) {
                    // Check if reviewer is the employee's manager or HR
                    $canReview = $reviewer->id === $employee->manager_id || 
                                str_contains($reviewer->position, 'HR') ||
                                str_contains($reviewer->position, 'Manager');
                    
                    if (!$canReview) {
                        $validator->errors()->add('reviewer_id', 'Selected reviewer does not have permission to review this employee.');
                    }
                }
            }
            
            // Validate scores against template if provided
            if ($this->filled('template_id') && $this->has('scores')) {
                $template = \App\Models\Template::find($this->template_id);
                if ($template && $template->fields) {
                    $requiredFields = collect($template->fields)
                        ->where('type', 'rating')
                        ->where('required', true)
                        ->pluck('id')
                        ->toArray();
                    
                    $providedScores = array_keys($this->scores ?? []);
                    $missingScores = array_diff($requiredFields, $providedScores);
                    
                    if (!empty($missingScores)) {
                        $validator->errors()->add('scores', 'Please provide scores for all required fields.');
                    }
                }
            }
            
            // Check for duplicate reviews in the same period
            if ($this->filled(['employee_id', 'reviewer_id', 'review_period'])) {
                $existingReview = \App\Models\PerformanceReview::where('employee_id', $this->employee_id)
                    ->where('reviewer_id', $this->reviewer_id)
                    ->where('review_period', $this->review_period);
                
                // Exclude current review if updating
                if ($this->route('performance')) {
                    $existingReview->where('id', '!=', $this->route('performance')->id);
                }
                
                if ($existingReview->exists()) {
                    $validator->errors()->add('review_period', 'A review for this employee by this reviewer already exists for this period.');
                }
            }
            
            // Validate review date is not too far in the past
            if ($this->filled('review_date')) {
                $reviewDate = \Carbon\Carbon::parse($this->review_date);
                if ($reviewDate->isBefore(now()->subYear())) {
                    $validator->errors()->add('review_date', 'Review date cannot be more than one year in the past.');
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
        // Clean up scores - remove empty values and convert to proper numeric values
        if ($this->has('scores') && is_array($this->scores)) {
            $cleanedScores = [];
            foreach ($this->scores as $field => $score) {
                if (!is_null($score) && $score !== '') {
                    $cleanedScores[$field] = (float) $score;
                }
            }
            $this->merge(['scores' => $cleanedScores]);
        }
        
        // Clean up responses - remove empty values
        if ($this->has('responses') && is_array($this->responses)) {
            $cleanedResponses = [];
            foreach ($this->responses as $field => $response) {
                if (!is_null($response) && trim($response) !== '') {
                    $cleanedResponses[$field] = trim($response);
                }
            }
            $this->merge(['responses' => $cleanedResponses]);
        }
        
        // Trim text fields
        $textFields = ['strengths', 'areas_for_improvement', 'goals', 'development_plan', 'comments', 'review_period'];
        $cleanedData = [];
        
        foreach ($textFields as $field) {
            if ($this->filled($field)) {
                $cleanedData[$field] = trim($this->$field);
            }
        }
        
        $this->merge($cleanedData);
        
        // Set default review period if not provided
        if (!$this->filled('review_period') && $this->filled('review_date')) {
            $reviewDate = \Carbon\Carbon::parse($this->review_date);
            $quarter = 'Q' . $reviewDate->quarter;
            $this->merge(['review_period' => $reviewDate->year . '-' . $quarter]);
        }
    }
}