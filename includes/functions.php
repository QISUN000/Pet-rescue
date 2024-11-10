<?php
function checkAuth() {
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

function checkAdmin() {
    $auth = new Auth();
    if (!$auth->isAdmin()) {
        header('Location: /login.php');
        exit();
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags($data));
}
?>