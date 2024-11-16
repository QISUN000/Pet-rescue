<?php
require_once '../config/database.php';
require_once '../includes/auth.php';


$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);

    if (!$id || !$name) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit();
    }

    try {
        $db = Database::getInstance()->getConnection();
        
        // Check if category exists
        $sql = "SELECT COUNT(*) FROM categories WHERE name = :name AND id != :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':name' => $name, ':id' => $id]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Category name already exists']);
            exit();
        }

        // Update category
        $sql = "UPDATE categories SET name = :name WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':name' => $name, ':id' => $id]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}