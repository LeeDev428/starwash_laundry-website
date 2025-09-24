// Orders panel JS: toggle panel and basic UI handlers
(function(){
    document.addEventListener('click', function(e){
        // Handle panel toggle buttons
        const btn = e.target.closest && e.target.closest('.panel-toggle-btn');
        if (btn) {
            const panel = document.getElementById('adminOrdersPanel');
            if (!panel) return;
            const isOpen = panel.getAttribute('aria-hidden') === 'false';
            panel.setAttribute('aria-hidden', isOpen ? 'true' : 'false');
            return;
        }

        // Close on close button
        if (e.target.id === 'closeOrdersPanel' || e.target.closest && e.target.closest('#closeOrdersPanel')) {
            const panel = document.getElementById('adminOrdersPanel');
            if (!panel) return;
            panel.setAttribute('aria-hidden', 'true');
            return;
        }
    });

    // Bulk action handlers (UI-only)
    document.addEventListener('DOMContentLoaded', function(){
        const selectAll = document.getElementById('selectAllOrders');
        const performBulk = document.getElementById('performBulk');
        const bulkSelect = document.getElementById('bulkActionSelect');

        if (selectAll) {
            selectAll.addEventListener('change', function(){
                const checkboxes = document.querySelectorAll('.order-checkbox');
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });
        }

        if (performBulk) {
            performBulk.addEventListener('click', function(){
                const action = bulkSelect.value;
                if (!action) {
                    alert('Select a bulk action to perform');
                    return;
                }
                // Collect selected orders
                const selected = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
                if (selected.length === 0) {
                    alert('No orders selected');
                    return;
                }
                // For now, just show a message
                alert(`Performing "${action}" on ${selected.length} orders (IDs: ${selected.join(', ')})`);
                // TODO: Wire to backend endpoints to perform actual actions
            });
        }
    });
})();
