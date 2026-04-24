<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'Patient Record';
$ACTIVE_NAV = '';
$did = $_SESSION['user_id'];

$pid = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id=?");
$stmt->bind_param('s',$pid);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    $PAGE_TITLE = 'Patient Not Found';
    include __DIR__ . '/../../includes/layout.php';
    echo '<div class="alert alert-danger">Patient not found.</div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$vitals = $conn->query("SELECT * FROM vitals WHERE patient_id='$pid' ORDER BY recorded_at DESC LIMIT 5");
$history = $conn->query("SELECT m.*, d.full_name AS doctor_name FROM medical_records m LEFT JOIN doctors d ON m.doctor_id=d.doctor_id WHERE m.patient_id='$pid' ORDER BY m.visit_date DESC");
$labs = $conn->query("SELECT * FROM lab_tests WHERE patient_id='$pid' ORDER BY requested_at DESC");
$rxs = $conn->query("SELECT * FROM prescriptions WHERE patient_id='$pid' ORDER BY created_at DESC");
$flash = $_GET['flash'] ?? '';
include __DIR__ . '/../../includes/layout.php';
?>

<?php if ($flash): ?><div class="alert alert-success"><?= e($flash) ?></div><?php endif; ?>

<div class="card mb-3">
    <div class="card-title">👤 <?= e($patient['suffix']) ?> <?= e($patient['full_name']) ?> <span class="badge badge-info"><?= e($patient['patient_id']) ?></span></div>
    <div class="form-row">
        <div><strong>DOB:</strong> <?= e($patient['date_of_birth']) ?> (<?= date('Y') - date('Y', strtotime($patient['date_of_birth'])) ?> yrs)</div>
        <div><strong>Gender:</strong> <?= e($patient['gender']) ?></div>
        <div><strong>Phone:</strong> <?= e($patient['phone']) ?></div>
        <div><strong>Email:</strong> <?= e($patient['email']) ?></div>
        <div><strong>Insurance:</strong> <?= e($patient['insurance_company'] ?: 'None') ?></div>
        <div><strong>Reason for visit:</strong> <?= e($patient['reason_visit']) ?></div>
    </div>
</div>

<div class="content-grid">
    <div>
        <!-- Vitals -->
        <div class="card mb-3">
            <div class="card-title">❤️ Recent Vitals</div>
            <?php if ($vitals->num_rows > 0): ?>
                <table class="data-table">
                    <tr><th>Date</th><th>BP</th><th>HR</th><th>Temp</th><th>SpO₂</th></tr>
                    <?php while ($v = $vitals->fetch_assoc()): ?>
                        <tr><td><?= date('d M H:i', strtotime($v['recorded_at'])) ?></td>
                            <td><?= e($v['bp_systolic']) ?>/<?= e($v['bp_diastolic']) ?></td>
                            <td><?= e($v['heart_rate']) ?></td>
                            <td><?= e($v['temperature']) ?>°C</td>
                            <td><?= e($v['oxygen_saturation']) ?>%</td></tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?><p class="text-muted">No vitals recorded.</p><?php endif; ?>
        </div>

        <!-- Write Prescription -->
        <div class="card mb-3">
            <div class="card-title">💊 Write New Prescription</div>
            <form method="POST" action="<?= BASE_URL ?>/api/doctor_actions.php">
                <input type="hidden" name="action" value="prescribe">
                <input type="hidden" name="patient_id" value="<?= e($pid) ?>">
                <div class="form-row">
                    <div class="form-group"><label>Diagnosis</label><input type="text" name="diagnosis" required></div>
                    <div class="form-group"><label>ICD-10 Code</label><input type="text" name="icd10_code" placeholder="e.g. J06.9"></div>
                </div>
                <div id="medItems">
                    <div class="med-item" style="border:1px dashed #ccc;padding:1rem;border-radius:8px;margin-bottom:0.5rem;">
                        <div class="form-row">
                            <div class="form-group"><label>Medicine</label><input type="text" name="medicine_name[]" required></div>
                            <div class="form-group"><label>Dosage</label><input type="text" name="dosage[]" placeholder="e.g. 500mg"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Frequency</label><input type="text" name="frequency[]" placeholder="e.g. 2x daily"></div>
                            <div class="form-group"><label>Duration</label><input type="text" name="duration[]" placeholder="e.g. 7 days"></div>
                        </div>
                        <div class="form-group"><label>Instructions</label><input type="text" name="instructions[]" placeholder="e.g. After meals"></div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addMed()">+ Add Another Medicine</button>
                <div class="form-group mt-2"><label>Notes</label><textarea name="notes"></textarea></div>
                <button class="btn btn-primary" type="submit">Save Prescription</button>
            </form>
        </div>

        <!-- Request Lab Test -->
        <div class="card mb-3">
            <div class="card-title">🧪 Request Lab Test</div>
            <form method="POST" action="<?= BASE_URL ?>/api/doctor_actions.php">
                <input type="hidden" name="action" value="request_lab">
                <input type="hidden" name="patient_id" value="<?= e($pid) ?>">
                <div class="form-row">
                    <div class="form-group"><label>Test Name</label><input type="text" name="test_name" required placeholder="e.g. Complete Blood Count"></div>
                    <div class="form-group"><label>Test Type</label>
                        <select name="test_type">
                            <option>Hematology</option><option>Biochemistry</option><option>Microbiology</option>
                            <option>Urinalysis</option><option>Serology</option><option>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label>Notes</label><textarea name="notes"></textarea></div>
                <button class="btn btn-primary" type="submit">Request Test</button>
            </form>
        </div>

        <!-- Add Medical Record Entry -->
        <div class="card">
            <div class="card-title">📝 Add Diagnosis / Medical Record</div>
            <form method="POST" action="<?= BASE_URL ?>/api/doctor_actions.php">
                <input type="hidden" name="action" value="add_record">
                <input type="hidden" name="patient_id" value="<?= e($pid) ?>">
                <div class="form-group"><label>Diagnosis</label><input type="text" name="diagnosis" required></div>
                <div class="form-group"><label>Treatment / Plan</label><textarea name="treatment"></textarea></div>
                <div class="form-group"><label>Notes</label><textarea name="notes"></textarea></div>
                <button class="btn btn-primary" type="submit">Save Record</button>
            </form>
        </div>
    </div>

    <div>
        <div class="card mb-3">
            <div class="card-title">📋 Medical History</div>
            <?php if ($history->num_rows > 0): while ($r = $history->fetch_assoc()): ?>
                <div style="border-left:3px solid var(--primary); padding:0.5rem 0.75rem; margin-bottom:0.75rem;">
                    <div class="text-sm text-muted"><?= e($r['visit_date']) ?> — <?= e($r['doctor_name']) ?></div>
                    <strong><?= e($r['diagnosis']) ?></strong>
                    <div class="text-sm"><?= e($r['treatment']) ?></div>
                </div>
            <?php endwhile; else: ?><p class="text-muted">No prior history.</p><?php endif; ?>
        </div>

        <div class="card mb-3">
            <div class="card-title">🧪 Lab Results</div>
            <?php if ($labs->num_rows > 0): while ($l = $labs->fetch_assoc()): ?>
                <div style="padding:0.5rem 0; border-bottom:1px solid var(--border);">
                    <strong><?= e($l['test_name']) ?></strong> — <span class="badge badge-<?= $l['status']==='completed'?'confirmed':'pending' ?>"><?= e($l['status']) ?></span>
                    <?php if ($l['results']): ?><div class="text-sm mt-1"><?= e($l['results']) ?></div><?php endif; ?>
                </div>
            <?php endwhile; else: ?><p class="text-muted">No lab tests.</p><?php endif; ?>
        </div>

        <div class="card">
            <div class="card-title">💊 Prior Prescriptions</div>
            <?php if ($rxs->num_rows > 0): while ($r = $rxs->fetch_assoc()): ?>
                <div style="padding:0.5rem 0; border-bottom:1px solid var(--border);">
                    <strong><?= date('d M Y', strtotime($r['created_at'])) ?></strong> — <?= e($r['diagnosis']) ?>
                    <a href="<?= BASE_URL ?>/api/prescription_pdf.php?id=<?= (int)$r['prescription_id'] ?>" class="btn btn-sm btn-outline" target="_blank">📄 PDF</a>
                </div>
            <?php endwhile; else: ?><p class="text-muted">No prior prescriptions.</p><?php endif; ?>
        </div>
    </div>
</div>

<script>
function addMed() {
    const c = document.getElementById('medItems');
    const d = document.createElement('div');
    d.className = 'med-item';
    d.style.cssText='border:1px dashed #ccc;padding:1rem;border-radius:8px;margin-bottom:0.5rem;';
    d.innerHTML = `<div class="form-row"><div class="form-group"><label>Medicine</label><input type="text" name="medicine_name[]" required></div><div class="form-group"><label>Dosage</label><input type="text" name="dosage[]"></div></div><div class="form-row"><div class="form-group"><label>Frequency</label><input type="text" name="frequency[]"></div><div class="form-group"><label>Duration</label><input type="text" name="duration[]"></div></div><div class="form-group"><label>Instructions</label><input type="text" name="instructions[]"></div><button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">Remove</button>`;
    c.appendChild(d);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
