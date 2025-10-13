<?php

namespace Modules\Essentials\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Essentials\Entities\EssentialsPerformanceTemplate;
use App\User;
use App\Business;
use App\Category;

class PerformanceTemplateTestSeeder extends Seeder
{
    public function run()
    {
        // Get the first business and a user to use as creator
        $business = Business::first();
        $user = User::where('business_id', $business->id)->first();
        
        if (!$business || !$user) {
            echo "No business or user found. Skipping seeder.\n";
            return;
        }

        // Get Sales department
        $salesDepartment = Category::where('business_id', $business->id)
            ->where('category_type', 'hrm_department')
            ->where('name', 'Sales')
            ->first();

        // Create sample Sales Performance Template
        $salesQuestions = [
            [
                'id' => 'question_1',
                'type' => 'rating',
                'question' => 'How well does this employee achieve their sales targets?',
                'options' => [],
                'required' => true,
                'weight' => 25.0
            ],
            [
                'id' => 'question_2',
                'type' => 'rating',
                'question' => 'Rate the quality of client relationships maintained by this employee.',
                'options' => [],
                'required' => true,
                'weight' => 20.0
            ],
            [
                'id' => 'question_3',
                'type' => 'select',
                'question' => 'What is their primary strength in sales?',
                'options' => ['Prospecting', 'Closing Deals', 'Customer Service', 'Product Knowledge'],
                'required' => false,
                'weight' => 0.0
            ],
            [
                'id' => 'question_4',
                'type' => 'textarea',
                'question' => 'Describe any major sales achievements during this review period.',
                'options' => [],
                'required' => false,
                'weight' => 0.0
            ]
        ];

        $existingTemplate = EssentialsPerformanceTemplate::where('business_id', $business->id)
            ->where('name', 'Sample Sales Performance Evaluation')
            ->first();

        if (!$existingTemplate) {
            EssentialsPerformanceTemplate::create([
                'business_id' => $business->id,
                'name' => 'Sample Sales Performance Evaluation',
                'description' => 'A sample template for evaluating sales team performance',
                'department_id' => $salesDepartment ? $salesDepartment->id : null,
                'questions' => json_encode($salesQuestions),
                'fields' => json_encode([]), // Empty fields array
                'scoring_criteria' => json_encode([
                    'excellent' => ['min' => 90, 'max' => 100],
                    'good' => ['min' => 80, 'max' => 89],
                    'satisfactory' => ['min' => 70, 'max' => 79],
                    'needs_improvement' => ['min' => 0, 'max' => 69]
                ]),
                'is_active' => true,
                'created_by' => $user->id
            ]);
            
            echo "Created sample Sales Performance Template\n";
        } else {
            echo "Sample template already exists\n";
        }

        // Create a general template without department
        $generalQuestions = [
            [
                'id' => 'question_1',
                'type' => 'rating',
                'question' => 'Overall job performance quality',
                'options' => [],
                'required' => true,
                'weight' => 30.0
            ],
            [
                'id' => 'question_2',
                'type' => 'rating',
                'question' => 'Communication and teamwork skills',
                'options' => [],
                'required' => true,
                'weight' => 25.0
            ],
            [
                'id' => 'question_3',
                'type' => 'text',
                'question' => 'What are this employee\'s key strengths?',
                'options' => [],
                'required' => false,
                'weight' => 0.0
            ]
        ];

        $generalTemplate = EssentialsPerformanceTemplate::where('business_id', $business->id)
            ->where('name', 'General Performance Review Template')
            ->first();

        if (!$generalTemplate) {
            EssentialsPerformanceTemplate::create([
                'business_id' => $business->id,
                'name' => 'General Performance Review Template',
                'description' => 'A basic template suitable for all departments',
                'department_id' => null,
                'questions' => json_encode($generalQuestions),
                'fields' => json_encode([]), // Empty fields array
                'scoring_criteria' => json_encode([
                    'excellent' => ['min' => 90, 'max' => 100],
                    'good' => ['min' => 80, 'max' => 89],
                    'satisfactory' => ['min' => 70, 'max' => 79],
                    'needs_improvement' => ['min' => 0, 'max' => 69]
                ]),
                'is_active' => true,
                'created_by' => $user->id
            ]);
            
            echo "Created general Performance Template\n";
        } else {
            echo "General template already exists\n";
        }
    }
}