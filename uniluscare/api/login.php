<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$role = $_POST['role'] ?? '';
$err = '';

function fail($msg) {
    header('Location: ' . BASE_URL . '/index.php?error=' . urlencode($msg));
    exit;
}

switch ($role) {
    case 'patient':
        $pid = trim($_POST['patient_id'] ?? '');
        if ($pid === '') fail('Please enter your Patient ID.');
        $stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id = ?");
        $stmt->bind_param('s', $pid);
        $stmt->execute();
        $patient = $stmt->get_result()->fetch_assoc();
        if (!$patient) fail('Invalid Patient ID. If you are new, please visit reception to register.');
        $_SESSION['role'] = 'patient';
        $_SESSION['user_id'] = $patient['patient_id'];
        $_SESSION['full_name'] = $patient['full_name'];
        $_SESSION['suffix'] = $patient['suffix'];
        header('Location: ' . BASE_URL . '/pages/patient/dashboard.php');
        exit;

    case 'doctor':
        $did = trim($_POST['doctor_id'] ?? '');
        $dept = trim($_POST['department'] ?? '');
        if ($did === '' || $dept === '') fail('Please enter your Doctor ID and select a department.');
        $stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
        $stmt->bind_param('s', $did);
        $stmt->execute();
        $doc = $stmt->get_result()->fetch_assoc();
        if (!$doc) fail('Invalid Doctor ID.');
        $_SESSION['role'] = 'doctor';
        $_SESSION['user_id'] = $doc['doctor_id'];
        $_SESSION['full_name'] = $doc['full_name'];
        $_SESSION['department'] = $dept;
        header('Location: ' . BASE_URL . '/pages/doctor/dashboard.php');
        exit;

    case 'staff':
        $staff_role = $_POST['staff_role'] ?? '';
        $sid = trim($_POST['staff_id'] ?? '');
        if ($staff_role === '' || $sid === '') fail('Please select your role and enter your Staff ID.');

        $map = [
            'receptionist' => ['receptionists', 'receptionist_id', 'receptionist'],
            'nurse'        => ['nurses', 'nurse_id', 'nurse'],
            'pharmacist'   => ['pharmacists', 'pharmacist_id', 'pharmacist'],
            'lab'          => ['lab_technicians', 'lab_tech_id', 'lab'],
            'radiologist'  => ['radiologists', 'radiologist_id', 'radiologist'],
            'inventory'    => ['inventory_managers', 'inv_manager_id', 'inventory'],
            'triage'       => ['triage_officers', 'triage_id', 'triage'],
        ];
        if (!isset($map[$staff_role])) fail('Unknown role.');
        [$table, $idCol, $sessionRole] = $map[$staff_role];

        $stmt = $conn->prepare("SELECT * FROM {$table} WHERE {$idCol} = ?");
        $stmt->bind_param('s', $sid);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if (!$user) fail('Invalid Staff ID for the selected role.');

        $_SESSION['role'] = $sessionRole;
        $_SESSION['user_id'] = $user[$idCol];
        $_SESSION['full_name'] = $user['full_name'];
        header('Location: ' . BASE_URL . '/pages/' . $sessionRole . '/dashboard.php');
        exit;

    default:
        fail('Invalid login request.');
}
