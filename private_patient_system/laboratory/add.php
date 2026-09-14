<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/* =========================================================
   GET PATIENT ID
========================================================= */

$patientId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patientId <= 0) {
    die("Invalid patient ID.");
}

/* =========================================================
   GET PATIENT
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        patient_id,
        last_name,
        first_name,
        middle_name,
        birthdate,
        sex,
        address,
        contact_no
    FROM patients
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $patientId);
$stmt->execute();

$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

if (!$patient) {
    die("Patient not found.");
}

/* =========================================================
   PATIENT NAME / AGE
========================================================= */

$middleInitial = "";

if (!empty($patient['middle_name'])) {
    $middleName = trim($patient['middle_name']);
    $middleInitial = " " . strtoupper(substr($middleName, 0, 1)) . ".";
}

$fullName =
    strtoupper(trim($patient['last_name'])) . ", " .
    strtoupper(trim($patient['first_name'])) .
    $middleInitial;

$age = "";

if (!empty($patient['birthdate'])) {
    try {
        $birthDate = new DateTime($patient['birthdate']);
        $today = new DateTime();
        $age = $birthDate->diff($today)->y;
    } catch (Exception $e) {
        $age = "";
    }
}

/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Add Laboratory";
$pageSubtitle = "Laboratory Results";
$basePath = "../";
$activePage = "laboratory";

include "../includes/header.php";
include "../includes/navigation.php";

/* =========================================================
   LABORATORY TEST LIST
   ORDER:
   1. CLINICAL CHEMISTRY
   2. LIPID PROFILE
   3. HEMATOLOGY
   4. URINALYSIS
   5. RADIOLOGY
   6. STOOL
   7. OTHERS
========================================================= */

$clinicalChemistryTests = array(
    "FASTING BLOOD SUGAR",
    "RANDOM BLOOD SUGAR",
    "CREATININE",
    "URIC ACID",
    "SGPT / ALT",
    "AST / SGOT",
    "HBA1C",
    "VLDL",
    "ORAL GLUCOSE TOLERANCE TEST"
);

$lipidTests = array(
    "TOTAL CHOLESTEROL",
    "TRIGLYCERIDES",
    "HDL",
    "LDL"
);

$hematologyTests = array(
    "HEMOGLOBIN",
    "HEMATOCRIT",
    "WBC COUNT",
    "SEGMENTER",
    "LYMPHOCYTE",
    "EOSINOPHIL",
    "MONOCYTE",
    "BASOPHIL",
    "PLATELET COUNT"
);

$urinalysisTests = array(
    "COLOR",
    "APPEARANCE",
    "PH",
    "SPECIFIC GRAVITY",
    "ALBUMIN",
    "SUGAR",
    "KETONES",
    "BLOOD",
    "NITRITE",
    "LEUKOCYTE ESTERASE",
    "EPITHELIAL CELLS",
    "MUCUS THREADS",
    "RBC CELLS",
    "PUS CELLS",
    "AMORPHOUS URATES",
    "AMORPHOUS PHOSPHATES",
    "BACTERIA"
);

$stoolTests = array(
    "FECAL OCCULT BLOOD",
    "FECALYSIS"
);

$otherTests = array(
    "PAP SMEAR",
    "PPD TEST",
    "SPUTUM MICROSCOPY"
);

/* =========================================================
   REFERENCE VALUES
   Display only. These are NOT editable form fields.
========================================================= */

$referenceRanges = array(

    /* CLINICAL CHEMISTRY */
    "FASTING BLOOD SUGAR" => "70-105 mg/dL",
    "RANDOM BLOOD SUGAR" => "70-140 mg/dL",
    "CREATININE" => "Male: 0.74-1.35 mg/dL | Female: 0.59-1.04 mg/dL",
    "URIC ACID" => "2.5-7.7 mg/dL",
    "SGPT / ALT" => "0-40 U/L",
    "AST / SGOT" => "0-40 U/L",
    "HBA1C" => "<5.7%",
    "ORAL GLUCOSE TOLERANCE TEST" => "See OGTT parameters",

    /* LIPID PROFILE */
    "TOTAL CHOLESTEROL" => "100-200 mg/dL",
    "TRIGLYCERIDES" => "44-148 mg/dL",
    "HDL" => "30-70 mg/dL",
    "LDL" => "Less than 130 mg/dL",
    "VLDL" => "10-40 mg/dL",

    /* HEMATOLOGY */
    "HEMOGLOBIN" => "Male: 140-180 g/L | Female: 120-160 g/L",
    "HEMATOCRIT" => "Male: 0.40-0.54 | Female: 0.37-0.47",
    "WBC COUNT" => "5-10 x10^9/L",
    "SEGMENTER" => "0.50-0.70",
    "LYMPHOCYTE" => "0.20-0.40",
    "EOSINOPHIL" => "0.01-0.05",
    "MONOCYTE" => "0.02-0.06",
    "BASOPHIL" => "0.00-0.01",
    "PLATELET COUNT" => "Male: 150-450 x10^9/L | Female: 150-450 x10^9/L",

    /* URINALYSIS */
    "COLOR" => "Light yellow",
    "APPEARANCE" => "Clear",
    "PH" => "5.0-8.0",
    "SPECIFIC GRAVITY" => "1.005-1.030",
    "ALBUMIN" => "Negative",
    "SUGAR" => "Negative",
    "KETONES" => "Negative",
    "BLOOD" => "Negative",
    "NITRITE" => "Negative",
    "LEUKOCYTE ESTERASE" => "Negative",
    "EPITHELIAL CELLS" => "Few",
    "MUCUS THREADS" => "Few",
    "RBC CELLS" => "0-1 /HPF",
    "PUS CELLS" => "0-1 /HPF",
    "AMORPHOUS URATES" => "None / Few",
    "AMORPHOUS PHOSPHATES" => "None / Few",
    "BACTERIA" => "Rare",

    /* STOOL */
    "FECAL OCCULT BLOOD" => "Negative",
    "FECALYSIS" => "See fecalysis parameters",

    /* OTHERS */
    "PAP SMEAR" => "See cytology report",
    "PPD TEST" => "Negative",
    "SPUTUM MICROSCOPY" => "Negative"
);

/* =========================================================
   CATEGORY DATA
========================================================= */

$categories = array(
    array(
        "key" => "CHEMISTRY",
        "title" => "Clinical Chemistry",
        "tests" => $clinicalChemistryTests,
        "facilityLabel" => "Where was the Clinical Chemistry performed?"
    ),
    array(
        "key" => "LIPID",
        "title" => "Lipid Profile",
        "tests" => $lipidTests,
        "facilityLabel" => "Where was the Lipid Profile performed?"
    ),
    array(
        "key" => "HEMATOLOGY",
        "title" => "Hematology",
        "tests" => $hematologyTests,
        "facilityLabel" => "Where was the Hematology examination performed?"
    ),
    array(
        "key" => "URINALYSIS",
        "title" => "Urinalysis",
        "tests" => $urinalysisTests,
        "facilityLabel" => "Where was the Urinalysis performed?"
    ),
    array(
        "key" => "STOOL",
        "title" => "Stool",
        "tests" => $stoolTests,
        "facilityLabel" => "Where was the Stool examination performed?"
    ),
    array(
        "key" => "OTHERS",
        "title" => "Others",
        "tests" => $otherTests,
        "facilityLabel" => "Where were the Other tests performed?"
    )
);

$stepNames = array(
    "Clinical Chemistry",
    "Lipid Profile",
    "Hematology",
    "Urinalysis",
    "Radiology",
    "Stool",
    "Others"
);

?>

<style>

/* =========================================================
   PAGE
========================================================= */

.lab-page {
    max-width: 1280px;
    margin: 25px auto 50px;
    padding: 0 20px;
    font-family: Arial, Helvetica, sans-serif;
}

.lab-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 20px;
}

.lab-title h2 {
    margin: 0;
    color: #1f2937;
    font-size: 29px;
    font-weight: 700;
}

.lab-title p {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 14px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none;
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
    padding: 10px 15px;
    border-radius: 7px;
    font-size: 14px;
    font-weight: 700;
}

.back-button:hover {
    background: #e5e7eb;
}

/* =========================================================
   PATIENT CARD
========================================================= */

.patient-card,
.lab-card {
    background: #ffffff;
    border: 1px solid #dbe3ea;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.patient-card {
    margin-bottom: 20px;
}

.patient-card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    padding: 14px 18px;
    font-size: 15px;
    font-weight: 700;
    color: #374151;
}

.patient-card-body {
    padding: 18px;
}

.patient-grid {
    display: grid;
    grid-template-columns: 1.4fr 0.8fr 0.8fr 1.8fr;
    gap: 18px;
}

.patient-item {
    min-width: 0;
}

.patient-label {
    display: block;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 5px;
    letter-spacing: 0.3px;
}

.patient-value {
    color: #111827;
    font-size: 15px;
    font-weight: 600;
    word-break: break-word;
}

/* =========================================================
   LAB HEADER
========================================================= */

.lab-card-header {
    background: #0f5f9f;
    color: #ffffff;
    padding: 16px 18px;
}

.lab-card-title {
    font-size: 18px;
    font-weight: 700;
}

.lab-card-subtitle {
    margin-top: 4px;
    font-size: 13px;
    opacity: 0.95;
}

/* =========================================================
   DATE
========================================================= */

.date-section {
    padding: 18px;
    border-bottom: 1px solid #e5e7eb;
    background: #fafafa;
}

.date-field {
    max-width: 280px;
}

.date-field label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
}

.date-field input {
    width: 100%;
    box-sizing: border-box;
    padding: 11px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    color: #111827;
    font-size: 14px;
    outline: none;
}

.date-field input:focus {
    border-color: #0f5f9f;
    box-shadow: 0 0 0 2px rgba(15,95,159,0.10);
}

.required-note {
    color: #64748b;
    font-size: 11px;
    margin-top: 5px;
}

/* =========================================================
   STEP NAVIGATION
========================================================= */

.step-navigation {
    padding: 16px 18px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

.step-list {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
}

.step-item {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    user-select: none;
}

.step-item:hover {
    color: #0f5f9f;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
}

.step-item.active {
    color: #0f5f9f;
}

.step-item.active .step-number {
    background: #0f5f9f;
    color: #ffffff;
}

.step-item.completed {
    color: #15803d;
}

.step-item.completed .step-number {
    background: #dcfce7;
    color: #15803d;
}

.step-line {
    width: 32px;
    height: 1px;
    background: #cbd5e1;
}

/* =========================================================
   STEPS
========================================================= */

.lab-step {
    display: none;
}

.lab-step.active {
    display: block;
}

.step-header {
    padding: 18px;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}

.step-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 20px;
}

.step-header p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 13px;
}

/* =========================================================
   WHERE PERFORMED
========================================================= */

.facility-section {
    padding: 16px 18px;
    border-bottom: 1px solid #e5e7eb;
    background: #fafcff;
}

.facility-title {
    color: #374151;
    font-size: 13px;
    font-weight: 800;
    margin-bottom: 10px;
}

.facility-row {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.facility-options {
    display: flex;
    align-items: center;
    gap: 20px;
}

.facility-radio {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    white-space: nowrap;
    color: #374151;
    font-size: 14px;
    font-weight: 600;
}

.facility-radio input[type="radio"] {
    width: 18px;
    height: 18px;
    margin: 0;
    cursor: pointer;
}

.outside-details {
    display: none;
    flex: 1;
    min-width: 260px;
}

.outside-details.show {
    display: block;
}

.outside-field {
    width: 100%;
    max-width: 520px;
}

.outside-field label {
    display: block;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 5px;
}

.outside-field input {
    width: 100%;
    height: 40px;
    box-sizing: border-box;
    padding: 8px 11px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    color: #111827;
    font-size: 14px;
    outline: none;
}

/* =========================================================
   TABLE
========================================================= */

.table-scroll {
    width: 100%;
    overflow-x: auto;
}

.lab-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
    table-layout: fixed;
}

.lab-table thead th {
    background: #f1f5f9;
    color: #334155;
    border-bottom: 2px solid #cbd5e1;
    padding: 13px 12px;
    text-align: left;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
}

.lab-table thead th:nth-child(1) {
    width: 35%;
}

.lab-table thead th:nth-child(2) {
    width: 27%;
}

.lab-table thead th:nth-child(3) {
    width: 38%;
}

.lab-table tbody tr {
    border-bottom: 1px solid #e5e7eb;
}

.lab-table tbody tr:nth-child(even) {
    background: #fafafa;
}

.lab-table tbody tr:hover {
    background: #f8fbff;
}

.lab-table td {
    padding: 10px 12px;
    vertical-align: middle;
    box-sizing: border-box;
}

/* =========================================================
   TEST NAME
========================================================= */

.test-name-cell {
    color: #1f2937;
    font-size: 14px;
    font-weight: 700;
    word-break: break-word;
}

/* =========================================================
   RESULT
   ONLY RESULT IS A FORM FIELD.
========================================================= */

.lab-input,
.ecg-result-select {
    width: 100%;
    box-sizing: border-box;
    height: 40px;
    padding: 8px 11px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    color: #111827;
    font-size: 14px;
    outline: none;
}

.lab-input:focus,
.ecg-result-select:focus {
    border-color: #0f5f9f;
    box-shadow: 0 0 0 2px rgba(15,95,159,0.10);
}

/* =========================================================
   REFERENCE
   NOT A FORM FIELD
========================================================= */

.stool-table .test-name-cell {
    font-size: 14px;
    font-weight: 700;
}

.stool-table .lab-input,
.stool-table .reference-display {
    min-height: 38px;
}

.stool-table .reference-display {
    font-size: 12px;
}

.reference-display {
    width: 100%;
    min-height: 40px;
    box-sizing: border-box;
    padding: 9px 11px;
    background: #f8fafc;
    color: #334155;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    display: flex;
    align-items: center;
    word-break: break-word;
}

/* =========================================================
   RADIOLOGY
========================================================= */

.radiology-block {
    border-bottom: 1px solid #e5e7eb;
}

.radiology-title {
    padding: 16px 18px 8px;
    color: #1f2937;
    font-size: 17px;
    font-weight: 800;
}

.radiology-note {
    padding: 0 18px 14px;
    color: #64748b;
    font-size: 12px;
}

.radiology-facility {
    background: #fafcff;
}

.radiology-table {
    min-width: 900px;
}

.radiology-table thead th:nth-child(1) {
    width: 22%;
}

.radiology-table thead th:nth-child(2) {
    width: 39%;
}

.radiology-table thead th:nth-child(3) {
    width: 39%;
}

.radiology-textarea {
    width: 100%;
    min-height: 105px;
    box-sizing: border-box;
    resize: vertical;
    padding: 10px 11px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    color: #111827;
    font-size: 14px;
    outline: none;
}

.radiology-textarea:focus {
    border-color: #0f5f9f;
    box-shadow: 0 0 0 2px rgba(15,95,159,0.10);
}

.radiology-label {
    display: block;
    color: #475569;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.ecg-finding-box {
    display: none;
    margin-top: 10px;
}

.ecg-finding-box.show {
    display: block;
}

/* =========================================================
   OTHER TESTS
========================================================= */

.other-action {
    padding: 14px 18px;
    background: #fffdf5;
    border-top: 1px solid #eee2ad;
}

.add-other-btn {
    border: 1px solid #0f5f9f;
    background: #ffffff;
    color: #0f5f9f;
    border-radius: 7px;
    padding: 10px 15px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
}

.add-other-btn:hover {
    background: #0f5f9f;
    color: #ffffff;
}

.other-name-input {
    width: 100%;
    height: 40px;
    box-sizing: border-box;
    padding: 8px 11px;
    border: 1px solid #d6b656;
    border-radius: 7px;
    background: #fffef8;
    color: #111827;
    font-size: 14px;
    outline: none;
}

.remove-other-btn {
    border: 0;
    background: transparent;
    color: #dc2626;
    font-size: 20px;
    font-weight: 700;
    cursor: pointer;
    width: 34px;
    height: 34px;
    border-radius: 5px;
}

.remove-other-btn:hover {
    background: #fee2e2;
}

/* =========================================================
   HELP / SAVE
========================================================= */

.lab-help {
    padding: 13px 18px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
}

.save-action {
    display: flex;
    justify-content: flex-end;
    padding: 18px;
    border-top: 1px solid #e5e7eb;
    background: #ffffff;
}

.save-btn {
    min-width: 210px;
    padding: 11px 18px;
    border-radius: 7px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    background: #15803d;
    color: #ffffff;
    border: 1px solid #15803d;
}

.save-btn:hover {
    background: #166534;
}

/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 900px) {

    .patient-grid {
        grid-template-columns: 1fr 1fr;
    }

    .facility-row {
        align-items: flex-start;
        flex-direction: column;
    }

    .outside-details {
        width: 100%;
    }

    .outside-field {
        max-width: 100%;
    }

    .step-list {
        justify-content: flex-start;
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 3px;
    }

    .step-item {
        flex: 0 0 auto;
    }

    .step-line {
        flex: 0 0 25px;
    }
}

@media (max-width: 600px) {

    .lab-page {
        padding: 0 12px;
        margin-top: 18px;
    }

    .lab-page-header {
        flex-direction: column;
    }

    .patient-grid {
        grid-template-columns: 1fr;
    }

    .lab-title h2 {
        font-size: 24px;
    }

    .facility-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .save-action {
        justify-content: stretch;
    }

    .save-btn {
        width: 100%;
    }
}

</style>

<div class="lab-page">

    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="lab-page-header">

        <div class="lab-title">
            <h2>Laboratory Results</h2>
            <p>Enter laboratory results for this patient.</p>
        </div>

        <a
            href="index.php?id=<?php echo $patientId; ?>"
            class="back-button"
        >
            ← Back to Laboratory
        </a>

    </div>

    <!-- =====================================================
         PATIENT INFORMATION
    ====================================================== -->

    <div class="patient-card">

        <div class="patient-card-header">
            Patient Information
        </div>

        <div class="patient-card-body">

            <div class="patient-grid">

                <div class="patient-item">
                    <span class="patient-label">Patient Name</span>
                    <div class="patient-value">
                        <?php echo htmlspecialchars($fullName); ?>
                    </div>
                </div>

                <div class="patient-item">
                    <span class="patient-label">Patient ID</span>
                    <div class="patient-value">
                        <?php echo htmlspecialchars($patient['patient_id']); ?>
                    </div>
                </div>

                <div class="patient-item">
                    <span class="patient-label">Age / Sex</span>
                    <div class="patient-value">
                        <?php echo htmlspecialchars($age); ?>
                        /
                        <?php
                        echo !empty($patient['sex'])
                            ? htmlspecialchars(strtoupper($patient['sex']))
                            : "-";
                        ?>
                    </div>
                </div>

                <div class="patient-item">
                    <span class="patient-label">Address</span>
                    <div class="patient-value">
                        <?php echo htmlspecialchars($patient['address']); ?>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- =====================================================
         LABORATORY CARD
    ====================================================== -->

    <div class="lab-card">

        <div class="lab-card-header">
            <div class="lab-card-title">Laboratory Examination</div>
            <div class="lab-card-subtitle">
                Results are entered manually. Reference values are displayed for guidance only.
            </div>
        </div>

        <form
            action="save.php"
            method="POST"
            id="laboratoryForm"
            autocomplete="off"
        >

            <input
                type="hidden"
                name="patient_id"
                value="<?php echo $patientId; ?>"
            >

            <!-- DATE -->

            <div class="date-section">

                <div class="date-field">

                    <label for="test_date">
                        Laboratory Date
                    </label>

                    <input
                        type="date"
                        id="test_date"
                        name="test_date"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                    <div class="required-note">
                        Date when the laboratory examination was performed.
                    </div>

                </div>

            </div>

            <!-- STEP NAVIGATION -->

            <div class="step-navigation">

                <div class="step-list">

                    <?php foreach ($stepNames as $index => $stepName): ?>

                        <?php if ($index > 0): ?>
                            <div class="step-line"></div>
                        <?php endif; ?>

                        <div
                            class="step-item <?php echo $index === 0 ? 'active' : ''; ?>"
                            data-step="<?php echo $index; ?>"
                            onclick="showStep(<?php echo $index; ?>)"
                            role="button"
                            tabindex="0"
                            onkeydown="handleStepKey(event, <?php echo $index; ?>)"
                        >

                            <span class="step-number">
                                <?php echo $index + 1; ?>
                            </span>

                            <?php echo htmlspecialchars($stepName); ?>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <!-- =================================================
                 STEPS 1-4, 6-7
            ================================================== -->

            <?php foreach ($categories as $index => $category): ?>

                <div
                    class="lab-step <?php echo $index === 0 ? 'active' : ''; ?>"
                    data-step="<?php echo ($index >= 4 ? $index + 1 : $index); ?>"
                >

                    <div class="step-header">

                        <h3>
                            <?php echo htmlspecialchars($category['title']); ?>
                            <?php if ($category['key'] === "STOOL"): ?>
                                Examination
                            <?php endif; ?>
                        </h3>

                        <p>
                            Enter the stool examination result. Reference value is for guidance only.
                        </p>

                    </div>

                    <!-- WHERE PERFORMED -->

                    <div class="facility-section">

                        <div class="facility-title">
                            <?php echo htmlspecialchars($category['facilityLabel']); ?>
                        </div>

                        <div class="facility-row">

                            <div class="facility-options">

                                <label class="facility-radio">

                                    <input
                                        type="radio"
                                        name="facility_type[<?php echo htmlspecialchars($category['key']); ?>]"
                                        value="WITHIN_FACILITY"
                                        checked
                                        onchange="toggleFacility('<?php echo htmlspecialchars($category['key']); ?>')"
                                    >

                                    <span>Within Facility</span>

                                </label>

                                <label class="facility-radio">

                                    <input
                                        type="radio"
                                        name="facility_type[<?php echo htmlspecialchars($category['key']); ?>]"
                                        value="ACCREDITED_FACILITY"
                                        onchange="toggleFacility('<?php echo htmlspecialchars($category['key']); ?>')"
                                    >

                                    <span>Accredited Diagnostic Facilities</span>

                                </label>

                            </div>

                            <div
                                class="outside-details"
                                id="outside_<?php echo htmlspecialchars($category['key']); ?>"
                            >

                                <div class="outside-field">

                                    <label for="institution_<?php echo htmlspecialchars($category['key']); ?>">
                                        Laboratory Name
                                    </label>

                                    <input
                                        type="text"
                                        name="health_care_institution[<?php echo htmlspecialchars($category['key']); ?>]"
                                        id="institution_<?php echo htmlspecialchars($category['key']); ?>"
                                        placeholder="Enter laboratory name"
                                        class="uppercase-input"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- RESULTS TABLE -->

                    <div class="table-scroll">

                        <table class="lab-table <?php echo $category['key'] === "STOOL" ? "stool-table" : ""; ?>">

                            <thead>

                                <tr>
                                    <th>Test</th>
                                    <th>Result</th>
                                    <th>Reference Value</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($category['tests'] as $test): ?>

                                    <tr>

                                        <td class="test-name-cell">

                                            <?php echo htmlspecialchars($test); ?>

                                            <input
                                                type="hidden"
                                                name="test_name[]"
                                                value="<?php echo htmlspecialchars($test); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="test_category[]"
                                                value="<?php echo htmlspecialchars($category['key']); ?>"
                                            >

                                        </td>

                                        <td>

                                            <input
                                                type="text"
                                                name="result[]"
                                                class="lab-input uppercase-input"
                                                placeholder="Enter result"
                                            >

                                        </td>

                                        <td>

                                            <div class="reference-display">

                                                <?php
                                                echo isset($referenceRanges[$test])
                                                    ? htmlspecialchars($referenceRanges[$test])
                                                    : "-";
                                                ?>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                    <?php if ($category['key'] === "OTHERS"): ?>

                        <!-- ADD OTHER TEST -->

                        <div class="other-action">

                            <button
                                type="button"
                                class="add-other-btn"
                                onclick="addOtherTest()"
                            >
                                + Add Other Test
                            </button>

                        </div>

                        <div class="table-scroll">

                            <table class="lab-table">

                                <thead>

                                    <tr>
                                        <th>Test Name</th>
                                        <th>Result</th>
                                        <th>Action</th>
                                    </tr>

                                </thead>

                                <tbody id="otherTestsBody">

                                    <tr class="other-row">

                                        <td>

                                            <input
                                                type="text"
                                                name="other_test_name[]"
                                                class="other-name-input uppercase-input"
                                                placeholder="Enter other laboratory test"
                                            >

                                        </td>

                                        <td>

                                            <input
                                                type="text"
                                                name="other_result[]"
                                                class="lab-input uppercase-input"
                                                placeholder="Enter result"
                                            >

                                        </td>

                                        <td>

                                            <button
                                                type="button"
                                                class="remove-other-btn"
                                                title="Remove this test"
                                                onclick="removeOtherTest(this)"
                                            >
                                                ×
                                            </button>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                        <!-- SAVE BUTTON — OTHERS ONLY -->
                        <div class="save-action">

                            <button
                                type="submit"
                                class="save-btn"
                            >
                                ✓ Save Laboratory Results
                            </button>

                        </div>

                    <?php endif; ?>

                </div>

            <?php if ($index === 3): ?>

            <!-- =================================================
                 STEP 5 - RADIOLOGY
                 X-RAY + ECG ARE IN ONE CATEGORY,
                 BUT EACH HAS ITS OWN WHERE-PERFORMED.
            ================================================== -->

            <div
                class="lab-step"
                data-step="4"
            >

                <div class="step-header">

                    <h3>Radiology</h3>

                    <p>
                        X-ray and ECG are grouped under Radiology, but their place of performance is recorded separately.
                    </p>

                </div>

                <!-- =================================================
                     X-RAY
                ================================================== -->

                <div class="radiology-block">

                    <div class="radiology-title">
                        Chest X-ray
                    </div>

                    <div class="radiology-note">
                        Enter the X-ray findings and impression.
                    </div>

                    <div class="facility-section radiology-facility">

                        <div class="facility-title">
                            Where was the Chest X-ray performed?
                        </div>

                        <div class="facility-row">

                            <div class="facility-options">

                                <label class="facility-radio">

                                    <input
                                        type="radio"
                                        name="facility_type[X_RAY]"
                                        value="WITHIN_FACILITY"
                                        checked
                                        onchange="toggleFacility('X_RAY')"
                                    >

                                    <span>Within Facility</span>

                                </label>

                                <label class="facility-radio">

                                    <input
                                        type="radio"
                                        name="facility_type[X_RAY]"
                                        value="ACCREDITED_FACILITY"
                                        onchange="toggleFacility('X_RAY')"
                                    >

                                    <span>Accredited Diagnostic Facilities</span>

                                </label>

                            </div>

                            <div
                                class="outside-details"
                                id="outside_X_RAY"
                            >

                                <div class="outside-field">

                                    <label for="institution_X_RAY">
                                        Facility Name
                                    </label>

                                    <input
                                        type="text"
                                        name="health_care_institution[X_RAY]"
                                        id="institution_X_RAY"
                                        placeholder="Enter X-ray facility name"
                                        class="uppercase-input"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="table-scroll">

                        <table class="lab-table radiology-table">

                            <thead>

                                <tr>
                                    <th>Test</th>
                                    <th>Findings</th>
                                    <th>Impression</th>
                                </tr>

                            </thead>

                            <tbody>

                                <tr>

                                    <td class="test-name-cell">

                                        CHEST X-RAY

                                        <input
                                            type="hidden"
                                            name="test_name[]"
                                            value="CHEST X-RAY"
                                        >

                                        <input
                                            type="hidden"
                                            name="test_category[]"
                                            value="RADIOLOGY"
                                        >

                                        <input
                                            type="hidden"
                                            name="result[]"
                                            value=""
                                        >

                                    </td>

                                    <td>

                                        <label class="radiology-label">
                                            Findings
                                        </label>

                                        <textarea
                                            name="xray_findings"
                                            id="xray_findings"
                                            class="radiology-textarea uppercase-input"
                                            placeholder="Enter X-ray findings..."
                                        ></textarea>

                                    </td>

                                    <td>

                                        <label class="radiology-label">
                                            Impression
                                        </label>

                                        <textarea
                                            name="xray_impression"
                                            id="xray_impression"
                                            class="radiology-textarea uppercase-input"
                                            placeholder="Enter X-ray impression..."
                                        ></textarea>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

                <!-- =================================================
                     ECG
                ================================================== -->

                <div class="radiology-block">

                    <div class="radiology-title">
                        Electrocardiogram (ECG)
                    </div>

                    <div class="radiology-note">
                        Select the ECG result. If there is a finding, enter the finding.
                    </div>

                    <div class="facility-section radiology-facility">

                        <div class="facility-title">
                            Where was the ECG performed?
                        </div>

                        <div class="facility-row">

                            <div class="facility-options">

                                <label class="facility-radio">

                                    <input
                                        type="radio"
                                        name="facility_type[ECG]"
                                        value="WITHIN_FACILITY"
                                        checked
                                        onchange="toggleFacility('ECG')"
                                    >

                                    <span>Within Facility</span>

                                </label>

                                <label class="facility-radio">

                                    <input
                                        type="radio"
                                        name="facility_type[ECG]"
                                        value="ACCREDITED_FACILITY"
                                        onchange="toggleFacility('ECG')"
                                    >

                                    <span>Accredited Diagnostic Facilities</span>

                                </label>

                            </div>

                            <div
                                class="outside-details"
                                id="outside_ECG"
                            >

                                <div class="outside-field">

                                    <label for="institution_ECG">
                                        Facility Name
                                    </label>

                                    <input
                                        type="text"
                                        name="health_care_institution[ECG]"
                                        id="institution_ECG"
                                        placeholder="Enter ECG facility name"
                                        class="uppercase-input"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="table-scroll">

                        <table class="lab-table">

                            <thead>

                                <tr>
                                    <th>Test</th>
                                    <th>Result</th>
                                    <th>ECG Finding</th>
                                </tr>

                            </thead>

                            <tbody>

                                <tr>

                                    <td class="test-name-cell">

                                        ELECTROCARDIOGRAM (ECG)

                                        <input
                                            type="hidden"
                                            name="test_name[]"
                                            value="ELECTROCARDIOGRAM (ECG)"
                                        >

                                        <input
                                            type="hidden"
                                            name="test_category[]"
                                            value="RADIOLOGY"
                                        >

                                    </td>

                                    <td>

                                        <select
                                            name="result[]"
                                            id="ecg_result"
                                            class="ecg-result-select"
                                        >

                                            <option value="">
                                                Select Result
                                            </option>

                                            <option value="ESSENTIALLY NORMAL">
                                                Essentially Normal
                                            </option>

                                            <option value="WITH FINDING">
                                                With Finding
                                            </option>

                                        </select>

                                    </td>

                                    <td>

                                        <div
                                            class="ecg-finding-box"
                                            id="ecgFindingBox"
                                        >

                                            <label
                                                class="radiology-label"
                                                for="ecg_finding"
                                            >
                                                Finding
                                            </label>

                                            <textarea
                                                name="ecg_finding"
                                                id="ecg_finding"
                                                class="radiology-textarea uppercase-input"
                                                placeholder="Enter ECG finding..."
                                            ></textarea>

                                        </div>

                                        <div
                                            id="ecgNoFinding"
                                            class="reference-display"
                                        >
                                            Select "With Finding" if applicable.
                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!-- =================================================
                 HELP
            ================================================== -->

            <div class="lab-help">

                <strong>Tip:</strong>
                Leave tests blank if they were not performed.
                Only the <strong>Result</strong> fields are editable;
                the <strong>Reference Value</strong> is displayed for guidance.

                For Chest X-ray, enter both
                <strong>Findings</strong> and <strong>Impression</strong>.

                For ECG, select a result and enter the finding when
                <strong>With Finding</strong> is selected.

            </div>

            <?php endif; ?>

            <?php endforeach; ?>

        </form>

    </div>

</div>

<script>

/* =========================================================
   FACILITY TOGGLE
========================================================= */

function toggleFacility(category) {

    var selected = document.querySelector(
        'input[name="facility_type[' + category + ']"]:checked'
    );

    var outside = document.getElementById(
        "outside_" + category
    );

    var institution = document.getElementById(
        "institution_" + category
    );

    if (!selected || !outside) {
        return;
    }

    if (selected.value === "ACCREDITED_FACILITY") {

        outside.classList.add("show");

        if (institution) {
            institution.required = true;
        }

    } else {

        outside.classList.remove("show");

        if (institution) {
            institution.required = false;
            institution.value = "";
        }

    }
}

/* =========================================================
   ECG TOGGLE
========================================================= */

function toggleECGFinding() {

    var result = document.getElementById("ecg_result");
    var findingBox = document.getElementById("ecgFindingBox");
    var noFinding = document.getElementById("ecgNoFinding");
    var finding = document.getElementById("ecg_finding");

    if (!result || !findingBox || !noFinding) {
        return;
    }

    if (result.value === "WITH FINDING") {

        findingBox.classList.add("show");
        noFinding.style.display = "none";

        if (finding) {
            finding.required = true;
        }

    } else {

        findingBox.classList.remove("show");
        noFinding.style.display = "flex";

        if (finding) {
            finding.required = false;
            finding.value = "";
        }

    }
}

/* =========================================================
   STEP SYSTEM
========================================================= */

var currentStep = 0;
var totalSteps = 7;

function showStep(step) {

    var steps = document.querySelectorAll(".lab-step");
    var indicators = document.querySelectorAll(".step-item");
    var i;

    if (step < 0) {
        step = 0;
    }

    if (step >= totalSteps) {
        step = totalSteps - 1;
    }

    currentStep = step;

    for (i = 0; i < steps.length; i++) {
        steps[i].classList.remove("active");
    }

    if (steps[currentStep]) {
        steps[currentStep].classList.add("active");
    }

    for (i = 0; i < indicators.length; i++) {

        indicators[i].classList.remove("active");
        indicators[i].classList.remove("completed");

        if (i === currentStep) {
            indicators[i].classList.add("active");
        } else if (i < currentStep) {
            indicators[i].classList.add("completed");
        }
    }
}

function handleStepKey(event, step) {

    if (event.key === "Enter" || event.key === " ") {

        event.preventDefault();
        showStep(step);

    }
}

/* =========================================================
   UPPERCASE INPUT
========================================================= */

document.addEventListener("input", function(event) {

    if (
        event.target.classList.contains("uppercase-input")
    ) {

        var start = event.target.selectionStart;
        var end = event.target.selectionEnd;

        event.target.value =
            event.target.value.toUpperCase();

        try {
            event.target.setSelectionRange(start, end);
        } catch (e) {
            /* Ignore cursor errors */
        }

    }

});

/* =========================================================
   ADD OTHER TEST
========================================================= */

function addOtherTest() {

    var body =
        document.getElementById("otherTestsBody");

    if (!body) {
        return;
    }

    var row =
        document.createElement("tr");

    row.className = "other-row";

    row.innerHTML =
        '<td>' +
            '<input ' +
                'type="text" ' +
                'name="other_test_name[]" ' +
                'class="other-name-input uppercase-input" ' +
                'placeholder="Enter other laboratory test"' +
            '>' +
        '</td>' +
        '<td>' +
            '<input ' +
                'type="text" ' +
                'name="other_result[]" ' +
                'class="lab-input uppercase-input" ' +
                'placeholder="Enter result"' +
            '>' +
        '</td>' +
        '<td>' +
            '<button ' +
                'type="button" ' +
                'class="remove-other-btn" ' +
                'title="Remove this test" ' +
                'onclick="removeOtherTest(this)"' +
            '>' +
                '×' +
            '</button>' +
        '</td>';

    body.appendChild(row);

    var input =
        row.querySelector(".other-name-input");

    if (input) {
        input.focus();
    }
}

/* =========================================================
   REMOVE OTHER TEST
========================================================= */

function removeOtherTest(button) {

    var row = button.closest("tr");

    if (!row) {
        return;
    }

    row.remove();
}

/* =========================================================
   FORM VALIDATION
========================================================= */

document.getElementById("laboratoryForm")
    .addEventListener("submit", function(event) {

        var facilityCategories = array(
            "CHEMISTRY",
            "LIPID",
            "HEMATOLOGY",
            "URINALYSIS",
            "X_RAY",
            "ECG",
            "STOOL",
            "OTHERS"
        );

        var i;

        /* -----------------------------------------------
           FACILITY NAME VALIDATION
        ------------------------------------------------ */

        for (i = 0; i < facilityCategories.length; i++) {

            var category =
                facilityCategories[i];

            var selected =
                document.querySelector(
                    'input[name="facility_type[' +
                    category +
                    ']"]:checked'
                );

            if (
                selected &&
                selected.value === "ACCREDITED_FACILITY"
            ) {

                var institution =
                    document.getElementById(
                        "institution_" + category
                    );

                if (
                    institution &&
                    institution.value.trim() === ""
                ) {

                    event.preventDefault();

                    alert(
                        "Please enter the facility name."
                    );

                    var facilityStep = 0;

                    if (category === "LIPID") {
                        facilityStep = 1;
                    } else if (category === "HEMATOLOGY") {
                        facilityStep = 2;
                    } else if (category === "URINALYSIS") {
                        facilityStep = 3;
                    } else if (
                        category === "X_RAY" ||
                        category === "ECG"
                    ) {
                        facilityStep = 4;
                    } else if (category === "STOOL") {
                        facilityStep = 5;
                    } else if (category === "OTHERS") {
                        facilityStep = 6;
                    }

                    showStep(facilityStep);
                    institution.focus();

                    return false;
                }
            }
        }

        /* -----------------------------------------------
           CHECK COMMON RESULTS
        ------------------------------------------------ */

        var commonResults =
            document.querySelectorAll(
                'input[name="result[]"], select[name="result[]"]'
            );

        var otherResults =
            document.querySelectorAll(
                'input[name="other_result[]"]'
            );

        var otherNames =
            document.querySelectorAll(
                'input[name="other_test_name[]"]'
            );

        var hasResult = false;

        for (i = 0; i < commonResults.length; i++) {

            if (
                commonResults[i].value &&
                commonResults[i].value.trim() !== ""
            ) {

                hasResult = true;
                break;

            }
        }

        /* -----------------------------------------------
           X-RAY
        ------------------------------------------------ */

        var xrayFindings =
            document.getElementById("xray_findings");

        var xrayImpression =
            document.getElementById("xray_impression");

        if (!hasResult && xrayFindings) {

            if (xrayFindings.value.trim() !== "") {
                hasResult = true;
            }

        }

        if (!hasResult && xrayImpression) {

            if (xrayImpression.value.trim() !== "") {
                hasResult = true;
            }

        }

        /* -----------------------------------------------
           OTHER RESULTS
        ------------------------------------------------ */

        if (!hasResult) {

            for (i = 0; i < otherResults.length; i++) {

                if (
                    otherResults[i].value &&
                    otherResults[i].value.trim() !== ""
                ) {

                    hasResult = true;
                    break;

                }

            }

        }

        /* -----------------------------------------------
           REQUIRE AT LEAST ONE RESULT
        ------------------------------------------------ */

        if (!hasResult) {

            event.preventDefault();

            alert(
                "Please enter at least one laboratory result before saving."
            );

            return false;
        }

        /* -----------------------------------------------
           ECG FINDING VALIDATION
        ------------------------------------------------ */

        var ecgResult =
            document.getElementById("ecg_result");

        var ecgFinding =
            document.getElementById("ecg_finding");

        if (
            ecgResult &&
            ecgResult.value === "WITH FINDING"
        ) {

            if (
                !ecgFinding ||
                ecgFinding.value.trim() === ""
            ) {

                event.preventDefault();

                alert(
                    "Please enter the ECG Finding."
                );

                showStep(4);

                if (ecgFinding) {
                    ecgFinding.focus();
                }

                return false;
            }
        }

        /* -----------------------------------------------
           X-RAY VALIDATION
        ------------------------------------------------ */

        if (xrayFindings && xrayImpression) {

            var findings =
                xrayFindings.value.trim();

            var impression =
                xrayImpression.value.trim();

            if (
                findings !== "" &&
                impression === ""
            ) {

                event.preventDefault();

                alert(
                    "Please enter the X-ray Impression."
                );

                showStep(4);
                xrayImpression.focus();

                return false;
            }

            if (
                findings === "" &&
                impression !== ""
            ) {

                event.preventDefault();

                alert(
                    "Please enter the X-ray Findings."
                );

                showStep(4);
                xrayFindings.focus();

                return false;
            }
        }

        /* -----------------------------------------------
           OTHER TEST NAME VALIDATION
        ------------------------------------------------ */

        for (i = 0; i < otherResults.length; i++) {

            var otherResult =
                otherResults[i].value.trim();

            var otherName = "";

            if (otherNames[i]) {
                otherName =
                    otherNames[i].value.trim();
            }

            if (
                otherResult !== "" &&
                otherName === ""
            ) {

                event.preventDefault();

                alert(
                    "Please enter the laboratory test name for every OTHER result."
                );

                showStep(6);

                if (otherNames[i]) {
                    otherNames[i].focus();
                }

                return false;
            }
        }

        return true;

    });

/* =========================================================
   ARRAY HELPER
========================================================= */

function array() {

    var arr = [];
    var i;

    for (i = 0; i < arguments.length; i++) {
        arr.push(arguments[i]);
    }

    return arr;
}

/* =========================================================
   INITIALIZATION
========================================================= */

document.addEventListener("DOMContentLoaded", function() {

    var categories = array(
        "CHEMISTRY",
        "LIPID",
        "HEMATOLOGY",
        "URINALYSIS",
        "X_RAY",
        "ECG",
        "STOOL",
        "OTHERS"
    );

    var i;

    for (i = 0; i < categories.length; i++) {
        toggleFacility(categories[i]);
    }

    var ecgResult =
        document.getElementById("ecg_result");

    if (ecgResult) {

        ecgResult.addEventListener(
            "change",
            function() {
                toggleECGFinding();
            }
        );

    }

    toggleECGFinding();
    showStep(0);

});

</script>

<?php include "../includes/footer.php"; ?>
