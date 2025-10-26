<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../helpers/session.php';

$auth = new AuthController($conn);
$auth->logout();
?>