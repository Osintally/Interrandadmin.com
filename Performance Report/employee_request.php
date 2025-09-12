<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // In a real app, you'd check user permissions here
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $employeeId = $this->route('employee') ? $this->route('employee')->id : null;
        
        return [
            'employee_id' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'employee_id')->ignore($employeeId)
            ],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employeeId)
            ],
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date|before_or_equal:today',
            'position' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'manager_id' => 'nullable|exists:employees,id|different:id',
            'salary' => 'nullable|numeric|min:0|max:9999999.99',
            'status' => 'required|in:active,inactive,terminated',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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
            'employee_id.unique' => 'This employee ID is already taken.',
            'email.unique' => 'This email address is already registered.',
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'department_id.exists' => 'Selected department does not exist.',
            'manager_id.exists' => 'Selected manager does not exist.',
            'manager_id.different' => 'An employee cannot be their own manager.',
            'salary.numeric' => 'Salary must be a valid number.',
            'salary.min' => 'Salary cannot be negative.',
            'salary.max' => 'Salary exceeds maximum allowed value.',
            'skills.*.max' => 'Each skill must not exceed 100 characters.',
            'bio.max' => 'Bio must not exceed 1000 characters.',
            'avatar.image' => 'Avatar must be an image file.',
            'avatar.mimes' => 'Avatar must be a JPEG, PNG, JPG, or GIF file.',
            'avatar.max' => 'Avatar file size must not exceed 2MB.'
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
            'employee_id' => 'employee ID',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'hire_date' => 'hire date',
            'department_id' => 'department',
            'manager_id' => 'manager'
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
            // Additional custom validation logic
            
            // Check if manager is in the same or higher level department
            if ($this->filled('manager_id') && $this->filled('department_id')) {
                $manager = \App\Models\Employee::find($this->manager_id);
                if ($manager && $manager->department_id !== $this->department_id) {
                    // Allow cross-department management for senior positions
                    $seniorPositions = ['CEO', 'CTO', 'CFO', 'COO', 'VP', 'Director', 'HR Manager'];
                    $isManagerSenior = collect($seniorPositions)->some(function ($position) use ($manager) {
                        return str_contains($manager->position, $position);
                    });
                    
                    if (!$isManagerSenior) {
                        $validator->errors()->add('manager_id', 'Manager should typically be in the same department or a senior leadership position.');
                    }
                }
            }
            
            // Validate skills array if provided
            if ($this->has('skills') && is_array($this->skills)) {
                $skills = array_filter($this->skills); // Remove empty values
                if (count($skills) > 10) {
                    $validator->errors()->add('skills', 'Maximum 10 skills allowed.');
                }
            }
            
            // Check for appropriate hire date based on position level
            if ($this->filled('hire_date') && $this->filled('position')) {
                $hireDate = \Carbon\Carbon::parse($this->hire_date);
                $seniorPositions = ['Senior', 'Lead', 'Manager', 'Director', 'VP', 'Chief'];
                
                $isSeniorPosition = collect($seniorPositions)->some(function ($position) {
                    return str_contains($this->position, $position);
                });
                
                if ($isSeniorPosition && $hireDate->isAfter(now()->subYears(2))) {
                    // This is just a warning, not a hard validation error
                    // In a real application, you might want to add this as a soft warning
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
        // Clean up skills array - remove empty values
        if ($this->has('skills') && is_array($this->skills)) {
            $this->merge([
                'skills' => array_values(array_filter($this->skills, function ($skill) {
                    return !empty(trim($skill));
                }))
            ]);
        }
        
        // Normalize phone number
        if ($this->has('phone')) {
            $phone = preg_replace('/[^0-9+\-\(\)\s]/', '', $this->phone);
            $this->merge(['phone' => $phone]);
        }
        
        // Trim text fields
        $this->merge([
            'first_name' => $this->filled('first_name') ? trim($this->first_name) : null,
            'last_name' => $this->filled('last_name') ? trim($this->last_name) : null,
            'position' => $this->filled('position') ? trim($this->position) : null,
            'bio' => $this->filled('bio') ? trim($this->bio) : null,
        ]);
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Add any additional error handling here if needed
        parent::failedValidation($validator);
    }
}