<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once '../config/database.php';

try {
    $conn = getDatabaseConnection();
    
    // Get scoreboard data with detailed information
    $sql = "SELECT u.id, u.username, u.display_name, 
                   COALESCE(SUM(s.points), 0) as total_points,
                   COUNT(s.id) as judge_count,
                   MAX(s.updated_at) as last_score_update
            FROM users u 
            LEFT JOIN scores s ON u.id = s.user_id 
            GROUP BY u.id, u.username, u.display_name 
            ORDER BY total_points DESC, u.display_name ASC";
    
    $result = executeQuery($conn, $sql);
    
    if ($result === false) {
        throw new Exception('Database query failed');
    }
    
    $scoreboard = [];
    while ($row = $result->fetch_assoc()) {
        // Convert numeric values to ensure proper JSON formatting
        $row['total_points'] = (int)$row['total_points'];
        $row['judge_count'] = (int)$row['judge_count'];
        $row['id'] = (int)$row['id'];
        
        $scoreboard[] = $row;
    }
    
    // Add metadata
    $metadata = [
        'total_participants' => count($scoreboard),
        'last_updated' => date('Y-m-d H:i:s'),
        'timestamp' => time()
    ];
    
    // Get total judges count
    $judge_result = executeQuery($conn, "SELECT COUNT(*) as count FROM judges");
    if ($judge_result) {
        $judge_data = $judge_result->fetch_assoc();
        $metadata['total_judges'] = (int)$judge_data['count'];
    }
    
    closeDatabaseConnection($conn);
    
    // Return response
    $response = [
        'success' => true,
        'data' => $scoreboard,
        'metadata' => $metadata,
        'message' => 'Scoreboard data retrieved successfully'
    ];
    
    // For the JavaScript scoreboard manager, we need just the data array
    // But we'll return the full response for API documentation
    if (isset($_GET['format']) && $_GET['format'] === 'simple') {
        echo json_encode($scoreboard);
    } else {
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    error_log("Scoreboard API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => 'Failed to retrieve scoreboard data',
        'timestamp' => time()
    ]);
}
?>