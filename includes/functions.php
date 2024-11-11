<?php
// Validation for animal data
function validateAnimalData($data) {
    $errors = [];
    
    // Name validation
    if (empty($data['name'])) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($data['name']) > 100) {
        $errors['name'] = 'Name must be less than 100 characters';
    }

    // Species validation
    if (empty($data['species'])) {
        $errors['species'] = 'Species is required';
    } elseif (!in_array($data['species'], ['cat', 'dog'])) {
        $errors['species'] = 'Invalid species';
    }

    // Category validation
    if (empty($data['category_id'])) {
        $errors['category_id'] = 'Category is required';
    } elseif (!filter_var($data['category_id'], FILTER_VALIDATE_INT)) {
        $errors['category_id'] = 'Invalid category';
    }

    // Optional fields validation
    if (!empty($data['breed']) && strlen($data['breed']) > 100) {
        $errors['breed'] = 'Breed must be less than 100 characters';
    }

    if (!empty($data['description']) && strlen($data['description']) > 1000) {
        $errors['description'] = 'Description must be less than 1000 characters';
    }

    return $errors;
}

// Sanitize input
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
    } else {
        $data = htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

// Validate ID
function validateId($id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        return false;
    }
    return $id;
}

// Validate image upload
function validateImage($file) {
    $errors = [];
    
    if (empty($file['tmp_name'])) {
        return $errors;  // Image is optional
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check file type
    if (!in_array($file_ext, $allowed)) {
        $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', $allowed);
    }

    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'File size must be less than 5MB';
    }

    // Verify it's actually an image
    if (!getimagesize($file['tmp_name'])) {
        $errors[] = 'Invalid image file';
    }

    return $errors;
}

// Check if user is logged in
function checkAuth() {
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        header('Location: ../public/login.php');
        exit();
    }
}

function checkAdmin() {
    $auth = new Auth();
    if (!$auth->isAdmin()) {
        header('Location: ../public/login.php');
        exit();
    }
}


?>