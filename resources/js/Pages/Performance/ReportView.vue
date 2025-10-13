<template>
  <div class="report-view">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Performance Report</h1>
        <p class="text-gray-600 mt-1">View performance evaluation details</p>
      </div>
      <div class="flex space-x-3">
        <Button 
          label="Export PDF" 
          icon="pi pi-file-pdf" 
          severity="secondary"
          @click="exportPDF"
        />
        <Button 
          label="Back to Reports" 
          icon="pi pi-arrow-left" 
          @click="goBack"
        />
      </div>
    </div>

    <div v-if="report" class="space-y-6">
      <!-- Report Header -->
      <Card>
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <h3 class="font-semibold text-gray-900 mb-2">Employee Information</h3>
              <p class="text-gray-600">{{ report.employee_name }}</p>
              <p class="text-sm text-gray-500">{{ report.department }}</p>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 mb-2">Template</h3>
              <p class="text-gray-600">{{ report.template_name }}</p>
              <p class="text-sm text-gray-500">{{ report.template_description }}</p>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 mb-2">Overall Score</h3>
              <div class="text-3xl font-bold text-blue-600">{{ report.overall_score }}%</div>
              <span class="status-badge" :class="getStatusClass(report.status)">
                {{ report.status }}
              </span>
            </div>
          </div>
        </template>
      </Card>

      <!-- KPI Breakdown -->
      <Card v-if="report.kpi_scores && report.kpi_scores.length">
        <template #title>
          <div class="flex items-center">
            <i class="pi pi-chart-line mr-2"></i>
            KPI Breakdown
          </div>
        </template>
        <template #content>
          <div class="space-y-4">
            <div 
              v-for="kpi in report.kpi_scores" 
              :key="kpi.id"
              class="kpi-item p-4 border border-gray-200 rounded-lg"
            >
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium text-gray-900">{{ kpi.kpi_name }}</h4>
                <span class="text-sm text-gray-500">{{ kpi.weight }}% weight</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="text-2xl font-bold text-blue-600">{{ kpi.score }}%</div>
                <div class="text-sm text-gray-600">{{ getKPICategory(kpi.score) }}</div>
              </div>
            </div>
          </div>
        </template>
      </Card>

      <!-- Detailed Responses -->
      <Card v-if="report.responses">
        <template #title>
          <div class="flex items-center">
            <i class="pi pi-file-edit mr-2"></i>
            Detailed Responses
          </div>
        </template>
        <template #content>
          <div class="space-y-4">
            <div 
              v-for="(response, fieldId) in report.responses" 
              :key="fieldId"
              class="response-item p-4 bg-gray-50 rounded-lg"
            >
              <h4 class="font-medium text-gray-900 mb-2">{{ fieldId }}</h4>
              <p class="text-gray-600">
                {{ Array.isArray(response) ? response.join(', ') : response }}
              </p>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <div v-else class="text-center py-12">
      <i class="pi pi-exclamation-triangle text-4xl text-gray-400 mb-4"></i>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Report Not Found</h3>
      <p class="text-gray-500 mb-4">The requested performance report could not be found</p>
      <Button 
        label="Back to Reports" 
        icon="pi pi-arrow-left" 
        @click="goBack"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import Button from 'primevue/button'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const report = ref(null)

const goBack = () => {
  router.push('/performance/reports')
}

const exportPDF = () => {
  toast.add({
    severity: 'info',
    summary: 'Export PDF',
    detail: 'PDF export functionality coming soon',
    life: 3000
  })
}

const getStatusClass = (status: string) => {
  const classes = {
    draft: 'status-draft',
    submitted: 'status-submitted',
    approved: 'status-approved',
    rejected: 'status-rejected'
  }
  return classes[status] || 'status-draft'
}

const getKPICategory = (score: number) => {
  if (score >= 90) return 'Excellent'
  if (score >= 80) return 'Good'
  if (score >= 70) return 'Satisfactory'
  if (score >= 60) return 'Needs Improvement'
  return 'Poor'
}

onMounted(() => {
  const reportId = route.params.id
  // Load report from API
  // report.value = await performanceStore.getReport(reportId)
  
  // Mock data for now
  report.value = {
    id: reportId,
    employee_name: 'John Doe',
    department: 'Sales',
    template_name: 'Sales Performance Evaluation',
    template_description: 'Quarterly sales performance review',
    overall_score: 85,
    status: 'approved',
    kpi_scores: [
      { id: 1, kpi_name: 'Sales Target Achievement', weight: 40, score: 90 },
      { id: 2, kpi_name: 'Customer Satisfaction', weight: 30, score: 85 },
      { id: 3, kpi_name: 'Team Collaboration', weight: 30, score: 80 }
    ],
    responses: {
      'Sales Performance': 'Exceeded quarterly targets by 15%',
      'Customer Feedback': 'Received positive feedback from 95% of clients',
      'Team Work': 'Collaborated effectively with team members'
    }
  }
})
</script>

<style scoped>
.report-view {
  @apply p-6;
}

.kpi-item {
  @apply transition-all duration-200;
}

.response-item {
  @apply transition-all duration-200;
}

.status-badge {
  @apply px-2 py-1 rounded-full text-xs font-medium;
}

.status-draft {
  @apply bg-gray-100 text-gray-800;
}

.status-submitted {
  @apply bg-yellow-100 text-yellow-800;
}

.status-approved {
  @apply bg-green-100 text-green-800;
}

.status-rejected {
  @apply bg-red-100 text-red-800;
}
</style>
