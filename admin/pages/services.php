<?php
require_once '../../includes/config.php';

// Check if user is logged in and has seller role
if (!isLoggedIn()) {
    redirectTo('../../pages/login.php');
}

if (isUser()) {
    redirectTo('../../user/pages/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        $service_name = trim($_POST['service_name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $duration = trim($_POST['duration']);
        
        if (empty($service_name) || empty($price)) {
            $error = 'Service name and price are required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO services (seller_id, service_name, description, price, duration) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $service_name, $description, $price, $duration]);
                $message = 'Service added successfully!';
            } catch (PDOException $e) {
                $error = 'Failed to add service. Please try again.';
            }
        }
    }
    
    if (isset($_POST['update_service'])) {
        $service_id = (int)$_POST['service_id'];
        $service_name = trim($_POST['service_name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $duration = trim($_POST['duration']);
        $status = $_POST['status'];
        
        try {
            $stmt = $pdo->prepare("UPDATE services SET service_name = ?, description = ?, price = ?, duration = ?, status = ? WHERE id = ? AND seller_id = ?");
            $stmt->execute([$service_name, $description, $price, $duration, $status, $service_id, $user_id]);
            $message = 'Service updated successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to update service.';
        }
    }
    
    if (isset($_POST['delete_service'])) {
        $service_id = (int)$_POST['service_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND seller_id = ?");
            $stmt->execute([$service_id, $user_id]);
            $message = 'Service deleted successfully!';
        } catch (PDOException $e) {
            $error = 'Cannot delete service. It may have existing appointments.';
        }
    }
}

// Get services
try {
    $stmt = $pdo->prepare("
        SELECT s.*, 
               COUNT(a.id) as appointment_count,
               SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending_appointments
        FROM services s 
        LEFT JOIN appointments a ON s.id = a.service_id 
        WHERE s.seller_id = ? 
        GROUP BY s.id 
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
}

// Use main layout
$page_title = 'Manage Services';
$current_page = 'services';
require_once '../layouts/main.php';
startAdminLayout($page_title, $current_page);
?>

<div class="admin-content">
    <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Services Header -->
    <div class="page-header">
        <div class="header-content">
            <h1>Manage Services</h1>
            <p>Add, edit, and manage your laundry services</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openAddServiceModal()">
                <i class="fas fa-plus"></i>
                Add New Service
            </button>
        </div>
    </div>

    <!-- Services Grid -->
    <div class="services-grid">
        <?php if (empty($services)): ?>
            <div class="empty-state">
                <i class="fas fa-tshirt"></i>
                <h3>No services yet</h3>
                <p>Add your first laundry service to get started!</p>
                <button class="btn btn-primary" onclick="openAddServiceModal()">
                    <i class="fas fa-plus"></i>
                    Add Service
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <div class="service-header">
                        <div class="service-title">
                            <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
                            <span class="service-status status-<?php echo $service['status']; ?>">
                                <?php echo ucfirst($service['status']); ?>
                            </span>
                        </div>
                        <div class="service-price">₱<?php echo number_format($service['price'], 2); ?></div>
                    </div>
                    
                    <div class="service-body">
                        <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                        
                        <div class="service-meta">
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo htmlspecialchars($service['duration']); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar-check"></i>
                                <span><?php echo $service['appointment_count']; ?> appointments</span>
                            </div>
                            <?php if ($service['pending_appointments'] > 0): ?>
                                <div class="meta-item pending">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $service['pending_appointments']; ?> pending</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="service-actions">
                        <button class="btn btn-outline" onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                        <button class="btn btn-danger" onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['service_name']); ?>')">
                            <i class="fas fa-trash"></i>
                            Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Service Modal -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Service</h2>
            <button class="modal-close" onclick="closeServiceModal()">&times;</button>
        </div>
        
        <form id="serviceForm" method="POST">
            <input type="hidden" id="serviceId" name="service_id">
            <input type="hidden" id="formAction" name="add_service" value="1">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="serviceName">Service Name *</label>
                    <input type="text" id="serviceName" name="service_name" required>
                </div>
                
                <div class="form-group">
                    <label for="servicePrice">Price ($) *</label>
                    <input type="number" id="servicePrice" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="serviceDuration">Duration</label>
                    <select id="serviceDuration" name="duration">
                        <option value="2 hours">2 hours</option>
                        <option value="4 hours">4 hours</option>
                        <option value="6 hours">6 hours</option>
                        <option value="12 hours">12 hours</option>
                        <option value="24 hours" selected>24 hours</option>
                        <option value="48 hours">48 hours</option>
                        <option value="72 hours">72 hours</option>
                    </select>
                </div>
                
                <div class="form-group" id="statusGroup" style="display: none;">
                    <label for="serviceStatus">Status</label>
                    <select id="serviceStatus" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label for="serviceDescription">Description</label>
                    <textarea id="serviceDescription" name="description" rows="3" placeholder="Describe your service..."></textarea>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeServiceModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <span id="submitText">Add Service</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content small">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <p>Are you sure you want to delete "<span id="deleteServiceName"></span>"?</p>
            <p class="warning">This action cannot be undone.</p>
        </div>
        
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                <input type="hidden" id="deleteServiceId" name="service_id">
                <input type="hidden" name="delete_service" value="1">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Delete Service
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.admin-content {
    padding: 2rem;
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.header-content h1 {
    margin: 0 0 0.5rem;
    color: #1f2937;
    font-size: 2rem;
    font-weight: 700;
}

.header-content p {
    margin: 0;
    color: #6b7280;
    font-size: 1.125rem;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.service-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.service-header {
    padding: 1.5rem;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.service-title h3 {
    margin: 0 0 0.5rem;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.service-status {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.service-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #059669;
}

.service-body {
    padding: 1.5rem;
}

.service-description {
    margin: 0 0 1.5rem;
    color: #6b7280;
    line-height: 1.6;
}

.service-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.meta-item i {
    width: 1rem;
    color: #9ca3af;
}

.meta-item.pending {
    color: #d97706;
}

.meta-item.pending i {
    color: #d97706;
}

.service-actions {
    padding: 1rem 1.5rem;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

.empty-state h3 {
    margin: 0 0 0.5rem;
    color: #6b7280;
    font-size: 1.5rem;
}

.empty-state p {
    margin: 0 0 2rem;
    font-size: 1.125rem;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    margin: 5% auto;
    border-radius: 1rem;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-content.small {
    max-width: 400px;
}

.modal-header {
    padding: 2rem 2rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: #1f2937;
    font-size: 1.5rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 2rem;
    color: #9ca3af;
    cursor: pointer;
    padding: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #6b7280;
}

.modal-body {
    padding: 2rem;
}

.modal-body .warning {
    color: #dc2626;
    font-weight: 500;
    margin-top: 1rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    padding: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.modal-actions {
    padding: 1rem 2rem 2rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-outline {
    background: transparent;
    color: #3b82f6;
    border: 1px solid #3b82f6;
}

.btn-outline:hover {
    background: #3b82f6;
    color: white;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        padding: 1.5rem;
    }
    
    .modal-content {
        margin: 2% auto;
        width: 95%;
    }
    
    .service-actions {
        flex-direction: column;
    }
}
</style>

<script>
function openAddServiceModal() {
    document.getElementById('modalTitle').textContent = 'Add New Service';
    document.getElementById('submitText').textContent = 'Add Service';
    document.getElementById('formAction').name = 'add_service';
    document.getElementById('statusGroup').style.display = 'none';
    document.getElementById('serviceForm').reset();
    document.getElementById('serviceModal').style.display = 'block';
}

function editService(service) {
    document.getElementById('modalTitle').textContent = 'Edit Service';
    document.getElementById('submitText').textContent = 'Update Service';
    document.getElementById('formAction').name = 'update_service';
    document.getElementById('statusGroup').style.display = 'block';
    
    document.getElementById('serviceId').value = service.id;
    document.getElementById('serviceName').value = service.service_name;
    document.getElementById('serviceDescription').value = service.description;
    document.getElementById('servicePrice').value = service.price;
    document.getElementById('serviceDuration').value = service.duration;
    document.getElementById('serviceStatus').value = service.status;
    
    document.getElementById('serviceModal').style.display = 'block';
}

function closeServiceModal() {
    document.getElementById('serviceModal').style.display = 'none';
}

function deleteService(id, name) {
    document.getElementById('deleteServiceId').value = id;
    document.getElementById('deleteServiceName').textContent = name;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const serviceModal = document.getElementById('serviceModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === serviceModal) {
        closeServiceModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
}
</script>

<?php
endAdminLayout();
?>
