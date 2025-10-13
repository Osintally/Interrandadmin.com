/**
 * Performance Management System - Core JavaScript
 * Version: 1.0.0
 * Description: Core functionality for Laravel 9.5 PMS Frontend
 */

(function() {
    'use strict';

    // ================================
    // GLOBAL PMS NAMESPACE
    // ================================
    
    window.PMS = window.PMS || {};
    
    // Application state
    PMS.state = {
        user: null,
        permissions: [],
        currentView: null,
        sidebarOpen: false,
        loading: false,
        notifications: [],
        cache: new Map(),
        eventListeners: new Map()
    };
    
    // Configuration
    PMS.config = {
        apiUrl: '/api',
        csrfToken: null,
        debug: false,
        cacheTimeout: 300000, // 5 minutes
        retryAttempts: 3,
        retryDelay: 1000
    };
    
    // ================================
    // CORE UTILITIES
    // ================================
    
    PMS.utils = {
        // DOM utilities
        dom: {
            query: (selector, context = document) => context.querySelector(selector),
            queryAll: (selector, context = document) => context.querySelectorAll(selector),
            create: (tag, attributes = {}, content = '') => {
                const element = document.createElement(tag);
                Object.entries(attributes).forEach(([key, value]) => {
                    if (key === 'className') {
                        element.className = value;
                    } else if (key === 'innerHTML') {
                        element.innerHTML = value;
                    } else {
                        element.setAttribute(key, value);
                    }
                });
                if (content) element.textContent = content;
                return element;
            },
            addClass: (element, className) => element.classList.add(className),
            removeClass: (element, className) => element.classList.remove(className),
            toggleClass: (element, className) => element.classList.toggle(className),
            hasClass: (element, className) => element.classList.contains(className)
        },

        // String utilities
        string: {
            capitalize: (str) => str.charAt(0).toUpperCase() + str.slice(1),
            truncate: (str, length = 50) => str.length > length ? str.substring(0, length) + '...' : str,
            slugify: (str) => str.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''),
            format: (template, data) => {
                return template.replace(/\{(\w+)\}/g, (match, key) => data[key] || match);
            }
        },

        // Number utilities
        number: {
            format: (num, decimals = 2) => {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }).format(num);
            },
            percentage: (num, total) => {
                return total > 0 ? ((num / total) * 100).toFixed(1) + '%' : '0%';
            },
            currency: (amount, currency = 'USD') => {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: currency
                }).format(amount);
            }
        },

        // Date utilities
        date: {
            format: (date, format = 'short') => {
                const d = new Date(date);
                const options = {
                    short: { year: 'numeric', month: 'short', day: 'numeric' },
                    long: { year: 'numeric', month: 'long', day: 'numeric' },
                    time: { hour: '2-digit', minute: '2-digit' },
                    datetime: { 
                        year: 'numeric', month: 'short', day: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    }
                };
                return d.toLocaleDateString('en-US', options[format] || options.short);
            },
            relative: (date) => {
                const now = new Date();
                const diff = now - new Date(date);
                const minutes = Math.floor(diff / 60000);
                const hours = Math.floor(minutes / 60);
                const days = Math.floor(hours / 24);

                if (minutes < 1) return 'Just now';
                if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
                if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
                if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;
                return PMS.utils.date.format(date);
            }
        },

        // Validation utilities
        validate: {
            email: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
            phone: (phone) => /^\+?[\d\s\-\(\)]+$/.test(phone),
            required: (value) => value !== null && value !== undefined && value.toString().trim() !== '',
            minLength: (value, min) => value && value.toString().length >= min,
            maxLength: (value, max) => value && value.toString().length <= max
        },

        // Storage utilities
        storage: {
            set: (key, value, expiry = null) => {
                const data = { value, expiry: expiry ? Date.now() + expiry : null };
                localStorage.setItem(`pms_${key}`, JSON.stringify(data));
            },
            get: (key) => {
                try {
                    const item = localStorage.getItem(`pms_${key}`);
                    if (!item) return null;
                    
                    const data = JSON.parse(item);
                    if (data.expiry && Date.now() > data.expiry) {
                        localStorage.removeItem(`pms_${key}`);
                        return null;
                    }
                    return data.value;
                } catch {
                    return null;
                }
            },
            remove: (key) => localStorage.removeItem(`pms_${key}`),
            clear: () => {
                Object.keys(localStorage).forEach(key => {
                    if (key.startsWith('pms_')) {
                        localStorage.removeItem(key);
                    }
                });
            }
        }
    };

    // ================================
    // API CLIENT
    // ================================
    
    PMS.api = {
        // Base request method
        async request(url, options = {}) {
            const config = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': PMS.config.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    ...options.headers
                },
                ...options
            };

            try {
                const response = await fetch(`${PMS.config.apiUrl}${url}`, config);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return await response.json();
                }
                return await response.text();
            } catch (error) {
                PMS.notifications.error('API Request failed: ' + error.message);
                throw error;
            }
        },

        // Convenience methods
        get: (url, options = {}) => PMS.api.request(url, { ...options, method: 'GET' }),
        post: (url, data, options = {}) => PMS.api.request(url, { 
            ...options, 
            method: 'POST', 
            body: JSON.stringify(data) 
        }),
        put: (url, data, options = {}) => PMS.api.request(url, { 
            ...options, 
            method: 'PUT', 
            body: JSON.stringify(data) 
        }),
        delete: (url, options = {}) => PMS.api.request(url, { ...options, method: 'DELETE' }),

        // Performance API endpoints
        performance: {
            getDashboardData: () => PMS.api.get('/performance/dashboard/summary'),
            getTrends: (period = '30d') => PMS.api.get(`/performance/dashboard/trends?period=${period}`),
            getKPIBreakdown: () => PMS.api.get('/performance/dashboard/kpi-breakdown'),
            getTopPerformers: (limit = 10) => PMS.api.get(`/performance/dashboard/top-performers?limit=${limit}`),
            getAlerts: () => PMS.api.get('/performance/dashboard/alerts'),
            markAlertAsRead: (alertId) => PMS.api.post(`/performance/dashboard/alerts/${alertId}/read`)
        },

        // Templates API
        templates: {
            list: () => PMS.api.get('/performance/templates'),
            get: (id) => PMS.api.get(`/performance/templates/${id}`),
            create: (data) => PMS.api.post('/performance/templates', data),
            update: (id, data) => PMS.api.put(`/performance/templates/${id}`, data),
            delete: (id) => PMS.api.delete(`/performance/templates/${id}`),
            getKPIs: () => PMS.api.get('/performance/templates/kpis/available'),
            preview: (id, data) => PMS.api.post(`/performance/templates/${id}/kpi-preview`, data)
        },

        // Reports API
        reports: {
            list: () => PMS.api.get('/performance/reports'),
            get: (id) => PMS.api.get(`/performance/reports/${id}`),
            create: (data) => PMS.api.post('/performance/reports', data),
            update: (id, data) => PMS.api.put(`/performance/reports/${id}`, data),
            delete: (id) => PMS.api.delete(`/performance/reports/${id}`),
            saveDraft: (id, data) => PMS.api.post(`/performance/reports/${id}/save-draft`, data),
            approve: (id) => PMS.api.post(`/performance/reports/${id}/approve`),
            reject: (id, reason) => PMS.api.post(`/performance/reports/${id}/reject`, { reason })
        }
    };

    // ================================
    // NOTIFICATION SYSTEM
    // ================================
    
    PMS.notifications = {
        container: null,

        init() {
            this.container = PMS.utils.dom.query('#toastContainer');
            if (!this.container) {
                this.container = PMS.utils.dom.create('div', { id: 'toastContainer', className: 'toast-container' });
                document.body.appendChild(this.container);
            }
        },

        show(message, type = 'info', duration = 5000) {
            const toast = this.createToast(message, type);
            this.container.appendChild(toast);

            // Trigger animation
            setTimeout(() => PMS.utils.dom.addClass(toast, 'show'), 10);

            // Auto remove
            if (duration > 0) {
                setTimeout(() => this.remove(toast), duration);
            }

            return toast;
        },

        createToast(message, type) {
            const icons = {
                success: 'fas fa-check',
                error: 'fas fa-times',
                warning: 'fas fa-exclamation',
                info: 'fas fa-info'
            };

            const titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Warning',
                info: 'Information'
            };

            const toast = PMS.utils.dom.create('div', { className: `toast ${type}` });
            
            toast.innerHTML = `
                <div class="toast-header">
                    <div class="toast-icon">
                        <i class="${icons[type]}"></i>
                    </div>
                    <div class="toast-title">${titles[type]}</div>
                    <button class="toast-close" onclick="PMS.notifications.remove(this.closest('.toast'))">&times;</button>
                </div>
                <div class="toast-body">${message}</div>
            `;

            return toast;
        },

        remove(toast) {
            PMS.utils.dom.addClass(toast, 'hide');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        },

        success: (message, duration) => PMS.notifications.show(message, 'success', duration),
        error: (message, duration) => PMS.notifications.show(message, 'error', duration),
        warning: (message, duration) => PMS.notifications.show(message, 'warning', duration),
        info: (message, duration) => PMS.notifications.show(message, 'info', duration)
    };

    // ================================
    // SIDEBAR MANAGEMENT
    // ================================
    
    PMS.sidebar = {
        element: null,
        overlay: null,
        isOpen: false,

        init() {
            this.element = PMS.utils.dom.query('#sidebar');
            this.overlay = PMS.utils.dom.query('#sidebarOverlay');
            
            if (this.overlay) {
                this.overlay.addEventListener('click', () => this.close());
            }

            // Handle window resize
            window.addEventListener('resize', () => this.handleResize());
            this.handleResize();
        },

        toggle() {
            this.isOpen ? this.close() : this.open();
        },

        open() {
            if (!this.element) return;
            
            PMS.utils.dom.addClass(this.element, 'show');
            if (this.overlay) PMS.utils.dom.addClass(this.overlay, 'show');
            this.isOpen = true;
            PMS.state.sidebarOpen = true;
        },

        close() {
            if (!this.element) return;
            
            PMS.utils.dom.removeClass(this.element, 'show');
            if (this.overlay) PMS.utils.dom.removeClass(this.overlay, 'show');
            this.isOpen = false;
            PMS.state.sidebarOpen = false;
        },

        handleResize() {
            if (window.innerWidth > 1024) {
                this.close();
            }
        }
    };

    // ================================
    // MODAL SYSTEM
    // ================================
    
    PMS.modal = {
        current: null,

        show(content, options = {}) {
            const defaults = {
                title: 'Modal',
                size: 'medium',
                closable: true,
                backdrop: true
            };
            
            const config = { ...defaults, ...options };
            
            const modal = this.create(content, config);
            document.body.appendChild(modal);
            
            setTimeout(() => PMS.utils.dom.addClass(modal, 'show'), 10);
            
            this.current = modal;
            return modal;
        },

        create(content, config) {
            const modal = PMS.utils.dom.create('div', { className: 'modal-overlay' });
            
            modal.innerHTML = `
                <div class="modal modal-${config.size}">
                    <div class="modal-header">
                        <h3 class="modal-title">${config.title}</h3>
                        ${config.closable ? '<button class="modal-close" onclick="PMS.modal.close()">&times;</button>' : ''}
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                </div>
            `;

            if (config.backdrop) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) this.close();
                });
            }

            return modal;
        },

        close() {
            if (!this.current) return;
            
            PMS.utils.dom.removeClass(this.current, 'show');
            setTimeout(() => {
                if (this.current && this.current.parentNode) {
                    this.current.parentNode.removeChild(this.current);
                }
                this.current = null;
            }, 300);
        }
    };

    // ================================
    // LOADING STATES
    // ================================
    
    PMS.loading = {
        show(element) {
            if (element) {
                PMS.utils.dom.addClass(element, 'loading');
            } else {
                PMS.state.loading = true;
                this.showGlobalLoader();
            }
        },

        hide(element) {
            if (element) {
                PMS.utils.dom.removeClass(element, 'loading');
            } else {
                PMS.state.loading = false;
                this.hideGlobalLoader();
            }
        },

        showGlobalLoader() {
            let loader = PMS.utils.dom.query('#globalLoader');
            if (!loader) {
                loader = PMS.utils.dom.create('div', { 
                    id: 'globalLoader',
                    className: 'global-loader'
                });
                loader.innerHTML = `
                    <div class="loader-content">
                        <div class="loader-spinner"></div>
                        <div class="loader-text">Loading...</div>
                    </div>
                `;
                document.body.appendChild(loader);
            }
            PMS.utils.dom.addClass(loader, 'show');
        },

        hideGlobalLoader() {
            const loader = PMS.utils.dom.query('#globalLoader');
            if (loader) {
                PMS.utils.dom.removeClass(loader, 'show');
            }
        }
    };

    // ================================
    // CHART UTILITIES
    // ================================
    
    PMS.charts = {
        defaultOptions: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    }
                },
                y: {
                    display: true,
                    beginAtZero: true
                }
            }
        },

        create(canvas, type, data, options = {}) {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return null;
            }

            const config = {
                type,
                data,
                options: { ...this.defaultOptions, ...options }
            };

            return new Chart(canvas, config);
        },

        colors: {
            primary: '#4f46e5',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6',
            secondary: '#6b7280'
        }
    };

    // ================================
    // EVENT MANAGEMENT
    // ================================
    
    PMS.events = {
        listeners: new Map(),

        on(event, callback) {
            if (!this.listeners.has(event)) {
                this.listeners.set(event, []);
            }
            this.listeners.get(event).push(callback);
        },

        off(event, callback) {
            if (!this.listeners.has(event)) return;
            
            const callbacks = this.listeners.get(event);
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
        },

        emit(event, data = null) {
            if (!this.listeners.has(event)) return;
            
            this.listeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Error in event listener for ${event}:`, error);
                }
            });
        }
    };

    // ================================
    // SEARCH FUNCTIONALITY
    // ================================
    
    PMS.search = {
        init() {
            const searchInputs = PMS.utils.dom.queryAll('.search-input, .pms-search-input');
            searchInputs.forEach(input => {
                input.addEventListener('input', (e) => this.handleSearch(e));
                input.addEventListener('keydown', (e) => this.handleKeydown(e));
            });
        },

        handleSearch(event) {
            const query = event.target.value.trim();
            const resultsContainer = event.target.parentNode.querySelector('.search-results');
            
            if (query.length < 2) {
                if (resultsContainer) resultsContainer.innerHTML = '';
                return;
            }

            this.performSearch(query, resultsContainer);
        },

        handleKeydown(event) {
            if (event.key === 'Escape') {
                event.target.value = '';
                const resultsContainer = event.target.parentNode.querySelector('.search-results');
                if (resultsContainer) resultsContainer.innerHTML = '';
            }
        },

        async performSearch(query, resultsContainer) {
            if (!resultsContainer) return;

            try {
                // Add your search API call here
                // const results = await PMS.api.get(`/search?q=${encodeURIComponent(query)}`);
                
                // Mock results for now
                const results = [
                    { type: 'user', title: 'John Doe', subtitle: 'HR Manager' },
                    { type: 'report', title: 'Q1 Performance Report', subtitle: 'Created 2 days ago' }
                ];

                this.displayResults(results, resultsContainer);
            } catch (error) {
                console.error('Search error:', error);
            }
        },

        displayResults(results, container) {
            if (results.length === 0) {
                container.innerHTML = '<div class="search-no-results">No results found</div>';
                return;
            }

            const html = results.map(result => `
                <div class="search-result-item">
                    <div class="search-result-title">${result.title}</div>
                    <div class="search-result-subtitle">${result.subtitle}</div>
                </div>
            `).join('');

            container.innerHTML = html;
        }
    };

    // ================================
    // MAIN INITIALIZATION
    // ================================
    
    PMS.init = function(config = {}) {
        // Merge configuration
        Object.assign(PMS.config, config);
        Object.assign(PMS.state, config);

        // Initialize components
        PMS.notifications.init();
        PMS.sidebar.init();
        PMS.search.init();

        // Set up global error handling
        window.addEventListener('error', (e) => {
            if (PMS.config.debug) {
                console.error('Global error:', e.error);
            }
        });

        // Set up unhandled promise rejection handling
        window.addEventListener('unhandledrejection', (e) => {
            if (PMS.config.debug) {
                console.error('Unhandled promise rejection:', e.reason);
            }
        });

        // Initialize mobile menu toggle
        const menuToggle = PMS.utils.dom.query('.mobile-menu-toggle, .small-view-button');
        if (menuToggle) {
            menuToggle.addEventListener('click', () => PMS.sidebar.toggle());
        }

        // Mark as initialized
        PMS.state.initialized = true;
        PMS.events.emit('pms:initialized');

        console.log('PMS Core initialized successfully');
    };

    // Auto-initialize if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (!PMS.state.initialized) {
                PMS.init();
            }
        });
    } else if (!PMS.state.initialized) {
        PMS.init();
    }

})();
