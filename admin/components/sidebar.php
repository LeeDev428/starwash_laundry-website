<?php
// Admin Sidebar Component
function renderAdminSidebar($currentPage = '') {
?>
    <aside class="admin-sidebar sidebar-icon-only" id="adminSidebar">
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
                <a href="../pages/dashboard.php" class="nav-item show-label <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" aria-label="Dashboard">
                    <i class="fas fa-chart-pie" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>

                <a href="../pages/appointment.php" class="nav-item show-label <?php echo $currentPage === 'appointments' ? 'active' : ''; ?>" aria-label="Appointments">
                    <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                    <span>Appointments</span>
                </a>

                <a href="../pages/services.php" class="nav-item show-label <?php echo $currentPage === 'services' ? 'active' : ''; ?>" aria-label="Services">
                    <i class="fas fa-cogs" aria-hidden="true"></i>
                    <span>Services</span>
                </a>

                <!-- Profile moved to a bottom circular button for icon-only sidebar -->
            </div>
        </nav>
        
        <!-- Bottom profile button: use same nav-item design as other sidebar links; preserve avatar logic -->
        <div class="sidebar-footer">
            <a href="../pages/profile.php" class="nav-item show-label sidebar-profile-btn <?php echo $currentPage === 'profile' ? 'active' : ''; ?>" aria-label="Profile" title="Profile">
                <span class="sidebar-profile-icon">
                    <?php if (!empty($_SESSION['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="Profile avatar" class="sidebar-profile-avatar">
                    <?php else: ?>
                        <i class="fas fa-user" aria-hidden="true"></i>
                    <?php endif; ?>
                </span>
                <span class="sidebar-profile-label">Profile</span>
            </a>
        </div>
    </aside>
<?php
}
?>