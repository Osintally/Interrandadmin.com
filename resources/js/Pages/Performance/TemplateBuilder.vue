<template>
  <div class="template-builder">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Performance Template Builder</h1>
        <p class="text-gray-600 mt-1">Create and customize performance evaluation templates</p>
      </div>
      <div class="flex space-x-3">
        <Button 
          label="Save Draft" 
          icon="pi pi-save" 
          severity="info" 
          @click="saveDraft"
          :disabled="!template.name"
        />
        <Button 
          label="Publish" 
          icon="pi pi-check" 
          severity="success" 
          @click="publishTemplate"
          :disabled="!canPublish"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Field Library -->
      <div class="lg:col-span-1">
        <Card class="h-fit">
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-th-large mr-2"></i>
              Field Library
            </div>
          </template>
          <template #content>
            <div class="space-y-2">
              <div 
                v-for="fieldType in fieldTypes" 
                :key="fieldType.type"
                class="field-type-item p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                draggable="true"
                @dragstart="onDragStart($event, fieldType)"
              >
                <div class="flex items-center">
                  <i :class="fieldType.icon" class="mr-2 text-blue-600"></i>
                  <span class="font-medium">{{ fieldType.label }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ fieldType.description }}</p>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Template Builder -->
      <div class="lg:col-span-2">
        <Card>
          <template #title>
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <i class="pi pi-file-edit mr-2"></i>
                Template Builder
              </div>
              <Button 
                label="Clear All" 
                icon="pi pi-trash" 
                severity="danger" 
                size="small"
                @click="clearTemplate"
                :disabled="!template.fields.length"
              />
            </div>
          </template>
          <template #content>
            <!-- Template Basic Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Template Name *</label>
                  <InputText 
                    v-model="template.name" 
                    placeholder="Enter template name"
                    class="w-full"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                  <Dropdown 
                    v-model="template.department_id" 
                    :options="departments" 
                    option-label="name" 
                    option-value="id"
                    placeholder="Select department"
                    class="w-full"
                  />
                </div>
              </div>
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <Textarea 
                  v-model="template.description" 
                  placeholder="Enter template description"
                  rows="3"
                  class="w-full"
                />
              </div>
            </div>

            <!-- Drag and Drop Area -->
            <div 
              class="min-h-96 border-2 border-dashed border-gray-300 rounded-lg p-4"
              :class="{ 'border-blue-400 bg-blue-50': isDragOver }"
              @drop="onDrop"
              @dragover.prevent
              @dragenter.prevent="isDragOver = true"
              @dragleave="isDragOver = false"
            >
              <div v-if="!template.fields.length" class="text-center py-12">
                <i class="pi pi-plus-circle text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Start Building Your Template</h3>
                <p class="text-gray-500">Drag fields from the library to create your performance evaluation form</p>
              </div>

              <!-- Template Fields -->
              <div v-else class="space-y-4">
                <div 
                  v-for="(field, index) in template.fields" 
                  :key="field.id"
                  class="field-item p-4 border border-gray-200 rounded-lg bg-white"
                >
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                      <i :class="getFieldTypeIcon(field.type)" class="mr-2 text-blue-600"></i>
                      <span class="font-medium">{{ field.label || `${getFieldTypeLabel(field.type)} Field` }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <Button 
                        icon="pi pi-cog" 
                        size="small" 
                        severity="secondary"
                        @click="configureField(index)"
                        v-tooltip="'Configure'"
                      />
                      <Button 
                        icon="pi pi-trash" 
                        size="small" 
                        severity="danger"
                        @click="removeField(index)"
                        v-tooltip="'Remove'"
                      />
                    </div>
                  </div>

                  <!-- Field Preview -->
                  <div class="field-preview">
                    <component 
                      :is="getFieldComponent(field.type)" 
                      :field="field" 
                      :disabled="true"
                    />
                  </div>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- KPI Configuration -->
      <div class="lg:col-span-1">
        <Card class="h-fit">
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-chart-line mr-2"></i>
              KPI Configuration
            </div>
          </template>
          <template #content>
            <div class="space-y-4">
              <div v-if="template.kpi_config && template.kpi_config.length">
                <div 
                  v-for="(kpi, index) in template.kpi_config" 
                  :key="index"
                  class="kpi-item p-3 border border-gray-200 rounded-lg"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-sm">{{ kpi.name }}</span>
                    <Button 
                      icon="pi pi-trash" 
                      size="small" 
                      severity="danger"
                      @click="removeKPI(index)"
                    />
                  </div>
                  <div class="text-xs text-gray-500">
                    Weight: {{ kpi.weight }}% | Method: {{ kpi.calculation_method }}
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <i class="pi pi-chart-line text-2xl text-gray-400 mb-2"></i>
                <p class="text-sm text-gray-500">No KPIs configured</p>
              </div>
              <Button 
                label="Add KPI" 
                icon="pi pi-plus" 
                size="small"
                class="w-full"
                @click="showKPIModal = true"
              />
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Field Configuration Modal -->
    <Dialog 
      v-model:visible="showFieldModal" 
      :header="`Configure ${selectedField?.type} Field`"
      :modal="true"
      :style="{ width: '600px' }"
    >
      <FieldConfiguration 
        v-if="selectedField"
        :field="selectedField"
        :field-index="selectedFieldIndex"
        @save="saveFieldConfiguration"
        @cancel="showFieldModal = false"
      />
    </Dialog>

    <!-- KPI Configuration Modal -->
    <Dialog 
      v-model:visible="showKPIModal" 
      header="Configure KPI"
      :modal="true"
      :style="{ width: '700px' }"
    >
      <KPIConfiguration 
        :template="template"
        @save="saveKPIConfiguration"
        @cancel="showKPIModal = false"
      />
    </Dialog>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { usePerformanceStore } from '@/Stores/performanceStore'
import type { PerformanceTemplate, FormField } from '@/Types/performance'

// Components
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Dialog from 'primevue/dialog'
import FieldConfiguration from '@/Components/Performance/FieldConfiguration.vue'
import KPIConfiguration from '@/Components/Performance/KPIConfiguration.vue'

// Composables
const router = useRouter()
const toast = useToast()
const performanceStore = usePerformanceStore()

// Reactive data
const template = ref<PerformanceTemplate>({
  id: null,
  name: '',
  description: '',
  department_id: null,
  fields: [],
  kpi_config: [],
  status: 'draft',
  created_at: null,
  updated_at: null
})

const departments = ref([])
const isDragOver = ref(false)
const showFieldModal = ref(false)
const showKPIModal = ref(false)
const selectedField = ref<FormField | null>(null)
const selectedFieldIndex = ref(-1)

// Field types configuration
const fieldTypes = [
  {
    type: 'text',
    label: 'Text Input',
    description: 'Single line text input',
    icon: 'pi pi-font'
  },
  {
    type: 'textarea',
    label: 'Text Area',
    description: 'Multi-line text input',
    icon: 'pi pi-align-left'
  },
  {
    type: 'number',
    label: 'Number Input',
    description: 'Numeric input with validation',
    icon: 'pi pi-hashtag'
  },
  {
    type: 'select',
    label: 'Dropdown',
    description: 'Single selection dropdown',
    icon: 'pi pi-list'
  },
  {
    type: 'radio',
    label: 'Radio Buttons',
    description: 'Single selection from options',
    icon: 'pi pi-circle-fill'
  },
  {
    type: 'checkbox',
    label: 'Checkboxes',
    description: 'Multiple selection options',
    icon: 'pi pi-check-square'
  },
  {
    type: 'rating',
    label: 'Rating Scale',
    description: '1-5 or 1-10 rating scale',
    icon: 'pi pi-star'
  },
  {
    type: 'slider',
    label: 'Slider',
    description: 'Range slider input',
    icon: 'pi pi-sliders-h'
  },
  {
    type: 'date',
    label: 'Date Picker',
    description: 'Date selection input',
    icon: 'pi pi-calendar'
  },
  {
    type: 'file',
    label: 'File Upload',
    description: 'File attachment input',
    icon: 'pi pi-upload'
  },
  {
    type: 'section',
    label: 'Section Header',
    description: 'Section divider with title',
    icon: 'pi pi-minus'
  }
]

// Computed properties
const canPublish = computed(() => {
  return template.value.name && 
         template.value.fields.length > 0 && 
         template.value.kpi_config.length > 0
})

// Methods
const onDragStart = (event: DragEvent, fieldType: any) => {
  if (event.dataTransfer) {
    event.dataTransfer.setData('application/json', JSON.stringify(fieldType))
  }
}

const onDrop = (event: DragEvent) => {
  event.preventDefault()
  isDragOver.value = false
  
  if (event.dataTransfer) {
    const fieldTypeData = event.dataTransfer.getData('application/json')
    if (fieldTypeData) {
      const fieldType = JSON.parse(fieldTypeData)
      addField(fieldType)
    }
  }
}

const addField = (fieldType: any) => {
  const newField: FormField = {
    id: `field_${Date.now()}`,
    type: fieldType.type,
    label: '',
    description: '',
    required: false,
    options: fieldType.type === 'select' || fieldType.type === 'radio' || fieldType.type === 'checkbox' ? [] : undefined,
    validation: {},
    kpi_config: null
  }
  
  template.value.fields.push(newField)
  toast.add({
    severity: 'success',
    summary: 'Field Added',
    detail: `${fieldType.label} field added to template`,
    life: 3000
  })
}

const removeField = (index: number) => {
  template.value.fields.splice(index, 1)
  toast.add({
    severity: 'info',
    summary: 'Field Removed',
    detail: 'Field removed from template',
    life: 3000
  })
}

const configureField = (index: number) => {
  selectedFieldIndex.value = index
  selectedField.value = { ...template.value.fields[index] }
  showFieldModal.value = true
}

const saveFieldConfiguration = (field: FormField) => {
  if (selectedFieldIndex.value >= 0) {
    template.value.fields[selectedFieldIndex.value] = { ...field }
  }
  showFieldModal.value = false
  toast.add({
    severity: 'success',
    summary: 'Field Configured',
    detail: 'Field configuration saved',
    life: 3000
  })
}

const saveKPIConfiguration = (kpiConfig: any) => {
  if (!template.value.kpi_config) {
    template.value.kpi_config = []
  }
  template.value.kpi_config.push(kpiConfig)
  showKPIModal.value = false
  toast.add({
    severity: 'success',
    summary: 'KPI Added',
    detail: 'KPI configuration added to template',
    life: 3000
  })
}

const removeKPI = (index: number) => {
  template.value.kpi_config?.splice(index, 1)
  toast.add({
    severity: 'info',
    summary: 'KPI Removed',
    detail: 'KPI removed from template',
    life: 3000
  })
}

const clearTemplate = () => {
  template.value.fields = []
  template.value.kpi_config = []
  toast.add({
    severity: 'info',
    summary: 'Template Cleared',
    detail: 'All fields and KPIs removed',
    life: 3000
  })
}


const saveDraft = async () => {
  try {
    await performanceStore.createTemplate(template.value)
    toast.add({
      severity: 'success',
      summary: 'Draft Saved',
      detail: 'Template saved as draft',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Save Failed',
      detail: 'Failed to save template draft',
      life: 5000
    })
  }
}

const publishTemplate = async () => {
  try {
    const templateToPublish = { ...template.value, status: 'active' }
    await performanceStore.createTemplate(templateToPublish)
    toast.add({
      severity: 'success',
      summary: 'Template Published',
      detail: 'Template published successfully',
      life: 3000
    })
    router.push('/performance/templates')
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Publish Failed',
      detail: 'Failed to publish template',
      life: 5000
    })
  }
}

const getFieldTypeIcon = (type: string) => {
  const fieldType = fieldTypes.find(ft => ft.type === type)
  return fieldType?.icon || 'pi pi-question'
}

const getFieldTypeLabel = (type: string) => {
  const fieldType = fieldTypes.find(ft => ft.type === type)
  return fieldType?.label || type
}

const getFieldComponent = (type: string) => {
  // This would return the appropriate Vue component for field preview
  return 'div' // Placeholder
}

// Lifecycle
onMounted(async () => {
  try {
    // Load departments for dropdown
    // departments.value = await performanceStore.getDepartments()
  } catch (error) {
    // Handle error silently or with proper logging
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load departments'
    })
  }
})
</script>

<style scoped>
.template-builder {
  @apply p-6;
}

.field-type-item {
  transition: all 0.2s ease;
}

.field-type-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.field-item {
  transition: all 0.2s ease;
}

.field-item:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.kpi-item {
  transition: all 0.2s ease;
}

.kpi-item:hover {
  background-color: #f8fafc;
}
</style>
