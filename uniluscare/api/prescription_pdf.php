<?php
require_once __DIR__ . '/../config/db.php';
if (empty($_SESSION['role'])) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT p.*, d.full_name AS doctor_name, d.department, pt.full_name AS patient_name, pt.suffix, pt.patient_id AS pid FROM prescriptions p LEFT JOIN doctors d ON p.doctor_id=d.doctor_id LEFT JOIN patients pt ON p.patient_id=pt.patient_id WHERE prescription_id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) die('Prescription not found');

// authorisation — patient must own it, or staff
if ($_SESSION['role'] === 'patient' && $_SESSION['user_id'] !== $p['pid']) die('Not authorised');

$items = $conn->query("SELECT * FROM prescription_items WHERE prescription_id=$id");

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Prescription #<?= $id ?></title>
<style>
body{font-family:Georgia,serif;max-width:800px;margin:2rem auto;padding:2rem;color:#222;}
.header{text-align:center;border-bottom:3px double #0b5e68;padding-bottom:1rem;margin-bottom:2rem;}
.header h1{color:#0b5e68;margin:0;font-size:2rem;}
.header p{margin:0.25rem 0;color:#666;}
.details{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;padding:1rem;background:#f5f7f8;border-radius:8px;}
table{width:100%;border-collapse:collapse;margin:1rem 0;}
th{background:#0b5e68;color:#fff;padding:0.6rem;text-align:left;}
td{padding:0.6rem;border-bottom:1px solid #ddd;}
.signature{margin-top:3rem;border-top:1px solid #000;padding-top:0.5rem;width:250px;}
@media print{.noprint{display:none;}}
</style>
</head>
<body>
<div class="noprint" style="text-align:right;margin-bottom:1rem;">
    <button onclick="window.print()" style="padding:0.5rem 1rem;background:#0b5e68;color:#fff;border:none;border-radius:6px;cursor:pointer;">🖨 Print / Save as PDF</button>
</div>
<div class="header">
    <h1>UnilusCare</h1>
    <p>Hospital Management System</p>
    <p><strong>PRESCRIPTION</strong> — No. <?= str_pad($id,6,'0',STR_PAD_LEFT) ?></p>
</div>

<div class="details">
    <div><strong>Patient:</strong> <?= e($p['suffix']) ?> <?= e($p['patient_name']) ?></div>
    <div><strong>Patient ID:</strong> <?= e($p['pid']) ?></div>
    <div><strong>Date:</strong> <?= date('d F Y', strtotime($p['created_at'])) ?></div>
    <div><strong>Doctor:</strong> <?= e($p['doctor_name']) ?></div>
    <div><strong>Department:</strong> <?= e($p['department']) ?></div>
    <div><strong>ICD-10:</strong> <?= e($p['icd10_code'] ?: '—') ?></div>
</div>

<p><strong>Diagnosis:</strong> <?= e($p['diagnosis']) ?></p>

<table>
<tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Instructions</th></tr>
<?php while ($i = $items->fetch_assoc()): ?>
<tr>
    <td><strong><?= e($i['medicine_name']) ?></strong></td>
    <td><?= e($i['dosage']) ?></td>
    <td><?= e($i['frequency']) ?></td>
    <td><?= e($i['duration']) ?></td>
    <td><?= e($i['instructions']) ?></td>
</tr>
<?php endwhile; ?>
</table>

<?php if ($p['notes']): ?><p><strong>Notes:</strong> <?= e($p['notes']) ?></p><?php endif; ?>

<div class="signature">Doctor's Signature</div>
<p style="font-size:0.75rem;color:#888;margin-top:2rem;">This is a digitally generated prescription. Not valid without stamp.</p>
</body>
</html>
