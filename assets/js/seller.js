// Seller-specific JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize seller-specific features
    initializeSellerFeatures();
});

// Initialize seller features
function initializeSellerFeatures() {
    // Setup modal functionality
    setupModals();
    
    // Setup service management
    setupServiceManagement();
}

// Setup modal functionality
function setupModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                closeModal();
            });
        }
        
        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    });
}

// Show add service modal
function showAddServiceModal() {
    const modal = document.getElementById('addServiceModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Focus on first input
        const firstInput = modal.querySelector('input');
        if (firstInput) {
            firstInput.focus();
        }
    }
}

// Close modal
function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
    document.body.style.overflow = 'auto';
    
    // Reset forms
    const forms = document.querySelectorAll('.modal form');
    forms.forEach(form => {
        form.reset();
    });
}

// Setup service management
function setupServiceManagement() {
    // Add service form submission
    const addServiceForm = document.querySelector('#addServiceModal form');
    if (addServiceForm) {
        addServiceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAddService(this);
        });
    }
}

// Handle add service
function handleAddService(form) {
    const formData = new FormData(form);
    const serviceData = {
        service_name: formData.get('service_name'),
        description: formData.get('description'),
        price: formData.get('price'),
        duration: formData.get('duration')
    };
    
    // Validate form data
    if (!validateServiceData(serviceData)) {
        return;
    }
    
    // Show loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Service...';
    submitBtn.disabled = true;
    
    // Simulate API call (replace with actual AJAX request)
    setTimeout(() => {
        // Success simulation
        showAlert('Service added successfully!', 'success');
        closeModal();
        
        // Add service to the grid (in real implementation, reload or update dynamically)
        addServiceToGrid(serviceData);
        
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 1500);
}

// Validate service data
function validateServiceData(data) {
    if (!data.service_name || data.service_name.trim().length < 3) {
        showAlert('Service name must be at least 3 characters long', 'error');
        return false;
    }
    
    if (!data.description || data.description.trim().length < 10) {
        showAlert('Description must be at least 10 characters long', 'error');
        return false;
    }
    
    if (!data.price || parseFloat(data.price) <= 0) {
        showAlert('Price must be greater than 0', 'error');
        return false;
    }
    
    if (!data.duration || data.duration.trim().length < 3) {
        showAlert('Duration is required', 'error');
        return false;
    }
    
    return true;
}

// Add service to grid (visual update)
function addServiceToGrid(serviceData) {
    const servicesGrid = document.querySelector('.services-grid');
    const emptyState = document.querySelector('.empty-state');
    
    if (emptyState) {
        emptyState.remove();
    }
    
    if (!servicesGrid) {
        // Create services grid if it doesn't exist
        const servicesSection = document.getElementById('services');
        const newGrid = document.createElement('div');
        newGrid.className = 'services-grid';
        servicesSection.appendChild(newGrid);
    }
    
    const serviceCard = document.createElement('div');
    serviceCard.className = 'service-card';
    serviceCard.innerHTML = `
        <div class="service-header">
            <h3>${serviceData.service_name}</h3>
            <div class="service-actions">
                <button class="btn btn-small" onclick="editService(0)">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-small btn-danger" onclick="deleteService(0)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <p>${serviceData.description}</p>
        <div class="service-details">
            <span class="service-price">$${parseFloat(serviceData.price).toFixed(2)}</span>
            <span class="service-duration">
                <i class="fas fa-clock"></i> 
                ${serviceData.duration}
            </span>
        </div>
        <div class="service-status">
            <span class="status status-active">Active</span>
        </div>
    `;
    
    const grid = document.querySelector('.services-grid');
    if (grid) {
        grid.appendChild(serviceCard);
    }
}

// Edit service
function editService(serviceId) {
    // Create edit modal or redirect to edit page
    showAlert('Edit functionality will be implemented here', 'info');
}

// Delete service
function deleteService(serviceId) {
    if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
        // Here you would make an AJAX request to delete the service
        showAlert('Service deleted successfully', 'success');
        
        // Remove service card from DOM (in real implementation)
        // const serviceCard = document.querySelector(`[data-service-id="${serviceId}"]`);
        // if (serviceCard) {
        //     serviceCard.remove();
        // }
    }
}

// Export functions for global use
window.showAddServiceModal = showAddServiceModal;
window.closeModal = closeModal;
window.editService = editService;
window.deleteService = deleteService;