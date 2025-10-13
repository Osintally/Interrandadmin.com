/**
 * CSRF Token Utility
 * Provides consistent CSRF token handling across the application
 */

(function() {
    'use strict';
    
    // CSRF Token utility object
    window.CSRF = {
        /**
         * Get CSRF token from various sources
         * @returns {string} CSRF token
         */
        getToken: function() {
            // Try to get from meta tag first
            var metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken && metaToken.getAttribute('content')) {
                return metaToken.getAttribute('content');
            }
            
            // Try Laravel global object
            if (window.Laravel && window.Laravel.csrfToken) {
                return window.Laravel.csrfToken;
            }
            
            // Fallback to global window object
            if (window.csrfToken) {
                return window.csrfToken;
            }
            
            // Fallback to global APP object
            if (window.APP && window.APP.CSRF_TOKEN) {
                return window.APP.CSRF_TOKEN;
            }
            
            // Last resort: try to get from any existing form
            var formToken = document.querySelector('input[name="_token"]');
            if (formToken && formToken.value) {
                return formToken.value;
            }
            
            console.warn('CSRF token not found! Please ensure the CSRF meta tag is present in the page head.');
            return null;
        },
        
        /**
         * Setup CSRF token for jQuery AJAX requests
         */
        setupAjax: function() {
            var token = this.getToken();
            if (token && window.jQuery) {
                jQuery.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                });
            }
        },
        
        /**
         * Add CSRF token to a form if not present
         * @param {HTMLElement|jQuery} form - Form element
         */
        addToForm: function(form) {
            var $form = jQuery ? jQuery(form) : form;
            var token = this.getToken();
            
            if (token && $form.length) {
                // Check if token already exists
                var existingToken = $form.find('input[name="_token"]');
                if (existingToken.length === 0) {
                    $form.append('<input type="hidden" name="_token" value="' + token + '">');
                } else {
                    // Update existing token
                    existingToken.val(token);
                }
            }
        },
        
        /**
         * Add CSRF token to all forms on the page
         */
        addToAllForms: function() {
            var token = this.getToken();
            if (token) {
                var forms = document.querySelectorAll('form');
                for (var i = 0; i < forms.length; i++) {
                    this.addToForm(forms[i]);
                }
            }
        },
        
        /**
         * Get headers object for fetch/axios requests
         * @returns {Object} Headers object
         */
        getHeaders: function() {
            var token = this.getToken();
            return token ? {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            } : {
                'X-Requested-With': 'XMLHttpRequest'
            };
        }
    };
    
    // Auto-setup when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            CSRF.setupAjax();
            CSRF.addToAllForms();
        });
    } else {
        CSRF.setupAjax();
        CSRF.addToAllForms();
    }
    
    // Also setup when jQuery is ready (if available)
    if (window.jQuery) {
        jQuery(document).ready(function() {
            CSRF.setupAjax();
            CSRF.addToAllForms();
        });
    }
    
})();
