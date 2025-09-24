<?php
require_once '../../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectTo('../../pages/login.php');
}

if (isSeller()) {
    redirectTo('../../admin/pages/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle form submission for new appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $service_id = (int)$_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $pickup_address = trim($_POST['pickup_address']);
    $delivery_address = trim($_POST['delivery_address']);
    $special_instructions = trim($_POST['special_instructions']);
    
    // Validation
    if (empty($service_id) || empty($appointment_date) || empty($appointment_time) || empty($pickup_address)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Get service price
            $stmt = $pdo->prepare("SELECT price FROM services WHERE id = ?");
            $stmt->execute([$service_id]);
            $service = $stmt->fetch();
            
            if ($service) {
                // Insert appointment
                $stmt = $pdo->prepare("
                    INSERT INTO appointments (user_id, service_id, appointment_date, appointment_time, 
                                            pickup_address, delivery_address, special_instructions, total_price) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $user_id, $service_id, $appointment_date, $appointment_time,
                    $pickup_address, $delivery_address, $special_instructions, $service['price']
                ]);
                
                $message = 'Appointment booked successfully!';
            } else {
                $error = 'Invalid service selected.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to book appointment. Please try again.';
        }
    }
}

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $user_id]);
        $message = 'Appointment cancelled successfully.';
    } catch (PDOException $e) {
        $error = 'Failed to cancel appointment.';
    }
}

// Get user's appointments
try {
    $stmt = $pdo->prepare("
        SELECT a.*, s.service_name, s.description as service_description, s.duration
        FROM appointments a 
        JOIN services s ON a.service_id = s.id 
        WHERE a.user_id = ? 
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $appointments = [];
}

// Get available services
try {
    $stmt = $pdo->prepare("SELECT * FROM services ORDER BY service_name");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
}

// Use main layout
$page_title = 'My Appointments';
$current_page = 'appointments';
require_once '../layouts/main.php';
startUserLayout($page_title, $current_page);
?>

<div class="appointments-page">
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

    <!-- Book New Appointment -->
    <div class="user-card">
        <div class="user-card-header">
            <h3 class="user-card-title">Book New Appointment</h3>
        </div>
        <div class="user-card-body">
            <form method="POST" class="appointment-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="service_id">Select Service *</label>
                        <select name="service_id" id="service_id" required>
                            <option value="">Choose a service...</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>" data-price="<?php echo $service['price']; ?>">
                                    <?php echo htmlspecialchars($service['service_name']); ?> - $<?php echo number_format($service['price'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="appointment_date">Appointment Date *</label>
                        <input type="date" name="appointment_date" id="appointment_date" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="appointment_time">Appointment Time *</label>
                        <select name="appointment_time" id="appointment_time" required>
                            <option value="">Select time...</option>
                            <option value="08:00:00">8:00 AM</option>
                            <option value="09:00:00">9:00 AM</option>
                            <option value="10:00:00">10:00 AM</option>
                            <option value="11:00:00">11:00 AM</option>
                            <option value="12:00:00">12:00 PM</option>
                            <option value="13:00:00">1:00 PM</option>
                            <option value="14:00:00">2:00 PM</option>
                            <option value="15:00:00">3:00 PM</option>
                            <option value="16:00:00">4:00 PM</option>
                            <option value="17:00:00">5:00 PM</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="pickup_address">Pickup Address *</label>
                        <textarea name="pickup_address" id="pickup_address" rows="3" 
                                  placeholder="Enter your pickup address..." required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="delivery_address">Delivery Address (optional)</label>
                        <textarea name="delivery_address" id="delivery_address" rows="3" 
                                  placeholder="Leave blank if same as pickup address..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="special_instructions">Special Instructions</label>
                        <textarea name="special_instructions" id="special_instructions" rows="3" 
                                  placeholder="Any special requests or instructions..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="book_appointment" class="user-btn user-btn-primary">
                        <i class="fas fa-calendar-plus"></i>
                        Book Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- My Appointments -->
    <div class="user-card">
        <div class="user-card-header">
            <h3 class="user-card-title">My Appointments</h3>
        </div>
        <div class="user-card-body">
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <h4>No appointments yet</h4>
                    <p>Book your first appointment using the form above!</p>
                </div>
            <?php else: ?>
                <div class="appointments-list">
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="appointment-item">
                            <div class="appointment-header">
                                <div class="appointment-service">
                                    <h4><?php echo htmlspecialchars($appointment['service_name']); ?></h4>
                                    <p class="service-desc"><?php echo htmlspecialchars($appointment['service_description']); ?></p>
                                </div>
                                <div class="appointment-status">
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="appointment-details">
                                <div class="detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($appointment['pickup_address']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span>$<?php echo number_format($appointment['total_price'], 2); ?></span>
                                </div>
                            </div>

                            <?php if (!empty($appointment['special_instructions'])): ?>
                                <div class="appointment-instructions">
                                    <strong>Special Instructions:</strong>
                                    <p><?php echo htmlspecialchars($appointment['special_instructions']); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($appointment['status'] === 'pending'): ?>
                                <div class="appointment-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <button type="submit" name="cancel_appointment" class="user-btn user-btn-secondary"
                                                onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                            <i class="fas fa-times"></i>
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.appointments-page {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
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

.appointment-form {
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
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
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
}

.appointments-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.appointment-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 1.5rem;
    transition: all 0.2s ease;
}

.appointment-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.appointment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.appointment-service h4 {
    margin: 0 0 0.25rem;
    color: #1f2937;
    font-size: 1.25rem;
}

.service-desc {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3cd;
    color: #92400e;
}

.status-confirmed {
    background: #dbeafe;
    color: #1e40af;
}

.status-in_progress {
    background: #e0e7ff;
    color: #5b21b6;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.appointment-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #4b5563;
}

.detail-item i {
    width: 1rem;
    color: #667eea;
}

.appointment-instructions {
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    margin-bottom: 1rem;
}

.appointment-instructions strong {
    color: #374151;
}

.appointment-instructions p {
    margin: 0.5rem 0 0;
    color: #6b7280;
}

.appointment-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

.empty-state h4 {
    margin: 0 0 0.5rem;
    color: #6b7280;
}

.empty-state p {
    margin: 0;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .appointment-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .appointment-details {
        grid-template-columns: 1fr;
    }
    
    .appointment-actions {
        justify-content: flex-start;
    }
}
</style>

<?php
endUserLayout();
?>
