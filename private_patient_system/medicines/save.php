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
   CHECK DUPLICATE
========================================================= */

$checkStmt = $conn->prepare("
    SELECT
        id,
        is_active
    FROM medicines
    WHERE
        medicine_name = ?
        AND
        (
            (strength = ?)
            OR
            (strength IS NULL AND ? = '')
        )
        AND
        (
            (form = ?)
            OR
            (form IS NULL AND ? = '')
        )
    LIMIT 1
");

if (!$checkStmt) {
    die("Database error: " . $conn->error);
}

$checkStmt->bind_param(
    "sssss",
    $medicineName,
    $strength,
    $strength,
    $form,
    $form
);

$checkStmt->execute();

$checkResult = $checkStmt->get_result();

$existingMedicine = $checkResult->fetch_assoc();

$checkStmt->close();

/* =========================================================
   DUPLICATE HANDLING
========================================================= */

if ($existingMedicine) {

    if ((int) $existingMedicine["is_active"] === 1) {

        die(
            "This medicine already exists in the active medicine list."
        );

    }

    /*
     * Existing record is inactive.
     *
     * Instead of creating a duplicate record,
     * reactivate the existing medicine.
     */

    $reactivateStmt = $conn->prepare("
        UPDATE medicines
        SET
            is_active = 1,
            medicine_name = ?,
            strength = ?,
            form = ?
        WHERE id = ?
        LIMIT 1
    ");

    if (!$reactivateStmt) {
        die("Database error: " . $conn->error);
    }

    $existingId = (int) $existingMedicine["id"];

    $reactivateStmt->bind_param(
        "sssi",
        $medicineName,
        $strength,
        $form,
        $existingId
    );

    if (!$reactivateStmt->execute()) {
        $reactivateStmt->close();

        die(
            "Failed to reactivate medicine: " .
            $reactivateStmt->error
        );
    }

    $reactivateStmt->close();

    /*
     * Log activity.
     */

    logActivity(
        $conn,
        "ACTIVATE_MEDICINE",
        "Activated medicine: " .
        $medicineName .
        (
            $strength !== ""
                ? " - " . $strength
                : ""
        ) .
        (
            $form !== ""
                ? " - " . $form
                : ""
        )
    );

    header("Location: index.php");
    exit;
}

/* =========================================================
   INSERT NEW MEDICINE
========================================================= */

$insertStmt = $conn->prepare("
    INSERT INTO medicines
    (
        medicine_name,
        strength,
        form,
        is_active
    )
    VALUES
    (
        ?,
        ?,
        ?,
        1
    )
");

if (!$insertStmt) {
    die("Database error: " . $conn->error);
}

$insertStmt->bind_param(
    "sss",
    $medicineName,
    $strength,
    $form
);

if (!$insertStmt->execute()) {

    $errorMessage = $insertStmt->error;

    $insertStmt->close();

    die(
        "Failed to save medicine: " .
        $errorMessage
    );
}

$insertedId = (int) $insertStmt->insert_id;

$insertStmt->close();

/* =========================================================
   ACTIVITY LOG
========================================================= */

$activityDescription =
    "Added medicine: " .
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
    "ADD_MEDICINE",
    $activityDescription
);

/* =========================================================
   REDIRECT
========================================================= */

header("Location: index.php");
exit;

?>
