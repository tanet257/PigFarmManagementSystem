/**
 * Storehouses Page JavaScript
 * Handles Bootstrap dropdown initialization for storehouses page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Wait for Bootstrap to be fully loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Storehouses: Bootstrap is not loaded');
        return;
    }

    // Get all dropdown toggles in this page only
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    
    // Initialize Bootstrap dropdowns
    const dropdownList = [...dropdownElementList].map(dropdownToggleEl => {
        return new bootstrap.Dropdown(dropdownToggleEl, {
            autoClose: true,
            popperConfig: null
        });
    });
    
    console.log('Storehouses: Initialized ' + dropdownList.length + ' dropdowns');
});
