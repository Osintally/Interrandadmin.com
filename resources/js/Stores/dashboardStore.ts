import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { 
  DashboardSummary,
  DepartmentKPI,
  PerformanceTrend,
  TopPerformer,
  PerformanceDistribution,
  KPIBreakdown,
  DepartmentSummary,
  TeamPerformance,
  PerformanceAlert
} from '@/Types/performance';

export const useDashboardStore = defineStore('dashboard', () => {
  // State
  const summary = ref<DashboardSummary | null>(null);
  const departmentKPIs = ref<DepartmentKPI[]>([]);
  const performanceTrends = ref<PerformanceTrend[]>([]);
  const topPerformers = ref<TopPerformer[]>([]);
  const performanceDistribution = ref<PerformanceDistribution | null>(null);
  const kpiBreakdown = ref<KPIBreakdown[]>([]);
  const departmentSummary = ref<DepartmentSummary | null>(null);
  const teamPerformance = ref<TeamPerformance[]>([]);
  const alerts = ref<PerformanceAlert[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const lastUpdated = ref<Date | null>(null);

  // Getters
  const hasData = computed(() => 
    summary.value !== null || 
    departmentKPIs.value.length > 0 || 
    performanceTrends.value.length > 0
  );

  const unreadAlertsCount = computed(() => 
    alerts.value.filter(alert => !alert.is_read).length
  );

  const highPriorityAlertsCount = computed(() => 
    alerts.value.filter(alert => 
      !alert.is_read && 
      (alert.type === 'submission_overdue' || alert.type === 'goal_missed')
    ).length
  );

  const performanceTrendDirection = computed(() => {
    if (performanceTrends.value.length < 2) return 'stable';
    
    const first = performanceTrends.value[0].average_score;
    const last = performanceTrends.value[performanceTrends.value.length - 1].average_score;
    
    if (last > first + 0.1) return 'up';
    if (last < first - 0.1) return 'down';
    return 'stable';
  });

  const topPerformingDepartments = computed(() => 
    departmentKPIs.value
      .sort((a, b) => b.avg_kpi - a.avg_kpi)
      .slice(0, 5)
  );

  const departmentsNeedingAttention = computed(() => 
    departmentKPIs.value
      .filter(dept => dept.avg_kpi < 7.0)
      .sort((a, b) => a.avg_kpi - b.avg_kpi)
  );

  const topKPIs = computed(() => 
    kpiBreakdown.value
      .sort((a, b) => b.average_score - a.average_score)
      .slice(0, 5)
  );

  const bottomKPIs = computed(() => 
    kpiBreakdown.value
      .sort((a, b) => a.average_score - b.average_score)
      .slice(0, 5)
  );

  // Actions
  const fetchDashboardSummary = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/dashboard/summary');
      const data = await response.json();
      summary.value = data;
      lastUpdated.value = new Date();
    } catch (err) {
      error.value = 'Failed to fetch dashboard summary';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchDepartmentKPIs = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/dashboard/department-kpis');
      const data = await response.json();
      departmentKPIs.value = data;
    } catch (err) {
      error.value = 'Failed to fetch department KPIs';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchPerformanceTrends = async (periods: number = 6) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/dashboard/trends?periods=${periods}`);
      const data = await response.json();
      performanceTrends.value = data.trends || data;
    } catch (err) {
      error.value = 'Failed to fetch performance trends';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchTopPerformers = async (limit: number = 10) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/dashboard/top-performers?limit=${limit}`);
      const data = await response.json();
      topPerformers.value = data;
    } catch (err) {
      error.value = 'Failed to fetch top performers';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchPerformanceDistribution = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/dashboard/performance-distribution');
      const data = await response.json();
      performanceDistribution.value = data;
    } catch (err) {
      error.value = 'Failed to fetch performance distribution';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchKPIBreakdown = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/dashboard/kpi-breakdown');
      const data = await response.json();
      kpiBreakdown.value = data;
    } catch (err) {
      error.value = 'Failed to fetch KPI breakdown';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchDepartmentSummary = async (departmentId: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/dashboard/department-summary/${departmentId}`);
      const data = await response.json();
      departmentSummary.value = data;
    } catch (err) {
      error.value = 'Failed to fetch department summary';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchTeamPerformance = async (departmentId: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/dashboard/team-performance/${departmentId}`);
      const data = await response.json();
      teamPerformance.value = data;
    } catch (err) {
      error.value = 'Failed to fetch team performance';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchAlerts = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/dashboard/alerts');
      const data = await response.json();
      alerts.value = data;
    } catch (err) {
      error.value = 'Failed to fetch alerts';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const markAlertAsRead = async (alertId: number) => {
    try {
      const response = await fetch(`/api/performance/dashboard/alerts/${alertId}/read`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      if (!response.ok) {
        throw new Error('Failed to mark alert as read');
      }
      
      const index = alerts.value.findIndex(a => a.id === alertId);
      if (index !== -1) {
        alerts.value[index].is_read = true;
      }
      
      return true;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to mark alert as read';
      throw err;
    }
  };

  const refreshAllData = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      await Promise.all([
        fetchDashboardSummary(),
        fetchDepartmentKPIs(),
        fetchPerformanceTrends(),
        fetchTopPerformers(),
        fetchPerformanceDistribution(),
        fetchKPIBreakdown(),
        fetchAlerts()
      ]);
      
      lastUpdated.value = new Date();
    } catch (err) {
      error.value = 'Failed to refresh dashboard data';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const refreshManagerData = async (departmentId: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      await Promise.all([
        fetchDepartmentSummary(departmentId),
        fetchTeamPerformance(departmentId),
        fetchAlerts()
      ]);
      
      lastUpdated.value = new Date();
    } catch (err) {
      error.value = 'Failed to refresh manager dashboard data';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const exportDashboardData = async (format: 'excel' | 'pdf' = 'excel') => {
    try {
      const response = await fetch('/api/performance/dashboard/export', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ format })
      });
      
      if (!response.ok) {
        throw new Error('Failed to export dashboard data');
      }
      
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `performance-dashboard-${new Date().toISOString().split('T')[0]}.${format === 'excel' ? 'xlsx' : 'pdf'}`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
      
      return true;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to export dashboard data';
      throw err;
    }
  };

  const getChartData = (type: 'trends' | 'distribution' | 'kpi-breakdown') => {
    switch (type) {
      case 'trends':
        return {
          labels: performanceTrends.value.map(t => t.month),
          datasets: [{
            label: 'Average Score',
            data: performanceTrends.value.map(t => t.average_score),
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
          }]
        };
        
      case 'distribution':
        if (!performanceDistribution.value) return null;
        return {
          labels: ['Excellent', 'Good', 'Satisfactory', 'Needs Improvement'],
          datasets: [{
            data: [
              performanceDistribution.value.excellent,
              performanceDistribution.value.good,
              performanceDistribution.value.satisfactory,
              performanceDistribution.value.needs_improvement
            ],
            backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444']
          }]
        };
        
      case 'kpi-breakdown':
        return {
          labels: kpiBreakdown.value.map(k => k.kpi_name),
          datasets: [{
            label: 'Average Score',
            data: kpiBreakdown.value.map(k => k.average_score),
            backgroundColor: '#3B82F6'
          }]
        };
        
      default:
        return null;
    }
  };

  const resetStore = () => {
    summary.value = null;
    departmentKPIs.value = [];
    performanceTrends.value = [];
    topPerformers.value = [];
    performanceDistribution.value = null;
    kpiBreakdown.value = [];
    departmentSummary.value = null;
    teamPerformance.value = [];
    alerts.value = [];
    loading.value = false;
    error.value = null;
    lastUpdated.value = null;
  };

  return {
    // State
    summary,
    departmentKPIs,
    performanceTrends,
    topPerformers,
    performanceDistribution,
    kpiBreakdown,
    departmentSummary,
    teamPerformance,
    alerts,
    loading,
    error,
    lastUpdated,
    
    // Getters
    hasData,
    unreadAlertsCount,
    highPriorityAlertsCount,
    performanceTrendDirection,
    topPerformingDepartments,
    departmentsNeedingAttention,
    topKPIs,
    bottomKPIs,
    
    // Actions
    fetchDashboardSummary,
    fetchDepartmentKPIs,
    fetchPerformanceTrends,
    fetchTopPerformers,
    fetchPerformanceDistribution,
    fetchKPIBreakdown,
    fetchDepartmentSummary,
    fetchTeamPerformance,
    fetchAlerts,
    markAlertAsRead,
    refreshAllData,
    refreshManagerData,
    exportDashboardData,
    getChartData,
    resetStore
  };
});
