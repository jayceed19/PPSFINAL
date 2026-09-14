<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/* =========================================================
   GET LABORATORY ID
========================================================= */

$labId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($labId <= 0) {
    die("Invalid laboratory ID.");
}


/* =========================================================
   GET LABORATORY RESULT + PATIENT
========================================================= */

$sql = "
    SELECT
        l.id,
        l.patient_id,
        l.test_date,
        l.test_name,
        l.result,
        l.unit,
        l.reference_range,
        l.remarks,
        l.facility_type,
        l.health_care_institution,

        p.patient_id AS patient_code,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.birthdate,
        p.sex,
        p.address

    FROM laboratory l

    INNER JOIN patients p
        ON p.id = l.patient_id

    WHERE l.id = ?

    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $labId);
$stmt->execute();

$result = $stmt->get_result();
$lab = $result->fetch_assoc();

$stmt->close();

if (!$lab) {
    die("Laboratory result not found.");
}


/* =========================================================
   PATIENT NAME
========================================================= */

$fullName = trim($lab['first_name']);

if (!empty($lab['middle_name'])) {
    $fullName .= " " . trim($lab['middle_name']);
}

$fullName .= " " . trim($lab['last_name']);


/* =========================================================
   AGE
========================================================= */

$age = "-";

if (!empty($lab['birthdate'])) {

    $birthDate = new DateTime($lab['birthdate']);
    $today = new DateTime();

    $age = $today->diff($birthDate)->y;
}


/* =========================================================
   TEST NAME
========================================================= */

$testName = strtoupper(trim($lab['test_name']));


/* =========================================================
   CHECK IMAGING TYPE
========================================================= */

$isXray = ($testName === "CHEST X-RAY");

$isECG =
    $testName === "ELECTROCARDIOGRAM (ECG)" ||
    $testName === "ECG";

$isImaging = $isXray || $isECG;


/* =========================================================
   EXISTING RESULT
========================================================= */

$existingResult = strtoupper(trim($lab['result']));


/* =========================================================
   EXISTING REMARKS
========================================================= */

$existingRemarks = "";

if (isset($lab['remarks'])) {
    $existingRemarks = trim($lab['remarks']);
}


/* =========================================================
   X-RAY FINDINGS / IMPRESSION
   New save.php stores:

   FINDINGS: ...
   | IMPRESSION: ...
========================================================= */

$xrayFindings = "";
$xrayImpression = "";

if ($isXray && $existingRemarks !== "") {

    $impressionMarker = "| IMPRESSION:";

    $position = strpos(
        strtoupper($existingRemarks),
        $impressionMarker
    );

    if ($position !== false) {

        $findingsPart = substr(
            $existingRemarks,
            0,
            $position
        );

        $impressionPart = substr(
            $existingRemarks,
            $position + strlen($impressionMarker)
        );

        $findingsPart = trim($findingsPart);
        $impressionPart = trim($impressionPart);

        if (stripos($findingsPart, "FINDINGS:") === 0) {

            $findingsPart = trim(
                substr(
                    $findingsPart,
                    strlen("FINDINGS:")
                )
            );
        }

        $xrayFindings = $findingsPart;
        $xrayImpression = $impressionPart;

    } else {

        /*
         * Fallback for older records where remarks
         * were not stored in the new format.
         */
        $xrayFindings = $existingRemarks;
    }
}


/* =========================================================
   ECG FINDING
========================================================= */

$ecgFinding = "";

if ($isECG) {

    $ecgFinding = $existingRemarks;
}


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Edit Laboratory";
$pageSubtitle = "Edit Laboratory Result";

$basePath = "../";
$activePage = "patients";


/* =========================================================
   SHARED HEADER
========================================================= */

include __DIR__ . "/../includes/header.php";


/* =========================================================
   SHARED NAVIGATION
========================================================= */

include __DIR__ . "/../includes/navigation.php";

?>

<style>

/* =========================================================
   PAGE
========================================================= */

.lab-edit-page {
    max-width: 900px;
    margin: 25px auto 50px;
    padding: 0 20px;
}


/* =========================================================
   HEADER
========================================================= */

.lab-edit-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 20px;
}

.lab-edit-title h2 {
    margin: 0;
    color: #1f2937;
    font-size: 28px;
    font-weight: 700;
}

.lab-edit-title p {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 14px;
}

.lab-edit-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}


/* =========================================================
   BUTTON
========================================================= */

.lab-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 40px;
    padding: 0 15px;
    border-radius: 7px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    border: 1px solid transparent;
    cursor: pointer;
}

.back-btn {
    background: #f3f4f6;
    color: #374151;
    border-color: #d1d5db;
}

.back-btn:hover {
    background: #e5e7eb;
}


/* =========================================================
   CARD
========================================================= */

.edit-card {
    background: #ffffff;
    border: 1px solid #dbe3ea;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.edit-card-header {
    background: #0f5f9f;
    color: #ffffff;
    padding: 18px 22px;
}

.edit-card-header-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
}

.edit-card-header-subtitle {
    font-size: 12px;
    opacity: 0.9;
}


/* =========================================================
   PATIENT INFORMATION
========================================================= */

.patient-information {
    padding: 20px 22px;
    border-bottom: 1px solid #e5e7eb;
    background: #ffffff;
}

.patient-grid {
    display: grid;
    grid-template-columns: 1.6fr 0.8fr 0.8fr;
    gap: 20px;
}

.info-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 5px;
}

.info-value {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    word-break: break-word;
}


/* =========================================================
   LAB DATE
========================================================= */

.lab-date-box {
    margin: 20px 22px;
    padding: 14px 16px;
    background: #f8fafc;
    border: 1px solid #dbe3ea;
    border-left: 4px solid #0f5f9f;
    border-radius: 7px;
}

.lab-date-label {
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 3px;
}

.lab-date-value {
    color: #111827;
    font-size: 16px;
    font-weight: 700;
}


/* =========================================================
   FORM
========================================================= */

.edit-form {
    padding: 5px 22px 25px;
}

.form-group {
    margin-bottom: 18px;
}

.form-label {
    display: block;
    margin-bottom: 7px;
    color: #374151;
    font-size: 12px;
    font-weight: 700;
}

.required {
    color: #dc2626;
}


/* =========================================================
   INPUT
========================================================= */

.form-input {
    width: 100%;
    min-height: 42px;
    padding: 9px 12px;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
    color: #111827;
    font-size: 13px;
    outline: none;
    transition: 0.2s ease;
}

.form-input:focus {
    border-color: #0f5f9f;
    box-shadow: 0 0 0 2px rgba(15, 95, 159, 0.10);
}

textarea.form-input {
    min-height: 100px;
    resize: vertical;
}

.readonly-input {
    background: #f8fafc;
    color: #475569;
    cursor: not-allowed;
}

.test-name-input {
    font-weight: 700;
    text-transform: uppercase;
}

.result-input {
    font-weight: 700;
    text-transform: uppercase;
}


/* =========================================================
   SELECT
========================================================= */

.result-select {
    width: 100%;
    min-height: 42px;
    padding: 9px 12px;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
    color: #111827;
    font-size: 13px;
    font-weight: 700;
    outline: none;
    cursor: pointer;
}

.result-select:focus {
    border-color: #0f5f9f;
    box-shadow: 0 0 0 2px rgba(15, 95, 159, 0.10);
}


/* =========================================================
   RADIOLOGY / ECG BOX
========================================================= */

.findings-box {
    margin-bottom: 18px;
    padding: 15px;
    background: #f8fafc;
    border: 1px solid #dbe3ea;
    border-radius: 7px;
}

.findings-box + .findings-box {
    margin-top: 12px;
}

.findings-box .form-label {
    margin-bottom: 7px;
}

.form-help {
    margin-top: 5px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.5;
}


/* =========================================================
   FACILITY INFORMATION
========================================================= */

.facility-box {
    margin-bottom: 20px;
    padding: 13px 15px;
    background: #f8fafc;
    border: 1px solid #dbe3ea;
    border-radius: 7px;
}

.facility-title {
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.facility-value {
    color: #111827;
    font-size: 13px;
    font-weight: 600;
}


/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 25px;
    padding-top: 18px;
    border-top: 1px solid #e5e7eb;
}

.cancel-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 17px;
    background: #ffffff;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
}

.cancel-btn:hover {
    background: #f3f4f6;
}

.save-btn {
    min-height: 40px;
    padding: 0 20px;
    background: #0f5f9f;
    color: #ffffff;
    border: 1px solid #0f5f9f;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.save-btn:hover {
    background: #0b4f85;
    border-color: #0b4f85;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .lab-edit-page {
        padding: 0 12px;
        margin-top: 18px;
    }

    .lab-edit-header {
        flex-direction: column;
    }

    .lab-edit-actions {
        width: 100%;
    }

    .lab-action-btn {
        flex: 1;
    }

    .patient-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

}


@media (max-width: 520px) {

    .lab-edit-actions {
        flex-direction: column;
    }

    .lab-action-btn {
        width: 100%;
    }

    .form-actions {
        flex-direction: column;
    }

    .cancel-btn,
    .save-btn {
        width: 100%;
    }

}

</style>


<main class="main-container">

    <div class="lab-edit-page">


        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->

        <div class="lab-edit-header">

            <div class="lab-edit-title">

                <h2>
                    Edit Laboratory Result
                </h2>

                <p>
                    Update the selected laboratory examination result.
                </p>

            </div>


            <div class="lab-edit-actions">

                <a
                    href="view.php?id=<?php
                        echo (int)$lab['patient_id'];
                    ?>&date=<?php
                        echo urlencode($lab['test_date']);
                    ?>"
                    class="lab-action-btn back-btn"
                >
                    ← Back to Results
                </a>

            </div>

        </div>


        <!-- =====================================================
             MAIN CARD
        ====================================================== -->

        <div class="edit-card">


            <!-- CARD HEADER -->

            <div class="edit-card-header">

                <div class="edit-card-header-title">
                    Laboratory Examination
                </div>

                <div class="edit-card-header-subtitle">
                    Edit laboratory result information
                </div>

            </div>


            <!-- =================================================
                 PATIENT INFORMATION
            ================================================== -->

            <div class="patient-information">

                <div class="patient-grid">


                    <div>

                        <span class="info-label">
                            Patient Name
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                strtoupper($fullName)
                            );
                            ?>

                        </div>

                    </div>


                    <div>

                        <span class="info-label">
                            Patient ID
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                $lab['patient_code']
                            );
                            ?>

                        </div>

                    </div>


                    <div>

                        <span class="info-label">
                            Age / Sex
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars($age);
                            ?>

                            /

                            <?php
                            echo htmlspecialchars(
                                strtoupper($lab['sex'])
                            );
                            ?>

                        </div>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 LAB DATE
            ================================================== -->

            <div class="lab-date-box">

                <div class="lab-date-label">
                    Laboratory Date
                </div>

                <div class="lab-date-value">

                    <?php

                    echo htmlspecialchars(
                        date(
                            "F d, Y",
                            strtotime($lab['test_date'])
                        )
                    );

                    ?>

                </div>

            </div>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                action="update.php"
                method="POST"
                class="edit-form"
                onsubmit="return validateLaboratoryForm();"
            >


                <input
                    type="hidden"
                    name="id"
                    value="<?php
                        echo (int)$lab['id'];
                    ?>"
                >


                <!-- =================================================
                     FACILITY
                ================================================== -->

                <div class="facility-box">

                    <div class="facility-title">
                        Laboratory Facility
                    </div>

                    <div class="facility-value">

                        <?php

                        $facilityType = isset($lab['facility_type'])
                            ? strtoupper(trim($lab['facility_type']))
                            : "WITHIN_FACILITY";

                        $healthCareInstitution =
                            isset($lab['health_care_institution'])
                            ? trim($lab['health_care_institution'])
                            : "";

                        if ($facilityType === "ACCREDITED_FACILITY") {

                            echo "Laboratory";

                            if ($healthCareInstitution !== "") {

                                echo " - " .
                                    htmlspecialchars(
                                        $healthCareInstitution
                                    );
                            }

                        } else {

                            echo "Within Facility";
                        }

                        ?>

                    </div>

                </div>


                <!-- =================================================
                     TEST NAME
                ================================================== -->

                <div class="form-group">

                    <label
                        for="test_name"
                        class="form-label"
                    >

                        Laboratory Test

                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="test_name"
                        name="test_name"
                        class="form-input test-name-input readonly-input"
                        value="<?php
                            echo htmlspecialchars(
                                $lab['test_name']
                            );
                        ?>"
                        readonly
                    >


                    <div class="form-help">
                        Laboratory test name cannot be changed.
                    </div>

                </div>


                <!-- =================================================
                     CHEST X-RAY
                ================================================== -->

                <?php if ($isXray) { ?>


                    <div class="form-group">

                        <label
                            for="result"
                            class="form-label"
                        >

                            Result

                            <span class="required">*</span>

                        </label>


                        <select
                            id="result"
                            name="result"
                            class="result-select"
                            onchange="toggleXrayFields();"
                            required
                        >

                            <option
                                value=""
                                <?php

                                if ($existingResult === "") {
                                    echo "selected";
                                }

                                ?>
                            >
                                Select Result
                            </option>


                            <option
                                value="ESSENTIALLY NORMAL"
                                <?php

                                if (
                                    $existingResult ===
                                    "ESSENTIALLY NORMAL"
                                ) {
                                    echo "selected";
                                }

                                ?>
                            >
                                ESSENTIALLY NORMAL
                            </option>


                            <option
                                value="WITH FINDING"
                                <?php

                                if (
                                    $existingResult ===
                                    "WITH FINDING"
                                ) {
                                    echo "selected";
                                }

                                ?>
                            >
                                WITH FINDING
                            </option>

                        </select>

                    </div>


                    <!-- X-RAY FINDINGS -->

                    <div
                        id="xrayFindingsBox"
                        class="findings-box"
                        <?php

                        if ($existingResult !== "WITH FINDING") {
                            echo 'style="display:none;"';
                        }

                        ?>
                    >

                        <label
                            for="xray_findings"
                            class="form-label"
                        >

                            Findings

                            <span class="required">*</span>

                        </label>


                        <textarea
                            id="xray_findings"
                            name="xray_findings"
                            class="form-input"
                            placeholder="Enter chest X-ray findings..."
                        ><?php

                        echo htmlspecialchars(
                            $xrayFindings
                        );

                        ?></textarea>

                    </div>


                    <!-- X-RAY IMPRESSION -->

                    <div
                        id="xrayImpressionBox"
                        class="findings-box"
                        <?php

                        if ($existingResult !== "WITH FINDING") {
                            echo 'style="display:none;"';
                        }

                        ?>
                    >

                        <label
                            for="xray_impression"
                            class="form-label"
                        >

                            Impression

                            <span class="required">*</span>

                        </label>


                        <textarea
                            id="xray_impression"
                            name="xray_impression"
                            class="form-input"
                            placeholder="Enter X-ray impression..."
                        ><?php

                        echo htmlspecialchars(
                            $xrayImpression
                        );

                        ?></textarea>


                        <div class="form-help">
                            Both Findings and Impression are required when the result is WITH FINDING.
                        </div>

                    </div>


                <?php } elseif ($isECG) { ?>


                    <!-- =================================================
                         ECG
                    ================================================== -->

                    <div class="form-group">

                        <label
                            for="result"
                            class="form-label"
                        >

                            Result

                            <span class="required">*</span>

                        </label>


                        <select
                            id="result"
                            name="result"
                            class="result-select"
                            onchange="toggleECGFinding();"
                            required
                        >

                            <option
                                value=""
                                <?php

                                if ($existingResult === "") {
                                    echo "selected";
                                }

                                ?>
                            >
                                Select Result
                            </option>


                            <option
                                value="ESSENTIALLY NORMAL"
                                <?php

                                if (
                                    $existingResult ===
                                    "ESSENTIALLY NORMAL"
                                ) {
                                    echo "selected";
                                }

                                ?>
                            >
                                ESSENTIALLY NORMAL
                            </option>


                            <option
                                value="WITH FINDING"
                                <?php

                                if (
                                    $existingResult ===
                                    "WITH FINDING"
                                ) {
                                    echo "selected";
                                }

                                ?>
                            >
                                WITH FINDING
                            </option>

                        </select>

                    </div>


                    <!-- ECG FINDING -->

                    <div
                        id="ecgFindingBox"
                        class="findings-box"
                        <?php

                        if ($existingResult !== "WITH FINDING") {
                            echo 'style="display:none;"';
                        }

                        ?>
                    >

                        <label
                            for="ecg_finding"
                            class="form-label"
                        >

                            ECG Finding

                            <span class="required">*</span>

                        </label>


                        <textarea
                            id="ecg_finding"
                            name="ecg_finding"
                            class="form-input"
                            placeholder="Enter ECG finding..."
                        ><?php

                        echo htmlspecialchars(
                            $ecgFinding
                        );

                        ?></textarea>


                        <div class="form-help">
                            Required when the result is WITH FINDING.
                        </div>

                    </div>


                <?php } else { ?>


                    <!-- =================================================
                         GENERAL LABORATORY TEST
                    ================================================== -->

                    <div class="form-group">

                        <label
                            for="result"
                            class="form-label"
                        >

                            Result

                            <span class="required">*</span>

                        </label>


                        <input
                            type="text"
                            id="result"
                            name="result"
                            class="form-input result-input"
                            value="<?php

                                echo htmlspecialchars(
                                    $lab['result']
                                );

                            ?>"
                            required
                            autocomplete="off"
                        >


                        <?php

                        if (
                            isset($lab['unit']) &&
                            trim($lab['unit']) !== ""
                        ) {

                        ?>

                            <div class="form-help">

                                Unit:
                                <?php
                                echo htmlspecialchars(
                                    $lab['unit']
                                );
                                ?>

                            </div>

                        <?php } ?>

                    </div>


                <?php } ?>


                <!-- =================================================
                     REFERENCE RANGE
                ================================================== -->

                <div class="form-group">

                    <label
                        for="reference_range"
                        class="form-label"
                    >

                        Reference Range

                    </label>


                    <input
                        type="text"
                        id="reference_range"
                        class="form-input readonly-input"
                        value="<?php

                            echo htmlspecialchars(
                                $lab['reference_range']
                            );

                        ?>"
                        readonly
                    >


                    <div class="form-help">
                        Reference range is for display only.
                    </div>

                </div>


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="form-actions">


                    <a
                        href="view.php?id=<?php
                            echo (int)$lab['patient_id'];
                        ?>&date=<?php
                            echo urlencode($lab['test_date']);
                        ?>"
                        class="cancel-btn"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="save-btn"
                    >
                        Save Changes
                    </button>


                </div>


            </form>

        </div>

    </div>

</main>


<script>

/* =========================================================
   UPPERCASE GENERAL RESULT
========================================================= */

var resultField =
    document.getElementById("result");

if (resultField) {

    resultField.addEventListener(
        "input",
        function () {

            this.value =
                this.value.toUpperCase();

        }
    );

}


/* =========================================================
   X-RAY TOGGLE
========================================================= */

function toggleXrayFields() {

    var result =
        document.getElementById("result");

    var findingsBox =
        document.getElementById("xrayFindingsBox");

    var impressionBox =
        document.getElementById("xrayImpressionBox");

    var findings =
        document.getElementById("xray_findings");

    var impression =
        document.getElementById("xray_impression");


    if (!result) {
        return;
    }


    if (result.value === "WITH FINDING") {

        if (findingsBox) {
            findingsBox.style.display = "block";
        }

        if (impressionBox) {
            impressionBox.style.display = "block";
        }

    } else {

        if (findingsBox) {
            findingsBox.style.display = "none";
        }

        if (impressionBox) {
            impressionBox.style.display = "none";
        }

        if (findings) {
            findings.value = "";
        }

        if (impression) {
            impression.value = "";
        }

    }

}


/* =========================================================
   ECG TOGGLE
========================================================= */

function toggleECGFinding() {

    var result =
        document.getElementById("result");

    var findingBox =
        document.getElementById("ecgFindingBox");

    var finding =
        document.getElementById("ecg_finding");


    if (!result || !findingBox) {
        return;
    }


    if (result.value === "WITH FINDING") {

        findingBox.style.display = "block";

    } else {

        findingBox.style.display = "none";

        if (finding) {
            finding.value = "";
        }

    }

}


/* =========================================================
   FORM VALIDATION
========================================================= */

function validateLaboratoryForm() {

    var resultField =
        document.getElementById("result");


    if (!resultField) {
        return false;
    }


    var result =
        resultField.value.trim();


    /* RESULT REQUIRED */

    if (result === "") {

        alert(
            "Laboratory result is required."
        );

        resultField.focus();

        return false;
    }


    /* =====================================================
       X-RAY VALIDATION
    ===================================================== */

    <?php if ($isXray) { ?>

    if (result === "WITH FINDING") {

        var findingsField =
            document.getElementById("xray_findings");

        var impressionField =
            document.getElementById("xray_impression");


        if (!findingsField) {
            return false;
        }


        if (!impressionField) {
            return false;
        }


        var findings =
            findingsField.value.trim();

        var impression =
            impressionField.value.trim();


        if (findings === "") {

            alert(
                "Please enter the X-ray findings."
            );

            findingsField.focus();

            return false;
        }


        if (impression === "") {

            alert(
                "Please enter the X-ray impression."
            );

            impressionField.focus();

            return false;
        }

    }

    <?php } ?>


    /* =====================================================
       ECG VALIDATION
    ===================================================== */

    <?php if ($isECG) { ?>

    if (result === "WITH FINDING") {

        var findingField =
            document.getElementById("ecg_finding");


        if (!findingField) {
            return false;
        }


        var finding =
            findingField.value.trim();


        if (finding === "") {

            alert(
                "Please enter the ECG finding."
            );

            findingField.focus();

            return false;
        }

    }

    <?php } ?>


    return true;
}


/* =========================================================
   INITIALIZE
========================================================= */

<?php if ($isXray) { ?>

toggleXrayFields();

<?php } ?>


<?php if ($isECG) { ?>

toggleECGFinding();

<?php } ?>

</script>


<?php

$conn->close();

include __DIR__ . "/../includes/footer.php";

?>