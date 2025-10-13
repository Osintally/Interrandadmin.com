import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import type { 
  PerformanceTemplate, 
  PerformanceReport, 
  DashboardSummary,
  KPIPreview,
  FormData 
} from '@/Types/performance';

export const usePerformanceStore = defineStore('performance', () => {
  // State
  const templates = ref<PerformanceTemplate[]>([]);
  const reports = ref<PerformanceReport[]>([]);
  const dashboardData = ref<DashboardSummary | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const kpiPreview = ref<KPIPreview[]>([]);
  const overallScore = ref<number>(0);

  // Getters
  const activeTemplates = computed(() => 
    templates.value.filter(t => t.status === 'active')
  );

  const pendingReports = computed(() => 
    reports.value.filter(r => r.status === 'draft' || r.status === 'submitted')
  );

  const completedReports = computed(() => 
    reports.value.filter(r => r.status === 'approved')
  );

  const draftReports = computed(() => 
    reports.value.filter(r => r.status === 'draft')
  );

  const submittedReports = computed(() => 
    reports.value.filter(r => r.status === 'submitted')
  );

  const approvedReports = computed(() => 
    reports.value.filter(r => r.status === 'approved')
  );

  const rejectedReports = computed(() => 
    reports.value.filter(r => r.status === 'rejected')
  );

  // Actions
  const fetchTemplates = async () => {
    loading.value = true;
    error.value = null;

    try {
      const response = await fetch('/api/performance/templates');
      const data = await response.json();
      templates.value = data;
    } catch (err) {
      error.value = 'Failed to fetch templates';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const getTemplate = async (templateId: string | number) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await fetch(`/api/performance/templates/${templateId}`);
      if (!response.ok) {
        throw new Error('Failed to fetch template');
      }
      const data = await response.json();
      return data;
    } catch (err) {
      error.value = 'Failed to fetch template';
      console.error(err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const fetchReports = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/reports');
      const data = await response.json();
      reports.value = data;
    } catch (err) {
      error.value = 'Failed to fetch reports';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const fetchMyReports = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/reports/my-reports');
      const data = await response.json();
      reports.value = data;
    } catch (err) {
      error.value = 'Failed to fetch my reports';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const createReport = async (reportData: any) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/reports', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(reportData)
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to create report');
      }
      
      const newReport = await response.json();
      reports.value.push(newReport);
      
      return newReport;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to create report';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateReport = async (reportId: number, reportData: any) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch(`/api/performance/reports/${reportId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(reportData)
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to update report');
      }
      
      const updatedReport = await response.json();
      const index = reports.value.findIndex(r => r.id === reportId);
      if (index !== -1) {
        reports.value[index] = updatedReport;
      }
      
      return updatedReport;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to update report';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const saveReportDraft = async (reportId: number, formData: FormData) => {
    try {
      const response = await fetch(`/api/performance/reports/${reportId}/save-draft`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ form_data: formData })
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to save draft');
      }
      
      const updatedReport = await response.json();
      const index = reports.value.findIndex(r => r.id === reportId);
      if (index !== -1) {
        reports.value[index] = updatedReport;
      }
      
      return updatedReport;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to save draft';
      throw err;
    }
  };

  const submitReport = async (reportId: number) => {
    try {
      const response = await fetch(`/api/performance/reports/${reportId}/submit`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to submit report');
      }
      
      const updatedReport = await response.json();
      const index = reports.value.findIndex(r => r.id === reportId);
      if (index !== -1) {
        reports.value[index] = updatedReport;
      }
      
      return updatedReport;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to submit report';
      throw err;
    }
  };

  const approveReport = async (reportId: number) => {
    try {
      const response = await fetch(`/api/performance/reports/${reportId}/approve`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to approve report');
      }
      
      const updatedReport = await response.json();
      const index = reports.value.findIndex(r => r.id === reportId);
      if (index !== -1) {
        reports.value[index] = updatedReport;
      }
      
      return updatedReport;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to approve report';
      throw err;
    }
  };

  const rejectReport = async (reportId: number, reason: string) => {
    try {
      const response = await fetch(`/api/performance/reports/${reportId}/reject`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ rejection_reason: reason })
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to reject report');
      }
      
      const updatedReport = await response.json();
      const index = reports.value.findIndex(r => r.id === reportId);
      if (index !== -1) {
        reports.value[index] = updatedReport;
      }
      
      return updatedReport;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to reject report';
      throw err;
    }
  };

  const deleteReport = async (reportId: number) => {
    try {
      const response = await fetch(`/api/performance/reports/${reportId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to delete report');
      }
      
      reports.value = reports.value.filter(r => r.id !== reportId);
      return true;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to delete report';
      throw err;
    }
  };

  const fetchDashboardData = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await fetch('/api/performance/dashboard/summary');
      const data = await response.json();
      dashboardData.value = data;
    } catch (err) {
      error.value = 'Failed to fetch dashboard data';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  const getKPIPreview = async (templateId: number, formData: FormData) => {
    try {
      const response = await fetch(`/api/performance/templates/${templateId}/kpi-preview`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ form_data: formData })
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to get KPI preview');
      }
      
      const data = await response.json();
      kpiPreview.value = data.kpi_preview;
      overallScore.value = data.overall_score;
      
      return data;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to get KPI preview';
      throw err;
    }
  };

  const clearKPIPreview = () => {
    kpiPreview.value = [];
    overallScore.value = 0;
  };

  const resetStore = () => {
    templates.value = [];
    reports.value = [];
    dashboardData.value = null;
    loading.value = false;
    error.value = null;
    kpiPreview.value = [];
    overallScore.value = 0;
  };

  return {
    // State
    templates,
    reports,
    dashboardData,
    loading,
    error,
    kpiPreview,
    overallScore,
    
    // Getters
    activeTemplates,
    pendingReports,
    completedReports,
    draftReports,
    submittedReports,
    approvedReports,
    rejectedReports,
    
    // Actions
    fetchTemplates,
    getTemplate,
    fetchReports,
    fetchMyReports,
    createReport,
    updateReport,
    saveReportDraft,
    submitReport,
    approveReport,
    rejectReport,
    deleteReport,
    fetchDashboardData,
    getKPIPreview,
    clearKPIPreview,
    resetStore
  };
});
