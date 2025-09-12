// Dashboard JavaScript - Full Laravel Integration
class PerformanceDashboard {
    constructor() {
        this.currentSection = 'dashboard';
        this.charts = {};
        this.data = window.performanceData || {};
        this.routes = this.data.routes || {};
        this.csrfToken = this.data.csrfToken;
        
        this.initializeDashboard();
    }

    // Initialize the dashboard
    initializeDashboard() {
        this.initializeCharts();
        this.bindEvents();
        this.loadSectionData();
        this.showNotification('Performance Management Dashboard loaded successfully', 'success');
    }

    // Event binding
    bindEvents() {
        // Tab switching
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', (e) => this.showSection(e.target.textContent.trim().split(' ')[1].toLowerCase()));
        });

        // Dropdown changes
        const trendPeriod = document.getElementById('trendPeriod');
        if (trendPeriod) {
            trendPeriod.addEventListener('change', () => this.updateTrendChart());
        }

        const departmentFilter = document.getElementById('departmentFilter');
        if (departmentFilter) {
            departmentFilter.addEventListener('change', () => this.updateDepartmentAnalytics());
        }

        // Window resize for responsive charts
        window.addEventListener('resize', () => this.resizeCharts());
    }

    // Section management
    showSection(section) {
        // Update active tab
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        const activeTab = Array.from(document.querySelectorAll('.nav-tab')).find(tab => 
            tab.textContent.toLowerCase().includes(section)
        );
        if (activeTab) {
            activeTab.classList.add('active');
        }

        // Hide all sections
        const sections = ['dashboard', 'reports', 'performance', 'leaderboard', 'analytics'];
        sections.forEach(s => {
            const element = document.getElementById(s + 'Section');
            if (element) {
                element.classList.add('hidden');
            }
        });

        // Show selected section
        const targetSection = document.getElementById(section + 'Section');
        if (targetSection) {
            targetSection.classList.remove('hidden');
            this.currentSection = section;
        }

        // Load section-specific data
        this.loadSectionData(section);
        
        this.showNotification(`Switched to ${section} section`, 'success');
    }

    // Load data for specific sections
    async loadSectionData(section = this.currentSection) {
        switch (section) {
            case 'reports':
                await this.loadTemplates();
                break;
            case 'performance':
                await this.loadIndividualPerformance();
                break;
            case 'leaderboard':
                await this.loadLeaderboard();
                break;
            case 'analytics':
                await this.loadAnalytics();
                break;
        }
    }

    // Data refresh functionality
    async refreshData() {
        try {
            this.showNotification('Refreshing data...', 'info');
            
            const response = await fetch(this.routes.refreshData, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                // Update stats cards
                this.updateStatsCards(data.stats);
                
                // Update top performers
                this.updateTopPerformers(data.top_performers);
                
                // Update charts
                this.updateCharts();
                
                this.showNotification('Data refreshed successfully', 'success');
            } else {
                throw new Error(data.message || 'Failed to refresh data');
            }
        } catch (error) {
            console.error('Error refreshing data:', error);
            this.showNotification('Error refreshing data: ' + error.message, 'error');
        }
    }

    // Update stats cards
    updateStatsCards(stats) {
        const updates = {
            'totalEmployees': stats.total_employees,
            'avgPerformance': stats.avg_performance,
            'reportsCompleted': stats.reports_completed,
            'topPerformer': stats.top_performer
        };

        Object.entries(updates).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }

    // Update top performers list
    updateTopPerformers(performers) {
        const container = document.getElementById('topPerformersList');
        if (!container || !performers) return;

        container.innerHTML = performers.map((performer, index) => {
            const rankClass = index === 0 ? 'first' : (index === 1 ? 'second' : (index === 2 ? 'third' : 'other'));
            const scoreDisplay = index < 3 
                ? `<div class="score-badge">${performer.score}</div>`
                : `<div class="score">${performer.score}</div>`;

            return `
                <div class="leaderboard-item">
                    <div class="rank ${rankClass}">${index + 1}</div>
                    <div class="employee-info">
                        <h4>${performer.name}</h4>
                        <p>${performer.department}</p>
                    </div>
                    ${scoreDisplay}
                </div>
            `;
        }).join('');
    }

    // Load templates for reports section
    async loadTemplates() {
        try {
            const response = await fetch('/api/templates');
            const templates = await response.json();
            
            const container = document.getElementById('templatesList');
            if (!container) return;

            if (templates.length === 0) {
                container.innerHTML = '<div class="text-center">No templates found. Create your first template!</div>';
                return;
            }

            container.innerHTML = templates.map(template => `
                <div class="form-field">
                    <div class="form-field-header">
                        <div>
                            <h4 class="form-field-title">${template.name}</h4>
                            <p style="color: #64748b; font-size: 14px; margin-top: 4px;">${template.description || 'No description'}</p>
                        </div>
                        <div class="field-controls">
                            <button class="edit-btn" onclick="editTemplate(${template.id})">Edit</button>
                            <button class="delete-btn" onclick="deleteTemplate(${template.id})">Delete</button>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading templates:', error);
            this.showNotification('Error loading templates', 'error');
        }
    }

    // Load individual performance data
    async loadIndividualPerformance() {
        try {
            const departmentId = document.getElementById('departmentFilter')?.value || '';
            const url = `/api/performance/individual${departmentId ? '?department=' + departmentId : ''}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            const container = document.getElementById('individualPerformance');
            if (!container) return;

            container.innerHTML = data.employees.map(employee => `
                <div class="leaderboard-item">
                    <div class="employee-info">
                        <h4>${employee.name}</h4>
                        <p>${employee.department} • Last Review: ${new Date(employee.last_review).toLocaleDateString()}</p>
                    </div>
                    <div class="score">${employee.score}</div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading individual performance:', error);
        }
    }

    // Load leaderboard data
    async loadLeaderboard(period = 'quarter') {
        try {
            const response = await fetch(this.routes.leaderboardData + '/' + period);
            const data = await response.json();
            
            this.updateLeaderboardDisplay(data.leaderboard);
            this.updateDepartmentChampions(data.department_champions);
        } catch (error) {
            console.error('Error loading leaderboard:', error);
        }
    }

    // Update leaderboard display
    updateLeaderboardDisplay(leaderboard) {
        const container = document.getElementById('companyLeaderboard');
        if (!container || !leaderboard) return;

        container.innerHTML = leaderboard.map((employee, index) => {
            const rankClass = index === 0 ? 'first' : (index === 1 ? 'second' : (index === 2 ? 'third' : 'other'));
            const scoreDisplay = index < 3 
                ? `<div class="score-badge">${employee.score}</div>`
                : `<div class="score">${employee.score}</div>`;

            return `
                <div class="leaderboard-item">
                    <div class="rank ${rankClass}">${index + 1}</div>
                    <div class="employee-info">
                        <h4>${employee.name}</h4>
                        <p>${employee.department} • ${employee.reviews_count} reviews completed</p>
                    </div>
                    ${scoreDisplay}
                </div>
            `;
        }).join('');
    }

    // Update department champions
    updateDepartmentChampions(champions) {
        const container = document.getElementById('departmentChampions');
        if (!container || !champions) return;

        container.innerHTML = champions.map(champion => `
            <div class="recommendation-card recognition">
                <h4>${champion.department} Champion</h4>
                <p><strong>${champion.name}</strong><br>Score: ${champion.score} • ${champion.reviews_count} completed evaluations</p>
            </div>
        `).join('');
    }

    // Load analytics data
    async loadAnalytics() {
        try {
            const response = await fetch(this.routes.analyticsDistribution);
            const data = await response.json();
            
            this.updateAnalyticsStats(data.stats);
            this.updateDistributionChart(data.distribution);
            this.updateImprovementAreas(data.improvement_areas);
        } catch (error) {
            console.error('Error loading analytics:', error);
        }
    }

    // Update analytics stats
    updateAnalyticsStats(stats) {
        const container = document.getElementById('analyticsStats');
        if (!container || !stats) return;

        container.innerHTML = `
            <div class="stat-card">
                <div class="stat-number">${stats.average_rating}</div>
                <div class="stat-label">Average Rating</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${stats.goal_achievement}%</div>
                <div class="stat-label">Goal Achievement</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${stats.total_reviews}</div>
                <div class="stat-label">Total Reviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${stats.high_performers}</div>
                <div class="stat-label">High Performers</div>
            </div>
        `;
    }

    // Update improvement areas
    updateImprovementAreas(areas) {
        const container = document.getElementById('improvementAreas');
        if (!container || !areas) return;

        container.innerHTML = areas.map(area => `
            <div class="recommendation-card training">
                <h4>${area.area}</h4>
                <p>${area.percentage}% of employees need improvement</p>
            </div>
        `).join('');
    }

    // Export report functionality
    async exportReport() {
        try {
            this.showNotification('Generating export...', 'info');
            
            const response = await fetch(this.routes.exportReport);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Handle file download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'performance_report.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            
            this.showNotification('Report exported successfully', 'success');
        } catch (error) {
            console.error('Export error:', error);
            this.showNotification('Export failed: ' + error.message, 'error');
        }
    }

    // Generate recommendations
    async generateRecommendations() {
        try {
            this.showNotification('Generating AI recommendations...', 'info');
            
            const response = await fetch(this.routes.recommendations || '/api/analytics/recommendations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateRecommendations(data.recommendations);
                this.showNotification('New recommendations generated', 'success');
            } else {
                throw new Error(data.message || 'Failed to generate recommendations');
            }
        } catch (error) {
            console.error('Recommendation error:', error);
            this.showNotification('Failed to generate recommendations', 'error');
        }
    }

    // Update recommendations display
    updateRecommendations(recommendations) {
        const container = document.querySelector('.recommendations');
        if (!container || !recommendations) return;

        container.innerHTML = recommendations.map(rec => `
            <div class="recommendation-card ${rec.type}">
                <h4>${rec.title}</h4>
                <p>${rec.description}</p>
            </div>
        `).join('');
    }

    // Chart update methods
    updateTrendChart() {
        const period = document.getElementById('trendPeriod').value;
        this.showNotification(`Updated trends for ${period} months`, 'info');
        // Implementation would update the chart with new data
    }

    updateDepartmentAnalytics() {
        const department = document.getElementById('departmentFilter').value;
        const label = department ? 'Selected Department' : 'All Departments';
        this.showNotification(`Showing analytics for: ${label}`, 'info');
        this.loadIndividualPerformance();
    }

    updateLeaderboardPeriod(period) {
        this.showNotification(`Leaderboard updated for: ${period}`, 'info');
        this.loadLeaderboard(period);
    }

    // Chart initialization and management
    initializeCharts() {
        setTimeout(() => {
            this.initPerformanceChart();
            this.initDepartmentChart();
            this.initTrendChart();
            this.initDistributionChart();
        }, 100);
    }

    initPerformanceChart() {
        const canvas = document.getElementById('performanceChart');
        if (!canvas || !window.Chart) return;
        
        const ctx = canvas.getContext('2d');
        
        if (this.charts.performance) {
            this.charts.performance.destroy();
        }

        const trendsData = this.data.trends || {};
        
        this.charts.performance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendsData.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Average Performance',
                    data: trendsData.data || [85, 87, 89, 86, 88, 91],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }, {
                    label: 'Target',
                    data: Array(trendsData.labels?.length || 6).fill(90),
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 80,
                        max: 100,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            color: '#f1f5f9'
                        }
                    }
                }
            }
        });
    }

    initDepartmentChart() {
        const canvas = document.getElementById('departmentChart');
        if (!canvas || !window.Chart) return;
        
        const ctx = canvas.getContext('2d');
        
        if (this.charts.department) {
            this.charts.department.destroy();
        }

        this.charts.department = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Sales', 'Marketing', 'Development', 'HR', 'Finance'],
                datasets: [{
                    label: 'Average Score',
                    data: [89.5, 93.2, 91.8, 87.3, 85.7],
                    backgroundColor: [
                        '#ff6384',
                        '#36a2eb',
                        '#ffce56',
                        '#4bc0c0',
                        '#9966ff'
                    ],
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 80,
                        max: 100,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    initTrendChart() {
        const canvas = document.getElementById('trendChart');
        if (!canvas || !window.Chart) return;
        
        const ctx = canvas.getContext('2d');
        
        if (this.charts.trend) {
            this.charts.trend.destroy();
        }

        this.charts.trend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Q1 2023', 'Q2 2023', 'Q3 2023', 'Q4 2023', 'Q1 2024'],
                datasets: [{
                    label: 'Quarterly Trend',
                    data: [87, 89, 91, 93, 95],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 80,
                        max: 100
                    }
                }
            }
        });
    }

    initDistributionChart() {
        const canvas = document.getElementById('distributionChart');
        if (!canvas || !window.Chart) return;
        
        const ctx = canvas.getContext('2d');
        
        if (this.charts.distribution) {
            this.charts.distribution.destroy();
        }

        this.charts.distribution = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent (90+)', 'Good (80-89)', 'Satisfactory (70-79)', 'Needs Improvement (<70)'],
                datasets: [{
                    data: [32, 65, 23, 5],
                    backgroundColor: [
                        '#10b981',
                        '#3b82f6',
                        '#f59e0b',
                        '#ef4444'
                    ],
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

    updateDistributionChart(data) {
        if (!this.charts.distribution || !data) return;
        
        this.charts.distribution.data.datasets[0].data = [
            data.excellent || 0,
            data.good || 0,
            data.satisfactory || 0,
            data.needs_improvement || 0
        ];
        
        this.charts.distribution.update();
    }

    updateCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart && chart.update) {
                chart.update();
            }
        });
    }

    resizeCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart && chart.resize) {
                chart.resize();
            }
        });
    }

    // Notification system
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        const container = document.getElementById('notificationContainer') || document.body;
        container.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
}

// Global functions for backward compatibility
function refreshData() {
    if (window.dashboard) {
        window.dashboard.refreshData();
    }
}

function exportReport() {
    if (window.dashboard) {
        window.dashboard.exportReport();
    }
}

function generateRecommendations() {
    if (window.dashboard) {
        window.dashboard.generateRecommendations();
    }
}

function showSection(section) {
    if (window.dashboard) {
        window.dashboard.showSection(section);
    }
}

function updateTrendChart() {
    if (window.dashboard) {
        window.dashboard.updateTrendChart();
    }
}

function updateDepartmentAnalytics() {
    if (window.dashboard) {
        window.dashboard.updateDepartmentAnalytics();
    }
}

function updateLeaderboardPeriod(period) {
    if (window.dashboard) {
        window.dashboard.updateLeaderboardPeriod(period);
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.dashboard = new PerformanceDashboard();
    console.log('Performance Management Dashboard initialized with Laravel backend');
});