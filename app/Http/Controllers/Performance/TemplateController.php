<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Get templates from database
        $templates = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->withCount('performanceReviews as usage_count')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'status' => $template->is_active ? 'active' : 'inactive',
                    'created_at' => $template->created_at,
                    'usage_count' => $template->usage_count
                ];
            })->toArray();
        
        return view('performance.templates.index', compact('templates'));
    }
    
    /**
     * Show the template builder
     */
    public function builder(Request $request, $id = null)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $template = null;
        if ($id) {
            // Load existing template for editing
            // $template = PerformanceTemplate::findOrFail($id);
        }
        
        // Sample KPIs for the builder
        $availableKPIs = [
            ['id' => 1, 'name' => 'Quality of Work', 'category' => 'Performance'],
            ['id' => 2, 'name' => 'Communication Skills', 'category' => 'Soft Skills'],
            ['id' => 3, 'name' => 'Team Collaboration', 'category' => 'Teamwork'],
            ['id' => 4, 'name' => 'Goal Achievement', 'category' => 'Results'],
        ];
        
        return view('performance.templates.builder', compact('template', 'availableKPIs'));
    }
    
    /**
     * Show the form for creating a new template
     */
    public function create(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Get departments for dropdown
        $departments = \App\Category::where('category_type', 'department')
            ->where('business_id', $business_id)
            ->select('id', 'name')
            ->get()
            ->toArray();
        
        return view('performance.templates.create', compact('departments'));
    }
    
    /**
     * Store a new template
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Validate and store template
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|in:performance,quarterly,annual,probation,custom',
            'department_id' => 'nullable|exists:categories,id',
            'submission_deadline' => 'nullable|date|after:today',
            'max_submissions' => 'nullable|integer|min:1|max:10',
            'is_public' => 'boolean',
            'allow_anonymous' => 'boolean',
            'collect_email' => 'boolean'
        ]);
        
        // Create new template
        $template = new \Modules\Essentials\Entities\EssentialsPerformanceTemplate();
        $template->business_id = $business_id;
        $template->name = $request->name;
        $template->description = $request->description;
        $template->category = $request->category ?? 'performance';
        $template->department_id = $request->department_id;
        $template->submission_deadline = $request->submission_deadline;
        $template->max_submissions = $request->max_submissions;
        $template->is_public = $request->has('is_public');
        $template->allow_anonymous = $request->has('allow_anonymous');
        $template->collect_email = $request->has('collect_email');
        $template->is_active = false; // Start as inactive
        $template->created_by = auth()->id();
        
        // Set default fields structure
        $template->fields = [];
        $template->questions = [];
        $template->scoring_criteria = [
            'excellent' => ['min' => 90, 'max' => 100, 'description' => 'Exceptional performance, exceeds expectations'],
            'good' => ['min' => 80, 'max' => 89, 'description' => 'Strong performance, meets expectations'],
            'satisfactory' => ['min' => 70, 'max' => 79, 'description' => 'Adequate performance, meets basic requirements'],
            'needs_improvement' => ['min' => 0, 'max' => 69, 'description' => 'Below expectations, requires improvement']
        ];
        
        $template->save();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'redirect_url' => route('performance.templates.builder', $template->id)
            ]);
        }
        
        return redirect()->route('performance.templates.builder', $template->id)
            ->with('success', 'Template created successfully');
    }
    
    /**
     * Show a specific template
     */
    public function show(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Load template details from database
        $templateData = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('id', $id)
            ->first();
        
        if (!$templateData) {
            abort(404, 'Template not found');
        }
        
        // Prepare template data for the view
        $template = [
            'id' => $templateData->id,
            'name' => $templateData->name,
            'description' => $templateData->description,
            'status' => $templateData->is_active ? 'active' : 'inactive',
            'created_at' => $templateData->created_at,
            'updated_at' => $templateData->updated_at,
            'usage_count' => $templateData->performanceReviews()->count(),
            'department_count' => 1, // Single department per template
            'field_count' => count($templateData->fields ?? []),
            'sections' => $this->convertFieldsToSections($templateData->fields ?? [], $templateData->name)
        ];
        
        return view('performance.templates.show', compact('template'));
    }
    
    /**
     * Show edit form
     */
    public function edit(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Load template details from database
        $templateData = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('id', $id)
            ->first();
        
        if (!$templateData) {
            abort(404, 'Template not found');
        }
        
        // Prepare template data for the view
        $template = [
            'id' => $templateData->id,
            'name' => $templateData->name,
            'description' => $templateData->description,
            'category' => $templateData->category ?? 'performance',
            'department_id' => $templateData->department_id,
            'submission_deadline' => $templateData->submission_deadline,
            'max_submissions' => $templateData->max_submissions,
            'is_public' => $templateData->is_public ?? false,
            'allow_anonymous' => $templateData->allow_anonymous ?? false,
            'collect_email' => $templateData->collect_email ?? true,
            'created_at' => $templateData->created_at,
            'updated_at' => $templateData->updated_at,
            'usage_count' => $templateData->performanceReviews()->count(),
            'department_count' => 1, // Single department per template
            'field_count' => count($templateData->fields ?? []),
            // Include template structure data
            'fields' => $templateData->fields ?? [],
            'sections' => $this->convertFieldsToSections($templateData->fields ?? [], $templateData->name),
            'questions' => $templateData->questions ?? [],
            'scoring_criteria' => $templateData->scoring_criteria ?? []
        ];
        
        // Get departments for dropdown
        $departments = \App\Category::where('category_type', 'department')
            ->where('business_id', $business_id)
            ->select('id', 'name')
            ->get()
            ->toArray();
        
        return view('performance.templates.edit', compact('template', 'departments'));
    }

    /**
     * Convert flat fields array to sections structure for the edit view
     */
    private function convertFieldsToSections($fields, $templateName = null)
    {
        if (empty($fields)) {
            return [];
        }

        // If fields are already in sections format, return as is
        if (isset($fields[0]) && isset($fields[0]['fields'])) {
            return $fields;
        }

        // Convert flat fields array to sections structure
        // Group fields by section or create a default section
        $sections = [];
        $currentSection = 0;

        foreach ($fields as $index => $field) {
            // If this is the first field or we need a new section
            if (!isset($sections[$currentSection])) {
                $sectionTitle = 'Performance Evaluation';
                if ($templateName) {
                    // Extract department from template name if possible
                    if (strpos($templateName, 'Sales') !== false) {
                        $sectionTitle = 'Sales Performance';
                    } elseif (strpos($templateName, 'Marketing') !== false) {
                        $sectionTitle = 'Marketing Performance';
                    } elseif (strpos($templateName, 'HR') !== false) {
                        $sectionTitle = 'HR Performance';
                    } elseif (strpos($templateName, 'Finance') !== false) {
                        $sectionTitle = 'Finance Performance';
                    } elseif (strpos($templateName, 'IT') !== false) {
                        $sectionTitle = 'IT Performance';
                    }
                }
                
                $sections[$currentSection] = [
                    'title' => $sectionTitle,
                    'description' => 'Main evaluation criteria and performance metrics',
                    'fields' => []
                ];
            }

            // Convert field structure to match edit view expectations
            $convertedField = [
                'label' => $field['label'] ?? $field['name'] ?? 'Field ' . ($index + 1),
                'type' => $field['type'] ?? 'text',
                'placeholder' => $field['placeholder'] ?? '',
                'required' => $field['required'] ?? false,
                'options' => $field['options'] ?? []
            ];

            // Handle different field types
            if (isset($field['options']) && is_array($field['options'])) {
                // Convert associative array to simple array for display
                if (array_keys($field['options']) !== range(0, count($field['options']) - 1)) {
                    $convertedField['options'] = array_values($field['options']);
                } else {
                    $convertedField['options'] = $field['options'];
                }
            } elseif (isset($field['min']) && isset($field['max'])) {
                // For rating fields, create options from min to max
                $convertedField['options'] = [];
                for ($i = $field['min']; $i <= $field['max']; $i++) {
                    $convertedField['options'][] = $i;
                }
            }

            $sections[$currentSection]['fields'][] = $convertedField;
        }

        return $sections;
    }

    /**
     * Convert sections structure back to flat fields array for storage
     */
    private function convertSectionsToFields($sections)
    {
        if (empty($sections)) {
            return [];
        }

        $fields = [];

        foreach ($sections as $section) {
            if (isset($section['fields']) && is_array($section['fields'])) {
                foreach ($section['fields'] as $field) {
                    // Convert field back to storage format
                    $convertedField = [
                        'label' => $field['label'] ?? '',
                        'type' => $field['type'] ?? 'text',
                        'required' => $field['required'] ?? false
                    ];

                    // Add placeholder if provided
                    if (!empty($field['placeholder'])) {
                        $convertedField['placeholder'] = $field['placeholder'];
                    }

                    // Handle options for select/radio/checkbox fields
                    if (in_array($field['type'] ?? '', ['select', 'radio', 'checkbox']) && !empty($field['options'])) {
                        if (is_string($field['options'])) {
                            // Convert newline-separated options to array
                            $convertedField['options'] = array_filter(array_map('trim', explode("\n", $field['options'])));
                        } else {
                            $convertedField['options'] = $field['options'];
                        }
                    }

                    // Handle rating fields
                    if (($field['type'] ?? '') === 'rating' && !empty($field['options'])) {
                        if (is_array($field['options'])) {
                            $convertedField['options'] = $field['options'];
                            $convertedField['min'] = min($field['options']);
                            $convertedField['max'] = max($field['options']);
                        }
                    }

                    $fields[] = $convertedField;
                }
            }
        }

        return $fields;
    }
    
    /**
     * Update template
     */
    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|in:performance,quarterly,annual,probation,custom',
            'department_id' => 'nullable|exists:categories,id',
            'submission_deadline' => 'nullable|date|after:today',
            'max_submissions' => 'nullable|integer|min:1|max:10',
            'is_public' => 'boolean',
            'allow_anonymous' => 'boolean',
            'collect_email' => 'boolean',
            // Template structure validation
            'fields' => 'nullable|array',
            'sections' => 'nullable|array',
            'questions' => 'nullable|array',
            'scoring_criteria' => 'nullable|array'
        ]);
        
        $template = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('id', $id)
            ->first();
        
        if (!$template) {
            abort(404, 'Template not found');
        }
        
        // Update template data
        $template->name = $request->name;
        $template->description = $request->description;
        $template->category = $request->category ?? 'performance';
        $template->department_id = $request->department_id;
        $template->submission_deadline = $request->submission_deadline;
        $template->max_submissions = $request->max_submissions;
        $template->is_public = $request->has('is_public');
        $template->allow_anonymous = $request->has('allow_anonymous');
        $template->collect_email = $request->has('collect_email');
        
        // Update template structure if provided
        if ($request->has('sections')) {
            // Convert sections back to flat fields array for storage
            $template->fields = $this->convertSectionsToFields($request->sections);
        }
        if ($request->has('questions')) {
            $template->questions = $request->questions;
        }
        if ($request->has('scoring_criteria')) {
            $template->scoring_criteria = $request->scoring_criteria;
        }
        
        $template->save();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully'
            ]);
        }
        
        return redirect()->route('performance.templates.show', $id)
            ->with('success', 'Template updated successfully');
    }
    
    /**
     * Delete template
     */
    public function destroy(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $template = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('id', $id)
            ->first();
        
        if ($template) {
            $template->delete();
        }
        
        return redirect()->route('performance.templates.index')
            ->with('success', 'Template deleted successfully');
    }
    
    /**
     * Duplicate template
     */
    public function duplicate(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $originalTemplate = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('id', $id)
            ->first();
        
        if ($originalTemplate) {
            $newTemplate = $originalTemplate->replicate();
            $newTemplate->name = $originalTemplate->name . ' (Copy)';
            $newTemplate->is_active = false; // Start as inactive
            $newTemplate->save();
            
            return redirect()->route('performance.templates.show', $newTemplate->id)
                ->with('success', 'Template duplicated successfully');
        }
        
        return redirect()->route('performance.templates.index')
            ->with('error', 'Template not found');
    }
    
    /**
     * Publish template
     */
    public function publish(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $template = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('id', $id)
            ->first();
        
        if ($template) {
            $template->is_active = true;
            $template->save();
            
            return redirect()->route('performance.templates.show', $id)
                ->with('success', 'Template published successfully');
        }
        
        return redirect()->route('performance.templates.index')
            ->with('error', 'Template not found');
    }
    
    /**
     * Unpublish template
     */
    public function unpublish(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $template = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('id', $id)
            ->first();
        
        if ($template) {
            $template->is_active = false;
            $template->save();
            
            return redirect()->route('performance.templates.show', $id)
                ->with('success', 'Template unpublished successfully');
        }
        
        return redirect()->route('performance.templates.index')
            ->with('error', 'Template not found');
    }
    
    
    /**
     * Get template weights
     */
    public function getWeights(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $template = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('id', $id)
            ->first();
        
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'weights' => $template->weights ?? []
            ]
        ]);
    }
    
    /**
     * Get template status
     */
    public function getStatus(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $template = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->where('id', $id)
            ->first();
        
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'status' => $template->is_active ? 'active' : 'inactive',
                'published' => $template->is_published ?? false
            ]
        ]);
    }
    
    /**
     * Bulk publish templates
     */
    public function bulkPublish(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $templateIds = $request->input('template_ids', []);
        
        if (empty($templateIds)) {
            return response()->json(['error' => 'No templates selected'], 400);
        }
        
        $updated = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->whereIn('id', $templateIds)
            ->update(['is_published' => true]);
        
        return response()->json([
            'success' => true,
            'message' => "Successfully published {$updated} templates"
        ]);
    }
    
    /**
     * Bulk archive templates
     */
    public function bulkArchive(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $templateIds = $request->input('template_ids', []);
        
        if (empty($templateIds)) {
            return response()->json(['error' => 'No templates selected'], 400);
        }
        
        $updated = \Modules\Essentials\Entities\EssentialsPerformanceTemplate::where('business_id', $business_id)
            ->whereIn('id', $templateIds)
            ->update(['is_active' => false]);
        
        return response()->json([
            'success' => true,
            'message' => "Successfully archived {$updated} templates"
        ]);
    }
}