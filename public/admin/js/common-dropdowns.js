/**
 * Common Dropdown Initializer
 * This file initializes all Bootstrap dropdowns across admin pages
 * Load this file in pages that use Bootstrap dropdowns
 */

document.addEventListener('DOMContentLoaded', function() {
    // Wait for Bootstrap to be fully loaded
    if (typeof bootstrap === 'undefined') {
        console.warn('Common Dropdowns: Bootstrap is not loaded yet');
        return;
    }

    // Get all dropdown toggles
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');

    if (dropdownElementList.length === 0) {
        console.log('Common Dropdowns: No dropdown elements found');
        return;
    }

    // Initialize Bootstrap dropdowns
    const dropdownList = [...dropdownElementList].map(dropdownToggleEl => {
        return new bootstrap.Dropdown(dropdownToggleEl, {
            autoClose: true,
            popperConfig: null
        });
    });

    console.log('Common Dropdowns: Initialized ' + dropdownList.length + ' dropdowns');
});
