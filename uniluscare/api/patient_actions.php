<?php
require_once __DIR__ . '/../config/db.php';
requireRole('patient');
$pid = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$back = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/pages/patient/dashboard.php';

switch ($action) {

case 'book_appointment':
    $doc = $_POST['doctor_id'] ?? '';
    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $type = $_POST['type'] ?? 'physical';
    if (!$doc || !$date || !$time) { header("Location: $back?flash=Missing+fields"); exit; }
    $room = 'uniluscare-' . uniqid();
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id,doctor_id,appointment_date,appointment_time,reason,type,status,room_id) VALUES (?,?,?,?,?,?,'pending',?)");
    $stmt->bind_param('sssssss', $pid, $doc, $date, $time, $reason, $type, $room);
    $stmt->execute();

    // notify patient
    $stmt = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES (?,'patient','Appointment Booked','Your appointment is pending confirmation.','appointment')");
    $stmt->bind_param('s', $pid);
    $stmt->execute();

    header("Location: $back?flash=Appointment+booked+successfully");
    exit;

case 'refill_request':
    $prId = (int)($_POST['prescription_id'] ?? 0);
    $stmt = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES (?,'pharmacist','Refill Request',?,'medication')");
    $msg = "Patient $pid has requested a refill for prescription #$prId";
    $everyone = 'PH001';
    $stmt->bind_param('ss', $everyone, $msg);
    $stmt->execute();
    header("Location: $back?flash=Refill+request+sent+to+pharmacy");
    exit;

case 'feedback':
    $rating = (int)$_POST['rating'];
    $comments = trim($_POST['comments'] ?? '');
    $stmt = $conn->prepare("INSERT INTO patient_feedback (patient_id,rating,comments) VALUES (?,?,?)");
    $stmt->bind_param('sis', $pid, $rating, $comments);
    $stmt->execute();
    header("Location: $back?flash=Thank+you+for+your+feedback");
    exit;

case 'image_upload':
    $type = $_POST['image_type'] ?? 'X-ray';
    $body = trim($_POST['body_part'] ?? '');
    if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== 0) {
        header("Location: $back?flash=Upload+failed"); exit;
    }
    $filename = $pid . '_' . time() . '_' . preg_replace('/[^A-Za-z0-9._-]/','', basename($_FILES['image_file']['name']));
    $dest = __DIR__ . '/../assets/uploads/imaging/' . $filename;
    move_uploaded_file($_FILES['image_file']['tmp_name'], $dest);

    // naive AI findings (demo)
    $ai = "AI preliminary analysis: Image quality adequate. No obvious acute abnormalities detected. Recommend radiologist confirmation for $type of $body.";

    $stmt = $conn->prepare("INSERT INTO imaging_reports (patient_id,image_type,body_part,ai_analysis,image_file,status) VALUES (?,?,?,?,?,'pending')");
    $stmt->bind_param('sssss', $pid, $type, $body, $ai, $filename);
    $stmt->execute();
    header("Location: $back?flash=Image+uploaded+and+sent+for+analysis");
    exit;

default:
    header("Location: $back");
    exit;
}
