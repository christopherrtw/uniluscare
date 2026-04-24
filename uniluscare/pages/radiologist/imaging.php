<?php
require_once __DIR__.'/../../config/db.php';
requireRole('radiologist');
$PAGE_TITLE='Imaging'; $ACTIVE_NAV='imaging';
$rid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)$_POST['imaging_id']; $findings = $_POST['findings'];
    $stmt = $conn->prepare("UPDATE imaging_reports SET findings=?,radiologist_id=?,status='completed' WHERE imaging_id=?");
    $stmt->bind_param('ssi',$findings,$rid,$id);
    $stmt->execute();
    $r = $conn->query("SELECT patient_id FROM imaging_reports WHERE imaging_id=$id")->fetch_assoc();
    if ($r) {
        $pid=$r['patient_id'];
        $conn->query("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES ('$pid','patient','Imaging Report Ready','Your imaging report is ready — check your records.','system')");
    }
    header("Location: ".BASE_URL."/pages/radiologist/imaging.php?flash=Findings+saved"); exit;
}

$imgs = $conn->query("SELECT i.*,p.full_name AS pname FROM imaging_reports i LEFT JOIN patients p ON i.patient_id=p.patient_id ORDER BY i.status='pending' DESC, i.created_at DESC LIMIT 50");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card">
    <div class="card-title">🩻 Imaging Queue</div>
    <?php while($i=$imgs->fetch_assoc()):?>
        <div style="border:1px solid var(--border);border-radius:var(--radius);padding:1rem;margin-bottom:0.75rem;">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                <div><strong><?=e($i['pname'])?></strong> — <?=e($i['image_type'])?> of <?=e($i['body_part'])?>
                <span class="badge badge-<?=$i['status']==='completed'?'confirmed':'pending'?>"><?=e($i['status'])?></span><br>
                <small class="text-muted"><?=date('d M Y H:i',strtotime($i['created_at']))?></small></div>
                <div>
                    <?php if($i['image_file']):?><a href="<?=BASE_URL?>/assets/uploads/imaging/<?=e($i['image_file'])?>" target="_blank" class="btn btn-sm btn-outline">🖼 View Image</a><?php endif;?>
                </div>
            </div>
            <?php if($i['ai_analysis']):?>
                <div class="alert alert-info mt-2" style="margin-bottom:0.5rem;"><strong>AI Preliminary:</strong> <?=e($i['ai_analysis'])?></div>
            <?php endif;?>
            <?php if($i['status']==='pending'):?>
                <form method="POST" class="mt-2">
                    <input type="hidden" name="imaging_id" value="<?=(int)$i['imaging_id']?>">
                    <div class="form-group"><label>Radiology Findings</label><textarea name="findings" rows="3" required></textarea></div>
                    <button class="btn btn-primary btn-sm" type="submit">Save Findings</button>
                </form>
            <?php else:?>
                <div class="mt-2"><strong>Findings:</strong> <?=e($i['findings'])?></div>
            <?php endif;?>
        </div>
    <?php endwhile;?>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
