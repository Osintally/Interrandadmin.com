import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import Tooltip from 'primevue/tooltip'

// Import PrimeVue CSS
import 'primevue/resources/themes/lara-light-blue/theme.css'
import 'primevue/resources/primevue.min.css'
import 'primeicons/primeicons.css'

// Import Tailwind CSS
import '../css/app.css'

// Import App Component
import App from './App.vue'

// Remove these static imports:
// import TemplateBuilder from './Pages/Performance/TemplateBuilder.vue'
// import Dashboard from './Pages/Performance/Dashboard.vue'
// import ReportForm from './Pages/Performance/ReportForm.vue'

// Import Stores (keep these - they're needed immediately)
import { usePerformanceStore } from './Stores/performanceStore'
import { useKPIStore } from './Stores/kpiStore'
import { useDashboardStore } from './Stores/dashboardStore'

// Import Services
import { RealTimeKPIService } from './Services/RealTimeKPIService'

// Router configuration - Use dynamic imports consistently
const routes = [
  {
    path: '/performance',
    name: 'performance.index',
    component: () => import('./Pages/Performance/Dashboard.vue')
  },
  {
    path: '/performance/templates',
    name: 'performance.templates',
    component: () => import('./Pages/Performance/TemplateList.vue')
  },
  {
    path: '/performance/templates/create',
    name: 'performance.templates.create',
    component: () => import('./Pages/Performance/TemplateBuilder.vue')  // Changed to dynamic
  },
  {
    path: '/performance/templates/:id/edit',
    name: 'performance.templates.edit',
    component: () => import('./Pages/Performance/TemplateBuilder.vue'),  // Changed to dynamic
    props: true
  },
  {
    path: '/performance/dashboard',
    name: 'performance.dashboard',
    component: () => import('./Pages/Performance/Dashboard.vue')  // Changed to dynamic
  },
  {
    path: '/performance/reports',
    name: 'performance.reports',
    component: () => import('./Pages/Performance/ReportList.vue')
  },
  {
    path: '/performance/reports/:id',
    name: 'performance.reports.show',
    component: () => import('./Pages/Performance/ReportView.vue'),
    props: true
  },
  {
    path: '/performance/reports/create/:templateId',
    name: 'performance.reports.create',
    component: () => import('./Pages/Performance/ReportForm.vue'),  // Changed to dynamic
    props: true
  },
  {
    path: '/performance/kpis',
    name: 'performance.kpis',
    component: () => import('./Pages/Performance/KPIList.vue')
  },
  {
    path: '/performance/goals',
    name: 'performance.goals',
    component: () => import('./Pages/Performance/GoalList.vue')
  }
]

// Rest of your code stays the same...
const router = createRouter({
  history: createWebHistory('/'),
  routes
})

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(PrimeVue)
app.use(ToastService)
app.use(ConfirmationService)
app.directive('tooltip', Tooltip)

app.config.globalProperties.$realTimeKPI = RealTimeKPIService.getInstance()

app.mount('#performance-app')

export { app, router, pinia }