<?php
require_once __DIR__.'/../config/db.php';
requireRole('receptionist');
$action = $_GET['action'] ?? '';
$back = $_SERVER['HTTP_REFERER'] ?? BASE_URL.'/pages/receptionist/dashboard.php';
if ($action === 'cancel_appt') {
    $id = (int)($_GET['id'] ?? 0);
    $conn->query("UPDATE appointments SET status='cancelled' WHERE appointment_id=$id");
    header("Location: $back?flash=Appointment+cancelled"); exit;
}
header("Location: $back");
