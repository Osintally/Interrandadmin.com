<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Dashboard Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #366092;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #366092;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            border-left: 4px solid #366092;
        }
        .summary-number {
            font-size: 32px;
            font-weight: bold;
            color: #366092;
            margin: 10px 0;
        }
        .summary-label {
            color: #666;
            font-size: 14px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h3 {
            color: #366092;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .kpi-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #10B981;
        }
        .kpi-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .kpi-details {
            font-size: 12px;
            color: #666;
        }
        .goals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .goals-table th,
        .goals-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .goals-table th {
            background-color: #366092;
            color: white;
            font-weight: bold;
        }
        .goals-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-achieved {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-missed {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Performance Dashboard Summary</h1>
        <p>Generated on {{ $generated_at->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-number">{{ $summary['total_employees'] }}</div>
            <div class="summary-label">Total Employees</div>
        </div>
        <div class="summary-card">
            <div class="summary-number">{{ $summary['completed_reviews'] }}</div>
            <div class="summary-label">Completed Reviews</div>
        </div>
        <div class="summary-card">
            <div class="summary-number">{{ $summary['pending_reviews'] }}</div>
            <div class="summary-label">Pending Reviews</div>
        </div>
        <div class="summary-card">
            <div class="summary-number">{{ number_format($summary['average_score'], 1) }}%</div>
            <div class="summary-label">Average Score</div>
        </div>
    </div>

    @if($kpis->count() > 0)
    <div class="section">
        <h3>KPI Performance Overview</h3>
        <div class="kpi-grid">
            @foreach($kpis as $kpi)
            <div class="kpi-item">
                <div class="kpi-name">{{ $kpi->name }}</div>
                <div class="kpi-details">
                    <div>Department: {{ $kpi->department->name ?? 'All Departments' }}</div>
                    <div>Weight: {{ $kpi->weight }}%</div>
                    <div>Target: {{ $kpi->target_value ?? 'N/A' }}</div>
                    <div>Method: {{ ucfirst(str_replace('_', ' ', $kpi->calculation_method)) }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($goals->count() > 0)
    <div class="section">
        <h3>Performance Goals</h3>
        <table class="goals-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>KPI</th>
                    <th>Target</th>
                    <th>Current</th>
                    <th>Achievement</th>
                    <th>Status</th>
                    <th>Period</th>
                </tr>
            </thead>
            <tbody>
                @foreach($goals as $goal)
                <tr>
                    <td>{{ $goal->employee->first_name }} {{ $goal->employee->surname }}</td>
                    <td>{{ $goal->kpi->name }}</td>
                    <td>{{ $goal->target_score }}</td>
                    <td>{{ $goal->current_score }}</td>
                    <td>{{ number_format($goal->achievement_percentage, 1) }}%</td>
                    <td>
                        <span class="status-badge status-{{ $goal->status }}">
                            {{ ucfirst($goal->status) }}
                        </span>
                    </td>
                    <td>{{ $goal->period_start->format('M j') }} - {{ $goal->period_end->format('M j, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>This dashboard summary was generated automatically by the Performance Management System</p>
        <p>For detailed analysis, please access the online dashboard</p>
    </div>
</body>
</html>
