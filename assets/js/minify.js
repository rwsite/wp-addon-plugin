/**
 * WP Addon Asset Minification
 * Client-side fallback for asset optimization
 */

(function() {
    'use strict';

    // Basic CSS minification
    function minifyCss(css) {
        return css
            .replace(/\/\*[\s\S]*?\*\//g, '') // Remove comments
            .replace(/\s+/g, ' ') // Collapse whitespace
            .replace(/\s*([{}:;,>+~])\s*/g, '$1') // Remove spaces around selectors
            .replace(/;}/g, '}') // Remove trailing semicolons
            .trim();
    }

    // Basic JS minification
    function minifyJs(js) {
        return js
            .replace(/\/\*[\s\S]*?\*\//g, '') // Remove block comments
            .replace(/\/\/.*$/gm, '') // Remove line comments
            .replace(/\s+/g, ' ') // Collapse whitespace
            .replace(/\s*([{}:;,=()+\-*/&|<>!?~%^])\s*/g, '$1') // Remove spaces around operators
            .trim();
    }

    // Defer non-critical CSS loading
    function deferCssLoading() {
        var links = document.querySelectorAll('link[rel="stylesheet"]');
        links.forEach(function(link) {
            if (!link.id || link.id !== 'wp-addon-critical-css') {
                link.media = 'print';
                link.onload = function() {
                    link.media = 'all';
                };
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', deferCssLoading);
    } else {
        deferCssLoading();
    }

    // Expose functions globally for potential use
    window.WPAddonMinify = {
        minifyCss: minifyCss,
        minifyJs: minifyJs,
        deferCssLoading: deferCssLoading
    };

})();
