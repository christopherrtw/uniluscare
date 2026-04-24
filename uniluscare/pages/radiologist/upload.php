<?php
require_once __DIR__.'/../../config/db.php';
requireRole('radiologist');
$PAGE_TITLE='Upload Report'; $ACTIVE_NAV='upload';
$rid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid = $_POST['patient_id']; $type = $_POST['image_type']; $body = $_POST['body_part'];
    $findings = $_POST['findings']; $ai = $_POST['ai_analysis'] ?? '';
    $fname = null;
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error']===0) {
        $fname = $pid.'_'.time().'_'.preg_replace('/[^A-Za-z0-9._-]/','',basename($_FILES['image_file']['name']));
        move_uploaded_file($_FILES['image_file']['tmp_name'], __DIR__.'/../../assets/uploads/imaging/'.$fname);
    }
    $stmt = $conn->prepare("INSERT INTO imaging_reports (patient_id,radiologist_id,image_type,body_part,findings,ai_analysis,image_file,status) VALUES (?,?,?,?,?,?,?,'completed')");
    $stmt->bind_param('sssssss',$pid,$rid,$type,$body,$findings,$ai,$fname);
    $stmt->execute();
    $conn->query("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES ('$pid','patient','New Imaging Report','An imaging report has been uploaded to your records.','system')");
    header("Location: ".BASE_URL."/pages/radiologist/upload.php?flash=Report+uploaded"); exit;
}

$patients = $conn->query("SELECT patient_id,full_name FROM patients ORDER BY full_name");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card">
    <div class="card-title">⬆️ Upload New Imaging Report</div>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group"><label>Patient</label>
                <select name="patient_id" required>
                    <option value="">-- Select --</option>
                    <?php while($p=$patients->fetch_assoc()):?>
                        <option value="<?=e($p['patient_id'])?>"><?=e($p['full_name'])?></option>
                    <?php endwhile;?>
                </select>
            </div>
            <div class="form-group"><label>Image Type</label>
                <select name="image_type"><option>X-ray</option><option>MRI</option><option>CT</option><option>Ultrasound</option></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Body Part</label><input type="text" name="body_part" required></div>
            <div class="form-group"><label>Image File</label><input type="file" name="image_file" accept="image/*,.dcm"></div>
        </div>
        <div class="form-group"><label>Radiology Findings</label><textarea name="findings" rows="4" required></textarea></div>
        <div class="form-group"><label>AI Analysis (optional)</label><textarea name="ai_analysis" rows="2"></textarea></div>
        <button class="btn btn-primary" type="submit">Upload Report</button>
    </form>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
