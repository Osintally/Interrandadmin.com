<template>
  <div class="performance-report-form">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ template.name }}</h1>
        <p class="text-gray-600 mt-1">{{ template.description }}</p>
      </div>
      <div class="flex space-x-3">
        <Button 
          label="Save Draft" 
          icon="pi pi-save" 
          severity="secondary"
          @click="saveDraft"
          :loading="saving"
        />
        <Button 
          label="Submit Report" 
          icon="pi pi-check" 
          severity="success"
          @click="submitReport"
          :loading="submitting"
          :disabled="!isFormValid"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Form Section -->
      <div class="lg:col-span-3">
        <Card>
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-file-edit mr-2"></i>
              Performance Evaluation Form
            </div>
          </template>
          <template #content>
            <form @submit.prevent="submitReport" class="space-y-6">
              <div 
                v-for="(field, index) in template.fields" 
                :key="field.id"
                class="form-field"
              >
                <!-- Section Header -->
                <div v-if="field.type === 'section'" class="section-header">
                  <h3 class="text-lg font-semibold text-gray-900">{{ field.label }}</h3>
                  <p v-if="field.description" class="text-gray-600 mt-1">{{ field.description }}</p>
                </div>

                <!-- Form Fields -->
                <div v-else class="field-input">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ field.label }}
                    <span v-if="field.required" class="text-red-500 ml-1">*</span>
                  </label>
                  
                  <p v-if="field.description" class="text-sm text-gray-500 mb-3">{{ field.description }}</p>

                  <!-- Text Input -->
                  <InputText 
                    v-if="field.type === 'text'"
                    v-model="formData[field.id]"
                    :placeholder="`Enter ${field.label.toLowerCase()}`"
                    class="w-full"
                    :class="{ 'p-invalid': errors[field.id] }"
                  />

                  <!-- Textarea -->
                  <Textarea 
                    v-else-if="field.type === 'textarea'"
                    v-model="formData[field.id]"
                    :placeholder="`Enter ${field.label.toLowerCase()}`"
                    rows="4"
                    class="w-full"
                    :class="{ 'p-invalid': errors[field.id] }"
                  />

                  <!-- Number Input -->
                  <InputNumber 
                    v-else-if="field.type === 'number'"
                    v-model="formData[field.id]"
                    :placeholder="`Enter ${field.label.toLowerCase()}`"
                    class="w-full"
                    :class="{ 'p-invalid': errors[field.id] }"
                    :min="field.validation?.min"
                    :max="field.validation?.max"
                  />

                  <!-- Select Dropdown -->
                  <Dropdown 
                    v-else-if="field.type === 'select'"
                    v-model="formData[field.id]"
                    :options="field.options || []"
                    placeholder="Select an option"
                    class="w-full"
                    :class="{ 'p-invalid': errors[field.id] }"
                  />

                  <!-- Radio Buttons -->
                  <div v-else-if="field.type === 'radio'" class="space-y-2">
                    <div 
                      v-for="option in field.options" 
                      :key="option"
                      class="flex items-center"
                    >
                      <RadioButton 
                        v-model="formData[field.id]"
                        :value="option" 
                        :input-id="`${field.id}_${option}`"
                      />
                      <label :for="`${field.id}_${option}`" class="ml-2 text-sm text-gray-700">
                        {{ option }}
                      </label>
                    </div>
                  </div>

                  <!-- Checkboxes -->
                  <div v-else-if="field.type === 'checkbox'" class="space-y-2">
                    <div 
                      v-for="option in field.options" 
                      :key="option"
                      class="flex items-center"
                    >
                      <Checkbox 
                        v-model="formData[field.id]"
                        :value="option" 
                        :input-id="`${field.id}_${option}`"
                      />
                      <label :for="`${field.id}_${option}`" class="ml-2 text-sm text-gray-700">
                        {{ option }}
                      </label>
                    </div>
                  </div>

                  <!-- Rating Scale -->
                  <div v-else-if="field.type === 'rating'" class="rating-input">
                    <div class="flex space-x-1">
                      <i 
                        v-for="i in 5" 
                        :key="i"
                        :class="[
                          'pi pi-star text-3xl cursor-pointer transition-colors',
                          i <= (formData[field.id] || 0) ? 'text-yellow-400' : 'text-gray-300'
                        ]"
                        @click="formData[field.id] = i"
                      ></i>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                      <span>Poor</span>
                      <span>Excellent</span>
                    </div>
                    <div class="text-center text-sm text-gray-600 mt-2">
                      {{ formData[field.id] || 0 }} / 5
                    </div>
                  </div>

                  <!-- Slider -->
                  <div v-else-if="field.type === 'slider'" class="slider-input">
                    <Slider 
                      v-model="formData[field.id]"
                      :min="0" 
                      :max="100" 
                      class="w-full"
                    />
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                      <span>0</span>
                      <span class="font-medium">{{ formData[field.id] || 0 }}</span>
                      <span>100</span>
                    </div>
                  </div>

                  <!-- Date Picker -->
                  <Calendar 
                    v-else-if="field.type === 'date'"
                    v-model="formData[field.id]"
                    placeholder="Select date"
                    class="w-full"
                    :class="{ 'p-invalid': errors[field.id] }"
                  />

                  <!-- File Upload -->
                  <div v-else-if="field.type === 'file'" class="file-upload">
                    <FileUpload
                      mode="basic"
                      :auto="true"
                      :multiple="false"
                      :max-file-size="5000000"
                      accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                      :custom-upload="true"
                      @uploader="handleFileUpload($event, field.id)"
                      class="w-full"
                    />
                    <div v-if="uploadedFiles[field.id]" class="mt-2 p-2 bg-green-50 rounded-lg">
                      <div class="flex items-center text-sm text-green-700">
                        <i class="pi pi-check-circle mr-2"></i>
                        {{ uploadedFiles[field.id].name }}
                      </div>
                    </div>
                  </div>

                  <!-- Field Error -->
                  <small v-if="errors[field.id]" class="p-error">
                    {{ errors[field.id] }}
                  </small>

                  <!-- KPI Preview -->
                  <div v-if="field.kpi_config && formData[field.id]" class="mt-3 p-3 bg-blue-50 rounded-lg">
                    <div class="flex items-center text-sm text-blue-700">
                      <i class="pi pi-chart-line mr-2"></i>
                      <span class="font-medium">KPI Preview:</span>
                      <span class="ml-2">{{ calculateKPIPreview(field) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="flex justify-end space-x-3 pt-6 border-t">
                <Button 
                  label="Save Draft" 
                  icon="pi pi-save" 
                  severity="secondary"
                  @click="saveDraft"
                  :loading="saving"
                />
                <Button 
                  label="Submit Report" 
                  icon="pi pi-check" 
                  severity="success"
                  type="submit"
                  :loading="submitting"
                  :disabled="!isFormValid"
                />
              </div>
            </form>
          </template>
        </Card>
      </div>

      <!-- KPI Preview Sidebar -->
      <div class="lg:col-span-1">
        <Card class="h-fit">
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-chart-line mr-2"></i>
              KPI Preview
            </div>
          </template>
          <template #content>
            <div v-if="kpiPreview.length" class="space-y-4">
              <div 
                v-for="kpi in kpiPreview" 
                :key="kpi.name"
                class="kpi-preview-item"
              >
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium text-sm">{{ kpi.name }}</span>
                  <span class="text-xs text-gray-500">{{ kpi.weight }}%</span>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ kpi.score }}</div>
                <div class="text-xs text-gray-500">{{ kpi.status }}</div>
                <ProgressBar 
                  :value="kpi.percentage" 
                  :show-value="false"
                  class="mt-2"
                />
              </div>
              
              <div class="border-t pt-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium">Overall Score</span>
                  <span class="text-sm text-gray-500">{{ totalWeight }}%</span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ overallScore }}</div>
                <div class="text-sm text-gray-500">{{ overallStatus }}</div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <i class="pi pi-chart-line text-3xl text-gray-400 mb-3"></i>
              <p class="text-gray-500">Fill in the form to see KPI preview</p>
            </div>
          </template>
        </Card>

        <!-- Form Progress -->
        <Card class="mt-6">
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-check-circle mr-2"></i>
              Form Progress
            </div>
          </template>
          <template #content>
            <div class="space-y-3">
              <div class="flex justify-between text-sm">
                <span>Completed Fields</span>
                <span>{{ completedFields }}/{{ totalFields }}</span>
              </div>
              <ProgressBar 
                :value="formProgress" 
                :show-value="false"
              />
              <div class="text-center text-sm text-gray-600">
                {{ Math.round(formProgress) }}% Complete
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { usePerformanceStore } from '@/Stores/performanceStore'
import type { PerformanceTemplate, FormField } from '@/Types/performance'

// Components
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'
import RadioButton from 'primevue/radiobutton'
import Checkbox from 'primevue/checkbox'
import Slider from 'primevue/slider'
import Calendar from 'primevue/calendar'
import FileUpload from 'primevue/fileupload'
import ProgressBar from 'primevue/progressbar'

// Composables
const route = useRoute()
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

const formData = ref<Record<string, any>>({})
const errors = ref<Record<string, string>>({})
const uploadedFiles = ref<Record<string, File>>({})
const saving = ref(false)
const submitting = ref(false)

// Computed properties
const isFormValid = computed(() => {
  return template.value.fields.every(field => {
    if (field.type === 'section') return true
    if (!field.required) return true
    const value = formData.value[field.id]
    return value !== null && value !== undefined && value !== ''
  })
})

const completedFields = computed(() => {
  return template.value.fields.filter(field => {
    if (field.type === 'section') return true
    const value = formData.value[field.id]
    return value !== null && value !== undefined && value !== ''
  }).length
})

const totalFields = computed(() => {
  return template.value.fields.filter(field => field.type !== 'section').length
})

const formProgress = computed(() => {
  return totalFields.value > 0 ? (completedFields.value / totalFields.value) * 100 : 0
})

const kpiPreview = computed(() => {
  const kpis = []
  
  template.value.fields.forEach(field => {
    if (field.kpi_config && formData.value[field.id] !== undefined) {
      const score = calculateKPIScore(field, formData.value[field.id])
      const percentage = (score / 100) * 100
      const status = getKPIStatus(score, field.kpi_config)
      
      kpis.push({
        name: field.kpi_config.name,
        weight: field.kpi_config.weight,
        score: score.toFixed(1),
        percentage: percentage,
        status: status
      })
    }
  })
  
  return kpis
})

const totalWeight = computed(() => {
  return kpiPreview.value.reduce((sum, kpi) => sum + kpi.weight, 0)
})

const overallScore = computed(() => {
  if (kpiPreview.value.length === 0) return 0
  
  const weightedSum = kpiPreview.value.reduce((sum, kpi) => {
    return sum + (parseFloat(kpi.score) * kpi.weight)
  }, 0)
  
  return (weightedSum / totalWeight.value).toFixed(1)
})

const overallStatus = computed(() => {
  const score = parseFloat(overallScore.value)
  if (score >= 90) return 'Excellent'
  if (score >= 80) return 'Good'
  if (score >= 70) return 'Satisfactory'
  if (score >= 60) return 'Needs Improvement'
  return 'Poor'
})

// Methods
const loadTemplate = async () => {
  try {
    const templateId = route.params.id
    template.value = await performanceStore.getTemplate(templateId as string)
    
    // Initialize form data
    template.value.fields.forEach(field => {
      if (field.type === 'checkbox') {
        formData.value[field.id] = []
      } else {
        formData.value[field.id] = null
      }
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Load Failed',
      detail: 'Failed to load performance template',
      life: 5000
    })
    router.push('/performance/templates')
  }
}

const validateForm = () => {
  errors.value = {}
  
  template.value.fields.forEach(field => {
    if (field.type === 'section') return
    
    const value = formData.value[field.id]
    
    if (field.required && (value === null || value === undefined || value === '')) {
      errors.value[field.id] = `${field.label} is required`
    }
    
    if (field.validation) {
      if (field.validation.minLength && value && value.length < field.validation.minLength) {
        errors.value[field.id] = `${field.label} must be at least ${field.validation.minLength} characters`
      }
      
      if (field.validation.maxLength && value && value.length > field.validation.maxLength) {
        errors.value[field.id] = `${field.label} must not exceed ${field.validation.maxLength} characters`
      }
      
      if (field.validation.min && value < field.validation.min) {
        errors.value[field.id] = `${field.label} must be at least ${field.validation.min}`
      }
      
      if (field.validation.max && value > field.validation.max) {
        errors.value[field.id] = `${field.label} must not exceed ${field.validation.max}`
      }
    }
  })
  
  return Object.keys(errors.value).length === 0
}

const saveDraft = async () => {
  if (!validateForm()) {
    toast.add({
      severity: 'warn',
      summary: 'Validation Error',
      detail: 'Please fix the form errors before saving',
      life: 5000
    })
    return
  }
  
  saving.value = true
  try {
    const reportData = {
      template_id: template.value.id,
      responses: formData.value,
      status: 'draft'
    }
    
    await performanceStore.saveDraft(reportData)
    toast.add({
      severity: 'success',
      summary: 'Draft Saved',
      detail: 'Your draft has been saved successfully',
      life: 3000
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Save Failed',
      detail: 'Failed to save draft',
      life: 5000
    })
  } finally {
    saving.value = false
  }
}

const submitReport = async () => {
  if (!validateForm()) {
    toast.add({
      severity: 'warn',
      summary: 'Validation Error',
      detail: 'Please fix the form errors before submitting',
      life: 5000
    })
    return
  }
  
  submitting.value = true
  try {
    const reportData = {
      template_id: template.value.id,
      responses: formData.value,
      status: 'submitted'
    }
    
    await performanceStore.submitReport(reportData)
    toast.add({
      severity: 'success',
      summary: 'Report Submitted',
      detail: 'Your performance report has been submitted successfully',
      life: 3000
    })
    router.push('/performance/reports')
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Submission Failed',
      detail: 'Failed to submit performance report',
      life: 5000
    })
  } finally {
    submitting.value = false
  }
}

const handleFileUpload = (event: any, fieldId: string) => {
  const file = event.files[0]
  if (file) {
    uploadedFiles.value[fieldId] = file
    formData.value[fieldId] = file.name
  }
}

// FIXED: Secure KPI calculation - no eval() usage
const calculateKPIScore = (field: FormField, value: any): number => {
  if (!field.kpi_config || value === null || value === undefined) return 0
  
  const config = field.kpi_config
  
  switch (config.calculation_method) {
    case 'direct_score':
      return typeof value === 'number' ? value : 0
      
    case 'percentage':
      return typeof value === 'number' ? (value / 100) * 100 : 0
      
    case 'formula':
      if (config.scoring_formula) {
        try {
          return evaluateSecureFormula(config.scoring_formula, {
            value: value,
            max: config.target_value || 100,
            min: 0,
            target: config.target_value || 50
          })
        } catch (error) {
          console.error('Formula evaluation error:', error)
          return 0
        }
      }
      return 0
      
    default:
      return 0
  }
}

// Secure formula evaluator - replaces dangerous eval()
const evaluateSecureFormula = (formula: string, variables: Record<string, number>): number => {
  // Replace variables with actual values
  let expression = formula
  Object.keys(variables).forEach(key => {
    const regex = new RegExp(`\\b${key}\\b`, 'g')
    expression = expression.replace(regex, variables[key].toString())
  })
  
  // Only allow safe mathematical operations - strip anything dangerous
  const safeExpression = expression.replace(/[^0-9+\-*/().\s]/g, '')
  
  // Use Function constructor with restricted scope (safer than eval)
  try {
    const func = new Function('return (' + safeExpression + ')')
    const result = func()
    
    // Ensure result is a valid number
    return typeof result === 'number' && isFinite(result) ? result : 0
  } catch (error) {
    console.error('Safe formula evaluation error:', error)
    return 0
  }
}

const calculateKPIPreview = (field: FormField): string => {
  const score = calculateKPIScore(field, formData.value[field.id])
  const status = getKPIStatus(score, field.kpi_config)
  return `${score.toFixed(1)} (${status})`
}

const getKPIStatus = (score: number, config: any): string => {
  if (config.excellent_threshold && score >= config.excellent_threshold) return 'Excellent'
  if (config.good_threshold && score >= config.good_threshold) return 'Good'
  if (config.needs_improvement_threshold && score < config.needs_improvement_threshold) return 'Needs Improvement'
  return 'Satisfactory'
}

// Watchers
watch(formData, () => {
  // Clear errors when user starts typing
  errors.value = {}
}, { deep: true })

// Lifecycle
onMounted(() => {
  loadTemplate()
})
</script>

<style scoped>
.performance-report-form {
  @apply p-6;
}

.form-field {
  @apply space-y-2;
}

.section-header {
  @apply pb-4 border-b border-gray-200 mb-6;
}

.field-input {
  @apply space-y-2;
}

.rating-input {
  @apply space-y-2;
}

.slider-input {
  @apply space-y-2;
}

.file-upload {
  @apply space-y-2;
}

.kpi-preview-item {
  @apply p-3 border border-gray-200 rounded-lg;
}
</style>