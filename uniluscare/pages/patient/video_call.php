<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('patient');
$PAGE_TITLE = 'Video Consultation';
$ACTIVE_NAV = 'telemedicine';
$room = preg_replace('/[^a-zA-Z0-9\-]/', '', $_GET['room'] ?? ('uniluscare-' . uniqid()));
$apptId = (int)($_GET['appt'] ?? 0);
$displayName = ($_SESSION['suffix'] ? $_SESSION['suffix'] . ' ' : '') . $_SESSION['full_name'];
include __DIR__ . '/../../includes/layout.php';
?>

<div class="card">
    <div class="card-title">📹 Virtual Consultation Room: <?= e($room) ?></div>
    <p class="text-muted mb-2">Your doctor will join shortly. Share the room ID <code><?= e($room) ?></code> if needed.</p>
    <div id="jitsi-container" style="width:100%; height:600px; border-radius:var(--radius); overflow:hidden; background:#000;"></div>
    <div class="mt-2">
        <a href="<?= BASE_URL ?>/pages/patient/telemedicine.php" class="btn btn-outline">← End & Return</a>
    </div>
</div>

<script src="https://meet.jit.si/external_api.js"></script>
<script>
const domain = 'meet.jit.si';
const options = {
    roomName: '<?= e($room) ?>',
    width: '100%',
    height: 600,
    parentNode: document.querySelector('#jitsi-container'),
    userInfo: { displayName: '<?= e($displayName) ?>' },
    configOverwrite: {
        startWithAudioMuted: false,
        startWithVideoMuted: false,
        prejoinPageEnabled: false
    },
    interfaceConfigOverwrite: {
        SHOW_JITSI_WATERMARK: false,
        SHOW_WATERMARK_FOR_GUESTS: false,
        TOOLBAR_BUTTONS: ['microphone','camera','closedcaptions','desktop','fullscreen','fodeviceselection','hangup','chat','settings','videoquality','filmstrip','tileview']
    }
};
const api = new JitsiMeetExternalAPI(domain, options);
api.addEventListener('readyToClose', () => window.location.href = '<?= BASE_URL ?>/pages/patient/telemedicine.php?flash=Call ended');
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
