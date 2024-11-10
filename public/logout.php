<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = new Auth();
$auth->logout();