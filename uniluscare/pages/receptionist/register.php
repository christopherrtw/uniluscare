<?php
require_once __DIR__.'/../../config/db.php';
requireRole('receptionist');
// Redirect to the main signup form (same registration flow, recorded with this receptionist)
header('Location: '.BASE_URL.'/signup.php');
exit;
