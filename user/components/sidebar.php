<?php
// User Sidebar Component
function renderUserSidebar($currentPage = '') {
?>
   
        <div class="left-brand-sidebar">
            <aside class="user-sidebar" id="userSidebar">
                <div class="sidebar-content">
            <div class="user-welcome">
                <div class="welcome-avatar">
                    <?php
                    $sideAvatar = '../../assets/images/default-avatar.png';
                    if (!empty($_SESSION['avatar'])) {
                        $sideAvatar = $_SESSION['avatar'];
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($sideAvatar); ?>" alt="Avatar" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-user-circle\'></i>'">
                </div>
                <div class="welcome-text">
                    <h3>Welcome back!</h3>
                    <p><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Customer'); ?></p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-group">
                    <a href="../pages/dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="../pages/services.php" class="nav-item <?php echo $currentPage === 'services' ? 'active' : ''; ?>">
                        <i class="fas fa-search"></i>
                        <span>Browse Services</span>
                    </a>
                </div>
                
                <!-- Removed account/support groups per request -->
            </nav>
            
            <!-- Removed promo card; add quick links for users -->
            <div class="nav-group">
                <a href="../pages/appointments.php" class="nav-item <?php echo $currentPage === 'appointments' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointment</span>
                </a>

                <a href="../pages/history.php" class="nav-item <?php echo $currentPage === 'history' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>History</span>
                </a>

                <!-- Services link removed (Browse Services is available in the top group) -->
            </div>
            </div>
            </aside>
        </div>
<?php
}
?>