// Dashboard JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Sidebar navigation
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;
            if (section) {
                showSection(section);
                setActiveNavItem(this);
            }
        });
    });
});

// Initialize dashboard
function initializeDashboard() {
    // Show overview section by default
    showSection('overview');
    
    // Initialize charts if needed
    initializeCharts();
    
    // Setup event listeners
    setupEventListeners();
}

// Show specific section
function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.dashboard-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Show target section
    const targetSection = document.getElementById(sectionName);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Update nav item
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.classList.remove('active');
        if (item.dataset.section === sectionName) {
            item.classList.add('active');
        }
    });
}

// Set active navigation item
function setActiveNavItem(activeItem) {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.classList.remove('active');
    });
    activeItem.classList.add('active');
}

// Setup event listeners
function setupEventListeners() {
    // Status select changes
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.className = `status-select status-${this.value}`;
        });
    });
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterResults(this.value);
        });
    }
}

// Initialize charts (placeholder for future implementation)
function initializeCharts() {
    // This can be expanded to include actual chart libraries like Chart.js
    console.log('Charts initialized');
}

// Filter results based on search
function filterResults(searchTerm) {
    const items = document.querySelectorAll('.filterable-item');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        item.style.display = matches ? 'block' : 'none';
    });
}

// Book service (for user dashboard)
function bookService(serviceId) {
    if (confirm('Are you sure you want to book this service?')) {
        // Here you would typically make an AJAX request to book the service
        showAlert('Service booking request sent! You will be contacted shortly.', 'success');
    }
}

// View order details
function viewOrder(orderId) {
    // Create and show order modal
    const modal = createOrderModal(orderId);
    document.body.appendChild(modal);
    modal.style.display = 'block';
}

// Create order modal
function createOrderModal(orderId) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details #${orderId.toString().padStart(4, '0')}</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    Loading order details...
                </div>
            </div>
        </div>
    `;
    
    // Close modal functionality
    const closeBtn = modal.querySelector('.close');
    closeBtn.addEventListener('click', function() {
        modal.remove();
    });
    
    // Click outside to close
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    // Load order details (placeholder)
    setTimeout(() => {
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = `
            <div class="order-details">
                <div class="detail-row">
                    <label>Order ID:</label>
                    <span>#${orderId.toString().padStart(4, '0')}</span>
                </div>
                <div class="detail-row">
                    <label>Service:</label>
                    <span>Wash & Fold</span>
                </div>
                <div class="detail-row">
                    <label>Status:</label>
                    <span class="status status-pending">Pending</span>
                </div>
                <div class="detail-row">
                    <label>Amount:</label>
                    <span>₱15.99</span>
                </div>
                <div class="detail-row">
                    <label>Order Date:</label>
                    <span>${new Date().toLocaleDateString()}</span>
                </div>
            </div>
        `;
    }, 1000);
    
    return modal;
}

// Update order status (for seller dashboard)
function updateOrderStatus(orderId, status) {
    // Here you would make an AJAX request to update the order status
    showAlert(`Order #${orderId} status updated to ${status}`, 'success');
}

// Show alert message
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
        ${message}
        <button class="alert-close">&times;</button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
    
    // Manual close
    const closeBtn = alert.querySelector('.alert-close');
    closeBtn.addEventListener('click', function() {
        alert.remove();
    });
}

// Responsive sidebar toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('mobile-open');
}

// Handle responsive design
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('mobile-open');
    }
});

// Export functions for global use
window.showSection = showSection;
window.bookService = bookService;
window.viewOrder = viewOrder;
window.updateOrderStatus = updateOrderStatus;
window.toggleSidebar = toggleSidebar;