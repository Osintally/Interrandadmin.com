<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Report - {{ $report->employee->first_name }} {{ $report->employee->surname }}</title>
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
        .employee-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .employee-info h3 {
            margin: 0 0 10px 0;
            color: #366092;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .score-summary {
            background-color: #e8f4fd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .overall-score {
            font-size: 48px;
            font-weight: bold;
            color: #366092;
            margin: 10px 0;
        }
        .score-status {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        .kpi-section {
            margin-bottom: 30px;
        }
        .kpi-section h3 {
            color: #366092;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .kpi-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .kpi-name {
            font-weight: bold;
        }
        .kpi-score {
            color: #366092;
            font-weight: bold;
        }
        .responses-section {
            margin-bottom: 30px;
        }
        .responses-section h3 {
            color: #366092;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .response-item {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .response-question {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        .response-answer {
            color: #333;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-submitted {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-draft {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Performance Evaluation Report</h1>
        <p>Generated on {{ $generated_at->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="employee-info">
        <h3>Employee Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $report->employee->first_name }} {{ $report->employee->surname }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Department:</span>
                <span class="info-value">{{ $report->employee->department->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Template:</span>
                <span class="info-value">{{ $report->template->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $report->status }}">
                        {{ ucfirst($report->status) }}
                    </span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Submitted:</span>
                <span class="info-value">{{ $report->submitted_at ? $report->submitted_at->format('F j, Y') : 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Reviewed by:</span>
                <span class="info-value">{{ $report->reviewer ? $report->reviewer->first_name . ' ' . $report->reviewer->surname : 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="score-summary">
        <h3>Overall Performance Score</h3>
        <div class="overall-score">{{ number_format($report->overall_score, 1) }}%</div>
        <div class="score-status">
            @if($report->overall_score >= 90)
                Excellent Performance
            @elseif($report->overall_score >= 80)
                Good Performance
            @elseif($report->overall_score >= 70)
                Satisfactory Performance
            @elseif($report->overall_score >= 60)
                Needs Improvement
            @else
                Poor Performance
            @endif
        </div>
    </div>

    @if($kpiScores->count() > 0)
    <div class="kpi-section">
        <h3>KPI Breakdown</h3>
        @foreach($kpiScores as $kpiScore)
        <div class="kpi-item">
            <div>
                <div class="kpi-name">{{ $kpiScore->kpi->name }}</div>
                <div style="font-size: 12px; color: #666;">Weight: {{ $kpiScore->kpi->weight }}%</div>
            </div>
            <div class="kpi-score">{{ number_format($kpiScore->score, 1) }}%</div>
        </div>
        @endforeach
    </div>
    @endif

    @if($report->responses)
    <div class="responses-section">
        <h3>Detailed Responses</h3>
        @foreach($report->responses as $fieldId => $response)
        <div class="response-item">
            <div class="response-question">{{ $fieldId }}</div>
            <div class="response-answer">
                @if(is_array($response))
                    {{ implode(', ', $response) }}
                @else
                    {{ $response }}
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($report->reviewer_notes)
    <div class="responses-section">
        <h3>Reviewer Notes</h3>
        <div class="response-item">
            <div class="response-answer">{{ $report->reviewer_notes }}</div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Performance Management System</p>
        <p>For questions or concerns, please contact your HR department</p>
    </div>
</body>
</html>
