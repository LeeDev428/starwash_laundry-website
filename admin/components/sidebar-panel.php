<?php
// Compact Admin Sidebar Panel Component (icon-only)
function renderAdminSidebarPanel($currentPage = '') {
?>
    <aside class="admin-sidebar-panel" id="adminSidebarPanel" aria-label="Admin sidebar">
        <div class="panel-top">
            <a href="../../index.php" class="panel-logo" title="StarWash">
                <img src="../../assets/images/logo-small.png" alt="StarWash" />
            </a>
        </div>

        <nav class="panel-nav" role="navigation">
            <a href="../pages/dashboard.php" class="panel-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" title="Dashboard">
                <i class="fas fa-chart-pie"></i>
            </a>

            <a href="../pages/create-order.php" class="panel-item <?php echo $currentPage === 'create-order' ? 'active' : ''; ?>" title="Create Order">
                <i class="fas fa-plus-square"></i>
            </a>

            <a href="../pages/invoice.php" class="panel-item <?php echo $currentPage === 'invoice' ? 'active' : ''; ?>" title="Raise Invoice">
                <i class="fas fa-file-invoice"></i>
            </a>

            <a href="../pages/pickups.php" class="panel-item <?php echo $currentPage === 'pickups' ? 'active' : ''; ?>" title="Customer Pickups">
                <i class="fas fa-truck"></i>
            </a>

            <a href="../pages/send-to-mss.php" class="panel-item <?php echo $currentPage === 'send-to-mss' ? 'active' : ''; ?>" title="Send to MSS">
                <i class="fas fa-share-square"></i>
            </a>

            <a href="../pages/mss-qc.php" class="panel-item <?php echo $currentPage === 'mss-qc' ? 'active' : ''; ?>" title="MSS QC Cases">
                <i class="fas fa-clipboard-check"></i>
            </a>

            <a href="../pages/reports.php" class="panel-item <?php echo $currentPage === 'reports' ? 'active' : ''; ?>" title="Reports">
                <i class="fas fa-chart-bar"></i>
            </a>
        </nav>

        <div class="panel-bottom">
            <div class="panel-language">
                <select aria-label="Language selector" class="panel-lang-select">
                    <option>English USA</option>
                    <option>Español</option>
                </select>
            </div>
        </div>
    </aside>
<?php
}
?>
