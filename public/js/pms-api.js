/**
 * Performance Management System - API Helper
 * Version: 1.0.0
 * Description: API endpoints and methods for Laravel 9.5 PMS
 */

(function() {
    'use strict';

    // Ensure PMS namespace exists
    window.PMS = window.PMS || {};
    
    // ================================
    // API ENDPOINTS CONFIGURATION
    // ================================
    
    const API_ENDPOINTS = {
        // Authentication
        auth: {
            login: '/auth/login',
            logout: '/auth/logout',
            refresh: '/auth/refresh',
            user: '/auth/user',
            permissions: '/auth/permissions'
        },
        
        // Dashboard
        dashboard: {
            stats: '/performance/dashboard/summary',
            chart: '/performance/dashboard/trends',
            refresh: '/performance/dashboard/summary',
            activities: '/performance/dashboard/activities'
        },
        
        // Users & Employees
        users: {
            index: '/users',
            show: '/users/{id}',
            store: '/users',
            update: '/users/{id}',
            destroy: '/users/{id}',
            profile: '/users/profile',
            updateProfile: '/users/profile'
        },
        
        employees: {
            index: '/employees',
            show: '/employees/{id}',
            search: '/employees/search',
            sync: '/employees/sync-erp'
        },
        
        // Performance Management
        performance: {
            index: '/performance',
            show: '/performance/{id}',
            dashboard: '/performance/dashboard',
            reports: '/performance/reports',
            templates: '/performance/templates',
            goals: '/performance/goals',
            analytics: '/performance/analytics'
        },
        
        // Reports & Templates
        reports: {
            index: '/performance/reports',
            show: '/performance/reports/{id}',
            store: '/performance/reports',
            update: '/performance/reports/{id}',
            destroy: '/performance/reports/{id}',
            submit: '/performance/reports/{id}/submit',
            draft: '/performance/reports/{id}/save-draft',
            export: '/performance/reports/{id}/export'
        },
        
        templates: {
            index: '/performance/templates',
            show: '/performance/templates/{id}',
            store: '/performance/templates',
            update: '/performance/templates/{id}',
            destroy: '/performance/templates/{id}',
            builder: '/performance/templates/builder',
            preview: '/performance/templates/{id}/preview',
            duplicate: '/performance/templates/{id}/duplicate',
            publish: '/performance/templates/{id}/publish',
            kpis: '/performance/templates/kpis/available'
        },
        
        // Goals & KPIs
        goals: {
            index: '/performance/goals',
            show: '/performance/goals/{id}',
            store: '/performance/goals',
            update: '/performance/goals/{id}',
            destroy: '/performance/goals/{id}',
            data: '/performance/goals/data'
        },
        
        // Leaderboards
        leaderboards: {
            index: '/performance/leaderboards',
            data: '/performance/leaderboards/data'
        },
        
        // Analytics
        analytics: {
            index: '/performance/analytics',
            performance: '/performance/analytics/performance',
            trends: '/performance/analytics/trends'
        }
    };
    
    // ================================
    // API SERVICE CLASS
    // ================================
    
    class APIService {
        constructor() {
            this.baseURL = PMS.config.apiUrl || '/api';
            this.timeout = 30000; // 30 seconds
            this.retryAttempts = 3;
            this.retryDelay = 1000;
        }
        
        // Helper to replace URL parameters
        buildUrl(endpoint, params = {}) {
            let url = endpoint;
            
            // Replace path parameters
            Object.keys(params).forEach(key => {
                url = url.replace(`{${key}}`, params[key]);
            });
            
            return url;
        }
        
        // Generic request method with retry logic
        async request(endpoint, options = {}, params = {}) {
            const url = this.buildUrl(endpoint, params);
            let lastError;
            
            for (let attempt = 1; attempt <= this.retryAttempts; attempt++) {
                try {
                    return await PMS.http.request(url, options);
                } catch (error) {
                    lastError = error;
                    
                    // Don't retry for client errors (4xx)
                    if (error.status && error.status >= 400 && error.status < 500) {
                        throw error;
                    }
                    
                    // Don't retry on the last attempt
                    if (attempt === this.retryAttempts) {
                        throw error;
                    }
                    
                    // Wait before retrying
                    await PMS.utils.async.delay(this.retryDelay * attempt);
                }
            }
            
            throw lastError;
        }
        
        // HTTP methods
        get(endpoint, queryParams = {}, pathParams = {}) {
            const url = this.buildUrl(endpoint, pathParams);
            return PMS.http.get(url, queryParams);
        }
        
        post(endpoint, data = {}, pathParams = {}) {
            const url = this.buildUrl(endpoint, pathParams);
            return PMS.http.post(url, data);
        }
        
        put(endpoint, data = {}, pathParams = {}) {
            const url = this.buildUrl(endpoint, pathParams);
            return PMS.http.put(url, data);
        }
        
        patch(endpoint, data = {}, pathParams = {}) {
            const url = this.buildUrl(endpoint, pathParams);
            return PMS.http.patch(url, data);
        }
        
        delete(endpoint, pathParams = {}) {
            const url = this.buildUrl(endpoint, pathParams);
            return PMS.http.delete(url);
        }
        
        upload(endpoint, formData, pathParams = {}) {
            const url = this.buildUrl(endpoint, pathParams);
            return PMS.http.upload(url, formData);
        }
    }
    
    // Create API service instance
    const api = new APIService();
    
    // ================================
    // API METHODS BY FEATURE
    // ================================
    
    // Performance Management API
    PMS.api = {
        // Dashboard API
        dashboard: {
            async getStats() {
                const cacheKey = 'dashboard_stats';
                
                // Check cache first
                const cached = PMS.cache.get(cacheKey);
                if (cached) return cached;
                
                const stats = await api.get(API_ENDPOINTS.dashboard.stats);
                PMS.cache.set(cacheKey, stats, 60000); // Cache for 1 minute
                
                return stats;
            },
            
            async getPerformanceChart(period = '6months') {
                return api.get(API_ENDPOINTS.dashboard.chart, { period });
            },
            
            async refresh() {
                PMS.cache.clear(); // Clear cache on refresh
                return api.get(API_ENDPOINTS.dashboard.refresh);
            },
            
            async getActivities() {
                return api.get(API_ENDPOINTS.dashboard.activities);
            }
        },
        
        // Performance API
        performance: {
            async getAll(filters = {}) {
                return api.get(API_ENDPOINTS.performance.index, filters);
            },
            
            async getById(id) {
                return api.get(API_ENDPOINTS.performance.show, {}, { id });
            },
            
            async getDashboard() {
                return api.get(API_ENDPOINTS.performance.dashboard);
            }
        },
        
        // Reports API
        reports: {
            async getAll(filters = {}) {
                return api.get(API_ENDPOINTS.reports.index, filters);
            },
            
            async getById(id) {
                return api.get(API_ENDPOINTS.reports.show, {}, { id });
            },
            
            async create(reportData) {
                return api.post(API_ENDPOINTS.reports.store, reportData);
            },
            
            async update(id, reportData) {
                return api.put(API_ENDPOINTS.reports.update, reportData, { id });
            },
            
            async delete(id) {
                return api.delete(API_ENDPOINTS.reports.destroy, { id });
            },
            
            async submit(id, responseData) {
                return api.post(API_ENDPOINTS.reports.submit, responseData, { id });
            },
            
            async saveDraft(id, responseData) {
                return api.post(API_ENDPOINTS.reports.draft, responseData, { id });
            },
            
            async export(id, format = 'pdf') {
                return api.get(API_ENDPOINTS.reports.export, { format }, { id });
            }
        },
        
        // Templates API
        templates: {
            async getAll(filters = {}) {
                return api.get(API_ENDPOINTS.templates.index, filters);
            },
            
            async getById(id) {
                return api.get(API_ENDPOINTS.templates.show, {}, { id });
            },
            
            async create(templateData) {
                return api.post(API_ENDPOINTS.templates.store, templateData);
            },
            
            async update(id, templateData) {
                return api.put(API_ENDPOINTS.templates.update, templateData, { id });
            },
            
            async delete(id) {
                return api.delete(API_ENDPOINTS.templates.destroy, { id });
            },
            
            async duplicate(id) {
                return api.post(API_ENDPOINTS.templates.duplicate, {}, { id });
            },
            
            async publish(id) {
                return api.post(API_ENDPOINTS.templates.publish, {}, { id });
            },
            
            async preview(id) {
                return api.get(API_ENDPOINTS.templates.preview, {}, { id });
            },
            
            async getKPIs() {
                return api.get(API_ENDPOINTS.templates.kpis);
            }
        },
        
        // Goals API
        goals: {
            async getAll(filters = {}) {
                return api.get(API_ENDPOINTS.goals.index, filters);
            },
            
            async getById(id) {
                return api.get(API_ENDPOINTS.goals.show, {}, { id });
            },
            
            async create(goalData) {
                return api.post(API_ENDPOINTS.goals.store, goalData);
            },
            
            async update(id, goalData) {
                return api.put(API_ENDPOINTS.goals.update, goalData, { id });
            },
            
            async delete(id) {
                return api.delete(API_ENDPOINTS.goals.destroy, { id });
            },
            
            async getData() {
                return api.get(API_ENDPOINTS.goals.data);
            }
        },
        
        // Leaderboards API
        leaderboards: {
            async getAll(filters = {}) {
                return api.get(API_ENDPOINTS.leaderboards.index, filters);
            },
            
            async getData(type = 'department', filters = {}) {
                return api.get(API_ENDPOINTS.leaderboards.data, { type, ...filters });
            }
        },
        
        // Analytics API
        analytics: {
            async getOverview() {
                return api.get(API_ENDPOINTS.analytics.index);
            },
            
            async getPerformanceAnalytics(filters = {}) {
                return api.get(API_ENDPOINTS.analytics.performance, filters);
            },
            
            async getTrends(period = '12months') {
                return api.get(API_ENDPOINTS.analytics.trends, { period });
            }
        },
        
        // Users API
        users: {
            async getAll(filters = {}) {
                return api.get(API_ENDPOINTS.users.index, filters);
            },
            
            async getById(id) {
                return api.get(API_ENDPOINTS.users.show, {}, { id });
            },
            
            async getProfile() {
                return api.get(API_ENDPOINTS.users.profile);
            },
            
            async updateProfile(profileData) {
                return api.put(API_ENDPOINTS.users.updateProfile, profileData);
            }
        },
        
        // Employees API
        employees: {
            async getAll(filters = {}) {
                return api.get(API_ENDPOINTS.employees.index, filters);
            },
            
            async getById(id) {
                return api.get(API_ENDPOINTS.employees.show, {}, { id });
            },
            
            async search(query) {
                return api.get(API_ENDPOINTS.employees.search, { q: query });
            }
        }
    };
    
    // ================================
    // API HELPERS & UTILITIES
    // ================================
    
    PMS.api.helpers = {
        // Build query string from object
        buildQuery(params) {
            return new URLSearchParams(params).toString();
        },
        
        // Handle file download
        downloadFile(url, filename) {
            const link = document.createElement('a');
            link.href = url;
            link.download = filename || 'download';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        
        // Handle API errors
        handleError(error, context = '') {
            console.error(`API Error ${context}:`, error);
            
            let message = 'An unexpected error occurred';
            
            if (error.response && error.response.data) {
                if (error.response.data.message) {
                    message = error.response.data.message;
                } else if (error.response.data.errors) {
                    // Handle validation errors
                    const errors = Object.values(error.response.data.errors).flat();
                    message = errors.join(', ');
                }
            } else if (error.message) {
                message = error.message;
            }
            
            PMS.notifications.error(message);
            
            // Handle specific error codes
            if (error.status === 401) {
                // Unauthorized - redirect to login
                PMS.utils.storage.remove('api_token');
                window.location.href = '/login';
            } else if (error.status === 403) {
                // Forbidden
                PMS.notifications.error('You do not have permission to perform this action');
            }
        },
        
        // Batch requests
        async batch(requests) {
            try {
                const promises = requests.map(request => {
                    const { method, endpoint, data, params } = request;
                    
                    switch (method.toLowerCase()) {
                        case 'get':
                            return api.get(endpoint, data, params);
                        case 'post':
                            return api.post(endpoint, data, params);
                        case 'put':
                            return api.put(endpoint, data, params);
                        case 'patch':
                            return api.patch(endpoint, data, params);
                        case 'delete':
                            return api.delete(endpoint, params);
                        default:
                            throw new Error(`Unsupported HTTP method: ${method}`);
                    }
                });
                
                return await Promise.allSettled(promises);
            } catch (error) {
                console.error('Batch request error:', error);
                throw error;
            }
        }
    };

})();
