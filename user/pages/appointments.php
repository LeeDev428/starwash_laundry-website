<?php
require_once '../../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectTo('../../pages/login.php');
}

if (!isUser()) {
    redirectTo('../../admin/pages/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $service_id = (int)$_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $time_slot_id = (int)$_POST['time_slot_id'];
    $pickup_address = trim($_POST['pickup_address']);
    $delivery_address = trim($_POST['delivery_address']);
    $special_instructions = trim($_POST['special_instructions']);
    
    // Validate required fields
    if (empty($service_id) || empty($appointment_date) || empty($time_slot_id) || empty($pickup_address)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Check if slot is available (one appointment per slot)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND time_slot_id = ? AND status != 'cancelled'");
            $stmt->execute([$appointment_date, $time_slot_id]);
            $existing_bookings = $stmt->fetchColumn();
            
            if ($existing_bookings > 0) {
                $error = 'This time slot is already booked. Please choose another time.';
            } else {
                // Get service details for pricing
                $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
                $stmt->execute([$service_id]);
                $service = $stmt->fetch();
                
                if (!$service) {
                    $error = 'Selected service not found.';
                } else {
                    // Calculate pricing
                    $total_price = $service['price'];
                    
                    // Create appointment datetime
                    $stmt = $pdo->prepare("SELECT slot_time FROM time_slots WHERE id = ?");
                    $stmt->execute([$time_slot_id]);
                    $slot_time = $stmt->fetchColumn();
                    $appointment_datetime = $appointment_date . ' ' . $slot_time;
                    
                    // Insert appointment
                    $stmt = $pdo->prepare("
                        INSERT INTO appointments (
                            user_id, service_id, time_slot_id, appointment_date, appointment_datetime,
                            pickup_address, delivery_address, special_instructions, total_price, status
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                    ");
                    
                    $stmt->execute([
                        $user_id, $service_id, $time_slot_id, $appointment_date, $appointment_datetime,
                        $pickup_address, $delivery_address, $special_instructions, $total_price
                    ]);
                    
                    $message = 'Appointment booked successfully! We will contact you soon to confirm.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Failed to book appointment. Please try again.';
        }
    }
}

// Get user's appointments
try {
    $stmt = $pdo->prepare("
        SELECT a.*, s.service_name, s.description as service_description, 
               ts.slot_label, ts.slot_time
        FROM appointments a
        JOIN services s ON a.service_id = s.id
        JOIN time_slots ts ON a.time_slot_id = ts.id
        WHERE a.user_id = ?
        ORDER BY a.appointment_datetime DESC
    ");
    $stmt->execute([$user_id]);
    $user_appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $user_appointments = [];
}

// Get active services
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE status = 'active' ORDER BY service_name");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
}

// Get time slots
try {
    $stmt = $pdo->prepare("SELECT * FROM time_slots WHERE is_active = 1 ORDER BY slot_time");
    $stmt->execute();
    $time_slots = $stmt->fetchAll();
} catch (PDOException $e) {
    $time_slots = [];
}

// Get user info for default values
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch();
} catch (PDOException $e) {
    $user_info = [];
}

$page_title = 'Book Appointment';
$current_page = 'appointments';
require_once '../layouts/main.php';
startUserLayout($page_title, $current_page);
?>

<div class="appointments-container">
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

    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-calendar-alt"></i> Book Your Appointment</h1>
            <p>Select a date and time that works best for you</p>
        </div>
    </div>

    <div class="appointments-layout">
        <!-- Calendar Section -->
        <div class="calendar-section">
            <div class="calendar-container">
                <div class="calendar-header">
                    <button class="nav-btn" id="prevMonth">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h3 id="currentMonth">September 2025</h3>
                    <button class="nav-btn" id="nextMonth">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="calendar-grid">
                    <div class="calendar-weekdays">
                        <div class="weekday">Sun</div>
                        <div class="weekday">Mon</div>
                        <div class="weekday">Tue</div>
                        <div class="weekday">Wed</div>
                        <div class="weekday">Thu</div>
                        <div class="weekday">Fri</div>
                        <div class="weekday">Sat</div>
                    </div>
                    <div class="calendar-days" id="calendarDays">
                        <!-- Days will be generated by JavaScript -->
                    </div>
                </div>
                
                <div class="calendar-legend">
                    <div class="legend-item">
                        <div class="legend-color today"></div>
                        <span>Today</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color available"></div>
                        <span>Available</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color busy"></div>
                        <span>Busy</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color selected"></div>
                        <span>Selected</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Appointments Section -->
        <div class="appointments-list-section">
            <h2><i class="fas fa-list"></i> My Appointments</h2>
            
            <?php if (empty($user_appointments)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No appointments yet</h3>
                    <p>Select a date from the calendar to book your first appointment!</p>
                </div>
            <?php else: ?>
                <div class="appointments-list">
                    <?php foreach ($user_appointments as $appointment): ?>
                        <div class="appointment-card status-<?php echo $appointment['status']; ?>">
                            <div class="appointment-header">
                                <div class="service-info">
                                    <h4><?php echo htmlspecialchars($appointment['service_name']); ?></h4>
                                    <span class="appointment-number">#<?php echo str_pad($appointment['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <div class="appointment-status">
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $appointment['status'])); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="appointment-details">
                                <div class="detail-row">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $appointment['slot_label']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($appointment['pickup_address']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="currency-icon">₱</span>
                                    <span>₱<?php echo number_format($appointment['total_price'], 2); ?></span>
                                </div>
                            </div>
                            
                            <?php if ($appointment['status'] === 'pending'): ?>
                                <div class="appointment-actions">
                                    <button class="btn btn-outline btn-small" onclick="viewAppointment('<?php echo $appointment['id']; ?>')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Appointment Booking Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-calendar-plus"></i> Book Appointment</h2>
            <button class="modal-close" onclick="closeBookingModal()">&times;</button>
        </div>
        
        <form id="bookingForm" method="POST">
            <input type="hidden" name="book_appointment" value="1">
            <input type="hidden" id="selectedDate" name="appointment_date">
            
            <div class="modal-body">
                <div class="selected-date-info">
                    <h3>Selected Date: <span id="displayDate"></span></h3>
                </div>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="serviceSelect">Select Service *</label>
                        <select id="serviceSelect" name="service_id" required onchange="updateServiceDetails()">
                            <option value="">Choose a service...</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>" 
                                        data-price="<?php echo $service['price']; ?>"
                                        data-description="<?php echo htmlspecialchars($service['description']); ?>"
                                        data-duration="<?php echo htmlspecialchars($service['duration_text']); ?>">
                                    <?php echo htmlspecialchars($service['service_name']); ?> - ₱<?php echo number_format($service['price'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="serviceDetails" class="service-details" style="display: none;">
                            <p id="serviceDescription"></p>
                            <div class="service-meta">
                                <span><i class="fas fa-clock"></i> <span id="serviceDuration"></span></span>
                                <span><span class="currency-icon">₱</span> ₱<span id="servicePrice"></span></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="timeSlotSelect">Select Time *</label>
                        <div class="time-slots-grid" id="timeSlotsGrid">
                            <?php foreach ($time_slots as $slot): ?>
                                <label class="time-slot">
                                    <input type="radio" name="time_slot_id" value="<?php echo $slot['id']; ?>" required>
                                    <span class="time-slot-label"><?php echo $slot['slot_label']; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    

                    
                    <div class="form-group full-width">
                        <label for="pickupAddress">Pickup Address *</label>
                        <textarea id="pickupAddress" name="pickup_address" rows="2" required 
                                  placeholder="Enter your full pickup address..."><?php echo htmlspecialchars($user_info['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="deliveryAddress">Delivery Address (if different)</label>
                        <textarea id="deliveryAddress" name="delivery_address" rows="2" 
                                  placeholder="Leave blank if same as pickup address"></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="specialInstructions">Special Instructions</label>
                        <textarea id="specialInstructions" name="special_instructions" rows="3" 
                                  placeholder="Any special handling instructions..."></textarea>
                    </div>
                </div>
                
                <div class="booking-summary">
                    <h4>Booking Summary</h4>
                    <div class="summary-row">
                        <span>Service:</span>
                        <span id="summaryService">-</span>
                    </div>
                    <div class="summary-row">
                        <span>Date & Time:</span>
                        <span id="summaryDateTime">-</span>
                    </div>

                    <div class="summary-row total">
                        <span>Total Price:</span>
                        <span id="summaryTotal">₱0.00</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Book Appointment
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.appointments-container {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
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
    text-align: center;
    margin-bottom: 4rem;
    padding: 2rem 0;
}

.page-header h1 {
    color: #1e293b;
    font-size: 3rem;
    font-weight: 800;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.25rem;
    letter-spacing: -0.025em;
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-header h1 i {
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-header p {
    color: #64748b;
    font-size: 1.25rem;
    margin: 0;
    font-weight: 500;
}

.appointments-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: start;
}

/* Calendar Styles - Modern Design */
.calendar-section {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    overflow: hidden;
}

.calendar-container {
    padding: 2.5rem;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2.5rem;
}

.calendar-header h3 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: -0.025em;
}

.nav-btn {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #64748b;
    font-size: 0.875rem;
}

.nav-btn:hover {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding: 0 0.5rem;
}

.weekday {
    text-align: center;
    font-weight: 600;
    color: #64748b;
    padding: 1rem 0;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.75rem;
    padding: 0 0.5rem;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    position: relative;
    border: 2px solid transparent;
    font-size: 1rem;
    background: #f8fafc;
    color: #475569;
}

.calendar-day:hover {
    background: #e2e8f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.calendar-day.other-month {
    color: #cbd5e1;
    background: transparent;
    cursor: not-allowed;
}

.calendar-day.other-month:hover {
    transform: none;
    box-shadow: none;
    background: transparent;
}

.calendar-day.today {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.calendar-day.today:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.calendar-day.available {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-color: #10b981;
}

.calendar-day.available:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
}

.calendar-day.busy {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    cursor: not-allowed;
    opacity: 0.7;
}

.calendar-day.busy:hover {
    transform: none;
    box-shadow: none;
}

.calendar-day.selected {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    border-color: #8b5cf6;
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
}

.calendar-day.past {
    color: #cbd5e1;
    background: #f1f5f9;
    cursor: not-allowed;
    opacity: 0.6;
}

.calendar-day.past:hover {
    transform: none;
    box-shadow: none;
    background: #f1f5f9;
}

.calendar-legend {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}

.legend-color {
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.legend-color.today {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.legend-color.available {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.legend-color.busy {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.legend-color.selected {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

/* Appointments List Styles */
.appointments-list-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    padding: 2rem;
}

.appointments-list-section h2 {
    margin: 0 0 2rem;
    color: #1f2937;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
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

.empty-state h3 {
    margin: 0 0 0.5rem;
    color: #6b7280;
    font-size: 1.25rem;
}

.empty-state p {
    margin: 0;
    font-size: 1rem;
}

.appointments-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.appointment-card {
    background: #f9fafb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.appointment-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.appointment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.service-info h4 {
    margin: 0 0 0.25rem;
    color: #1f2937;
    font-size: 1.125rem;
    font-weight: 600;
}

.appointment-number {
    font-size: 0.875rem;
    color: #6b7280;
    font-family: monospace;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.status-in-progress {
    background: #dbeafe;
    color: #1e40af;
}

.status-completed {
    background: #e0e7ff;
    color: #5b21b6;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.appointment-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.detail-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.detail-row i {
    width: 1rem;
    color: #9ca3af;
}

.appointment-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
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
    margin: 2% auto;
    border-radius: 1rem;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
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
    display: flex;
    align-items: center;
    gap: 0.75rem;
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

.selected-date-info {
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 2rem;
    text-align: center;
}

.selected-date-info h3 {
    margin: 0;
    color: #0c4a6e;
    font-size: 1.125rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
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

.service-details {
    margin-top: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

.service-details p {
    margin: 0 0 1rem;
    color: #6b7280;
    line-height: 1.5;
}

.service-meta {
    display: flex;
    gap: 1.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.service-meta span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.time-slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
}

.time-slot {
    position: relative;
    cursor: pointer;
}

.time-slot input {
    display: none;
}

.time-slot-label {
    display: block;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    text-align: center;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.2s ease;
}

.time-slot input:checked + .time-slot-label {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.time-slot:hover .time-slot-label {
    border-color: #3b82f6;
    color: #3b82f6;
}

.booking-summary {
    background: #f9fafb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-top: 2rem;
    border: 1px solid #e5e7eb;
}

.booking-summary h4 {
    margin: 0 0 1rem;
    color: #1f2937;
    font-size: 1.125rem;
    font-weight: 600;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-weight: 600;
    font-size: 1.125rem;
    color: #1f2937;
    padding-top: 1rem;
    border-top: 2px solid #e5e7eb;
    margin-top: 1rem;
}

.modal-actions {
    padding: 1rem 2rem 2rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    border-top: 1px solid #e5e7eb;
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

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
}

@media (max-width: 1024px) {
    .appointments-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
    
    .calendar-legend {
        flex-wrap: wrap;
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .appointments-container {
        padding: 1rem;
    }
    
    .page-header h1 {
        font-size: 1.75rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .calendar-container,
    .appointments-list-section {
        padding: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .time-slots-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .appointment-details {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        margin: 1rem;
        width: calc(100% - 2rem);
    }
    
    .calendar-legend {
        justify-content: space-between;
        gap: 0.5rem;
    }
    
    .legend-item {
        font-size: 0.75rem;
    }
}
</style>

<script>
// Calendar variables
let currentDate = new Date();
let selectedDate = null;
const today = new Date();

// Appointment availability data (will be populated via AJAX)
let appointmentData = {};

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
    loadAppointmentData();
    
    document.getElementById('prevMonth').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
        loadAppointmentData();
    });
    
    document.getElementById('nextMonth').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
        loadAppointmentData();
    });
});

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Update header
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const firstDayOfWeek = firstDay.getDay();
    const daysInMonth = lastDay.getDate();
    
    // Get previous month info for padding
    const prevMonth = new Date(year, month, 0);
    const daysInPrevMonth = prevMonth.getDate();
    
    const calendarDays = document.getElementById('calendarDays');
    calendarDays.innerHTML = '';
    
    // Add previous month's trailing days
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const dayElement = createDayElement(daysInPrevMonth - i, 'other-month');
        calendarDays.appendChild(dayElement);
    }
    
    // Add current month's days
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = createDayElement(day, 'current-month');
        calendarDays.appendChild(dayElement);
    }
    
    // Add next month's leading days
    const totalCells = calendarDays.children.length;
    const remainingCells = 42 - totalCells; // 6 rows × 7 days
    for (let day = 1; day <= remainingCells; day++) {
        const dayElement = createDayElement(day, 'other-month');
        calendarDays.appendChild(dayElement);
    }
}

function createDayElement(day, monthType) {
    const dayElement = document.createElement('div');
    dayElement.className = 'calendar-day';
    dayElement.textContent = day;
    
    if (monthType === 'other-month') {
        dayElement.classList.add('other-month');
        return dayElement;
    }
    
    const currentDateObj = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
    const dateStr = formatDate(currentDateObj);
    
    // Check if it's today
    if (isSameDate(currentDateObj, today)) {
        dayElement.classList.add('today');
    }
    
    // Check if it's in the past
    if (currentDateObj < today) {
        dayElement.classList.add('past');
        return dayElement;
    }
    
    // Check availability (will be updated when data loads)
    updateDayAvailability(dayElement, dateStr);
    
    // Add click handler for future dates
    dayElement.addEventListener('click', () => selectDate(currentDateObj, dayElement));
    
    return dayElement;
}

function updateDayAvailability(dayElement, dateStr) {
    if (appointmentData[dateStr]) {
        const dayData = appointmentData[dateStr];
        
        if (dayData.is_fully_booked) {
            dayElement.classList.add('busy');
        } else {
            dayElement.classList.add('available');
        }
    } else {
        dayElement.classList.add('available');
    }
}

function selectDate(date, dayElement) {
    if (dayElement.classList.contains('past') || dayElement.classList.contains('other-month')) {
        return;
    }
    
    // Remove previous selection
    document.querySelectorAll('.calendar-day.selected').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Add selection to clicked day
    dayElement.classList.add('selected');
    selectedDate = date;
    
    openBookingModal(date);
}

function openBookingModal(date) {
    const dateStr = formatDate(date);
    const displayDate = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    document.getElementById('selectedDate').value = dateStr;
    document.getElementById('displayDate').textContent = displayDate;
    
    // Update time slots availability
    updateTimeSlots(dateStr);
    
    document.getElementById('bookingModal').style.display = 'block';
}

function updateTimeSlots(dateStr) {
    const timeSlots = document.querySelectorAll('.time-slot');
    
    // Fetch real-time availability for the specific date
    fetch(`../../api/availability.php?date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const slotsData = data.slots;
                
                timeSlots.forEach(slot => {
                    const slotId = slot.querySelector('input').value;
                    const slotData = slotsData.find(s => s.id == slotId);
                    
                    if (slotData && slotData.available <= 0) {
                        slot.classList.add('unavailable');
                        slot.querySelector('input').disabled = true;
                        slot.querySelector('.time-slot-label').style.opacity = '0.5';
                        slot.querySelector('.time-slot-label').style.cursor = 'not-allowed';
                    } else {
                        slot.classList.remove('unavailable');
                        slot.querySelector('input').disabled = false;
                        slot.querySelector('.time-slot-label').style.opacity = '1';
                        slot.querySelector('.time-slot-label').style.cursor = 'pointer';
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading time slot availability:', error);
        });
}

function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
    document.getElementById('bookingForm').reset();
    
    // Remove selection from calendar
    document.querySelectorAll('.calendar-day.selected').forEach(el => {
        el.classList.remove('selected');
    });
    
    selectedDate = null;
}

function updateServiceDetails() {
    const select = document.getElementById('serviceSelect');
    const selectedOption = select.options[select.selectedIndex];
    const detailsDiv = document.getElementById('serviceDetails');
    
    if (selectedOption.value) {
        const price = selectedOption.dataset.price;
        const description = selectedOption.dataset.description;
        const duration = selectedOption.dataset.duration;
        
        document.getElementById('serviceDescription').textContent = description;
        document.getElementById('serviceDuration').textContent = duration || 'Standard timing';
        document.getElementById('servicePrice').textContent = parseFloat(price).toFixed(2);
        
        detailsDiv.style.display = 'block';
        
        // Update summary
        document.getElementById('summaryService').textContent = selectedOption.text;
        updateTotalPrice();
    } else {
        detailsDiv.style.display = 'none';
        document.getElementById('summaryService').textContent = '-';
    document.getElementById('summaryTotal').textContent = '₱0.00';
    }
}

function updateTotalPrice() {
    const serviceSelect = document.getElementById('serviceSelect');
    
    if (serviceSelect.value) {
        const price = parseFloat(serviceSelect.options[serviceSelect.selectedIndex].dataset.price);
        document.getElementById('summaryTotal').textContent = `$${price.toFixed(2)}`;
    }
}

// Update summary when time slot is selected
document.addEventListener('change', function(e) {
    if (e.target.name === 'time_slot_id') {
        updateSummaryDateTime();
    }
});

function updateSummaryDateTime() {
    const selectedTime = document.querySelector('input[name="time_slot_id"]:checked');
    const displayDate = document.getElementById('displayDate').textContent;
    
    if (selectedTime) {
        const timeLabel = selectedTime.parentElement.querySelector('.time-slot-label').textContent;
        document.getElementById('summaryDateTime').textContent = `${displayDate} at ${timeLabel}`;
    } else {
        document.getElementById('summaryDateTime').textContent = displayDate;
    }
}

function loadAppointmentData() {
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    const monthStr = `${year}-${month}`;
    
    // Fetch availability data from API
    fetch(`../../api/availability.php?month=${monthStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                appointmentData = data.calendar_data;
                
                // Update calendar display
                document.querySelectorAll('.calendar-day.current-month').forEach(dayElement => {
                    const day = parseInt(dayElement.textContent);
                    const date = new Date(year, currentDate.getMonth(), day);
                    const dateStr = formatDate(date);
                    
                    // Remove existing availability classes
                    dayElement.classList.remove('available', 'busy');
                    
                    if (date >= today && !dayElement.classList.contains('past')) {
                        updateDayAvailability(dayElement, dateStr);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading appointment data:', error);
            // Fallback: mark all future dates as available
            document.querySelectorAll('.calendar-day.current-month').forEach(dayElement => {
                const day = parseInt(dayElement.textContent);
                const date = new Date(year, currentDate.getMonth(), day);
                
                if (date >= today && !dayElement.classList.contains('past')) {
                    dayElement.classList.add('available');
                }
            });
        });
}

function formatDate(date) {
    return date.getFullYear() + '-' + 
           String(date.getMonth() + 1).padStart(2, '0') + '-' + 
           String(date.getDate()).padStart(2, '0');
}

function isSameDate(date1, date2) {
    return date1.getFullYear() === date2.getFullYear() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getDate() === date2.getDate();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('bookingModal');
    if (event.target === modal) {
        closeBookingModal();
    }
}

// Handle form submission
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const selectedTime = document.querySelector('input[name="time_slot_id"]:checked');
    if (!selectedTime) {
        e.preventDefault();
        alert('Please select a time slot.');
        return;
    }
});
</script>

<?php
endUserLayout();
?>
