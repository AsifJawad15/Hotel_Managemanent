/**
 * SmartStay Query Sidebar Toggle
 * Handles showing/hiding SQL query sidebar on admin pages
 */

function toggleQuerySidebar() {
    const sidebar = document.getElementById('querySidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}

// Close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('querySidebar');
    const toggleBtn = document.querySelector('.query-toggle-btn');
    
    if (sidebar && toggleBtn) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnToggleBtn = toggleBtn.contains(event.target);
        
        if (!isClickInsideSidebar && !isClickOnToggleBtn && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    }
});

// Keyboard shortcut: Press 'Q' to toggle sidebar
document.addEventListener('keydown', function(event) {
    if (event.key === 'q' || event.key === 'Q') {
        // Don't trigger if user is typing in an input field
        if (!event.target.matches('input, textarea')) {
            toggleQuerySidebar();
        }
    }
});
