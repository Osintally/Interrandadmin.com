<?php

namespace Modules\Essentials\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Essentials\Entities\EssentialsPerformanceTemplate;
use App\User;
use App\Business;

class DepartmentTemplatesSeeder extends Seeder
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

        $templates = $this->getTemplateDefinitions();

        foreach ($templates as $templateData) {
            $existingTemplate = EssentialsPerformanceTemplate::forBusiness($business->id)
                ->where('name', $templateData['name'])
                ->first();

            if (!$existingTemplate) {
                EssentialsPerformanceTemplate::create([
                    'business_id' => $business->id,
                    'name' => $templateData['name'],
                    'description' => $templateData['description'],
                    'department_id' => null, // General templates, can be associated later
                    'fields' => $templateData['fields'],
                    'scoring_criteria' => $this->getDefaultScoringCriteria(),
                    'is_active' => true,
                    'created_by' => $user->id
                ]);
                
                echo "Created template: {$templateData['name']}\n";
            } else {
                echo "Template already exists: {$templateData['name']}\n";
            }
        }
    }

    private function getTemplateDefinitions()
    {
        return [
            [
                'name' => 'Sales Performance Evaluation',
                'description' => 'Comprehensive performance review template for sales team members, focusing on revenue generation, client relationships, and sales skills.',
                'fields' => [
                    [
                        'id' => 'sales_target_achievement',
                        'type' => 'rating',
                        'label' => 'Sales Target Achievement',
                        'description' => 'Success in meeting or exceeding assigned sales quotas and targets',
                        'required' => true,
                        'weight' => 2.5,
                        'order' => 0,
                        'scale' => 5,
                        'scale_labels' => [1 => 'Well Below Target', 2 => 'Below Target', 3 => 'Meets Target', 4 => 'Exceeds Target', 5 => 'Far Exceeds Target']
                    ],
                    [
                        'id' => 'revenue_generated',
                        'type' => 'number',
                        'label' => 'Total Revenue Generated',
                        'description' => 'Total sales revenue generated during the review period',
                        'required' => true,
                        'weight' => 2.0,
                        'order' => 1,
                        'min' => 0
                    ],
                    [
                        'id' => 'client_relationship',
                        'type' => 'rating',
                        'label' => 'Client Relationship Management',
                        'description' => 'Ability to build, maintain, and strengthen client relationships',
                        'required' => true,
                        'weight' => 1.8,
                        'order' => 2,
                        'scale' => 5,
                        'scale_labels' => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent']
                    ],
                    [
                        'id' => 'prospecting_leads',
                        'type' => 'rating',
                        'label' => 'Prospecting & Lead Generation',
                        'description' => 'Effectiveness in identifying, pursuing, and converting new business opportunities',
                        'required' => true,
                        'weight' => 1.5,
                        'order' => 3,
                        'scale' => 5
                    ],
                    [
                        'id' => 'product_knowledge',
                        'type' => 'rating',
                        'label' => 'Product Knowledge',
                        'description' => 'Understanding of products/services, features, benefits, and competitive positioning',
                        'required' => true,
                        'weight' => 1.2,
                        'order' => 4,
                        'scale' => 5
                    ],
                    [
                        'id' => 'negotiation_skills',
                        'type' => 'rating',
                        'label' => 'Negotiation & Closing Skills',
                        'description' => 'Ability to negotiate favorable terms and successfully close deals',
                        'required' => true,
                        'weight' => 1.8,
                        'order' => 5,
                        'scale' => 5
                    ],
                    [
                        'id' => 'sales_activities',
                        'type' => 'number',
                        'label' => 'Number of Sales Activities',
                        'description' => 'Total number of calls, meetings, demos, and other sales activities',
                        'required' => false,
                        'weight' => 0.8,
                        'order' => 6,
                        'min' => 0
                    ],
                    [
                        'id' => 'primary_sales_channel',
                        'type' => 'select',
                        'label' => 'Primary Sales Channel',
                        'description' => 'Main channel or method used for sales activities',
                        'required' => false,
                        'weight' => 0,
                        'order' => 7,
                        'options' => ['Direct Sales', 'Online Sales', 'Phone/Cold Calling', 'Referrals', 'Events/Trade Shows', 'Channel Partners']
                    ],
                    [
                        'id' => 'major_deals',
                        'type' => 'textarea',
                        'label' => 'Major Deals & Achievements',
                        'description' => 'List significant deals closed, key achievements, and notable successes during the review period',
                        'required' => false,
                        'weight' => 0,
                        'order' => 8,
                        'max_length' => 1000
                    ],
                    [
                        'id' => 'sales_development_goals',
                        'type' => 'textarea',
                        'label' => 'Sales Development Goals',
                        'description' => 'Specific goals and objectives for improving sales performance in the next period',
                        'required' => false,
                        'weight' => 0,
                        'order' => 9,
                        'max_length' => 1000
                    ]
                ]
            ],
            [
                'name' => 'Marketing Performance Assessment',
                'description' => 'Detailed performance review template for marketing professionals, covering campaign management, brand development, and digital marketing skills.',
                'fields' => [
                    [
                        'id' => 'campaign_performance',
                        'type' => 'rating',
                        'label' => 'Campaign Performance & ROI',
                        'description' => 'Success and return on investment of marketing campaigns and initiatives managed',
                        'required' => true,
                        'weight' => 2.5,
                        'order' => 0,
                        'scale' => 5
                    ],
                    [
                        'id' => 'brand_awareness',
                        'type' => 'rating',
                        'label' => 'Brand Awareness & Engagement',
                        'description' => 'Effectiveness in building brand presence, awareness, and audience engagement',
                        'required' => true,
                        'weight' => 2.0,
                        'order' => 1,
                        'scale' => 5
                    ],
                    [
                        'id' => 'content_creation',
                        'type' => 'rating',
                        'label' => 'Content Creation & Quality',
                        'description' => 'Quality, relevance, and effectiveness of marketing content created',
                        'required' => true,
                        'weight' => 1.8,
                        'order' => 2,
                        'scale' => 5
                    ],
                    [
                        'id' => 'digital_marketing',
                        'type' => 'rating',
                        'label' => 'Digital Marketing Proficiency',
                        'description' => 'Skills and effectiveness in SEO, SEM, social media, email marketing, and other digital channels',
                        'required' => true,
                        'weight' => 1.8,
                        'order' => 3,
                        'scale' => 5
                    ],
                    [
                        'id' => 'analytics_interpretation',
                        'type' => 'rating',
                        'label' => 'Analytics & Data Interpretation',
                        'description' => 'Ability to analyze marketing data, derive insights, and make data-driven decisions',
                        'required' => true,
                        'weight' => 1.5,
                        'order' => 4,
                        'scale' => 5
                    ],
                    [
                        'id' => 'creative_innovation',
                        'type' => 'rating',
                        'label' => 'Creative Thinking & Innovation',
                        'description' => 'Originality, creativity, and innovative approaches to marketing challenges',
                        'required' => true,
                        'weight' => 1.3,
                        'order' => 5,
                        'scale' => 5
                    ],
                    [
                        'id' => 'budget_management',
                        'type' => 'rating',
                        'label' => 'Budget & Resource Management',
                        'description' => 'Effectiveness in managing marketing budgets and optimizing resource allocation',
                        'required' => true,
                        'weight' => 1.5,
                        'order' => 6,
                        'scale' => 5
                    ],
                    [
                        'id' => 'lead_generation',
                        'type' => 'number',
                        'label' => 'Marketing Qualified Leads Generated',
                        'description' => 'Number of qualified leads generated through marketing efforts',
                        'required' => false,
                        'weight' => 1.0,
                        'order' => 7,
                        'min' => 0
                    ],
                    [
                        'id' => 'marketing_focus',
                        'type' => 'select',
                        'label' => 'Primary Marketing Specialization',
                        'description' => 'Main area of marketing focus and expertise',
                        'required' => false,
                        'weight' => 0,
                        'order' => 8,
                        'options' => ['Digital Marketing', 'Content Marketing', 'Brand Management', 'Event Marketing', 'Email Marketing', 'Social Media Marketing', 'SEO/SEM', 'Marketing Analytics', 'Product Marketing']
                    ],
                    [
                        'id' => 'key_achievements',
                        'type' => 'textarea',
                        'label' => 'Key Marketing Achievements',
                        'description' => 'Notable marketing successes, campaign highlights, and significant achievements during the review period',
                        'required' => false,
                        'weight' => 0,
                        'order' => 9,
                        'max_length' => 1000
                    ],
                    [
                        'id' => 'tools_platforms',
                        'type' => 'textarea',
                        'label' => 'Marketing Tools & Platforms Expertise',
                        'description' => 'List marketing tools, platforms, and technologies used proficiently (e.g., Google Analytics, HubSpot, Adobe Creative Suite)',
                        'required' => false,
                        'weight' => 0,
                        'order' => 10,
                        'max_length' => 500
                    ],
                    [
                        'id' => 'improvement_areas',
                        'type' => 'textarea',
                        'label' => 'Areas for Skill Development',
                        'description' => 'Specific marketing skills or areas identified for further development and learning',
                        'required' => false,
                        'weight' => 0,
                        'order' => 11,
                        'max_length' => 1000
                    ]
                ]
            ],
            [
                'name' => 'Technical Performance Review',
                'description' => 'Specialized template for technical roles including developers, engineers, and IT professionals.',
                'fields' => [
                    [
                        'id' => 'technical_expertise',
                        'type' => 'rating',
                        'label' => 'Technical Skills & Expertise',
                        'description' => 'Proficiency in required technical skills, tools, and technologies',
                        'required' => true,
                        'weight' => 2.5,
                        'order' => 0,
                        'scale' => 5
                    ],
                    [
                        'id' => 'code_quality',
                        'type' => 'rating',
                        'label' => 'Code Quality & Best Practices',
                        'description' => 'Code quality, documentation, testing, and adherence to development best practices',
                        'required' => true,
                        'weight' => 2.0,
                        'order' => 1,
                        'scale' => 5
                    ],
                    [
                        'id' => 'problem_solving',
                        'type' => 'rating',
                        'label' => 'Problem Solving & Debugging',
                        'description' => 'Ability to troubleshoot issues, debug problems, and find efficient solutions',
                        'required' => true,
                        'weight' => 2.0,
                        'order' => 2,
                        'scale' => 5
                    ],
                    [
                        'id' => 'project_delivery',
                        'type' => 'rating',
                        'label' => 'Project Delivery & Deadlines',
                        'description' => 'Consistency in meeting project deadlines and delivering quality deliverables',
                        'required' => true,
                        'weight' => 1.8,
                        'order' => 3,
                        'scale' => 5
                    ],
                    [
                        'id' => 'learning_adaptability',
                        'type' => 'rating',
                        'label' => 'Learning & Adaptability',
                        'description' => 'Willingness to learn new technologies and adapt to changing technical requirements',
                        'required' => true,
                        'weight' => 1.5,
                        'order' => 4,
                        'scale' => 5
                    ],
                    [
                        'id' => 'technical_communication',
                        'type' => 'rating',
                        'label' => 'Technical Communication',
                        'description' => 'Ability to explain technical concepts clearly to both technical and non-technical audiences',
                        'required' => true,
                        'weight' => 1.3,
                        'order' => 5,
                        'scale' => 5
                    ],
                    [
                        'id' => 'technologies_used',
                        'type' => 'textarea',
                        'label' => 'Technologies & Programming Languages',
                        'description' => 'List of technologies, programming languages, frameworks, and tools used proficiently',
                        'required' => false,
                        'weight' => 0,
                        'order' => 6,
                        'max_length' => 500
                    ],
                    [
                        'id' => 'major_projects',
                        'type' => 'textarea',
                        'label' => 'Major Projects & Contributions',
                        'description' => 'Significant projects completed, technical contributions, and achievements during the review period',
                        'required' => false,
                        'weight' => 0,
                        'order' => 7,
                        'max_length' => 1000
                    ]
                ]
            ],
            [
                'name' => 'Customer Service Performance Review',
                'description' => 'Performance template for customer service representatives and support staff.',
                'fields' => [
                    [
                        'id' => 'customer_satisfaction',
                        'type' => 'rating',
                        'label' => 'Customer Satisfaction Ratings',
                        'description' => 'Average customer satisfaction scores and feedback received',
                        'required' => true,
                        'weight' => 2.5,
                        'order' => 0,
                        'scale' => 5
                    ],
                    [
                        'id' => 'response_time',
                        'type' => 'rating',
                        'label' => 'Response Time & Efficiency',
                        'description' => 'Speed and efficiency in responding to and resolving customer inquiries',
                        'required' => true,
                        'weight' => 2.0,
                        'order' => 1,
                        'scale' => 5
                    ],
                    [
                        'id' => 'issue_resolution',
                        'type' => 'rating',
                        'label' => 'Issue Resolution Effectiveness',
                        'description' => 'Ability to effectively resolve customer issues and problems',
                        'required' => true,
                        'weight' => 2.0,
                        'order' => 2,
                        'scale' => 5
                    ],
                    [
                        'id' => 'product_knowledge',
                        'type' => 'rating',
                        'label' => 'Product/Service Knowledge',
                        'description' => 'Understanding of company products, services, and policies',
                        'required' => true,
                        'weight' => 1.5,
                        'order' => 3,
                        'scale' => 5
                    ],
                    [
                        'id' => 'communication_skills',
                        'type' => 'rating',
                        'label' => 'Communication & Interpersonal Skills',
                        'description' => 'Clarity, empathy, and professionalism in customer interactions',
                        'required' => true,
                        'weight' => 1.8,
                        'order' => 4,
                        'scale' => 5
                    ]
                ]
            ]
        ];
    }

    private function getDefaultScoringCriteria()
    {
        return [
            'excellent' => [
                'min' => 90,
                'max' => 100,
                'description' => 'Exceptional performance that consistently exceeds expectations and delivers outstanding results'
            ],
            'good' => [
                'min' => 80,
                'max' => 89,
                'description' => 'Strong performance that meets and often exceeds expectations with consistent quality results'
            ],
            'satisfactory' => [
                'min' => 70,
                'max' => 79,
                'description' => 'Acceptable performance that meets most expectations with room for improvement in some areas'
            ],
            'needs_improvement' => [
                'min' => 0,
                'max' => 69,
                'description' => 'Performance that falls below expectations and requires focused improvement and development'
            ]
        ];
    }
}