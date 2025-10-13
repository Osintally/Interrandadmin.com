<template>
  <div class="template-list">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Performance Templates</h1>
        <p class="text-gray-600 mt-1">Manage your performance evaluation templates</p>
      </div>
      <Button 
        label="Create Template" 
        icon="pi pi-plus" 
        severity="success"
        @click="createTemplate"
      />
    </div>

    <Card>
      <template #content>
        <div v-if="templates.length" class="space-y-4">
          <div 
            v-for="template in templates" 
            :key="template.id"
            class="template-item p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ template.name }}</h3>
                <p class="text-gray-600 mt-1">{{ template.description }}</p>
                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                  <span><i class="pi pi-file mr-1"></i>{{ template.fields?.length || 0 }} Fields</span>
                  <span><i class="pi pi-chart-line mr-1"></i>{{ template.kpi_config?.length || 0 }} KPIs</span>
                  <span><i class="pi pi-clock mr-1"></i>{{ template.status }}</span>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <Button 
                  icon="pi pi-eye" 
                  size="small" 
                  severity="info"
                  @click="previewTemplate(template)"
                  v-tooltip="'Preview'"
                />
                <Button 
                  icon="pi pi-pencil" 
                  size="small" 
                  severity="secondary"
                  @click="editTemplate(template)"
                  v-tooltip="'Edit'"
                />
                <Button 
                  icon="pi pi-trash" 
                  size="small" 
                  severity="danger"
                  @click="deleteTemplate(template)"
                  v-tooltip="'Delete'"
                />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12">
          <i class="pi pi-file-edit text-4xl text-gray-400 mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Templates Found</h3>
          <p class="text-gray-500 mb-4">Create your first performance evaluation template</p>
          <Button 
            label="Create Template" 
            icon="pi pi-plus" 
            @click="createTemplate"
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
const templates = ref([])

const createTemplate = () => {
  router.push('/performance/templates/create')
}

const editTemplate = (template: any) => {
  router.push(`/performance/templates/${template.id}/edit`)
}

const previewTemplate = (template: any) => {
  // Implement preview functionality
  toast.add({
    severity: 'info',
    summary: 'Preview',
    detail: 'Template preview functionality coming soon',
    life: 3000
  })
}

const deleteTemplate = (template: any) => {
  // Implement delete functionality
  toast.add({
    severity: 'warn',
    summary: 'Delete',
    detail: 'Template deletion functionality coming soon',
    life: 3000
  })
}

onMounted(() => {
  // Load templates from API
  // templates.value = await performanceStore.getTemplates()
})
</script>

<style scoped>
.template-list {
  @apply p-6;
}

.template-item {
  @apply transition-all duration-200;
}
</style>
