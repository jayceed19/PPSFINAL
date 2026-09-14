<?php

require_once "../config/auth.php";
require_once "../config/database.php";


/* =========================================================
   GET CONSULTATION ID
========================================================= */

$id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($id <= 0) {
    die("Invalid consultation ID.");
}


/* =========================================================
   GET CONSULTATION + PATIENT
========================================================= */

$sql = "
    SELECT
        c.*,

        p.patient_id AS display_patient_id,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.birthdate,
        p.sex,
        p.civil_status,
        p.philhealth_no,
        p.address,
        p.contact_no

    FROM consultations c

    INNER JOIN patients p
        ON c.patient_id = p.id

    WHERE c.id = ?

    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();

    die("Consultation not found.");
}

$data = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   FULL NAME
========================================================= */

$fullName = trim(
    $data['first_name'] . " " .
    $data['middle_name'] . " " .
    $data['last_name']
);


/* =========================================================
   AGE AT VISIT
========================================================= */

$age = "-";

if (
    !empty($data['birthdate']) &&
    !empty($data['visit_date'])
) {

    try {

        $birthDate = new DateTime(
            $data['birthdate']
        );

        $visitDate = new DateTime(
            $data['visit_date']
        );

        $age = $visitDate->diff(
            $birthDate
        )->y;

    } catch (Exception $e) {

        $age = "-";
    }
}


/* =========================================================
   CONSULTATION NUMBER
   PATIENT-SPECIFIC SEQUENCE
========================================================= */

$consultationNumber = 1;
$totalConsultations = 1;

$sequenceSql = "
    SELECT
        COUNT(*) AS total_consultations,

        SUM(
            CASE
                WHEN visit_date < ?
                THEN 1

                WHEN visit_date = ?
                     AND id <= ?
                THEN 1

                ELSE 0
            END
        ) AS consultation_number

    FROM consultations

    WHERE patient_id = ?
";

$sequenceStmt = $conn->prepare(
    $sequenceSql
);

if ($sequenceStmt) {

    $sequenceStmt->bind_param(
        "ssii",
        $data['visit_date'],
        $data['visit_date'],
        $id,
        $data['patient_id']
    );

    if ($sequenceStmt->execute()) {

        $sequenceResult =
            $sequenceStmt->get_result();

        if ($sequenceResult->num_rows > 0) {

            $sequenceData =
                $sequenceResult->fetch_assoc();


            if (
                isset(
                    $sequenceData['consultation_number']
                ) &&
                $sequenceData['consultation_number'] !== null
            ) {

                $consultationNumber =
                    (int)$sequenceData[
                        'consultation_number'
                    ];
            }


            if (
                isset(
                    $sequenceData['total_consultations']
                ) &&
                $sequenceData['total_consultations'] !== null
            ) {

                $totalConsultations =
                    (int)$sequenceData[
                        'total_consultations'
                    ];
            }
        }
    }

    $sequenceStmt->close();
}


/* =========================================================
   FORMAT VISIT DATE
========================================================= */

$visitDateFormatted = "-";

if (!empty($data['visit_date'])) {

    $visitTimestamp = strtotime(
        $data['visit_date']
    );

    if ($visitTimestamp !== false) {

        $visitDateFormatted = date(
            "F d, Y",
            $visitTimestamp
        );
    }
}


/* =========================================================
   FORMAT BIRTHDATE
========================================================= */

$birthdateFormatted = "-";

if (!empty($data['birthdate'])) {

    $birthTimestamp = strtotime(
        $data['birthdate']
    );

    if ($birthTimestamp !== false) {

        $birthdateFormatted = date(
            "F d, Y",
            $birthTimestamp
        );
    }
}


/* =========================================================
   FORMAT FOLLOW-UP DATE
========================================================= */

$followUpFormatted = "-";

if (!empty($data['follow_up_date'])) {

    $followUpTimestamp = strtotime(
        $data['follow_up_date']
    );

    if ($followUpTimestamp !== false) {

        $followUpFormatted = date(
            "F d, Y",
            $followUpTimestamp
        );
    }
}


/* =========================================================
   BP STATUS
========================================================= */

$bpStatus = "-";

if (
    isset($data['bp_status']) &&
    $data['bp_status'] !== null &&
    trim($data['bp_status']) !== ""
) {

    $bpStatus = $data['bp_status'];
}


/* =========================================================
   BP STATUS CSS CLASS
========================================================= */

$bpStatusClass = "";

if ($bpStatus === "Normal") {

    $bpStatusClass = "bp-status-normal";

} elseif ($bpStatus === "Elevated") {

    $bpStatusClass = "bp-status-elevated";

} elseif ($bpStatus === "High BP") {

    $bpStatusClass = "bp-status-high";

} elseif ($bpStatus === "Very High BP / Urgent Alert") {

    $bpStatusClass = "bp-status-urgent";

} elseif ($bpStatus === "Low BP") {

    $bpStatusClass = "bp-status-low";
}


?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<link
    rel="icon"
    type="image/png"
    href="../asset/images/DCMDLOGO.png?v=1"
>

<title>
Consultation Record -
<?php echo htmlspecialchars($fullName); ?>
</title>


<style>

/* =========================================================
   PAGE SETUP
========================================================= */

@page {
    size: A4 portrait;
    margin: 7mm;
}

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
}

body {

    background: #f4f6f9;

    color: #222;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 10px;
}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.print-container {

    width: 100%;

    max-width: 196mm;

    min-height: 283mm;

    margin: 0 auto;

    background: #fff;

    padding: 5mm 6mm;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,0.08);

    display: flex;

    flex-direction: column;
}


/* =========================================================
   HEADER
========================================================= */

.header {

    border-bottom:
        2px solid #1f4e78;

    padding-bottom: 6px;

    margin-bottom: 7px;
}

.header-top {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    gap: 12px;
}

.header-left {

    display: flex;

    align-items: center;

    gap: 9px;

    min-width: 0;
}

.clinic-logo {

    width: 46px;

    height: 46px;

    object-fit: contain;

    flex-shrink: 0;
}

.clinic-information {
    min-width: 0;
}

.clinic-name {

    font-size: 16px;

    font-weight: bold;

    color: #1f4e78;

    letter-spacing: 0.2px;

    line-height: 1.1;
}

.clinic-subtitle {

    margin-top: 2px;

    font-size: 8px;

    color: #555;

    font-weight: 600;

    line-height: 1.2;
}

.document-title {

    margin-top: 3px;

    font-size: 9px;

    font-weight: bold;

    color: #555;

    letter-spacing: 0.8px;
}

.document-label {

    text-align: right;

    font-size: 8px;

    color: #777;

    flex-shrink: 0;
}

.document-label strong {

    display: block;

    color: #222;

    font-size: 10px;

    margin-top: 2px;
}


/* =========================================================
   PATIENT SUMMARY
========================================================= */

.patient-summary {

    border:
        1px solid #cfd6dc;

    border-radius: 3px;

    padding: 7px 9px;

    margin-bottom: 7px;

    background: #fafbfc;
}

.patient-main {

    display: grid;

    grid-template-columns:
        2.5fr
        1fr
        0.8fr;

    gap: 12px;

    align-items: center;
}

.patient-name {

    font-size: 17px;

    font-weight: bold;

    color: #1f4e78;
}

.patient-id {

    margin-top: 2px;

    font-size: 8.5px;

    color: #666;
}

.summary-item {

    font-size: 7.5px;

    color: #777;
}

.summary-item strong {

    display: block;

    color: #222;

    font-size: 9.5px;

    margin-top: 2px;
}


/* =========================================================
   SECTIONS
========================================================= */

.section {

    margin-bottom: 6px;

    page-break-inside: avoid;
}

.section-title {

    background: #1f4e78;

    color: white;

    font-size: 9px;

    font-weight: bold;

    letter-spacing: 0.4px;

    padding: 4px 6px;

    margin-bottom: 4px;
}


/* =========================================================
   INFORMATION GRID
========================================================= */

.info-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 2px 8px;
}

.info-item {

    min-width: 0;

    padding: 3px 4px;

    border-bottom:
        1px solid #dfe4e8;
}

.info-item.full {

    grid-column: 1 / -1;
}


/* =========================================================
   LABELS
========================================================= */

.label {

    font-size: 7px;

    font-weight: bold;

    color: #666;

    text-transform: uppercase;

    margin-bottom: 1px;
}


/* =========================================================
   VALUES
========================================================= */

.value {

    font-size: 10px;

    font-weight: normal;

    line-height: 1.25;

    white-space: pre-line;

    overflow-wrap: break-word;
}


/* =========================================================
   CLINICAL INFORMATION
========================================================= */

.clinical-item {

    border:
        1px solid #d1d8de;

    border-radius: 3px;

    padding: 5px 6px;

    margin-bottom: 3px;

    min-height: 28px;
}

.clinical-item:last-child {

    margin-bottom: 0;
}

.clinical-item .label {

    margin-bottom: 2px;
}

.clinical-item .value {

    font-size: 10px;

    line-height: 1.25;
}


/* =========================================================
   VITAL SIGNS
========================================================= */

.vitals {

    display: grid;

    grid-template-columns:
        repeat(5, 1fr);

    border:
        1px solid #cfd6dc;
}

.vital {

    text-align: center;

    padding: 5px 3px;

    border-right:
        1px solid #d8dde1;

    border-bottom:
        1px solid #d8dde1;

    min-height: 39px;
}

.vital:nth-child(5),
.vital:nth-child(10) {

    border-right: none;
}

.vital:nth-child(6),
.vital:nth-child(7),
.vital:nth-child(8),
.vital:nth-child(9),
.vital:nth-child(10) {

    border-bottom: none;
}

.vital-label {

    font-size: 6.5px;

    color: #666;

    font-weight: bold;

    margin-bottom: 2px;
}

.vital-value {

    font-size: 10px;

    font-weight: bold;

    color: #222;

    line-height: 1.15;
}


/* =========================================================
   BP STATUS
========================================================= */

.bp-status-normal {
    color: #198754;
}

.bp-status-elevated {
    color: #b8860b;
}

.bp-status-high {
    color: #dc3545;
}

.bp-status-urgent {
    color: #8b0000;
}

.bp-status-low {
    color: #0d6efd;
}


/* =========================================================
   BUTTONS
   ALWAYS AT THE BOTTOM OF THE SCREEN PAGE
========================================================= */

.buttons {

    text-align: center;

    margin-top: auto;

    padding-top: 12px;

    flex-shrink: 0;
}

.btn {

    display: inline-block;

    padding: 7px 14px;

    margin: 3px;

    border-radius: 4px;

    text-decoration: none;

    font-size: 10px;

    font-weight: bold;

    border: none;

    cursor: pointer;
}

.btn-print {

    background: #1f4e78;

    color: white;
}

.btn-back {

    background: #ddd;

    color: #333;
}


/* =========================================================
   PRINT MODE
========================================================= */

@media print {

    @page {

        size: A4 portrait;

        margin: 7mm;
    }

    html,
    body {

        width: 210mm;

        min-height: 297mm;

        background: white;
    }

    body {

        margin: 0;

        padding: 0;

        font-size: 10px;
    }

    .print-container {

        width: 196mm;

        max-width: 196mm;

        min-height: 283mm;

        margin: 0 auto;

        padding: 4mm 5mm;

        box-shadow: none;

        background: white;

        display: flex;

        flex-direction: column;
    }

    .clinic-logo {

        width: 44px;

        height: 44px;
    }

    .clinic-name {

        font-size: 16px;
    }

    .clinic-subtitle {

        font-size: 8px;
    }

    .document-title {

        font-size: 9px;
    }

    .patient-name {

        font-size: 17px;
    }

    .section-title {

        font-size: 9px;

        padding: 4px 6px;
    }

    .label {

        font-size: 7px;
    }

    .value {

        font-size: 10px;
    }

    .clinical-item .value {

        font-size: 10px;
    }

    .vital {

        min-height: 39px;

        padding: 5px 3px;
    }

    .vital-label {

        font-size: 6.5px;
    }

    .vital-value {

        font-size: 10px;
    }

    .buttons {

        display: none !important;
    }

    .section,
    .patient-summary,
    .vitals,
    .clinical-item,
    .info-item {

        page-break-inside: avoid;

        break-inside: avoid;
    }
}


/* =========================================================
   SCREEN VIEW
========================================================= */

@media screen {

    .print-container {

        margin-top: 15px;

        margin-bottom: 15px;
    }
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    body {

        padding: 8px;
    }

    .print-container {

        padding: 15px;
    }

    .header-top {

        align-items: flex-start;
    }

    .header-left {

        align-items: flex-start;
    }

    .clinic-logo {

        width: 42px;

        height: 42px;
    }

    .clinic-name {

        font-size: 14px;
    }

    .clinic-subtitle {

        font-size: 7px;
    }

    .patient-main {

        grid-template-columns: 1fr;

        gap: 6px;
    }

    .info-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .info-item.full {

        grid-column: 1 / -1;
    }

    .vitals {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .vital:nth-child(5),
    .vital:nth-child(10) {

        border-right:
            1px solid #d8dde1;
    }

    .vital:nth-child(2),
    .vital:nth-child(4),
    .vital:nth-child(6),
    .vital:nth-child(8),
    .vital:nth-child(10) {

        border-right: none;
    }

    .vital:nth-child(6),
    .vital:nth-child(7),
    .vital:nth-child(8),
    .vital:nth-child(9),
    .vital:nth-child(10) {

        border-bottom:
            1px solid #d8dde1;
    }
}


/* =========================================================
   PAGE BREAK PROTECTION
========================================================= */

.section-title,
.clinical-item,
.vital,
.patient-summary,
.info-item {

    break-inside: avoid;
}

</style>

</head>


<body>


<div class="print-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="header">

        <div class="header-top">

            <div class="header-left">

                <img
                    src="../asset/images/DCMD.png?v=1"
                    alt="DCMD Logo"
                    class="clinic-logo"
                >

                <div class="clinic-information">

                    <div class="clinic-name">
                        PRIVATE PATIENT SYSTEM
                    </div>

                    <div class="clinic-subtitle">
                        DCMD DIABETES AND HYPERTENSION CLINIC
                    </div>

                    <div class="document-title">
                        CONSULTATION RECORD
                    </div>

                </div>

            </div>


            <div class="document-label">

                CONSULTATION NO.

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $consultationNumber
                    );

                    ?>

                </strong>

            </div>

        </div>

    </div>


    <!-- =====================================================
         PATIENT SUMMARY
    ====================================================== -->

    <div class="patient-summary">

        <div class="patient-main">


            <div>

                <div class="patient-name">

                    <?php

                    echo htmlspecialchars(
                        $fullName
                    );

                    ?>

                </div>

                <div class="patient-id">

                    Patient ID:

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $data['display_patient_id']
                        );

                        ?>

                    </strong>

                </div>

            </div>


            <div class="summary-item">

                VISIT DATE

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $visitDateFormatted
                    );

                    ?>

                </strong>

            </div>


            <div class="summary-item">

                AGE AT VISIT

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $age
                    );

                    ?>

                    <?php if ($age !== "-") { ?>

                        years old

                    <?php } ?>

                </strong>

            </div>


        </div>

    </div>


    <!-- =====================================================
         PATIENT INFORMATION
    ====================================================== -->

    <div class="section">

        <div class="section-title">

            PATIENT INFORMATION

        </div>


        <div class="info-grid">


            <!-- FULL NAME -->

            <div class="info-item">

                <div class="label">
                    FULL NAME
                </div>

                <div class="value">

                    <?php

                    echo htmlspecialchars(
                        $fullName
                    );

                    ?>

                </div>

            </div>


            <!-- SEX -->

            <div class="info-item">

                <div class="label">
                    SEX
                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['sex']
                    )
                        ? htmlspecialchars(
                            $data['sex']
                        )
                        : "-";

                    ?>

                </div>

            </div>


            <!-- BIRTHDATE -->

            <div class="info-item">

                <div class="label">
                    BIRTHDATE
                </div>

                <div class="value">

                    <?php

                    echo htmlspecialchars(
                        $birthdateFormatted
                    );

                    ?>

                </div>

            </div>


            <!-- CIVIL STATUS -->

            <div class="info-item">

                <div class="label">
                    CIVIL STATUS
                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['civil_status']
                    )
                        ? htmlspecialchars(
                            $data['civil_status']
                        )
                        : "-";

                    ?>

                </div>

            </div>


            <!-- CONTACT -->

            <div class="info-item">

                <div class="label">
                    CONTACT NUMBER
                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['contact_no']
                    )
                        ? htmlspecialchars(
                            $data['contact_no']
                        )
                        : "-";

                    ?>

                </div>

            </div>


            <!-- PHILHEALTH -->

            <div class="info-item">

                <div class="label">
                    PHILHEALTH NO.
                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['philhealth_no']
                    )
                        ? htmlspecialchars(
                            $data['philhealth_no']
                        )
                        : "-";

                    ?>

                </div>

            </div>


            <!-- ADDRESS -->

            <div class="info-item full">

                <div class="label">
                    ADDRESS
                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['address']
                    )
                        ? htmlspecialchars(
                            $data['address']
                        )
                        : "-";

                    ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         CLINICAL INFORMATION
    ====================================================== -->

    <div class="section">

        <div class="section-title">

            CLINICAL INFORMATION

        </div>


        <!-- CHIEF COMPLAINT -->

        <div class="clinical-item">

            <div class="label">

                CHIEF COMPLAINT

            </div>

            <div class="value">

                <?php

                echo !empty(
                    $data['chief_complaint']
                )
                    ? htmlspecialchars(
                        $data['chief_complaint']
                    )
                    : "-";

                ?>

            </div>

        </div>


        <!-- HISTORY -->

        <div class="clinical-item">

            <div class="label">

                HISTORY OF PRESENT ILLNESS

            </div>

            <div class="value">

                <?php

                echo !empty(
                    $data['history_illness']
                )
                    ? htmlspecialchars(
                        $data['history_illness']
                    )
                    : "-";

                ?>

            </div>

        </div>


    </div>


    <!-- =====================================================
         VITAL SIGNS
    ====================================================== -->

    <div class="section">

        <div class="section-title">

            VITAL SIGNS

        </div>


        <div class="vitals">


            <!-- BLOOD PRESSURE -->

            <div class="vital">

                <div class="vital-label">

                    BLOOD PRESSURE

                </div>

                <div class="vital-value">

                    <?php

                    echo !empty(
                        $data['blood_pressure']
                    )
                        ? htmlspecialchars(
                            $data['blood_pressure']
                        )
                        : "-";

                    ?>

                </div>

            </div>


            <!-- BP STATUS -->

            <div class="vital">

                <div class="vital-label">

                    BP STATUS

                </div>

                <div
                    class="vital-value <?php echo htmlspecialchars($bpStatusClass); ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $bpStatus
                    );

                    ?>

                </div>

            </div>


            <!-- TEMPERATURE -->

            <div class="vital">

                <div class="vital-label">

                    TEMPERATURE

                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['temperature'] !== null &&
                        $data['temperature'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['temperature']
                        ) . " °C"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- PULSE -->

            <div class="vital">

                <div class="vital-label">

                    PULSE RATE

                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['pulse_rate'] !== null &&
                        $data['pulse_rate'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['pulse_rate']
                        ) . " bpm"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- RESPIRATORY -->

            <div class="vital">

                <div class="vital-label">

                    RESPIRATORY RATE

                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['respiratory_rate'] !== null &&
                        $data['respiratory_rate'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['respiratory_rate']
                        ) . " /min"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- WEIGHT -->

            <div class="vital">

                <div class="vital-label">

                    WEIGHT

                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['weight'] !== null &&
                        $data['weight'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['weight']
                        ) . " kg"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- HEIGHT -->

            <div class="vital">

                <div class="vital-label">

                    HEIGHT

                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['height'] !== null &&
                        $data['height'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['height']
                        ) . " cm"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- BMI -->

            <div class="vital">

                <div class="vital-label">

                    BMI

                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        isset($data['bmi']) &&
                        $data['bmi'] !== null &&
                        $data['bmi'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['bmi']
                        )
                        : "-";

                    ?>

                </div>

            </div>


            <!-- BMI STATUS -->

            <div class="vital">

                <div class="vital-label">

                    BMI STATUS

                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        isset($data['bmi_status']) &&
                        $data['bmi_status'] !== null &&
                        $data['bmi_status'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['bmi_status']
                        )
                        : "-";

                    ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         ASSESSMENT & TREATMENT
    ====================================================== -->

    <div class="section">

        <div class="section-title">

            ASSESSMENT &amp; TREATMENT

        </div>


        <!-- ASSESSMENT -->

        <div class="clinical-item">

            <div class="label">

                ASSESSMENT / DIAGNOSIS

            </div>

            <div class="value">

                <?php

                echo !empty(
                    $data['assessment']
                )
                    ? htmlspecialchars(
                        $data['assessment']
                    )
                    : "-";

                ?>

            </div>

        </div>


        <!-- MANAGEMENT -->

        <div class="clinical-item">

            <div class="label">

                MANAGEMENT / PLAN

            </div>

            <div class="value">

                <?php

                echo !empty(
                    $data['management']
                )
                    ? htmlspecialchars(
                        $data['management']
                    )
                    : "-";

                ?>

            </div>

        </div>


    </div>


    <!-- =====================================================
         FOLLOW-UP & REMARKS
    ====================================================== -->

    <div class="section">

        <div class="section-title">

            FOLLOW-UP &amp; REMARKS

        </div>


        <div class="info-grid">


            <!-- FOLLOW-UP DATE -->

            <div class="info-item">

                <div class="label">

                    FOLLOW-UP DATE

                </div>

                <div class="value">

                    <?php

                    echo htmlspecialchars(
                        $followUpFormatted
                    );

                    ?>

                </div>

            </div>


            <!-- REMARKS -->

            <div class="info-item">

                <div class="label">

                    REMARKS

                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['remarks']
                    )
                        ? htmlspecialchars(
                            $data['remarks']
                        )
                        : "-";

                    ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         BUTTONS - PINAKA IBABA
    ====================================================== -->

    <div class="buttons">

        <button
            type="button"
            class="btn btn-print"
            onclick="window.print();"
        >

            🖨 Print Consultation

        </button>


        <a
            href="consultation_view.php?id=<?php echo (int)$id; ?>"
            class="btn btn-back"
        >

            ← Back

        </a>

    </div>


</div>


</body>

</html>

<?php

$conn->close();

?>