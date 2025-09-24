<?php
// Admin Sidebar Component
function renderAdminSidebar($currentPage = '') {
?>
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-tshirt"></i>
                <h2>StarWash</h2>
                <span class="admin-badge">ADMIN</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-section-title">MAIN</span>
                <a href="../pages/dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>

                <a href="../pages/appointment.php" class="nav-item <?php echo $currentPage === 'appointments' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Appointments</span>
                </a>

                <a href="../pages/analytics.php" class="nav-item <?php echo $currentPage === 'analytics' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </div>
            
            <div class="nav-section">
                <span class="nav-section-title">MANAGEMENT</span>
                <a href="../pages/users.php" class="nav-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                    <span class="nav-badge">245</span>
                </a>
                <a href="../pages/sellers.php" class="nav-item <?php echo $currentPage === 'sellers' ? 'active' : ''; ?>">
                    <i class="fas fa-store"></i>
                    <span>Service Providers</span>
                    <span class="nav-badge">15</span>
                </a>
                <a href="../pages/services.php" class="nav-item <?php echo $currentPage === 'services' ? 'active' : ''; ?>">
                    <i class="fas fa-cogs"></i>
                    <span>Services</span>
                </a>
                <div class="nav-item-wrapper">
                    <a href="../pages/orders.php" class="nav-item <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                        <i class="fas fa-list-alt"></i>
                        <span>Orders</span>
                        <span class="nav-badge new">12</span>
                    </a>
                    <button class="panel-toggle-btn" data-panel="orders" aria-label="Open orders panel">
                        <i class="fas fa-angle-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="nav-section">
                <span class="nav-section-title">FINANCE</span>
                <a href="../pages/revenue.php" class="nav-item <?php echo $currentPage === 'revenue' ? 'active' : ''; ?>">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Revenue</span>
                </a>
                <a href="../pages/payments.php" class="nav-item <?php echo $currentPage === 'payments' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
                <a href="../pages/reports.php" class="nav-item <?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                </a>
            </div>
            
            <div class="nav-section">
                <span class="nav-section-title">SYSTEM</span>
                <a href="../pages/settings.php" class="nav-item <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-sliders-h"></i>
                    <span>Settings</span>
                </a>
                <a href="../pages/notifications.php" class="nav-item <?php echo $currentPage === 'notifications' ? 'active' : ''; ?>">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
                <!-- System Logs removed per request -->
            </div>
        </nav>
        
        <!-- Sidebar footer (system status) removed -->
    </aside>
<?php
}
?>