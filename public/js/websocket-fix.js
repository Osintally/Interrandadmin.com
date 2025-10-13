/**
 * WebSocket Connection Fix
 * Fixes the double slash issue in WebSocket URLs
 * Usage: Include this script before any WebSocket connections
 */

(function() {
    'use strict';
    
    // Store original WebSocket constructor
    const OriginalWebSocket = window.WebSocket;
    
    // Override WebSocket constructor to fix URL issues
    window.WebSocket = function(url, protocols) {
        // Fix double slash issue in WebSocket URLs
        if (typeof url === 'string') {
            // Replace double slashes with single slash, but preserve protocol
            url = url.replace(/([^:]\/)\/+/g, '$1');
            
            // Ensure proper WebSocket protocol
            if (url.startsWith('ws://') && url.includes('//ws')) {
                url = url.replace('//ws', '/ws');
            }
            if (url.startsWith('wss://') && url.includes('//ws')) {
                url = url.replace('//ws', '/ws');
            }
            
            console.log('WebSocket URL fixed:', url);
        }
        
        // Create WebSocket with fixed URL
        const ws = new OriginalWebSocket(url, protocols);
        
        // Add error handling
        ws.addEventListener('error', function(event) {
            console.error('WebSocket connection error:', event);
            console.error('Failed URL:', url);
        });
        
        ws.addEventListener('open', function(event) {
            console.log('WebSocket connected successfully:', url);
        });
        
        return ws;
    };
    
    // Copy static properties from original WebSocket
    Object.setPrototypeOf(window.WebSocket, OriginalWebSocket);
    Object.defineProperty(window.WebSocket, 'prototype', {
        value: OriginalWebSocket.prototype,
        writable: false
    });
    
    // Copy static constants
    Object.defineProperty(window.WebSocket, 'CONNECTING', {
        value: OriginalWebSocket.CONNECTING,
        writable: false
    });
    Object.defineProperty(window.WebSocket, 'OPEN', {
        value: OriginalWebSocket.OPEN,
        writable: false
    });
    Object.defineProperty(window.WebSocket, 'CLOSING', {
        value: OriginalWebSocket.CLOSING,
        writable: false
    });
    Object.defineProperty(window.WebSocket, 'CLOSED', {
        value: OriginalWebSocket.CLOSED,
        writable: false
    });
    
    console.log('WebSocket fix applied successfully');
})();
