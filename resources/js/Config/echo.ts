import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Declare Pusher on window object for Laravel Echo
declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo
  }
}

// Initialize Pusher
window.Pusher = Pusher

// Create Echo instance
const echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
  encrypted: true,
  authEndpoint: '/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${getAuthToken()}`,
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': getCSRFToken()
    }
  },
  // Enable logging in development
  enableLogging: import.meta.env.DEV,
  // Connection options
  enabledTransports: ['ws', 'wss'],
  // Reconnection options
  reconnectionAttempts: 5,
  reconnectionDelay: 1000,
  // Heartbeat options
  heartbeatTimeout: 60000,
  heartbeatInterval: 30000
})

// Helper functions
function getAuthToken(): string {
  // Get auth token from localStorage or cookies
  const token = localStorage.getItem('auth_token') || 
                getCookie('auth_token') || 
                getCookie('laravel_session')
  return token
}

function getCSRFToken(): string {
  // Get CSRF token from meta tag
  const token = document.querySelector('meta[name="csrf-token"]')
  return token ? token.getAttribute('content') || '' : ''
}

function getCookie(name: string): string {
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) {
    return parts.pop()?.split(';').shift() || ''
  }
  return ''
}

// Connection event handlers
echo.connector.pusher.connection.bind('connected', () => {
  console.log('Echo connected to Pusher')
})

echo.connector.pusher.connection.bind('disconnected', () => {
  console.log('Echo disconnected from Pusher')
})

echo.connector.pusher.connection.bind('error', (error: any) => {
  console.error('Echo connection error:', error)
})

// Make Echo available globally
window.Echo = echo

export default echo
