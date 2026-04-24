<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'AI Diagnosis'; $ACTIVE_NAV = 'ai_diagnosis';
include __DIR__ . '/../../includes/layout.php';
?>
<div class="card mb-3">
    <div class="card-title">🧠 AI-Assisted Diagnosis</div>
    <p class="text-muted mb-2">Enter patient symptoms and medical details to receive AI-powered preliminary diagnosis suggestions.</p>
    <form id="diagForm">
        <div class="form-row">
            <div class="form-group"><label>Patient ID</label><input type="text" id="diagPid" placeholder="e.g. P000001"></div>
            <div class="form-group"><label>Age</label><input type="number" id="diagAge"></div>
        </div>
        <div class="form-group"><label>Symptoms (comma-separated)</label><textarea id="diagSym" rows="3"></textarea></div>
        <button class="btn btn-primary" type="submit">Run AI Analysis</button>
    </form>
    <div id="diagResult" class="mt-3"></div>
</div>

<div class="card mb-3">
    <div class="card-title">📊 Predictive Analytics</div>
    <div class="stats-grid">
        <div class="stat-card"><div class="label">High-risk patients</div><div class="value">12</div></div>
        <div class="stat-card accent"><div class="label">Readmission risk</div><div class="value">4</div></div>
        <div class="stat-card success"><div class="label">Recovery trend</div><div class="value">↑ 8%</div></div>
    </div>
</div>

<div class="card">
    <div class="card-title">🩻 Medical Imaging Analysis Queue</div>
    <p class="text-muted">Images uploaded by patients with AI preliminary analysis are available under <a href="<?= BASE_URL ?>/pages/doctor/imaging.php">Imaging / PACS</a>.</p>
</div>

<script>
const kb = [
  {tags:['fever','cough','sore throat'], dx:'Upper Respiratory Infection (ICD-10: J06.9)', conf:78},
  {tags:['chest pain','shortness of breath','sweating'], dx:'⚠️ Acute Coronary Syndrome (ICD-10: I24.9) — URGENT', conf:85},
  {tags:['headache','nausea','photophobia'], dx:'Migraine (ICD-10: G43.9)', conf:72},
  {tags:['abdominal pain','vomiting','diarrhea'], dx:'Gastroenteritis (ICD-10: A09)', conf:76},
  {tags:['fever','joint pain','chills'], dx:'Malaria (ICD-10: B54) — order blood smear', conf:80},
  {tags:['polyuria','thirst','weight loss','fatigue'], dx:'Diabetes Mellitus (ICD-10: E11.9)', conf:74},
  {tags:['cough','night sweats','weight loss'], dx:'Pulmonary Tuberculosis (ICD-10: A15.0) — order CXR + sputum', conf:77},
];
document.getElementById('diagForm').addEventListener('submit', e => {
    e.preventDefault();
    const sym = document.getElementById('diagSym').value.toLowerCase();
    const hits = kb.filter(k => k.tags.some(t => sym.includes(t)));
    const r = document.getElementById('diagResult');
    if (!hits.length) { r.innerHTML = '<div class="alert alert-info">No AI matches. Consider further investigation.</div>'; return; }
    r.innerHTML = hits.map(h => `<div class="alert alert-warning"><strong>${h.dx}</strong> — Confidence: ${h.conf}%</div>`).join('');
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
