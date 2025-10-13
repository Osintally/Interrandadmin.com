// PMS Core functionality
window.PMS = {
    init: function(config) {
        this.config = config;
        this.initializeComponents();
        this.setupEventListeners();
    },

    initializeComponents: function() {
        // Initialize charts if they exist
        if (typeof Chart !== 'undefined') {
            Chart.defaults.color = '#64748b';
            Chart.defaults.borderColor = '#e2e8f0';
        }
    },

    setupEventListeners: function() {
        document.addEventListener('DOMContentLoaded', () => {
            this.setupSidebarToggle();
            this.setupNotifications();
            this.setupSearchFunctionality();
        });
    },

    setupSidebarToggle: function() {
        const toggleBtn = document.querySelector('[data-toggle="sidebar"]');
        const sidebar = document.querySelector('.pms-sidebar');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');
            });
        }
    },

    setupNotifications: function() {
        const notificationBtn = document.querySelector('[data-toggle="notifications"]');
        const notificationPanel = document.querySelector('.notifications-panel');
        
        if (notificationBtn && notificationPanel) {
            notificationBtn.addEventListener('click', () => {
                notificationPanel.classList.toggle('show');
            });

            // Close notifications when clicking outside
            document.addEventListener('click', (e) => {
                if (!notificationBtn.contains(e.target) && !notificationPanel.contains(e.target)) {
                    notificationPanel.classList.remove('show');
                }
            });
        }
    },

    setupSearchFunctionality: function() {
        const searchInput = document.querySelector('.pms-search-input');
        const searchResults = document.querySelector('.search-results');
        
        if (searchInput) {
            let debounceTimer;
            
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        }
    },

    performSearch: function(query) {
        if (!query.trim()) {
            document.querySelector('.search-results')?.classList.remove('show');
            return;
        }

        fetch(`${this.config.baseUrl}/api/pms/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                this.updateSearchResults(data);
            })
            .catch(error => {
                console.error('Search failed:', error);
            });
    },

    updateSearchResults: function(results) {
        const resultsContainer = document.querySelector('.search-results');
        if (!resultsContainer) return;

        resultsContainer.innerHTML = '';
        resultsContainer.classList.add('show');

        if (results.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No results found</div>';
            return;
        }

        const list = document.createElement('ul');
        results.forEach(result => {
            const item = document.createElement('li');
            item.innerHTML = `
                <a href="${result.url}" class="search-result-item">
                    <i class="${result.icon}"></i>
                    <div class="result-content">
                        <div class="result-title">${result.title}</div>
                        <div class="result-description">${result.description}</div>
                    </div>
                </a>
            `;
            list.appendChild(item);
        });

        resultsContainer.appendChild(list);
    },

    // Utility functions
    formatDate: function(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },

    formatNumber: function(number) {
        return new Intl.NumberFormat('en-US').format(number);
    },

    showToast: function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `pms-toast ${type}`;
        toast.innerHTML = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }, 100);
    }
};