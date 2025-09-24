<?php
// Admin Orders Panel Component (slide-over)
function renderAdminOrdersPanel() {
?>
    <aside class="admin-orders-panel" id="adminOrdersPanel" aria-hidden="true">
        <div class="orders-panel-header">
            <h3>Orders</h3>
            <button class="close-panel" id="closeOrdersPanel" aria-label="Close orders panel">&times;</button>
        </div>

        <div class="orders-panel-controls">
            <div class="filters">
                <input type="text" id="ordersSearch" placeholder="Search orders, users, IDs...">
                <select id="ordersStatusFilter">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button id="applyFilters" class="admin-btn admin-btn-secondary">Apply</button>
            </div>

            <div class="bulk-actions">
                <label class="bulk-select">
                    <input type="checkbox" id="selectAllOrders">
                    <span>Select all</span>
                </label>
                <select id="bulkActionSelect">
                    <option value="">Bulk actions</option>
                    <option value="assign">Assign to staff</option>
                    <option value="change_status">Change status</option>
                    <option value="print">Print</option>
                </select>
                <button id="performBulk" class="admin-btn admin-btn-primary">Apply</button>
            </div>
        </div>

        <div class="orders-panel-body">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <!-- Rows will be populated via server or JS -->
                    <tr>
                        <td><input type="checkbox" class="order-checkbox" value="1"></td>
                        <td>#1001</td>
                        <td>Jane Doe</td>
                        <td>3</td>
                        <td>Pending</td>
                        <td>$12.00</td>
                        <td><button class="admin-btn admin-btn-sm">View</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </aside>
<?php
}
?>
