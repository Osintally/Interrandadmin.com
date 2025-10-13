<template>
  <div class="goal-list">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Performance Goals</h1>
        <p class="text-gray-600 mt-1">Track and manage performance goals</p>
      </div>
      <Button 
        label="Create Goal" 
        icon="pi pi-plus" 
        severity="success"
        @click="createGoal"
      />
    </div>

    <Card>
      <template #content>
        <div v-if="goals.length" class="space-y-4">
          <div 
            v-for="goal in goals" 
            :key="goal.id"
            class="goal-item p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ goal.kpi_name }}</h3>
                <p class="text-gray-600 mt-1">{{ goal.employee_name }}</p>
                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                  <span><i class="pi pi-target mr-1"></i>Target: {{ goal.target_score }}</span>
                  <span><i class="pi pi-chart-line mr-1"></i>Current: {{ goal.current_score }}</span>
                  <span><i class="pi pi-calendar mr-1"></i>{{ formatDate(goal.period_end) }}</span>
                </div>
                <div class="mt-3">
                  <div class="flex justify-between text-sm mb-1">
                    <span>Progress</span>
                    <span>{{ goal.achievement_percentage }}%</span>
                  </div>
                  <ProgressBar :value="goal.achievement_percentage" :show-value="false" />
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span class="status-badge" :class="getStatusClass(goal.status)">
                  {{ goal.status }}
                </span>
                <Button 
                  icon="pi pi-pencil" 
                  size="small" 
                  severity="secondary"
                  @click="editGoal(goal)"
                  v-tooltip="'Edit'"
                />
                <Button 
                  icon="pi pi-trash" 
                  size="small" 
                  severity="danger"
                  @click="deleteGoal(goal)"
                  v-tooltip="'Delete'"
                />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12">
          <i class="pi pi-target text-4xl text-gray-400 mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Goals Found</h3>
          <p class="text-gray-500 mb-4">Create your first performance goal</p>
          <Button 
            label="Create Goal" 
            icon="pi pi-plus" 
            @click="createGoal"
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
import ProgressBar from 'primevue/progressbar'

const router = useRouter()
const toast = useToast()
const goals = ref([])

const createGoal = () => {
  toast.add({
    severity: 'info',
    summary: 'Create Goal',
    detail: 'Goal creation functionality coming soon',
    life: 3000
  })
}

const editGoal = (goal: any) => {
  toast.add({
    severity: 'info',
    summary: 'Edit Goal',
    detail: 'Goal editing functionality coming soon',
    life: 3000
  })
}

const deleteGoal = (goal: any) => {
  toast.add({
    severity: 'warn',
    summary: 'Delete Goal',
    detail: 'Goal deletion functionality coming soon',
    life: 3000
  })
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const getStatusClass = (status: string) => {
  const classes = {
    active: 'status-active',
    achieved: 'status-achieved',
    missed: 'status-missed',
    cancelled: 'status-cancelled'
  }
  return classes[status] || 'status-active'
}

onMounted(() => {
  // Load goals from API
  // goals.value = await dashboardStore.getGoals()
})
</script>

<style scoped>
.goal-list {
  @apply p-6;
}

.goal-item {
  @apply transition-all duration-200;
}

.status-badge {
  @apply px-2 py-1 rounded-full text-xs font-medium;
}

.status-active {
  @apply bg-blue-100 text-blue-800;
}

.status-achieved {
  @apply bg-green-100 text-green-800;
}

.status-missed {
  @apply bg-red-100 text-red-800;
}

.status-cancelled {
  @apply bg-gray-100 text-gray-800;
}
</style>
