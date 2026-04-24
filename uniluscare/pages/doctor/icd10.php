<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'ICD-10 Codes'; $ACTIVE_NAV = 'icd10';
include __DIR__ . '/../../includes/layout.php';
?>
<div class="card">
    <div class="card-title">📋 ICD-10 Disease Codes — Quick Reference</div>
    <input type="text" id="icdSearch" placeholder="Search for a disease or code..." style="margin-bottom:1rem;">
    <table class="data-table" id="icdTable">
        <tr><th>Code</th><th>Disease</th><th>Category</th></tr>
        <?php
        $codes = [
          ['A09', 'Infectious gastroenteritis', 'Infectious'],
          ['A15.0', 'Pulmonary tuberculosis', 'Infectious'],
          ['B20', 'HIV disease', 'Infectious'],
          ['B54', 'Malaria, unspecified', 'Infectious'],
          ['E11.9', 'Type 2 diabetes mellitus without complications', 'Endocrine'],
          ['G43.9', 'Migraine, unspecified', 'Neurological'],
          ['I10', 'Essential hypertension', 'Cardiovascular'],
          ['I24.9', 'Acute ischaemic heart disease, unspecified', 'Cardiovascular'],
          ['J06.9', 'Acute upper respiratory infection, unspecified', 'Respiratory'],
          ['J18.9', 'Pneumonia, unspecified', 'Respiratory'],
          ['K29.7', 'Gastritis, unspecified', 'Digestive'],
          ['N39.0', 'Urinary tract infection, site not specified', 'Genitourinary'],
          ['O80', 'Single spontaneous delivery', 'Pregnancy'],
          ['R50.9', 'Fever, unspecified', 'Symptoms'],
          ['R51', 'Headache', 'Symptoms'],
          ['S52.5', 'Fracture of lower end of radius', 'Injury'],
          ['Z00.0', 'General adult medical examination', 'Encounter'],
        ];
        foreach ($codes as $c): ?>
            <tr><td><strong><?= $c[0] ?></strong></td><td><?= $c[1] ?></td><td><?= $c[2] ?></td></tr>
        <?php endforeach; ?>
    </table>
</div>
<script>
document.getElementById('icdSearch').addEventListener('input', e => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#icdTable tr').forEach((r,i) => {
        if (i===0) return;
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
