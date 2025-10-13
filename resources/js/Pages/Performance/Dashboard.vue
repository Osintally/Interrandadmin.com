<template>
  <div class="performance-dashboard">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Performance Dashboard</h1>
        <p class="text-gray-600 mt-1">Monitor and analyze performance metrics</p>
      </div>
      <div class="flex space-x-3">
        <Button 
          label="Export Report" 
          icon="pi pi-download" 
          severity="secondary"
          @click="exportReport"
        />
        <Button 
          label="Refresh" 
          icon="pi pi-refresh" 
          @click="refreshDashboard"
          :loading="loading"
        />
      </div>
    </div>

    <!-- Dashboard Tabs -->
    <TabView v-model:activeIndex="activeTab" class="dashboard-tabs">
      <!-- Overview Tab -->
      <TabPanel header="Overview">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Summary Cards -->
          <Card class="summary-card">
            <template #content>
              <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                  <i class="pi pi-users text-2xl text-blue-600"></i>
                </div>
                <div class="ml-4">
                  <h3 class="text-2xl font-bold text-gray-900">{{ summary.totalEmployees }}</h3>
                  <p class="text-gray-600">Total Employees</p>
                </div>
              </div>
            </template>
          </Card>

          <Card class="summary-card">
            <template #content>
              <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                  <i class="pi pi-check-circle text-2xl text-green-600"></i>
                </div>
                <div class="ml-4">
                  <h3 class="text-2xl font-bold text-gray-900">{{ summary.completedReviews }}</h3>
                  <p class="text-gray-600">Completed Reviews</p>
                </div>
              </div>
            </template>
          </Card>

          <Card class="summary-card">
            <template #content>
              <div class="p-3 bg-yellow-100 rounded-full">
                <i class="pi pi-clock text-2xl text-yellow-600"></i>
              </div>
              <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900">{{ summary.pendingReviews }}</h3>
                <p class="text-gray-600">Pending Reviews</p>
              </div>
            </template>
          </Card>

          <Card class="summary-card">
            <template #content>
              <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                  <i class="pi pi-chart-line text-2xl text-purple-600"></i>
                </div>
                <div class="ml-4">
                  <h3 class="text-2xl font-bold text-gray-900">{{ summary.averageScore }}%</h3>
                  <p class="text-gray-600">Average Score</p>
                </div>
              </div>
            </template>
          </Card>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Performance Trend Chart -->
          <Card>
            <template #title>
              <div class="flex items-center">
                <i class="pi pi-chart-line mr-2"></i>
                Performance Trend
              </div>
            </template>
            <template #content>
              <div class="h-80">
                <apexchart
                  type="line"
                  height="100%"
                  :options="trendChartOptions"
                  :series="trendChartSeries"
                />
              </div>
            </template>
          </Card>

          <!-- Department Performance -->
          <Card>
            <template #title>
              <div class="flex items-center">
                <i class="pi pi-chart-bar mr-2"></i>
                Department Performance
              </div>
            </template>
            <template #content>
              <div class="h-80">
                <apexchart
                  type="bar"
                  height="100%"
                  :options="departmentChartOptions"
                  :series="departmentChartSeries"
                />
              </div>
            </template>
          </Card>
        </div>

        <!-- Recent Activity -->
        <Card>
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-history mr-2"></i>
              Recent Activity
            </div>
          </template>
          <template #content>
            <div class="space-y-4">
              <div 
                v-for="activity in recentActivity" 
                :key="activity.id"
                class="flex items-center p-3 border border-gray-200 rounded-lg"
              >
                <div class="p-2 bg-blue-100 rounded-full mr-3">
                  <i :class="getActivityIcon(activity.type)" class="text-blue-600"></i>
                </div>
                <div class="flex-1">
                  <p class="font-medium text-gray-900">{{ activity.description }}</p>
                  <p class="text-sm text-gray-500">{{ formatDate(activity.created_at) }}</p>
                </div>
                <div class="text-sm text-gray-500">
                  {{ activity.user_name }}
                </div>
              </div>
            </div>
          </template>
        </Card>
      </TabPanel>

      <!-- KPIs Tab -->
      <TabPanel header="KPIs">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <Card 
            v-for="kpi in kpis" 
            :key="kpi.id"
            class="kpi-card"
          >
            <template #title>
              <div class="flex items-center justify-between">
                <h3 class="font-semibold">{{ kpi.name }}</h3>
                <div class="flex items-center space-x-2">
                  <span class="text-sm text-gray-500">{{ kpi.weight }}%</span>
                  <Button 
                    icon="pi pi-cog" 
                    size="small" 
                    severity="secondary"
                    @click="configureKPI(kpi)"
                  />
                </div>
              </div>
            </template>
            <template #content>
              <div class="space-y-4">
                <div class="text-center">
                  <div class="text-3xl font-bold text-gray-900">{{ kpi.currentValue }}</div>
                  <div class="text-sm text-gray-500">Current Value</div>
                </div>
                
                <div class="space-y-2">
                  <div class="flex justify-between text-sm">
                    <span>Target</span>
                    <span class="font-medium">{{ kpi.targetValue }}</span>
                  </div>
                  <ProgressBar 
                    :value="kpi.achievementPercentage" 
                    :show-value="false"
                  />
                  <div class="text-center text-sm text-gray-600">
                    {{ kpi.achievementPercentage }}% achieved
                  </div>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                  <div class="p-2 bg-green-50 rounded">
                    <div class="font-medium text-green-700">{{ kpi.excellentThreshold }}</div>
                    <div class="text-green-600">Excellent</div>
                  </div>
                  <div class="p-2 bg-blue-50 rounded">
                    <div class="font-medium text-blue-700">{{ kpi.goodThreshold }}</div>
                    <div class="text-blue-600">Good</div>
                  </div>
                  <div class="p-2 bg-yellow-50 rounded">
                    <div class="font-medium text-yellow-700">{{ kpi.needsImprovementThreshold }}</div>
                    <div class="text-yellow-600">Needs Improvement</div>
                  </div>
                </div>
              </div>
            </template>
          </Card>
        </div>
      </TabPanel>

      <!-- Goals Tab -->
      <TabPanel header="Goals">
        <div class="space-y-6">
          <!-- Goal Filters -->
          <Card>
            <template #content>
              <div class="flex flex-wrap gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                  <Dropdown 
                    v-model="goalFilters.status" 
                    :options="goalStatusOptions" 
                    option-label="label" 
                    option-value="value"
                    placeholder="All Statuses"
                    class="w-40"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                  <Dropdown 
                    v-model="goalFilters.employee" 
                    :options="employees" 
                    option-label="name" 
                    option-value="id"
                    placeholder="All Employees"
                    class="w-48"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                  <Dropdown 
                    v-model="goalFilters.period" 
                    :options="periodOptions" 
                    option-label="label" 
                    option-value="value"
                    placeholder="All Periods"
                    class="w-40"
                  />
                </div>
                <div class="flex items-end">
                  <Button 
                    label="Apply Filters" 
                    @click="applyGoalFilters"
                    :loading="loading"
                  />
                </div>
              </div>
            </template>
          </Card>

          <!-- Goals List -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card 
              v-for="goal in filteredGoals" 
              :key="goal.id"
              class="goal-card"
            >
              <template #title>
                <div class="flex items-center justify-between">
                  <h3 class="font-semibold">{{ goal.kpi_name }}</h3>
                  <Tag 
                    :value="goal.status" 
                    :severity="getGoalStatusSeverity(goal.status)"
                  />
                </div>
              </template>
              <template #content>
                <div class="space-y-4">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Employee:</span>
                    <span class="font-medium">{{ goal.employee_name }}</span>
                  </div>
                  
                  <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Target Score:</span>
                      <span class="font-medium">{{ goal.target_score }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Current Score:</span>
                      <span class="font-medium">{{ goal.current_score }}</span>
                    </div>
                    <ProgressBar 
                      :value="goal.achievement_percentage" 
                      :show-value="false"
                    />
                    <div class="text-center text-sm text-gray-600">
                      {{ goal.achievement_percentage }}% achieved
                    </div>
                  </div>

                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Period:</span>
                    <span class="font-medium">
                      {{ formatDate(goal.period_start) }} - {{ formatDate(goal.period_end) }}
                    </span>
                  </div>
                </div>
              </template>
            </Card>
          </div>
        </div>
      </TabPanel>

      <!-- Alerts Tab -->
      <TabPanel header="Alerts">
        <div class="space-y-4">
          <div 
            v-for="alert in alerts" 
            :key="alert.id"
            class="flex items-center p-4 border border-gray-200 rounded-lg"
            :class="getAlertClass(alert.type)"
          >
            <div class="p-2 rounded-full mr-4" :class="getAlertIconClass(alert.type)">
              <i :class="getAlertIcon(alert.type)" class="text-white"></i>
            </div>
            <div class="flex-1">
              <h4 class="font-medium text-gray-900">{{ alert.message }}</h4>
              <p class="text-sm text-gray-600 mt-1">
                {{ alert.employee_name }} • {{ formatDate(alert.triggered_at) }}
              </p>
            </div>
            <div class="flex items-center space-x-2">
              <Button 
                v-if="!alert.is_read"
                label="Mark as Read" 
                size="small"
                @click="markAlertAsRead(alert.id)"
              />
              <Button 
                icon="pi pi-times" 
                size="small" 
                severity="secondary"
                @click="dismissAlert(alert.id)"
              />
            </div>
          </div>
        </div>
      </TabPanel>
    </TabView>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useDashboardStore } from '@/Stores/dashboardStore'
import { useKPIStore } from '@/Stores/kpiStore'
import type { DashboardSummary, PerformanceGoal, PerformanceAlert } from '@/Types/performance'

// Components
import Card from 'primevue/card'
import Button from 'primevue/button'
import TabView from 'primevue/tabview'
import TabPanel from 'primevue/tabpanel'
import Dropdown from 'primevue/dropdown'
import ProgressBar from 'primevue/progressbar'
import Tag from 'primevue/tag'
import apexchart from 'vue3-apexcharts'

// Composables
const toast = useToast()
const dashboardStore = useDashboardStore()
const kpiStore = useKPIStore()

// Reactive data
const activeTab = ref(0)
const loading = ref(false)
const summary = ref<DashboardSummary>({
  totalEmployees: 0,
  completedReviews: 0,
  pendingReviews: 0,
  averageScore: 0
})

const recentActivity = ref([])
const kpis = ref([])
const goals = ref<PerformanceGoal[]>([])
const alerts = ref<PerformanceAlert[]>([])

const goalFilters = ref({
  status: null,
  employee: null,
  period: null
})

const employees = ref([])

// Options
const goalStatusOptions = [
  { label: 'All Statuses', value: null },
  { label: 'Active', value: 'active' },
  { label: 'Achieved', value: 'achieved' },
  { label: 'Missed', value: 'missed' },
  { label: 'Cancelled', value: 'cancelled' }
]

const periodOptions = [
  { label: 'All Periods', value: null },
  { label: 'Current Month', value: 'current_month' },
  { label: 'Last Month', value: 'last_month' },
  { label: 'Current Quarter', value: 'current_quarter' },
  { label: 'Last Quarter', value: 'last_quarter' }
]

// Chart configurations
const trendChartOptions = ref({
  chart: {
    type: 'line',
    toolbar: { show: false }
  },
  xaxis: {
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
  },
  yaxis: {
    title: { text: 'Performance Score' }
  },
  colors: ['#3B82F6']
})

const trendChartSeries = ref([
  {
    name: 'Average Performance',
    data: [75, 78, 82, 80, 85, 88]
  }
])

const departmentChartOptions = ref({
  chart: {
    type: 'bar',
    toolbar: { show: false }
  },
  xaxis: {
    categories: ['Sales', 'Marketing', 'HR', 'IT', 'Finance']
  },
  yaxis: {
    title: { text: 'Average Score' }
  },
  colors: ['#10B981']
})

const departmentChartSeries = ref([
  {
    name: 'Department Performance',
    data: [85, 78, 92, 88, 75]
  }
])

// Computed properties
const filteredGoals = computed(() => {
  let filtered = goals.value

  if (goalFilters.value.status) {
    filtered = filtered.filter(goal => goal.status === goalFilters.value.status)
  }

  if (goalFilters.value.employee) {
    filtered = filtered.filter(goal => goal.employee_id === goalFilters.value.employee)
  }

  if (goalFilters.value.period) {
    // Add period filtering logic here
  }

  return filtered
})

// Methods
const refreshDashboard = async () => {
  loading.value = true
  try {
    await Promise.all([
      loadDashboardSummary(),
      loadRecentActivity(),
      loadKPIs(),
      loadGoals(),
      loadAlerts()
    ])
    toast.add({
      severity: 'success',
      summary: 'Dashboard Refreshed',
      detail: 'All data has been updated',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Refresh Failed',
      detail: 'Failed to refresh dashboard data',
      life: 5000
    })
  } finally {
    loading.value = false
  }
}

const loadDashboardSummary = async () => {
  try {
    summary.value = await dashboardStore.getDashboardSummary()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load dashboard summary'
    })
  }
}

const loadRecentActivity = async () => {
  try {
    recentActivity.value = await dashboardStore.getRecentActivity()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load recent activity'
    })
  }
}

const loadKPIs = async () => {
  try {
    kpis.value = await kpiStore.getKPIs()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load KPIs'
    })
  }
}

const loadGoals = async () => {
  try {
    goals.value = await dashboardStore.getGoals()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load goals'
    })
  }
}

const loadAlerts = async () => {
  try {
    alerts.value = await dashboardStore.getAlerts()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load alerts'
    })
  }
}

const exportReport = () => {
  // Implement export functionality
  toast.add({
    severity: 'info',
    summary: 'Export Started',
    detail: 'Report export has been initiated',
    life: 3000
  })
}

const configureKPI = (kpi: any) => {
  // Navigate to KPI configuration
  console.log('Configure KPI:', kpi)
}

const applyGoalFilters = () => {
  // Filters are applied automatically via computed property
  toast.add({
    severity: 'info',
    summary: 'Filters Applied',
    detail: 'Goal filters have been applied',
    life: 3000
  })
}

const markAlertAsRead = async (alertId: number) => {
  try {
    await dashboardStore.markAlertAsRead(alertId)
    const alert = alerts.value.find(a => a.id === alertId)
    if (alert) {
      alert.is_read = true
    }
    toast.add({
      severity: 'success',
      summary: 'Alert Marked as Read',
      detail: 'Alert has been marked as read',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Failed to Mark Alert',
      detail: 'Failed to mark alert as read',
      life: 5000
    })
  }
}

const dismissAlert = async (alertId: number) => {
  try {
    await dashboardStore.dismissAlert(alertId)
    alerts.value = alerts.value.filter(a => a.id !== alertId)
    toast.add({
      severity: 'success',
      summary: 'Alert Dismissed',
      detail: 'Alert has been dismissed',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Failed to Dismiss Alert',
      detail: 'Failed to dismiss alert',
      life: 5000
    })
  }
}

// Utility functions
const getActivityIcon = (type: string) => {
  const icons = {
    review_submitted: 'pi pi-check',
    review_approved: 'pi pi-thumbs-up',
    goal_achieved: 'pi pi-trophy',
    alert_triggered: 'pi pi-exclamation-triangle'
  }
  return icons[type] || 'pi pi-info-circle'
}

const getGoalStatusSeverity = (status: string) => {
  const severities = {
    active: 'info',
    achieved: 'success',
    missed: 'danger',
    cancelled: 'secondary'
  }
  return severities[status] || 'info'
}

const getAlertClass = (type: string) => {
  const classes = {
    threshold_breach: 'border-red-200 bg-red-50',
    goal_missed: 'border-yellow-200 bg-yellow-50',
    review_due: 'border-blue-200 bg-blue-50',
    submission_overdue: 'border-orange-200 bg-orange-50'
  }
  return classes[type] || 'border-gray-200 bg-gray-50'
}

const getAlertIconClass = (type: string) => {
  const classes = {
    threshold_breach: 'bg-red-500',
    goal_missed: 'bg-yellow-500',
    review_due: 'bg-blue-500',
    submission_overdue: 'bg-orange-500'
  }
  return classes[type] || 'bg-gray-500'
}

const getAlertIcon = (type: string) => {
  const icons = {
    threshold_breach: 'pi pi-exclamation-triangle',
    goal_missed: 'pi pi-times-circle',
    review_due: 'pi pi-clock',
    submission_overdue: 'pi pi-calendar-times'
  }
  return icons[type] || 'pi pi-info-circle'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

// Lifecycle
onMounted(() => {
  refreshDashboard()
})
</script>

<style scoped>
.performance-dashboard {
  @apply p-6;
}

.summary-card {
  @apply transition-all duration-200 hover:shadow-lg;
}

.kpi-card {
  @apply transition-all duration-200 hover:shadow-lg;
}

.goal-card {
  @apply transition-all duration-200 hover:shadow-lg;
}

.dashboard-tabs {
  @apply mt-6;
}
</style>
