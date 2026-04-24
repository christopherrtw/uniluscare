<?php
require_once __DIR__.'/../../config/db.php';
requireRole('triage');
$PAGE_TITLE='Rapid Register'; $ACTIVE_NAV='register';
$tid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $existingId = trim($_POST['patient_id'] ?? '');
    $name = trim($_POST['patient_name'] ?? '');
    $priority = $_POST['priority'];
    $complaint = $_POST['chief_complaint'];
    $assigned = $_POST['assigned_doctor_id'] ?? null;

    // If no existing ID, create a minimal patient record
    $pid = $existingId;
    if (!$pid && $name) {
        $pid = generatePatientId($conn);
        $dob = '2000-01-01';
        $stmt = $conn->prepare("INSERT INTO patients (patient_id,id_number,id_type,full_name,date_of_birth,phone,email,address,reason_visit) VALUES (?,'EMERGENCY','Emergency',?,?,'','','','emergency')");
        $stmt->bind_param('sss',$pid,$name,$dob);
        $stmt->execute();
    }

    $stmt = $conn->prepare("INSERT INTO triage_cases (patient_id,patient_name,priority,chief_complaint,triage_officer_id,assigned_doctor_id) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('ssssss',$pid,$name,$priority,$complaint,$tid,$assigned);
    $stmt->execute();

    // Notify on-call doctor
    if ($assigned) {
        $msg = "Emergency ($priority): $complaint";
        $n = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES (?,'doctor','Emergency Case',?,'system')");
        $n->bind_param('ss',$assigned,$msg); $n->execute();
    }
    header("Location: ".BASE_URL."/pages/triage/cases.php?flash=Case+registered"); exit;
}

$docs = $conn->query("SELECT doctor_id,full_name FROM doctors WHERE department IN ('Emergency Medicine','General Medicine','Surgery') ORDER BY full_name");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card">
    <div class="card-title">🚑 Rapid Emergency Registration</div>
    <form method="POST">
        <div class="form-row">
            <div class="form-group"><label>Existing Patient ID (if known)</label><input type="text" name="patient_id" placeholder="e.g. P000001"></div>
            <div class="form-group"><label>OR Patient Name</label><input type="text" name="patient_name" placeholder="Full name"></div>
        </div>
        <div class="form-group"><label>Priority</label>
            <select name="priority" required>
                <option value="red">🔴 RED — Critical / Life-threatening</option>
                <option value="yellow">🟡 YELLOW — Urgent</option>
                <option value="green">🟢 GREEN — Non-urgent</option>
            </select>
        </div>
        <div class="form-group"><label>Chief Complaint</label><textarea name="chief_complaint" rows="3" required></textarea></div>
        <div class="form-group"><label>Assign On-Call Doctor</label>
            <select name="assigned_doctor_id">
                <option value="">-- Not yet assigned --</option>
                <?php while($d=$docs->fetch_assoc()):?>
                    <option value="<?=e($d['doctor_id'])?>"><?=e($d['full_name'])?></option>
                <?php endwhile;?>
            </select>
        </div>
        <button class="btn btn-danger" type="submit">🚑 Register & Notify Doctor</button>
    </form>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
