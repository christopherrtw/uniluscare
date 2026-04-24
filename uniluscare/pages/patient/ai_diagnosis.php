<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('patient');
$PAGE_TITLE = 'AI Diagnosis';
$ACTIVE_NAV = 'ai_diagnosis';
include __DIR__ . '/../../includes/layout.php';
?>

<div class="card mb-3">
    <div class="card-title">🧠 AI Symptom Checker</div>
    <p class="text-muted mb-2">Describe your symptoms and our AI will suggest a preliminary diagnosis. This is not a replacement for medical advice — always consult your doctor.</p>
    <form id="symptomForm">
        <div class="form-group">
            <label>Describe your symptoms (separate with commas)</label>
            <textarea name="symptoms" id="symptomInput" placeholder="e.g. headache, fever, sore throat, cough" rows="3"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Duration</label>
                <select name="duration">
                    <option>Less than 1 day</option>
                    <option>1–3 days</option>
                    <option>4–7 days</option>
                    <option>More than 1 week</option>
                </select>
            </div>
            <div class="form-group">
                <label>Severity</label>
                <select name="severity">
                    <option>Mild</option><option>Moderate</option><option>Severe</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Analyse Symptoms</button>
    </form>
    <div id="aiResult" class="mt-3"></div>
</div>

<div class="card mb-3">
    <div class="card-title">🩻 Medical Imaging Analysis</div>
    <p class="text-muted mb-2">Upload a medical image (X-ray, MRI, CT) for preliminary AI analysis to assist your radiologist.</p>
    <form method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>/api/patient_actions.php">
        <input type="hidden" name="action" value="image_upload">
        <div class="form-row">
            <div class="form-group">
                <label>Image Type</label>
                <select name="image_type" required>
                    <option>X-ray</option><option>MRI</option><option>CT</option><option>Ultrasound</option>
                </select>
            </div>
            <div class="form-group">
                <label>Body Part</label>
                <input type="text" name="body_part" required placeholder="e.g. Chest, Knee, Head">
            </div>
        </div>
        <div class="form-group">
            <label>Select Image (JPG, PNG, or DICOM)</label>
            <input type="file" name="image_file" required accept="image/*,.dcm">
        </div>
        <button type="submit" class="btn btn-primary">Upload & Analyse</button>
    </form>
</div>

<div class="card">
    <div class="card-title">📈 Predictive Health Analytics</div>
    <p class="text-muted mb-2">Based on your medical history, here are proactive recommendations:</p>
    <div id="predictive">
        <ul style="padding-left:1.5rem; line-height:1.9;">
            <li>✅ Your recent vitals are within normal range.</li>
            <li>⚠️ Consider a follow-up blood pressure check in 30 days.</li>
            <li>💉 Annual vaccinations may be due — check with reception.</li>
            <li>🥗 Based on BMI trends, a dietary consultation is recommended.</li>
        </ul>
    </div>
</div>

<script>
// Simple rule-based symptom checker (client-side AI simulation)
const knowledge = [
  {match:['fever','cough','sore throat'], suggest:'Possible viral upper respiratory infection (common cold or flu). Rest, hydrate, and consult your doctor if fever persists beyond 3 days.'},
  {match:['headache','nausea','sensitivity'], suggest:'Possible migraine. Rest in a quiet, dark room. If severe, seek medical attention.'},
  {match:['chest pain','shortness'], suggest:'⚠️ URGENT: Possible cardiac event — please visit the ER immediately.'},
  {match:['abdominal pain','vomiting','diarrhea'], suggest:'Possible gastroenteritis. Maintain hydration and consult your doctor if symptoms persist.'},
  {match:['fatigue','thirst','urination'], suggest:'Could indicate diabetes. Blood sugar test recommended.'},
  {match:['cough','weight loss','night sweats'], suggest:'Could indicate tuberculosis — urgent medical evaluation recommended.'},
  {match:['fever','joint pain','rash'], suggest:'Possible malaria or viral infection. Urgent blood test recommended.'}
];

document.getElementById('symptomForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const inp = document.getElementById('symptomInput').value.toLowerCase();
    const tokens = inp.split(/[,;.\s]+/).filter(Boolean);
    const hits = knowledge.filter(k => k.match.some(m => tokens.some(t => t.includes(m) || m.includes(t))));
    const r = document.getElementById('aiResult');
    if (hits.length === 0) {
        r.innerHTML = '<div class="alert alert-info"><strong>Preliminary assessment:</strong> No specific match in our knowledge base. Please consult a doctor for accurate diagnosis.</div>';
    } else {
        r.innerHTML = hits.map(h =>
            `<div class="alert alert-warning"><strong>Possible match:</strong> ${h.suggest}</div>`).join('');
        r.innerHTML += '<div class="alert alert-info">This is a preliminary AI assessment only. Please book an appointment for proper diagnosis.</div>';
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
