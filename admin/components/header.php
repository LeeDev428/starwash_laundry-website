<?php
// Admin Header Component
function renderAdminHeader($pageTitle = 'Admin Dashboard', $user = null) {
    $user = $user ?: $_SESSION;
?>
    <header class="admin-header">
        <div class="header-left">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- header title moved into page content to avoid overlap -->

        <div class="header-right">
            <div class="header-actions">
                <button class="action-btn" id="notifBtn" aria-haspopup="dialog" aria-controls="notifModal">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </button>

                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search orders, users...">
                </div>
            </div>
            
            <div class="admin-profile">
                <div class="profile-pill">
                    <div class="profile-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($user['full_name'] ?? 'Admin'); ?></span>
                        <span class="profile-role">Administrator</span>
                    </div>
                    <div class="profile-dropdown">
                        <button class="dropdown-toggle" onclick="toggleProfileMenu()" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="profileMenu">
                            <a href="../pages/profile.php"><i class="fas fa-user"></i> Profile</a>
                            <a href="../../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Notifications Modal -->
    <div id="notifModal" class="notif-modal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="notif-modal-backdrop" data-close></div>
        <div class="notif-modal-panel" role="document">
            <header class="notif-modal-header">
                <h3>Notifications</h3>
                <button class="notif-close" aria-label="Close notifications" data-close>&times;</button>
            </header>
            <div class="notif-modal-body">
                <!-- placeholder notifications; these can be rendered server-side -->
                <ul class="notif-list">
                    <li class="notif-item">
                        <strong>New Order</strong>
                        <p>Order #1234 placed by John Doe</p>
                        <time>2h ago</time>
                    </li>
                    <li class="notif-item">
                        <strong>Service Request</strong>
                        <p>New service added: Express Wash</p>
                        <time>1d ago</time>
                    </li>
                    <li class="notif-item empty">You're all caught up.</li>
                </ul>
            </div>
        </div>
    </div>
<?php
}
?>