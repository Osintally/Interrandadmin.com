/**
 * CSRF Token Preloader
 * Ensures CSRF token is available before vendor.js loads
 */

(function() {
    'use strict';
    
    // Get CSRF token from meta tag (try multiple variations)
    var metaToken = document.querySelector('meta[name="csrf-token"]') || 
                   document.querySelector('meta[name="csrf_token"]') || 
                   document.querySelector('meta[name="X-CSRF-TOKEN"]');
    var token = metaToken ? metaToken.getAttribute('content') : null;
    
    if (token) {
        // Set multiple global variables for maximum compatibility
        window.csrfToken = token;
        window.Laravel = window.Laravel || {};
        window.Laravel.csrfToken = token;
        
        // Set up for common Laravel patterns
        window.Laravel = {
            csrfToken: token
        };
        
        // Override common CSRF token access patterns
        Object.defineProperty(window, 'csrfToken', {
            value: token,
            writable: false,
            configurable: false
        });
        
        // Set up global CSRF token accessor
        window.getCsrfToken = function() {
            return token;
        };
        
        // Set up jQuery when it becomes available
        var setupJQuery = function() {
            if (typeof $ !== 'undefined' && $.ajaxSetup) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                });
            } else if (typeof $ === 'undefined') {
                setTimeout(setupJQuery, 50);
            }
        };
        
        // Start checking for jQuery immediately
        setupJQuery();
        
        // Also set up when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupJQuery);
        } else {
            setupJQuery();
        }
    } else {
        console.error('CSRF token not found in meta tag!');
    }
})();
