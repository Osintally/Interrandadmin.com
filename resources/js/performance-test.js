import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'

// Import PrimeVue CSS
import 'primevue/resources/themes/lara-light-blue/theme.css'
import 'primevue/resources/primevue.min.css'
import 'primeicons/primeicons.css'

// Import Tailwind CSS
import '../css/app.css'

// Simple test component
const TestComponent = {
  template: `
    <div class="p-6">
      <h1 class="text-2xl font-bold text-blue-600 mb-4">Performance Management System</h1>
      <p class="text-gray-600 mb-4">Frontend setup is working correctly!</p>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        ✅ Vue.js 3 + TypeScript + PrimeVue + Tailwind CSS loaded successfully
      </div>
    </div>
  `
}

// Router configuration
const routes = [
  {
    path: '/',
    component: TestComponent
  }
]

const router = createRouter({
  history: createWebHistory('/'),
  routes
})

// Create Vue app
const app = createApp({
  template: '<router-view />'
})

// Create Pinia store
const pinia = createPinia()

// Configure app
app.use(pinia)
app.use(router)

// Mount the app
app.mount('#performance-app')

console.log('Performance Management System loaded successfully!')
