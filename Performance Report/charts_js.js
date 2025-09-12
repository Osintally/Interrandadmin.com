// Charts.js - Performance Management Dashboard Charts
class ChartManager {
    constructor() {
        this.charts = {};
        this.colors = {
            primary: '#667eea',
            secondary: '#764ba2',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6'
        };
        this.gradients = {};
        this.initializeGradients();
    }

    // Initialize gradient patterns
    initializeGradients() {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        // Primary gradient
        this.gradients.primary = ctx.createLinearGradient(0, 0, 0, 400);
        this.gradients.primary.addColorStop(0, 'rgba(102, 126, 234, 0.2)');
        this.gradients.primary.addColorStop(1, 'rgba(102, 126, 234, 0.05)');

        // Success gradient
        this.gradients.success = ctx.createLinearGradient(0, 0, 0, 400);
        this.gradients.success.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
        this.gradients.success.addColorStop(1, 'rgba(16, 185, 129, 0.05)');
    }

    // Destroy chart if it exists
    destroyChart(chartId) {
        if (this.charts[chartId]) {
            this.charts[chartId].destroy();
            delete this.charts[chartId];
        }
    }

    // Performance trends chart
    initPerformanceChart(canvasId = 'performanceChart', data = null) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !window.Chart) return;

        this.destroyChart(canvasId);

        const ctx = canvas.getContext('2d');
        
        // Default data if none provided
        const chartData = data || {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Average Performance',
                data: [85, 87, 89, 86, 88, 91, 89, 92, 90, 93, 91, 94],
                borderColor: this.colors.primary,
                backgroundColor: this.gradients.primary,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: this.colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }, {
                label: 'Target',
                data: Array(12).fill(90),
                borderColor: this.colors.success,
                backgroundColor: 'transparent',
                borderDash: [5, 5],
                pointRadius: 0,
                tension: 0
            }]
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 75,
                        max: 100,
                        grid: {
                            color: '#f1f5f9',
                            borderColor: '#e5e7eb'
                        },
                        ticks: {
                            color: '#6b7280',
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: '#f1f5f9',
                            borderColor: '#e5e7eb'
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });

        return this.charts[canvasId];
    }

    // Department performance chart
    initDepartmentChart(canvasId = 'departmentChart', data = null) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !window.Chart) return;

        this.destroyChart(canvasId);

        const ctx = canvas.getContext('2d');
        
        const chartData = data || {
            labels: ['Sales', 'Marketing', 'Development', 'HR', 'Finance', 'Operations'],
            datasets: [{
                label: 'Average Score',
                data: [89.5, 93.2, 91.8, 87.3, 85.7, 88.9],
                backgroundColor: [
                    '#ff6384',
                    '#36a2eb', 
                    '#ffce56',
                    '#4bc0c0',
                    '#9966ff',
                    '#ff9f40'
                ],
                borderColor: [
                    '#ff4757',
                    '#2f3640',
                    '#ffa502',
                    '#2ed573',
                    '#5f27cd',
                    '#ff6348'
                ],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                hoverBackgroundColor: [
                    '#ff4757',
                    '#2980b9',
                    '#f39c12',
                    '#16a085',
                    '#8e44ad',
                    '#e67e22'
                ]
            }]
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Score: ' + context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 80,
                        max: 100,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            color: '#6b7280',
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });

        return this.charts[canvasId];
    }

    // Trend chart (smaller version)
    initTrendChart(canvasId = 'trendChart', data = null) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !window.Chart) return;

        this.destroyChart(canvasId);

        const ctx = canvas.getContext('2d');
        
        const chartData = data || {
            labels: ['Q1 2023', 'Q2 2023', 'Q3 2023', 'Q4 2023', 'Q1 2024'],
            datasets: [{
                label: 'Quarterly Trend',
                data: [87, 89, 91, 93, 95],
                borderColor: this.colors.primary,
                backgroundColor: this.gradients.primary,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: this.colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 6,
                        padding: 8
                    }
                },
                scales: {
                    y: {
                        display: false,
                        beginAtZero: false,
                        min: 80,
                        max: 100
                    },
                    x: {
                        display: false
                    }
                },
                animation: {
                    duration: 800
                }
            }
        });

        return this.charts[canvasId];
    }

    // Performance distribution (doughnut chart)
    initDistributionChart(canvasId = 'distributionChart', data = null) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !window.Chart) return;

        this.destroyChart(canvasId);

        const ctx = canvas.getContext('2d');
        
        const chartData = data || {
            labels: ['Excellent (90+)', 'Good (80-89)', 'Satisfactory (70-79)', 'Needs Improvement (<70)'],
            datasets: [{
                data: [32, 65, 23, 5],
                backgroundColor: [
                    this.colors.success,
                    this.colors.info,
                    this.colors.warning,
                    this.colors.danger
                ],
                borderWidth: 0,
                hoverBackgroundColor: [
                    '#059669',
                    '#2563eb', 
                    '#d97706',
                    '#dc2626'
                ],
                hoverBorderWidth: 3,
                hoverBorderColor: '#fff'
            }]
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed * 100) / total).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 1000
                }
            }
        });

        return this.charts[canvasId];
    }

    // Update chart data
    updateChart(canvasId, newData) {
        if (!this.charts[canvasId]) return;

        const chart = this.charts[canvasId];
        
        if (newData.labels) {
            chart.data.labels = newData.labels;
        }
        
        if (newData.datasets) {
            newData.datasets.forEach((dataset, index) => {
                if (chart.data.datasets[index]) {
                    Object.assign(chart.data.datasets[index], dataset);
                }
            });
        }
        
        chart.update('active');
    }

    // Resize all charts
    resizeCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }

    // Initialize all charts
    initializeAll() {
        // Small delay to ensure DOM is ready
        setTimeout(() => {
            this.initPerformanceChart();
            this.initDepartmentChart(); 
            this.initTrendChart();
            this.initDistributionChart();
        }, 100);
    }

    // Destroy all charts
    destroyAll() {
        Object.keys(this.charts).forEach(chartId => {
            this.destroyChart(chartId);
        });
    }

    // Load data from API and update charts
    async loadChartData(endpoint, chartId) {
        try {
            const response = await fetch(endpoint);
            if (!response.ok) throw new Error('Failed to fetch chart data');
            
            const data = await response.json();
            this.updateChart(chartId, data);
        } catch (error) {
            console.warn('Failed to load chart data:', error);
        }
    }

    // Utility method to create animated counters
    animateNumber(element, finalValue, duration = 1000) {
        if (!element) return;
        
        const startValue = 0;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentValue = startValue + (finalValue - startValue) * easeOut;
            
            element.textContent = Math.round(currentValue * 10) / 10;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.textContent = finalValue;
            }
        };
        
        requestAnimationFrame(animate);
    }

    // Create sparkline chart (mini chart for cards)
    createSparkline(canvasId, data, color = '#667eea') {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !window.Chart) return;

        this.destroyChart(canvasId);

        const ctx = canvas.getContext('2d');
        
        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i),
                datasets: [{
                    data: data,
                    borderColor: color,
                    backgroundColor: 'transparent',
                    pointRadius: 0,
                    tension: 0.4,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                elements: {
                    point: { radius: 0 }
                },
                animation: {
                    duration: 800
                }
            }
        });

        return this.charts[canvasId];
    }
}

// Initialize chart manager when DOM is ready
let chartManager;

document.addEventListener('DOMContentLoaded', function() {
    chartManager = new ChartManager();
    
    // Initialize charts after a short delay to ensure everything is loaded
    setTimeout(() => {
        chartManager.initializeAll();
    }, 250);
});

// Handle window resize
window.addEventListener('resize', function() {
    if (chartManager) {
        chartManager.resizeCharts();
    }
});

// Export for global access
window.ChartManager = ChartManager;
window.chartManager = chartManager;