<template>
  <div class="kpi-list">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">KPI Management</h1>
        <p class="text-gray-600 mt-1">Configure and manage Key Performance Indicators</p>
      </div>
      <Button 
        label="Create KPI" 
        icon="pi pi-plus" 
        severity="success"
        @click="createKPI"
      />
    </div>

    <Card>
      <template #content>
        <div v-if="kpis.length" class="space-y-4">
          <div 
            v-for="kpi in kpis" 
            :key="kpi.id"
            class="kpi-item p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ kpi.name }}</h3>
                <p class="text-gray-600 mt-1">{{ kpi.description }}</p>
                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                  <span><i class="pi pi-building mr-1"></i>{{ kpi.department_name || 'All Departments' }}</span>
                  <span><i class="pi pi-percentage mr-1"></i>{{ kpi.weight }}% Weight</span>
                  <span><i class="pi pi-cog mr-1"></i>{{ kpi.calculation_method }}</span>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <Button 
                  icon="pi pi-chart-line" 
                  size="small" 
                  severity="info"
                  @click="viewKPIAnalytics(kpi)"
                  v-tooltip="'Analytics'"
                />
                <Button 
                  icon="pi pi-pencil" 
                  size="small" 
                  severity="secondary"
                  @click="editKPI(kpi)"
                  v-tooltip="'Edit'"
                />
                <Button 
                  icon="pi pi-trash" 
                  size="small" 
                  severity="danger"
                  @click="deleteKPI(kpi)"
                  v-tooltip="'Delete'"
                />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12">
          <i class="pi pi-chart-line text-4xl text-gray-400 mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No KPIs Found</h3>
          <p class="text-gray-500 mb-4">Create your first Key Performance Indicator</p>
          <Button 
            label="Create KPI" 
            icon="pi pi-plus" 
            @click="createKPI"
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
const kpis = ref([])

const createKPI = () => {
  toast.add({
    severity: 'info',
    summary: 'Create KPI',
    detail: 'KPI creation functionality coming soon',
    life: 3000
  })
}

const editKPI = (kpi: any) => {
  toast.add({
    severity: 'info',
    summary: 'Edit KPI',
    detail: 'KPI editing functionality coming soon',
    life: 3000
  })
}

const viewKPIAnalytics = (kpi: any) => {
  toast.add({
    severity: 'info',
    summary: 'KPI Analytics',
    detail: 'KPI analytics functionality coming soon',
    life: 3000
  })
}

const deleteKPI = (kpi: any) => {
  toast.add({
    severity: 'warn',
    summary: 'Delete KPI',
    detail: 'KPI deletion functionality coming soon',
    life: 3000
  })
}

onMounted(() => {
  // Load KPIs from API
  // kpis.value = await kpiStore.getKPIs()
})
</script>

<style scoped>
.kpi-list {
  @apply p-6;
}

.kpi-item {
  @apply transition-all duration-200;
}
</style>
