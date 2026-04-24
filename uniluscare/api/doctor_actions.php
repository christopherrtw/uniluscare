<?php
require_once __DIR__ . '/../config/db.php';
requireRole('doctor');
$did = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$back = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/pages/doctor/dashboard.php';

switch ($action) {

case 'prescribe':
    $pid = $_POST['patient_id'];
    $diag = $_POST['diagnosis'] ?? '';
    $icd = $_POST['icd10_code'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id,doctor_id,diagnosis,icd10_code,notes) VALUES (?,?,?,?,?)");
    $stmt->bind_param('sssss',$pid,$did,$diag,$icd,$notes);
    $stmt->execute();
    $rx = $stmt->insert_id;
    $meds = $_POST['medicine_name'] ?? [];
    $dos = $_POST['dosage'] ?? [];
    $fr = $_POST['frequency'] ?? [];
    $du = $_POST['duration'] ?? [];
    $ins = $_POST['instructions'] ?? [];
    for ($i=0; $i<count($meds); $i++) {
        if (!trim($meds[$i])) continue;
        $s2 = $conn->prepare("INSERT INTO prescription_items (prescription_id,medicine_name,dosage,frequency,duration,instructions) VALUES (?,?,?,?,?,?)");
        $m = $meds[$i]; $d2 = $dos[$i] ?? ''; $f2 = $fr[$i] ?? ''; $du2 = $du[$i] ?? ''; $in2 = $ins[$i] ?? '';
        $s2->bind_param('isssss',$rx,$m,$d2,$f2,$du2,$in2);
        $s2->execute();
    }
    // Notify patient & pharmacist
    $n = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES (?,'patient','New Prescription','A new prescription has been issued by your doctor.','medication')");
    $n->bind_param('s',$pid); $n->execute();
    $n2 = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES ('PH001','pharmacist','New Prescription',?,'medication')");
    $msg = "New prescription #$rx for patient $pid";
    $n2->bind_param('s',$msg); $n2->execute();
    header("Location: $back?flash=Prescription+saved"); exit;

case 'request_lab':
    $pid = $_POST['patient_id'];
    $name = $_POST['test_name'];
    $type = $_POST['test_type'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $stmt = $conn->prepare("INSERT INTO lab_tests (patient_id,doctor_id,test_name,test_type,notes) VALUES (?,?,?,?,?)");
    $stmt->bind_param('sssss',$pid,$did,$name,$type,$notes);
    $stmt->execute();
    $n = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES ('L0001','lab','New Lab Request',?,'lab')");
    $msg = "Test $name requested for patient $pid";
    $n->bind_param('s',$msg); $n->execute();
    header("Location: $back?flash=Lab+test+requested"); exit;

case 'add_record':
    $pid = $_POST['patient_id'];
    $diag = $_POST['diagnosis'];
    $tr = $_POST['treatment'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $date = date('Y-m-d');
    $stmt = $conn->prepare("INSERT INTO medical_records (patient_id,doctor_id,visit_date,diagnosis,treatment,notes) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('ssssss',$pid,$did,$date,$diag,$tr,$notes);
    $stmt->execute();
    header("Location: $back?flash=Medical+record+added"); exit;

case 'confirm_appt':
    $id = (int)($_GET['id'] ?? 0);
    $conn->query("UPDATE appointments SET status='confirmed' WHERE appointment_id=$id AND doctor_id='$did'");
    // notify patient
    $res = $conn->query("SELECT patient_id FROM appointments WHERE appointment_id=$id");
    if ($row = $res->fetch_assoc()) {
        $pid = $row['patient_id'];
        $stmt = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES (?,'patient','Appointment Confirmed','Your appointment has been confirmed.','appointment')");
        $stmt->bind_param('s',$pid); $stmt->execute();
    }
    header("Location: $back?flash=Appointment+confirmed"); exit;

default:
    header("Location: $back"); exit;
}
