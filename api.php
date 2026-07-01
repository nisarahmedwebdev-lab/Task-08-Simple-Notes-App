<?php
// api.php - Fixed version
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$host = 'localhost';
$dbname = 'notes_app';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// For PUT requests, we need to parse the input differently
if ($method === 'PUT') {
    parse_str(file_get_contents('php://input'), $putData);
    $_PUT = $putData;
}

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo);
        break;
    case 'PUT':
        handlePut($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleGet($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM notes ORDER BY created_at DESC");
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($notes);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching notes']);
    }
}

function handlePost($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['title']) || trim($data['title']) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        return;
    }
    
    $title = trim($data['title']);
    $content = isset($data['content']) ? trim($data['content']) : '';
    $color = isset($data['color']) ? $data['color'] : '#ffffff';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO notes (title, content, color, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$title, $content, $color]);
        
        $id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'note' => $note]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error saving note: ' . $e->getMessage()]);
    }
}

function handlePut($pdo) {
    // Get the ID from the query string
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Note ID required']);
        return;
    }
    
    $id = $_GET['id'];
    
    // Get the raw input data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    // If JSON decoding failed, try parsing as form data
    if ($data === null) {
        parse_str($rawData, $data);
    }
    
    if (!$data || !isset($data['title']) || trim($data['title']) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        return;
    }
    
    $title = trim($data['title']);
    $content = isset($data['content']) ? trim($data['content']) : '';
    $color = isset($data['color']) ? $data['color'] : '#ffffff';
    
    try {
        // First check if the note exists
        $checkStmt = $pdo->prepare("SELECT id FROM notes WHERE id = ?");
        $checkStmt->execute([$id]);
        
        if ($checkStmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Note not found']);
            return;
        }
        
        // Update the note
        $stmt = $pdo->prepare("UPDATE notes SET title = ?, content = ?, color = ? WHERE id = ?");
        $stmt->execute([$title, $content, $color, $id]);
        
        // Fetch the updated note
        $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'note' => $note]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating note: ' . $e->getMessage()]);
    }
}

function handleDelete($pdo) {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Note ID required']);
        return;
    }
    
    $id = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Note not found']);
            return;
        }
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting note']);
    }
}
?>