<!-- Analytics Dashboard with Interactive Charts -->
<div class="analytics-dashboard">
    <!-- Analytics Header -->
    <div class="analytics-header">
        <div class="header-controls">
            <div class="date-range-picker">
                <input type="date" id="analyticsStartDate" class="form-input" value="{{ date('Y-m-01') }}">
                <span>to</span>
                <input type="date" id="analyticsEndDate" class="form-input" value="{{ date('Y-m-d') }}">
                <button class="btn btn-primary" id="updateAnalyticsBtn">
                    <i class="fas fa-sync"></i>
                    Update
                </button>
            </div>
            <div class="export-controls">
                <button class="btn btn-secondary" id="exportAnalyticsBtn">
                    <i class="fas fa-download"></i>
                    Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Analytics Grid -->
    <div class="analytics-grid">
        <!-- Attendance Trends Chart -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Attendance Trends
                </h3>
                <div class="chart-controls">
                    <select id="trendsGroupBy" class="form-input">
                        <option value="day">Daily</option>
                        <option value="week">Weekly</option>
                        <option value="month">Monthly</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <canvas id="attendanceTrendsChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Performance Metrics Chart -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i>
                    Performance Metrics
                </h3>
            </div>
            <div class="card-body">
                <canvas id="performanceChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Punctuality Analysis -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i>
                    Punctuality Analysis
                </h3>
            </div>
            <div class="card-body">
                <canvas id="punctualityChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Department Comparison -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i>
                    Department Comparison
                </h3>
            </div>
            <div class="card-body">
                <canvas id="departmentChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trophy"></i>
                    Top Performers
                </h3>
            </div>
            <div class="card-body">
                <div class="performers-list" id="topPerformersList">
                    <!-- Dynamic content loaded here -->
                </div>
            </div>
        </div>

        <!-- Absence Patterns -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-times"></i>
                    Absence Patterns
                </h3>
            </div>
            <div class="card-body">
                <canvas id="absenceChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Real-time Stats -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tachometer-alt"></i>
                    Real-time Statistics
                </h3>
            </div>
            <div class="card-body">
                <div class="realtime-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="todayPresent">0</div>
                        <div class="stat-label">Present Today</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="todayLate">0</div>
                        <div class="stat-label">Late Today</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="todayAbsent">0</div>
                        <div class="stat-label">Absent Today</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="avgHoursToday">0h</div>
                        <div class="stat-label">Avg Hours</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Impact -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-money-bill-wave"></i>
                    Payroll Impact Analysis
                </h3>
            </div>
            <div class="card-body">
                <canvas id="payrollImpactChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<style>
.analytics-dashboard {
    padding: 0;
}

.analytics-header {
    background: var(--surface);
    padding: 1.5rem;
    border-radius: var(--radius);
    margin-bottom: 2rem;
    box-shadow: var(--shadow);
}

.header-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.date-range-picker {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.date-range-picker span {
    font-weight: 500;
    color: var(--text-secondary);
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

.chart-card {
    background: var(--surface);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.chart-card .card-header {
    background: var(--surface-light);
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-card .card-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-card .card-title i {
    color: var(--primary);
}

.chart-controls select {
    min-width: 120px;
}

.chart-card .card-body {
    padding: 1.5rem;
}

.performers-list {
    max-height: 300px;
    overflow-y: auto;
}

.performer-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-light);
}

.performer-item:last-child {
    border-bottom: none;
}

.performer-name {
    font-weight: 600;
    color: var(--text);
}

.performer-score {
    background: var(--primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.realtime-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: var(--surface-light);
    border-radius: var(--radius);
}

.stat-item .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.stat-item .stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    font-weight: 500;
}

@media (max-width: 768px) {
    .header-controls {
        flex-direction: column;
        gap: 1rem;
    }
    
    .date-range-picker {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .analytics-grid {
        grid-template-columns: 1fr;
    }
    
    .realtime-stats {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<script>
// Analytics Chart Management
class AnalyticsCharts {
    constructor() {
        this.charts = {};
        this.colors = {
            primary: '#3b82f6',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#06b6d4',
            secondary: '#6b7280'
        };
        
        this.init();
    }
    
    init() {
        this.initEventListeners();
        this.loadAnalyticsData();
    }
    
    initEventListeners() {
        document.getElementById('updateAnalyticsBtn')?.addEventListener('click', () => {
            this.loadAnalyticsData();
        });
        
        document.getElementById('trendsGroupBy')?.addEventListener('change', () => {
            this.updateTrendsChart();
        });
        
        document.getElementById('exportAnalyticsBtn')?.addEventListener('click', () => {
            this.exportAnalyticsReport();
        });
    }
    
    async loadAnalyticsData() {
        try {
            const startDate = document.getElementById('analyticsStartDate').value;
            const endDate = document.getElementById('analyticsEndDate').value;
            
            // Mock data for demonstration - replace with actual API calls
            const mockData = this.generateMockData(startDate, endDate);
            
            this.renderAllCharts(mockData);
            this.updateRealTimeStats(mockData);
            this.updateTopPerformers(mockData);
            
        } catch (error) {
            console.error('Error loading analytics data:', error);
            showNotification('Error loading analytics data', 'error');
        }
    }
    
    generateMockData(startDate, endDate) {
        // Generate comprehensive mock data for charts
        const days = Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24));
        
        return {
            trends: this.generateTrendData(days),
            performance: this.generatePerformanceData(),
            punctuality: this.generatePunctualityData(),
            departments: this.generateDepartmentData(),
            performers: this.generatePerformersData(),
            absence: this.generateAbsenceData(),
            realtime: this.generateRealtimeData(),
            payrollImpact: this.generatePayrollData()
        };
    }
    
    generateTrendData(days) {
        const labels = [];
        const present = [];
        const absent = [];
        const late = [];
        
        for (let i = 0; i < Math.min(days, 30); i++) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            labels.unshift(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            
            present.unshift(Math.floor(Math.random() * 50 + 150));
            absent.unshift(Math.floor(Math.random() * 10 + 5));
            late.unshift(Math.floor(Math.random() * 20 + 10));
        }
        
        return { labels, present, absent, late };
    }
    
    generatePerformanceData() {
        return {
            labels: ['Excellent', 'Good', 'Average', 'Poor'],
            data: [35, 45, 15, 5],
            colors: [this.colors.success, this.colors.primary, this.colors.warning, this.colors.danger]
        };
    }
    
    generatePunctualityData() {
        return {
            labels: ['On Time', 'Low Late', 'Late', 'Very Late'],
            data: [70, 15, 10, 5],
            colors: [this.colors.success, this.colors.info, this.colors.warning, this.colors.danger]
        };
    }
    
    generateDepartmentData() {
        return {
            labels: ['IT', 'Sales', 'Marketing', 'Finance', 'HR'],
            attendance: [95, 88, 92, 85, 90],
            colors: [this.colors.primary, this.colors.success, this.colors.info, this.colors.warning, this.colors.secondary]
        };
    }
    
    generatePerformersData() {
        return [
            { name: 'John Doe', score: 98, department: 'IT' },
            { name: 'Jane Smith', score: 96, department: 'Sales' },
            { name: 'Mike Johnson', score: 94, department: 'Marketing' },
            { name: 'Sarah Wilson', score: 92, department: 'Finance' },
            { name: 'Tom Brown', score: 90, department: 'HR' }
        ];
    }
    
    generateAbsenceData() {
        return {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            data: [5, 3, 4, 6, 8],
            colors: [this.colors.danger]
        };
    }
    
    generateRealtimeData() {
        return {
            present: 185,
            late: 12,
            absent: 8,
            avgHours: 7.5
        };
    }
    
    generatePayrollData() {
        return {
            labels: ['Base Pay', 'Overtime', 'Deductions', 'Net Pay'],
            data: [100000, 15000, -8000, 107000],
            colors: [this.colors.primary, this.colors.success, this.colors.danger, this.colors.info]
        };
    }
    
    renderAllCharts(data) {
        this.renderTrendsChart(data.trends);
        this.renderPerformanceChart(data.performance);
        this.renderPunctualityChart(data.punctuality);
        this.renderDepartmentChart(data.departments);
        this.renderAbsenceChart(data.absence);
        this.renderPayrollChart(data.payrollImpact);
    }
    
    renderTrendsChart(data) {
        const ctx = document.getElementById('attendanceTrendsChart');
        if (!ctx) return;
        
        if (this.charts.trends) {
            this.charts.trends.destroy();
        }
        
        this.charts.trends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Present',
                        data: data.present,
                        borderColor: this.colors.success,
                        backgroundColor: this.colors.success + '20',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Late',
                        data: data.late,
                        borderColor: this.colors.warning,
                        backgroundColor: this.colors.warning + '20',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Absent',
                        data: data.absent,
                        borderColor: this.colors.danger,
                        backgroundColor: this.colors.danger + '20',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e5e7eb'
                        }
                    },
                    x: {
                        grid: {
                            color: '#e5e7eb'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }
    
    renderPerformanceChart(data) {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) return;
        
        if (this.charts.performance) {
            this.charts.performance.destroy();
        }
        
        this.charts.performance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: data.colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    renderPunctualityChart(data) {
        const ctx = document.getElementById('punctualityChart');
        if (!ctx) return;
        
        if (this.charts.punctuality) {
            this.charts.punctuality.destroy();
        }
        
        this.charts.punctuality = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: data.colors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    renderDepartmentChart(data) {
        const ctx = document.getElementById('departmentChart');
        if (!ctx) return;
        
        if (this.charts.department) {
            this.charts.department.destroy();
        }
        
        this.charts.department = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: data.attendance,
                    backgroundColor: data.colors,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: '#e5e7eb'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    renderAbsenceChart(data) {
        const ctx = document.getElementById('absenceChart');
        if (!ctx) return;
        
        if (this.charts.absence) {
            this.charts.absence.destroy();
        }
        
        this.charts.absence = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Absences',
                    data: data.data,
                    backgroundColor: this.colors.danger,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e5e7eb'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    renderPayrollChart(data) {
        const ctx = document.getElementById('payrollImpactChart');
        if (!ctx) return;
        
        if (this.charts.payroll) {
            this.charts.payroll.destroy();
        }
        
        this.charts.payroll = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Amount (₦)',
                    data: data.data,
                    backgroundColor: data.colors,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        grid: {
                            color: '#e5e7eb'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    updateRealTimeStats(data) {
        const stats = data.realtime;
        
        document.getElementById('todayPresent').textContent = stats.present;
        document.getElementById('todayLate').textContent = stats.late;
        document.getElementById('todayAbsent').textContent = stats.absent;
        document.getElementById('avgHoursToday').textContent = stats.avgHours + 'h';
    }
    
    updateTopPerformers(data) {
        const container = document.getElementById('topPerformersList');
        if (!container) return;
        
        container.innerHTML = data.performers.map(performer => `
            <div class="performer-item">
                <div>
                    <div class="performer-name">${performer.name}</div>
                    <div style="font-size: 0.875rem; color: var(--text-muted);">${performer.department}</div>
                </div>
                <div class="performer-score">${performer.score}%</div>
            </div>
        `).join('');
    }
    
    exportAnalyticsReport() {
        // Generate and download analytics report
        const reportData = {
            period: {
                start: document.getElementById('analyticsStartDate').value,
                end: document.getElementById('analyticsEndDate').value
            },
            generated: new Date().toISOString(),
            // Add more report data here
        };
        
        const blob = new Blob([JSON.stringify(reportData, null, 2)], {
            type: 'application/json'
        });
        
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `attendance-analytics-${new Date().toISOString().slice(0, 10)}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
        showNotification('Analytics report exported successfully!', 'success');
    }
}

// Initialize analytics when the view becomes active
document.addEventListener('DOMContentLoaded', () => {
    // Initialize when analytics tab is shown
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const analyticsPanel = document.getElementById('analytics-panel');
                if (analyticsPanel && analyticsPanel.classList.contains('active')) {
                    if (!window.analyticsCharts) {
                        window.analyticsCharts = new AnalyticsCharts();
                    }
                }
            }
        });
    });
    
    const analyticsPanel = document.getElementById('analytics-panel');
    if (analyticsPanel) {
        observer.observe(analyticsPanel, { attributes: true });
    }
});
</script>