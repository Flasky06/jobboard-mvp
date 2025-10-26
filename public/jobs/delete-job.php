<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/csrf.php';

$jobController = new JobController($conn);
$jobController->deleteJob();
?>