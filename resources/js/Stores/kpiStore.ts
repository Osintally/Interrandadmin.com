import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { 
  PerformanceKPI, 
  PerformanceGoal, 
  PerformanceAlert,
  KPIStatistics,
  KPIAnalytics
} from '@/Types/performance';

export const useKPIStore = defineStore('kpi', () => {
  // State
  const kpis = ref<PerformanceKPI[]>([]);
  const goals = ref<PerformanceGoal[]>([]);
  const alerts = ref<PerformanceAlert[]>([]);
  const statistics = ref<KPIStatistics | null>(null);
  const analytics = ref<KPIAnalytics | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Getters
  const activeKPIs = computed(() => 
    kpis.value.filter(kpi => kpi.target_value !== null)
  );

  const departmentKPIs = computed(() => (departmentId: number) => 
    kpis.value.filter(kpi => kpi.department_id === departmentId)
  );

  const activeGoals = computed(() => 
    goals.value.filter(goal => goal.status === 'active')
  );

  const achievedGoals = computed(() => 
    goals.value.filter(goal => goal.status === 'achieved')
  );

  const missedGoals = computed(() => 
    goals.value.filter(goal => goal.status === 'missed')
  );

  const unreadAlerts = computed(() => 
    alerts.value.filter(alert => !alert.is_read)
  );

  const highPriorityAlerts = computed(() => 
    alerts.value.filter(alert => 
      alert.type === 'submission_overdue' || alert.type === 'goal_missed'
    )
  );

  // Actions
  const fetchKPIs = async (departmentId?: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const url = departmentId 
        ? `/api/performance/kpis?department_id=${departmentId}`
        : '/api/performance/kpis';
        
      const response = await fetch(url);
      const data = await response.json();
      kpis.value = data;
    } catch (err) {
      error.value = 'Failed to fetch KPIs';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const createKPI = async (kpiData: any) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/kpis', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(kpiData)
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to create KPI');
      }
      
      const newKPI = await response.json();
      kpis.value.push(newKPI);
      
      return newKPI;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to create KPI';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateKPI = async (kpiId: number, kpiData: any) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/kpis/${kpiId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(kpiData)
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to update KPI');
      }
      
      const updatedKPI = await response.json();
      const index = kpis.value.findIndex(k => k.id === kpiId);
      if (index !== -1) {
        kpis.value[index] = updatedKPI;
      }
      
      return updatedKPI;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to update KPI';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const deleteKPI = async (kpiId: number) => {
    try {
      const response = await fetch(`/api/performance/kpis/${kpiId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to delete KPI');
      }
      
      kpis.value = kpis.value.filter(k => k.id !== kpiId);
      return true;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to delete KPI';
      throw err;
    }
  };

  const fetchGoals = async (employeeId?: number, kpiId?: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      let url = '/api/performance/goals';
      const params = new URLSearchParams();
      
      if (employeeId) params.append('employee_id', employeeId.toString());
      if (kpiId) params.append('kpi_id', kpiId.toString());
      
      if (params.toString()) {
        url += `?${params.toString()}`;
      }
      
      const response = await fetch(url);
      const data = await response.json();
      goals.value = data;
    } catch (err) {
      error.value = 'Failed to fetch goals';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const setGoals = async (kpiId: number, goalsData: any[]) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/kpis/${kpiId}/goals`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ goals: goalsData })
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to set goals');
      }
      
      const result = await response.json();
      return result;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to set goals';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateGoal = async (goalId: number, goalData: any) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/goals/${goalId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(goalData)
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to update goal');
      }
      
      const updatedGoal = await response.json();
      const index = goals.value.findIndex(g => g.id === goalId);
      if (index !== -1) {
        goals.value[index] = updatedGoal;
      }
      
      return updatedGoal;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to update goal';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const deleteGoal = async (goalId: number) => {
    try {
      const response = await fetch(`/api/performance/goals/${goalId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to delete goal');
      }
      
      goals.value = goals.value.filter(g => g.id !== goalId);
      return true;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to delete goal';
      throw err;
    }
  };

  const fetchAlerts = async (employeeId?: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const url = employeeId 
        ? `/api/performance/alerts?employee_id=${employeeId}`
        : '/api/performance/alerts';
        
      const response = await fetch(url);
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
      const response = await fetch(`/api/performance/alerts/${alertId}/read`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to mark alert as read');
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

  const resolveAlert = async (alertId: number) => {
    try {
      const response = await fetch(`/api/performance/alerts/${alertId}/resolve`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to resolve alert');
      }
      
      const index = alerts.value.findIndex(a => a.id === alertId);
      if (index !== -1) {
        alerts.value[index].resolved_at = new Date().toISOString();
      }
      
      return true;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to resolve alert';
      throw err;
    }
  };

  const fetchKPIStatistics = async (kpiId: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/kpis/${kpiId}/statistics`);
      const data = await response.json();
      statistics.value = data;
    } catch (err) {
      error.value = 'Failed to fetch KPI statistics';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchKPIAnalytics = async (kpiId: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/kpis/${kpiId}/analytics`);
      const data = await response.json();
      analytics.value = data;
    } catch (err) {
      error.value = 'Failed to fetch KPI analytics';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchKPIPerformanceData = async (kpiId: number, period: string = '3months') => {
    try {
      const response = await fetch(`/api/performance/kpis/${kpiId}/performance-data?period=${period}`);
      const data = await response.json();
      return data;
    } catch (err) {
      error.value = 'Failed to fetch KPI performance data';
      throw err;
    }
  };

  const getKPIById = (id: number) => {
    return kpis.value.find(kpi => kpi.id === id);
  };

  const getGoalById = (id: number) => {
    return goals.value.find(goal => goal.id === id);
  };

  const getAlertById = (id: number) => {
    return alerts.value.find(alert => alert.id === id);
  };

  const resetStore = () => {
    kpis.value = [];
    goals.value = [];
    alerts.value = [];
    statistics.value = null;
    analytics.value = null;
    loading.value = false;
    error.value = null;
  };

  return {
    // State
    kpis,
    goals,
    alerts,
    statistics,
    analytics,
    loading,
    error,
    
    // Getters
    activeKPIs,
    departmentKPIs,
    activeGoals,
    achievedGoals,
    missedGoals,
    unreadAlerts,
    highPriorityAlerts,
    
    // Actions
    fetchKPIs,
    createKPI,
    updateKPI,
    deleteKPI,
    fetchGoals,
    setGoals,
    updateGoal,
    deleteGoal,
    fetchAlerts,
    markAlertAsRead,
    resolveAlert,
    fetchKPIStatistics,
    fetchKPIAnalytics,
    fetchKPIPerformanceData,
    getKPIById,
    getGoalById,
    getAlertById,
    resetStore
  };
});
