<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Performance\PerformanceKPI;
use App\Models\Performance\PerformanceGoal;
use App\Models\Performance\PerformanceAlert;
use App\Models\Performance\KPIThreshold;
use App\Category;
use App\User;
use Carbon\Carbon;

class PerformanceManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first business
        $business = \App\Business::first();
        if (!$business) {
            $this->command->warn('No business found. Please create a business first.');
            return;
        }

        // Get some users first
        $users = User::take(5)->get();
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please create some users first.');
            return;
        }

        $firstUser = $users->first();

        // Get or create departments
        $salesDept = Category::firstOrCreate([
            'name' => 'Sales',
            'category_type' => 'department',
            'business_id' => $business->id,
            'created_by' => $firstUser->id
        ]);

        $marketingDept = Category::firstOrCreate([
            'name' => 'Marketing',
            'category_type' => 'department',
            'business_id' => $business->id,
            'created_by' => $firstUser->id
        ]);

        $hrDept = Category::firstOrCreate([
            'name' => 'Human Resources',
            'category_type' => 'department',
            'business_id' => $business->id,
            'created_by' => $firstUser->id
        ]);


        // Create sample KPIs
        $kpis = [
            [
                'name' => 'Sales Revenue',
                'description' => 'Monthly sales revenue target',
                'department_id' => $salesDept->id,
                'weight' => 8.5,
                'target_value' => 100000.00,
                'calculation_method' => 'direct_score',
                'scoring_formula' => null,
            ],
            [
                'name' => 'Customer Satisfaction',
                'description' => 'Customer satisfaction rating',
                'department_id' => $salesDept->id,
                'weight' => 7.0,
                'target_value' => 4.5,
                'calculation_method' => 'direct_score',
                'scoring_formula' => null,
            ],
            [
                'name' => 'Lead Generation',
                'description' => 'Number of qualified leads generated',
                'department_id' => $marketingDept->id,
                'weight' => 6.5,
                'target_value' => 50.00,
                'calculation_method' => 'direct_score',
                'scoring_formula' => null,
            ],
            [
                'name' => 'Employee Retention',
                'description' => 'Employee retention rate percentage',
                'department_id' => $hrDept->id,
                'weight' => 9.0,
                'target_value' => 85.00,
                'calculation_method' => 'percentage',
                'scoring_formula' => null,
            ],
            [
                'name' => 'Project Completion Rate',
                'description' => 'Percentage of projects completed on time',
                'department_id' => null, // Company-wide KPI
                'weight' => 8.0,
                'target_value' => 90.00,
                'calculation_method' => 'percentage',
                'scoring_formula' => null,
            ],
        ];

        $createdKpis = [];
        foreach ($kpis as $kpiData) {
            $kpi = PerformanceKPI::create($kpiData);
            $createdKpis[] = $kpi;

            // Create thresholds for each KPI
            KPIThreshold::create([
                'kpi_id' => $kpi->id,
                'excellent_min' => $kpi->target_value * 1.1,
                'good_min' => $kpi->target_value * 0.9,
                'needs_improvement_min' => $kpi->target_value * 0.7,
            ]);
        }

        // Create sample performance goals
        foreach ($users as $user) {
            foreach ($createdKpis as $kpi) {
                // Create goals for some users (not all)
                if (rand(1, 3) == 1) { // 33% chance
                    $targetScore = $kpi->target_value * (0.8 + (rand(0, 40) / 100)); // 80-120% of target
                    $currentScore = $targetScore * (0.3 + (rand(0, 70) / 100)); // 30-100% of target
                    
                    PerformanceGoal::create([
                        'employee_id' => $user->id,
                        'kpi_id' => $kpi->id,
                        'target_score' => $targetScore,
                        'current_score' => $currentScore,
                        'achievement_percentage' => ($currentScore / $targetScore) * 100,
                        'period_start' => Carbon::now()->subMonths(2),
                        'period_end' => Carbon::now()->addMonths(1),
                        'status' => $currentScore >= $targetScore ? 'achieved' : 'active',
                    ]);
                }
            }
        }

        // Create sample performance alerts
        $alertTypes = ['threshold_breach', 'goal_missed', 'review_due', 'submission_overdue'];
        $alertMessages = [
            'threshold_breach' => 'Performance has fallen below the minimum threshold',
            'goal_missed' => 'Monthly goal target was not achieved',
            'review_due' => 'Performance review is due for completion',
            'submission_overdue' => 'Performance data submission is overdue',
        ];

        foreach ($users as $user) {
            // Create 1-3 alerts per user
            $alertCount = rand(1, 3);
            for ($i = 0; $i < $alertCount; $i++) {
                $alertType = $alertTypes[array_rand($alertTypes)];
                $kpi = $createdKpis[array_rand($createdKpis)];
                
                PerformanceAlert::create([
                    'type' => $alertType,
                    'title' => ucfirst(str_replace('_', ' ', $alertType)),
                    'employee_id' => $user->id,
                    'kpi_id' => $kpi->id,
                    'goal_id' => null,
                    'threshold_type' => $alertType === 'threshold_breach' ? 'needs_improvement' : null,
                    'message' => $alertMessages[$alertType],
                    'priority' => $alertType === 'submission_overdue' ? 'high' : ($alertType === 'goal_missed' ? 'medium' : 'low'),
                    'status' => rand(1, 3) == 1 ? 'resolved' : 'active',
                    'is_read' => rand(1, 2) == 1,
                    'triggered_at' => Carbon::now()->subDays(rand(1, 30)),
                    'resolved_at' => rand(1, 3) == 1 ? Carbon::now()->subDays(rand(1, 10)) : null,
                ]);
            }
        }

        $this->command->info('Performance Management sample data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . count($createdKpis) . ' KPIs');
        $this->command->info('- ' . PerformanceGoal::count() . ' Performance Goals');
        $this->command->info('- ' . PerformanceAlert::count() . ' Performance Alerts');
        $this->command->info('- ' . KPIThreshold::count() . ' KPI Thresholds');
    }
}