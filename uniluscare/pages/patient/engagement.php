<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('patient');
$PAGE_TITLE = 'Patient Engagement';
$ACTIVE_NAV = 'engagement';
$pid = $_SESSION['user_id'];
$flash = $_GET['flash'] ?? '';
include __DIR__ . '/../../includes/layout.php';
?>

<?php if ($flash): ?><div class="alert alert-success"><?= e($flash) ?></div><?php endif; ?>

<div class="card mb-3">
    <div class="card-title">📚 Health Education Resources</div>
    <div class="stats-grid">
        <div class="stat-card">
            <h3 style="color:var(--primary);">🫀 Heart Health</h3>
            <p class="text-sm mt-1">Keep your heart healthy with 150 minutes of moderate exercise weekly, a diet rich in vegetables and fish, and by managing blood pressure.</p>
            <a href="#" class="btn btn-sm btn-outline mt-2">Read more</a>
        </div>
        <div class="stat-card accent">
            <h3 style="color:var(--accent);">🍎 Nutrition</h3>
            <p class="text-sm mt-1">A balanced diet with adequate protein, whole grains, and local vegetables (e.g., rape, chibwabwa) supports strong immunity and wellbeing.</p>
            <a href="#" class="btn btn-sm btn-outline mt-2">Read more</a>
        </div>
        <div class="stat-card success">
            <h3 style="color:var(--success);">🦟 Malaria Prevention</h3>
            <p class="text-sm mt-1">Use insecticide-treated bed nets, clear stagnant water near your home, and seek early treatment for fever.</p>
            <a href="#" class="btn btn-sm btn-outline mt-2">Read more</a>
        </div>
        <div class="stat-card">
            <h3 style="color:var(--info);">💉 Vaccinations</h3>
            <p class="text-sm mt-1">Stay up to date with national immunisation schedules. Book an appointment through reception for immunisation services.</p>
            <a href="#" class="btn btn-sm btn-outline mt-2">Read more</a>
        </div>
        <div class="stat-card danger">
            <h3 style="color:var(--danger);">🧠 Mental Health</h3>
            <p class="text-sm mt-1">It is okay not to be okay. Reach out to our counselling services — mental health is as important as physical health.</p>
            <a href="#" class="btn btn-sm btn-outline mt-2">Read more</a>
        </div>
        <div class="stat-card">
            <h3 style="color:var(--primary);">🤰 Maternal Care</h3>
            <p class="text-sm mt-1">Antenatal check-ups, safe delivery planning, and postnatal support are essential for a healthy pregnancy.</p>
            <a href="#" class="btn btn-sm btn-outline mt-2">Read more</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">⭐ Share Your Feedback</div>
    <p class="text-muted mb-2">Help us improve. Your feedback shapes the care we provide.</p>
    <form method="POST" action="<?= BASE_URL ?>/api/patient_actions.php">
        <input type="hidden" name="action" value="feedback">
        <div class="form-group">
            <label>Rating (1–5)</label>
            <select name="rating" required>
                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                <option value="4">⭐⭐⭐⭐ Good</option>
                <option value="3">⭐⭐⭐ Average</option>
                <option value="2">⭐⭐ Poor</option>
                <option value="1">⭐ Very Poor</option>
            </select>
        </div>
        <div class="form-group">
            <label>Comments</label>
            <textarea name="comments" rows="4" required></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Submit Feedback</button>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
