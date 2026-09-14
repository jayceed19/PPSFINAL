<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";

/* =========================================================
   ADMINISTRATOR ONLY
========================================================= */

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "Administrator"
) {
    http_response_code(403);
    die("Access denied.");
}

/* =========================================================
   REQUEST METHOD
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

/* =========================================================
   GET FORM DATA
========================================================= */

$id = isset($_POST["id"])
    ? (int) $_POST["id"]
    : 0;

$medicineName = isset($_POST["medicine_name"])
    ? trim($_POST["medicine_name"])
    : "";

$strength = isset($_POST["strength"])
    ? trim($_POST["strength"])
    : "";

$form = isset($_POST["form"])
    ? trim($_POST["form"])
    : "";

/* =========================================================
   VALIDATE ID
========================================================= */

if ($id <= 0) {
    die("Invalid medicine ID.");
}

/* =========================================================
   CLEAN DATA
========================================================= */

$medicineName = preg_replace(
    '/\s+/',
    ' ',
    $medicineName
);

$strength = preg_replace(
    '/\s+/',
    ' ',
    $strength
);

$form = preg_replace(
    '/\s+/',
    ' ',
    $form
);

/* =========================================================
   UPPERCASE
========================================================= */

$medicineName = strtoupper($medicineName);
$strength = strtoupper($strength);
$form = strtoupper($form);

/* =========================================================
   VALIDATION
========================================================= */

if ($medicineName === "") {
    die("Medicine name is required.");
}

if (strlen($medicineName) > 150) {
    die("Medicine name is too long.");
}

if (strlen($strength) > 100) {
    die("Strength is too long.");
}

if (strlen($form) > 50) {
    die("Medicine form is too long.");
}

/* =========================================================
   GET CURRENT MEDICINE
========================================================= */

$getStmt = $conn->prepare("
    SELECT
        id,
        medicine_name,
        strength,
        form,
        is_active
    FROM medicines
    WHERE id = ?
    LIMIT 1
");

if (!$getStmt) {
    die("Database error: " . $conn->error);
}

$getStmt->bind_param(
    "i",
    $id
);

$getStmt->execute();

$getResult = $getStmt->get_result();

$currentMedicine = $getResult->fetch_assoc();

$getStmt->close();

/* =========================================================
   CHECK IF MEDICINE EXISTS
========================================================= */

if (!$currentMedicine) {
    die("Medicine not found.");
}

/* =========================================================
   CHECK DUPLICATE
   Exclude the current medicine ID.
========================================================= */

$duplicateStmt = $conn->prepare("
    SELECT
        id,
        medicine_name,
        strength,
        form
    FROM medicines
    WHERE
        id <> ?
        AND medicine_name = ?
        AND (
            strength = ?
            OR (
                strength IS NULL
                AND ? = ''
            )
        )
        AND (
            form = ?
            OR (
                form IS NULL
                AND ? = ''
            )
        )
    LIMIT 1
");

if (!$duplicateStmt) {
    die("Database error: " . $conn->error);
}

$duplicateStmt->bind_param(
    "isssss",
    $id,
    $medicineName,
    $strength,
    $strength,
    $form,
    $form
);

$duplicateStmt->execute();

$duplicateResult = $duplicateStmt->get_result();

$duplicateMedicine = $duplicateResult->fetch_assoc();

$duplicateStmt->close();

/* =========================================================
   DUPLICATE FOUND
========================================================= */

if ($duplicateMedicine) {
    die(
        "Another medicine with the same name, strength, and form already exists."
    );
}

/* =========================================================
   UPDATE MEDICINE
========================================================= */

$updateStmt = $conn->prepare("
    UPDATE medicines
    SET
        medicine_name = ?,
        strength = ?,
        form = ?
    WHERE id = ?
    LIMIT 1
");

if (!$updateStmt) {
    die("Database error: " . $conn->error);
}

$updateStmt->bind_param(
    "sssi",
    $medicineName,
    $strength,
    $form,
    $id
);

/* =========================================================
   EXECUTE UPDATE
========================================================= */

if (!$updateStmt->execute()) {

    $errorMessage = $updateStmt->error;

    $updateStmt->close();

    die(
        "Failed to update medicine: " .
        $errorMessage
    );
}

$updateStmt->close();

/* =========================================================
   ACTIVITY LOG
========================================================= */

$activityDescription =
    "Edited medicine ID " .
    $id .
    ": " .
    $medicineName;

if ($strength !== "") {
    $activityDescription .=
        " - " . $strength;
}

if ($form !== "") {
    $activityDescription .=
        " - " . $form;
}

logActivity(
    $conn,
    "EDIT_MEDICINE",
    $activityDescription
);

/* =========================================================
   REDIRECT
========================================================= */

header("Location: index.php");
exit;

?>