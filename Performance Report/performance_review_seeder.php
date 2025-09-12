<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerformanceReview;
use App\Models\Employee;
use App\Models\Template;
use Carbon\Carbon;

class PerformanceReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $employees = Employee::all();
        $templates = Template::all();
        $generalTemplate = $templates->where('name', 'General Performance Review')->first();
        $salesTemplate = $templates->where('name', 'Sales Performance Evaluation')->first();
        $techTemplate = $templates->where('name', 'Technical Performance Review')->first();
        $marketingTemplate = $templates->where('name', 'Marketing Performance Assessment')->first();
        
        $reviews = [];
        
        // Generate reviews for the past 12 months
        $reviewPeriods = [
            ['period' => '2023-Q4', 'date' => '2023-12-15'],
            ['period' => '2024-Q1', 'date' => '2024-03-15'],
            ['period' => '2024-Q2', 'date' => '2024-06-15'],
            ['period' => '2024-Q3', 'date' => '2024-09-15'],
        ];

        foreach ($employees as $employee) {
            // Skip if employee was hired after review periods
            foreach ($reviewPeriods as $period) {
                if ($employee->hire_date > $period['date']) {
                    continue;
                }

                // Determine appropriate template based on department
                $template = $generalTemplate;
                switch ($employee->department->code) {
                    case 'SALES':
                        $template = $salesTemplate ?: $generalTemplate;
                        break;
                    case 'DEV':
                        $template = $techTemplate ?: $generalTemplate;
                        break;
                    case 'MKT':
                        $template = $marketingTemplate ?: $generalTemplate;
                        break;
                }

                // Find appropriate reviewer (manager or HR)
                $reviewer = $employee->manager ?: Employee::where('position', 'HR Manager')->first();
                if (!$reviewer) {
                    $reviewer = $employees->where('department.code', 'HR')->first() ?: $employees->first();
                }

                // Generate realistic scores with some variation
                $baseScore = $this->generateBaseScore($employee, $period['period']);
                $scores = $this->generateScoresForTemplate($template, $baseScore);
                $overallScore = $this->calculateOverallScore($template, $scores);

                // Generate responses based on template
                $responses = $this->generateResponsesForTemplate($template, $baseScore);

                // Determine review status
                $status = rand(0, 10) > 1 ? 'approved' : (rand(0, 5) > 1 ? 'completed' : 'draft');
                
                $review = [
                    'employee_id' => $employee->id,
                    'reviewer_id' => $reviewer->id,
                    'template_id' => $template->id,
                    'review_period' => $period['period'],
                    'review_date' => $period['date'],
                    'overall_score' => $overallScore,
                    'scores' => json_encode($scores),
                    'responses' => json_encode($responses),
                    'strengths' => $this->generateStrengths($employee, $baseScore),
                    'areas_for_improvement' => $this->generateImprovementAreas($employee, $baseScore),
                    'goals' => $this->generateGoals($employee),
                    'development_plan' => $this->generateDevelopmentPlan($employee, $baseScore),
                    'comments' => $this->generateComments($employee, $baseScore),
                    'status' => $status,
                    'created_at' => Carbon::parse($period['date'])->subDays(rand(1, 10)),
                    'updated_at' => Carbon::parse($period['date'])->addDays(rand(1, 5)),
                ];

                if ($status === 'completed' || $status === 'approved') {
                    $review['submitted_at'] = Carbon::parse($period['date'])->addDays(rand(1, 3));
                }

                if ($status === 'approved') {
                    $review['approved_at'] = Carbon::parse($period['date'])->addDays(rand(3, 7));
                    $review['approved_by'] = $reviewer->manager_id ?: Employee::where('position', 'HR Manager')->first()->id;
                }

                $reviews[] = $review;
            }
        }

        foreach ($reviews as $review) {
            PerformanceReview::create($review);
        }
    }

    private function generateBaseScore($employee, $period)
    {
        // Generate realistic performance scores based on role and seniority
        $baseScores = [
            'Senior' => [85, 95],
            'Manager' => [82, 94],
            'Lead' => [80, 92],
            'Specialist' => [78, 90],
            'Representative' => [75, 88],
            'Coordinator' => [73, 86],
            'default' => [70, 85]
        ];

        foreach ($baseScores as $keyword => $range) {
            if ($keyword === 'default') continue;
            if (str_contains($employee->position, $keyword)) {
                return rand($range[0] * 10, $range[1] * 10) / 10;
            }
        }

        $range = $baseScores['default'];
        return rand($range[0] * 10, $range[1] * 10) / 10;
    }

    private function generateScoresForTemplate($template, $baseScore)
    {
        $scores = [];
        $fields = json_decode($template->fields, true);
        
        foreach ($fields as $field) {
            if ($field['type'] === 'rating') {
                // Add some variation around base score
                $variation = rand(-10, 10) / 10;
                $score = max(1, min(5, ($baseScore + $variation) / 20)); // Convert to 1-5 scale
                $scores[$field['id']] = round($score, 1);
            } elseif ($field['type'] === 'select' && !empty($field['options'])) {
                $scores[$field['id']] = $field['options'][array_rand($field['options'])];
            }
        }
        
        return $scores;
    }

    private function calculateOverallScore($template, $scores)
    {
        $fields = json_decode($template->fields, true);
        $totalScore = 0;
        $totalWeight = 0;
        
        foreach ($fields as $field) {
            if ($field['type'] === 'rating' && isset($scores[$field['id']])) {
                $weight = $field['weight'] ?? 1;
                $score = $scores[$field['id']] * 20; // Convert back to 100 scale
                $totalScore += $score * $weight;
                $totalWeight += $weight;
            }
        }
        
        return $totalWeight > 0 ? round($totalScore / $totalWeight, 1) : 75;
    }

    private function generateResponsesForTemplate($template, $baseScore)
    {
        $responses = [];
        $fields = json_decode($template->fields, true);
        
        foreach ($fields as $field) {
            if ($field['type'] === 'text') {
                switch ($field['id']) {
                    case 'strengths':
                    case 'key_strengths':
                        $responses[$field['id']] = $this->generateStrengthsText($baseScore);
                        break;
                    case 'improvement_areas':
                    case 'areas_for_improvement':
                        $responses[$field['id']] = $this->generateImprovementText($baseScore);
                        break;
                    case 'technologies':
                        $responses[$field['id']] = 'PHP, Laravel, JavaScript, MySQL, Git';
                        break;
                    case 'development_goals':
                        $responses[$field['id']] = $this->generateDevelopmentGoalsText();
                        break;
                    default:
                        $responses[$field['id']] = 'Standard response for ' . $field['label'];
                }
            }
        }
        
        return $responses;
    }

    private function generateStrengths($employee, $baseScore)
    {
        $strengths = [
            'Consistently delivers high-quality work on time',
            'Excellent communication skills and team collaboration',
            'Strong problem-solving abilities and attention to detail',
            'Shows initiative and takes ownership of responsibilities',
            'Demonstrates strong technical skills and expertise',
            'Positive attitude and willingness to help colleagues',
            'Adapts well to changing priorities and requirements'
        ];

        if ($baseScore >= 85) {
            $selectedStrengths = array_rand(array_flip($strengths), rand(3, 4));
        } else {
            $selectedStrengths = array_rand(array_flip($strengths), rand(2, 3));
        }

        return is_array($selectedStrengths) ? implode('. ', $selectedStrengths) . '.' : $selectedStrengths . '.';
    }

    private function generateImprovementAreas($employee, $baseScore)
    {
        $areas = [
            'Could improve time management skills',
            'Would benefit from additional technical training',
            'Should work on presentation and public speaking skills',
            'Could enhance project planning and organization',
            'Would benefit from leadership development opportunities',
            'Should focus on improving documentation practices',
            'Could work on being more proactive in communication'
        ];

        if ($baseScore < 75) {
            $selectedAreas = array_rand(array_flip($areas), rand(2, 3));
        } else {
            $selectedAreas = array_rand(array_flip($areas), rand(1, 2));
        }

        return is_array($selectedAreas) ? implode('. ', $selectedAreas) . '.' : $selectedAreas . '.';
    }

    private function generateGoals($employee)
    {
        $goals = [
            'Complete relevant professional certification within the next 6 months',
            'Improve performance metrics by 10-15% in the upcoming quarter',
            'Take on additional responsibilities and mentor junior team members',
            'Attend at least 2 industry conferences or training sessions',
            'Develop cross-functional skills to support team flexibility',
            'Improve client/customer satisfaction ratings',
            'Lead a major project or initiative within the department'
        ];

        $selectedGoals = array_rand(array_flip($goals), rand(2, 3));
        return is_array($selectedGoals) ? implode('. ', $selectedGoals) . '.' : $selectedGoals . '.';
    }

    private function generateDevelopmentPlan($employee, $baseScore)
    {
        $plans = [
            'Enroll in relevant online courses or professional development programs',
            'Schedule regular one-on-one meetings with supervisor for guidance',
            'Join relevant professional associations or networking groups',
            'Seek mentorship opportunities within the organization',
            'Attend workshops and seminars related to role responsibilities',
            'Read industry publications and stay updated with trends',
            'Collaborate with other departments to gain broader perspective'
        ];

        $selectedPlans = array_rand(array_flip($plans), rand(2, 3));
        return is_array($selectedPlans) ? implode('. ', $selectedPlans) . '.' : $selectedPlans . '.';
    }

    private function generateComments($employee, $baseScore)
    {
        if ($baseScore >= 90) {
            $comments = [
                'Exceptional performance throughout the review period. Consistently exceeds expectations.',
                'Outstanding contributor to the team with excellent results and positive attitude.',
                'Exemplary work quality and dedication. A valuable asset to the organization.'
            ];
        } elseif ($baseScore >= 80) {
            $comments = [
                'Solid performance with consistent results. Meets expectations regularly.',
                'Good overall performance with areas of strength and room for continued growth.',
                'Reliable team member who contributes positively to departmental goals.'
            ];
        } else {
            $comments = [
                'Shows potential but needs improvement in several key areas to meet expectations.',
                'Performance is below expectations. Focused improvement plan recommended.',
                'Requires additional support and development to reach performance targets.'
            ];
        }

        return $comments[array_rand($comments)];
    }

    private function generateStrengthsText($baseScore)
    {
        $strengths = [
            'Strong analytical and problem-solving capabilities',
            'Excellent communication and interpersonal skills',
            'Consistently meets deadlines and quality standards',
            'Shows initiative and proactive approach to work'
        ];

        return $strengths[array_rand($strengths)];
    }

    private function generateImprovementText($baseScore)
    {
        $improvements = [
            'Could benefit from additional training in specific technical areas',
            'Should focus on improving time management and prioritization',
            'Would benefit from enhanced communication in team settings',
            'Could work on developing more strategic thinking approaches'
        ];

        return $improvements[array_rand($improvements)];
    }

    private function generateDevelopmentGoalsText()
    {
        $goals = [
            'Develop leadership skills through mentoring and project management',
            'Enhance technical expertise through certification programs',
            'Improve cross-functional collaboration and communication',
            'Build strategic thinking and decision-making capabilities'
        ];

        return $goals[array_rand($goals)];
    }
}