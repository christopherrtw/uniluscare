<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'Video Consultation'; $ACTIVE_NAV = 'telemedicine';
$room = preg_replace('/[^a-zA-Z0-9\-]/', '', $_GET['room'] ?? 'uniluscare-room');
$name = 'Dr ' . $_SESSION['full_name'];
include __DIR__ . '/../../includes/layout.php';
?>
<div class="card">
    <div class="card-title">📹 Virtual Consultation Room: <?= e($room) ?></div>
    <div id="jitsi-container" style="width:100%;height:600px;border-radius:var(--radius);overflow:hidden;background:#000;"></div>
    <div class="mt-2"><a href="<?= BASE_URL ?>/pages/doctor/telemedicine.php" class="btn btn-outline">← End & Return</a></div>
</div>
<script src="https://meet.jit.si/external_api.js"></script>
<script>
const api = new JitsiMeetExternalAPI('meet.jit.si', {
    roomName: '<?= e($room) ?>', width: '100%', height: 600,
    parentNode: document.querySelector('#jitsi-container'),
    userInfo: { displayName: '<?= e($name) ?>' },
    configOverwrite: { prejoinPageEnabled: false },
    interfaceConfigOverwrite: { SHOW_JITSI_WATERMARK: false }
});
api.addEventListener('readyToClose', () => window.location.href='<?= BASE_URL ?>/pages/doctor/telemedicine.php');
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
