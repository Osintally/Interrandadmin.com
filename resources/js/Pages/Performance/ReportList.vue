<template>
  <div class="report-list">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Performance Reports</h1>
        <p class="text-gray-600 mt-1">View and manage performance evaluation reports</p>
      </div>
      <Button 
        label="Create Report" 
        icon="pi pi-plus" 
        severity="success"
        @click="createReport"
      />
    </div>

    <Card>
      <template #content>
        <div v-if="reports.length" class="space-y-4">
          <div 
            v-for="report in reports" 
            :key="report.id"
            class="report-item p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ report.employee_name }}</h3>
                <p class="text-gray-600 mt-1">{{ report.template_name }}</p>
                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                  <span><i class="pi pi-star mr-1"></i>{{ report.overall_score }}% Score</span>
                  <span><i class="pi pi-clock mr-1"></i>{{ formatDate(report.submitted_at) }}</span>
                  <span class="status-badge" :class="getStatusClass(report.status)">
                    {{ report.status }}
                  </span>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <Button 
                  icon="pi pi-eye" 
                  size="small" 
                  severity="info"
                  @click="viewReport(report)"
                  v-tooltip="'View'"
                />
                <Button 
                  icon="pi pi-download" 
                  size="small" 
                  severity="secondary"
                  @click="exportReport(report)"
                  v-tooltip="'Export'"
                />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12">
          <i class="pi pi-file text-4xl text-gray-400 mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Reports Found</h3>
          <p class="text-gray-500 mb-4">Create your first performance evaluation report</p>
          <Button 
            label="Create Report" 
            icon="pi pi-plus" 
            @click="createReport"
          />
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import Button from 'primevue/button'

const router = useRouter()
const toast = useToast()
const reports = ref([])

const createReport = () => {
  router.push('/performance/reports/create')
}

const viewReport = (report: any) => {
  router.push(`/performance/reports/${report.id}`)
}

const exportReport = (report: any) => {
  toast.add({
    severity: 'info',
    summary: 'Export',
    detail: 'Report export functionality coming soon',
    life: 3000
  })
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
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

onMounted(() => {
  // Load reports from API
  // reports.value = await performanceStore.getReports()
})
</script>

<style scoped>
.report-list {
  @apply p-6;
}

.report-item {
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
