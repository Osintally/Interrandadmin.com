<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business_id = 1;
        
        // Get existing users and their departments
        $users = DB::table('users')
            ->select('users.id', 'users.first_name', 'users.last_name', 'categories.name as department_name', 'categories.id as department_id')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->leftJoin('categories', 'users.essentials_department_id', '=', 'categories.id')
            ->where('users.business_id', $business_id)
            ->where('roles.name', '!=', 'Admin#' . $business_id)
            ->limit(5)
            ->get();

        // Get performance templates
        $templates = DB::table('essentials_performance_templates')
            ->where('business_id', $business_id)
            ->where('is_active', true)
            ->get()
            ->keyBy('department_id');

        // Clear existing performance data
        DB::table('essentials_user_performance')->where('business_id', $business_id)->delete();

        $reviewPeriods = [
            ['start' => '2024-01-01', 'end' => '2024-03-31', 'quarter' => 'Q1 2024'],
            ['start' => '2024-04-01', 'end' => '2024-06-30', 'quarter' => 'Q2 2024'],
            ['start' => '2024-07-01', 'end' => '2024-09-30', 'quarter' => 'Q3 2024'],
            ['start' => '2024-10-01', 'end' => '2024-12-31', 'quarter' => 'Q4 2024']
        ];

        foreach ($users as $user) {
            $template = $templates->get($user->department_id);
            
            if (!$template) {
                // Use a default template if no department-specific template exists
                $template = $templates->first();
            }

            if (!$template) {
                echo "No template found for user {$user->first_name} {$user->last_name}\n";
                continue;
            }

            $templateFields = json_decode($template->fields, true);

            foreach ($reviewPeriods as $period) {
                $performanceData = $this->generatePerformanceData($templateFields, $user, $period['quarter']);
                
                DB::table('essentials_user_performance')->insert([
                    'business_id' => $business_id,
                    'user_id' => $user->id,
                    'template_id' => $template->id,
                    'review_period' => $period['quarter'],
                    'review_cycle' => 'Quarterly',
                    'start_date' => $period['start'],
                    'end_date' => $period['end'],
                    'status' => 'completed',
                    'performance_data' => json_encode($performanceData),
                    'overall_rating' => $this->calculateOverallRating($performanceData),
                    'reviewer_id' => 1, // Admin as reviewer
                    'reviewed_at' => Carbon::parse($period['end'])->addDays(7),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        echo "Performance data created for " . count($users) . " users across 4 quarters!\n";
    }

    private function generatePerformanceData($templateFields, $user, $quarter)
    {
        $performanceData = [];
        
        foreach ($templateFields as $field) {
            $value = $this->generateFieldValue($field, $user, $quarter);
            $performanceData[] = [
                'field_id' => uniqid(),
                'label' => $field['label'],
                'type' => $field['type'],
                'value' => $value,
                'required' => $field['required']
            ];
        }

        return $performanceData;
    }

    private function generateFieldValue($field, $user, $quarter)
    {
        switch ($field['type']) {
            case 'rating':
                // Generate realistic ratings with slight variation
                $baseRating = rand(3, 5);
                return $baseRating;

            case 'number':
                if (stripos($field['label'], 'achievement') !== false || stripos($field['label'], 'target') !== false) {
                    return rand(85, 120); // Achievement percentage
                } elseif (stripos($field['label'], 'customers') !== false) {
                    return rand(5, 25); // New customers
                } elseif (stripos($field['label'], 'lead') !== false) {
                    return rand(20, 80); // Monthly leads
                } elseif (stripos($field['label'], 'bug') !== false) {
                    return rand(85, 98); // Bug resolution rate
                } elseif (stripos($field['label'], 'satisfaction') !== false) {
                    return rand(7, 10); // Satisfaction score
                } elseif (stripos($field['label'], 'cost') !== false) {
                    return rand(5000, 50000); // Cost savings
                }
                return rand(1, 100);

            case 'textarea':
                return $this->generateTextResponse($field['label'], $user, $quarter);

            default:
                return 'N/A';
        }
    }

    private function generateTextResponse($label, $user, $quarter)
    {
        $responses = [
            'Key Sales Achievements' => [
                "Exceeded {$quarter} sales target by 15% through strategic client engagement",
                "Successfully closed major enterprise deal worth $50K in {$quarter}",
                "Developed strong relationships with 3 key accounts resulting in repeat business",
                "Led product demonstration sessions that converted 70% of prospects"
            ],
            'Areas for Sales Improvement' => [
                "Focus on improving cold calling conversion rates",
                "Enhance product knowledge in emerging market segments",
                "Develop better time management for prospect follow-ups",
                "Strengthen negotiation skills for complex deal structures"
            ],
            'Next Quarter Sales Goals' => [
                "Achieve 110% of sales target through expanded territory coverage",
                "Develop 5 new strategic partnerships for referral business",
                "Complete advanced sales training certification",
                "Implement CRM best practices to improve pipeline management"
            ],
            'Successful Marketing Initiatives' => [
                "Launched social media campaign that increased engagement by 40%",
                "Developed content marketing strategy resulting in 25% more leads",
                "Executed email marketing automation improving conversion by 15%",
                "Created brand awareness campaign reaching 100K+ target audience"
            ],
            'Marketing Strategy Recommendations' => [
                "Increase investment in digital advertising for better ROI",
                "Develop video content strategy for improved engagement",
                "Implement marketing automation for better lead nurturing",
                "Focus on customer retention campaigns to reduce churn"
            ],
            'Technical Achievements' => [
                "Successfully delivered 3 major features ahead of schedule in {$quarter}",
                "Optimized application performance improving load times by 30%",
                "Implemented automated testing suite reducing bugs by 40%",
                "Mentored junior developers and improved team code quality"
            ],
            'Learning & Development Goals' => [
                "Complete cloud architecture certification by end of next quarter",
                "Learn new frontend framework to enhance development capabilities",
                "Attend industry conference to stay updated with latest trends",
                "Contribute to open source project to build community presence"
            ],
            'HR Initiatives Implemented' => [
                "Launched employee wellness program with 85% participation rate",
                "Implemented new onboarding process reducing time-to-productivity by 25%",
                "Developed performance management system improving feedback quality",
                "Created diversity and inclusion training for all departments"
            ],
            'Workplace Culture Improvements' => [
                "Organized team building activities improving collaboration",
                "Established flexible work arrangements increasing satisfaction",
                "Created employee recognition program boosting morale",
                "Implemented open communication channels for better transparency"
            ],
            'Key Financial Contributions' => [
                "Identified cost savings opportunities worth $30K in {$quarter}",
                "Implemented budget tracking system improving accuracy by 20%",
                "Streamlined financial reporting process saving 5 hours weekly",
                "Conducted financial analysis supporting strategic decision making"
            ],
            'Process Improvement Suggestions' => [
                "Automate monthly reporting to reduce manual errors and save time",
                "Implement real-time dashboard for better financial visibility",
                "Standardize approval workflows for improved efficiency",
                "Integrate financial systems for better data consistency"
            ]
        ];

        $defaultResponses = [
            "Demonstrated strong performance in {$quarter} with consistent results",
            "Made significant contributions to team objectives during {$quarter}",
            "Showed continuous improvement and dedication throughout {$quarter}",
            "Collaborated effectively with team members to achieve goals in {$quarter}"
        ];

        $responses_for_label = $responses[$label] ?? $defaultResponses;
        return $responses_for_label[array_rand($responses_for_label)];
    }

    private function calculateOverallRating($performanceData)
    {
        $ratings = [];
        
        foreach ($performanceData as $data) {
            if ($data['type'] === 'rating' && is_numeric($data['value'])) {
                $ratings[] = $data['value'];
            }
        }

        if (empty($ratings)) {
            return 4.0; // Default rating
        }

        return round(array_sum($ratings) / count($ratings), 1);
    }
}
