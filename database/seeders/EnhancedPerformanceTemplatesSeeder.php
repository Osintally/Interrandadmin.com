<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnhancedPerformanceTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business_id = 1;
        
        // Get departments
        $departments = DB::table('categories')
            ->where('business_id', $business_id)
            ->where('category_type', 'hrm_department')
            ->get()
            ->keyBy('name');

        // Clear existing templates
        DB::table('essentials_performance_templates')->where('business_id', $business_id)->delete();

        $templates = [
            [
                'name' => 'Sales Performance Evaluation',
                'description' => 'Comprehensive sales performance assessment focusing on targets, customer relationships, and business development',
                'department_name' => 'Sales',
                'fields' => [
                    [
                        'type' => 'number',
                        'label' => 'Sales Target Achievement (%)',
                        'required' => true,
                        'min' => 0,
                        'max' => 200
                    ],
                    [
                        'type' => 'number',
                        'label' => 'New Customers Acquired',
                        'required' => true,
                        'min' => 0
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Customer Relationship Management',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Product Knowledge',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Communication Skills',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Negotiation Abilities',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Key Sales Achievements',
                        'required' => true
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Areas for Sales Improvement',
                        'required' => false
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Next Quarter Sales Goals',
                        'required' => true
                    ]
                ]
            ],
            [
                'name' => 'Marketing Performance Review',
                'description' => 'Marketing effectiveness evaluation covering campaigns, brand management, and market analysis',
                'department_name' => 'Marketing',
                'fields' => [
                    [
                        'type' => 'rating',
                        'label' => 'Campaign Effectiveness',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Creative Innovation',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Market Research & Analysis',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Digital Marketing Skills',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Brand Management',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'number',
                        'label' => 'Lead Generation (Monthly Average)',
                        'required' => true,
                        'min' => 0
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Successful Marketing Initiatives',
                        'required' => true
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Marketing Strategy Recommendations',
                        'required' => false
                    ]
                ]
            ],
            [
                'name' => 'Development Team Assessment',
                'description' => 'Technical performance evaluation for developers focusing on code quality, problem-solving, and collaboration',
                'department_name' => 'Development',
                'fields' => [
                    [
                        'type' => 'rating',
                        'label' => 'Code Quality & Standards',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Problem Solving Skills',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Technical Innovation',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Team Collaboration',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Project Delivery Timeliness',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Documentation Quality',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'number',
                        'label' => 'Bug Resolution Rate (%)',
                        'required' => true,
                        'min' => 0,
                        'max' => 100
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Technical Achievements',
                        'required' => true
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Learning & Development Goals',
                        'required' => true
                    ]
                ]
            ],
            [
                'name' => 'Human Resources Performance Review',
                'description' => 'HR professional evaluation covering recruitment, employee relations, and policy implementation',
                'department_name' => 'Human Resources',
                'fields' => [
                    [
                        'type' => 'rating',
                        'label' => 'Recruitment Effectiveness',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Employee Relations',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Policy Development & Implementation',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Training & Development Programs',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Conflict Resolution',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'number',
                        'label' => 'Employee Satisfaction Score',
                        'required' => true,
                        'min' => 1,
                        'max' => 10
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'HR Initiatives Implemented',
                        'required' => true
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Workplace Culture Improvements',
                        'required' => false
                    ]
                ]
            ],
            [
                'name' => 'Finance Department Evaluation',
                'description' => 'Financial performance assessment covering analysis, reporting, compliance, and strategic planning',
                'department_name' => 'Finance',
                'fields' => [
                    [
                        'type' => 'rating',
                        'label' => 'Financial Analysis Accuracy',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Reporting Timeliness',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Compliance & Risk Management',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Budget Management',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'rating',
                        'label' => 'Strategic Financial Planning',
                        'required' => true,
                        'min' => 1,
                        'max' => 5
                    ],
                    [
                        'type' => 'number',
                        'label' => 'Cost Savings Achieved ($)',
                        'required' => false,
                        'min' => 0
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Key Financial Contributions',
                        'required' => true
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Process Improvement Suggestions',
                        'required' => false
                    ]
                ]
            ]
        ];

        foreach ($templates as $template) {
            $department_id = $departments->has($template['department_name']) 
                ? $departments[$template['department_name']]->id 
                : null;

            DB::table('essentials_performance_templates')->insert([
                'business_id' => $business_id,
                'name' => $template['name'],
                'description' => $template['description'],
                'department_id' => $department_id,
                'fields' => json_encode($template['fields']),
                'questions' => json_encode($template['fields']),
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        echo "Enhanced performance templates created for all 5 departments!\n";
    }
}
