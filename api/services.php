<?php
require_once '../includes/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get services
        $status_filter = $_GET['status'] ?? 'active';
        $limit = min((int) ($_GET['limit'] ?? 50), 100);
        $offset = (int) ($_GET['offset'] ?? 0);
        
        $where_conditions = ['1=1'];
        $params = [];
        
        if ($status_filter !== 'all') {
            $where_conditions[] = 's.status = ?';
            $params[] = $status_filter;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM services s 
            WHERE $where_clause
        ");
        $count_stmt->execute($params);
        $total_count = $count_stmt->fetchColumn();
        
        // Get services with seller info
        $stmt = $pdo->prepare("
            SELECT s.*, u.full_name as seller_name,
                   COUNT(a.id) as total_appointments,
                   COUNT(CASE WHEN a.status = 'pending' THEN 1 END) as pending_appointments,
                   COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_appointments
            FROM services s
            JOIN users u ON s.seller_id = u.id
            LEFT JOIN appointments a ON s.id = a.service_id
            WHERE $where_clause
            GROUP BY s.id
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $services = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'services' => $services,
            'total_count' => (int) $total_count,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
    } else {
        throw new Exception('Only GET requests are supported');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
}
?>