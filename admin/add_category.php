<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);

    if (!$name) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit();
    }

    try {
        $db = Database::getInstance()->getConnection();
        
        // Check if category exists
        $sql = "SELECT COUNT(*) FROM categories WHERE name = :name";
        $stmt = $db->prepare($sql);
        $stmt->execute([':name' => $name]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Category name already exists']);
            exit();
        }

        // Add category
        $sql = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':name' => $name]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}