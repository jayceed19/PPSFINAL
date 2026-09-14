<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


/* =========================================================
   POST ONLY
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../dashboard.php");
    exit;

}


/* =========================================================
   GET FORM DATA
========================================================= */

$patientId = isset($_POST["patient_id"])
    ? (int)$_POST["patient_id"]
    : 0;

$diagnosis = isset($_POST["diagnosis"])
    ? trim($_POST["diagnosis"])
    : "";


/* =========================================================
   VALIDATE PATIENT ID
========================================================= */

if ($patientId <= 0) {

    die("Invalid patient ID.");

}


/* =========================================================
   VALIDATE DIAGNOSIS
========================================================= */

if ($diagnosis === "") {

    die("Diagnosis is required.");

}


/* =========================================================
   CLEAN DIAGNOSIS
========================================================= */

$diagnosis = preg_replace(
    '/\s+/',
    ' ',
    $diagnosis
);

$diagnosis = strtoupper(
    $diagnosis
);


/* =========================================================
   DEFAULT VALUES
   These fields remain in the database for compatibility,
   but they are no longer entered by the user.
========================================================= */

$purpose = "MEDICAL CERTIFICATE";

$findings = "";

$recommendations = "";

$remarks = "";


/* =========================================================
   GET PATIENT INFORMATION
========================================================= */

$patientStmt = $conn->prepare("
    SELECT
        id,
        patient_id,
        first_name,
        middle_name,
        last_name
    FROM patients
    WHERE id = ?
    LIMIT 1
");


if (!$patientStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$patientStmt->bind_param(
    "i",
    $patientId
);


if (!$patientStmt->execute()) {

    $patientStmt->close();

    die("Unable to verify patient.");

}


$patientResult = $patientStmt->get_result();


if ($patientResult->num_rows === 0) {

    $patientStmt->close();

    die("Patient not found.");

}


$patientData = $patientResult->fetch_assoc();

$patientStmt->close();


/* =========================================================
   PATIENT INFORMATION
========================================================= */

$patientDisplayId = $patientData["patient_id"];


$patientName = trim(

    $patientData["first_name"] . " " .

    $patientData["middle_name"] . " " .

    $patientData["last_name"]

);


$patientName = preg_replace(
    '/\s+/',
    ' ',
    $patientName
);


$patientName = strtoupper(
    $patientName
);


/* =========================================================
   CERTIFICATE DATE
========================================================= */

$certificateDate = date("Y-m-d");


/* =========================================================
   SAVE MEDICAL CERTIFICATE
========================================================= */

$insertStmt = $conn->prepare("
    INSERT INTO medical_certificates
    (
        patient_id,
        consultation_id,
        certificate_date,
        purpose,
        diagnosis,
        findings,
        recommendations,
        remarks
    )
    VALUES
    (
        ?,
        NULL,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    )
");


if (!$insertStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


/* =========================================================
   BIND DATA
========================================================= */

$insertStmt->bind_param(
    "issssss",
    $patientId,
    $certificateDate,
    $purpose,
    $diagnosis,
    $findings,
    $recommendations,
    $remarks
);


/* =========================================================
   EXECUTE
========================================================= */

if (!$insertStmt->execute()) {

    $error = $insertStmt->error;

    $insertStmt->close();

    $conn->close();

    die(
        "Unable to save medical certificate: " .
        $error
    );

}


$certificateId = (int)$conn->insert_id;

$insertStmt->close();


/* =========================================================
   ACTIVITY LOG
========================================================= */

$activityDescription =
    "Added medical certificate: " .
    $patientDisplayId .
    " - " .
    $patientName .
    " - Diagnosis: " .
    $diagnosis .
    " - " .
    $certificateDate;


logActivity(
    $conn,
    "MEDICAL_CERTIFICATE",
    $activityDescription
);


/* =========================================================
   CLOSE DATABASE
========================================================= */

$conn->close();


/* =========================================================
   REDIRECT TO PRINT
========================================================= */

header(
    "Location: print.php?id=" .
    $certificateId
);

exit;

?>