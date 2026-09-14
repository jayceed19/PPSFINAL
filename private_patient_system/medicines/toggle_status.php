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
   GET PARAMETERS
========================================================= */

$id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

$status = isset($_GET["status"])
    ? (int) $_GET["status"]
    : -1;

/* =========================================================
   VALIDATION
========================================================= */

if ($id <= 0) {
    die("Invalid medicine ID.");
}

if ($status !== 0 && $status !== 1) {
    die("Invalid medicine status.");
}

/* =========================================================
   GET MEDICINE
========================================================= */

$stmt = $conn->prepare("
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

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();

$medicine = $result->fetch_assoc();

$stmt->close();

/* =========================================================
   CHECK IF MEDICINE EXISTS
========================================================= */

if (!$medicine) {
    die("Medicine not found.");
}

/* =========================================================
   CHECK IF STATUS IS ALREADY THE SAME
========================================================= */

$currentStatus = (int) $medicine["is_active"];

if ($currentStatus === $status) {
    header("Location: index.php");
    exit;
}

/* =========================================================
   UPDATE STATUS
========================================================= */

$updateStmt = $conn->prepare("
    UPDATE medicines
    SET is_active = ?
    WHERE id = ?
    LIMIT 1
");

if (!$updateStmt) {
    die("Database error: " . $conn->error);
}

$updateStmt->bind_param(
    "ii",
    $status,
    $id
);

if (!$updateStmt->execute()) {

    $errorMessage = $updateStmt->error;

    $updateStmt->close();

    die(
        "Failed to update medicine status: " .
        $errorMessage
    );
}

$updateStmt->close();

/* =========================================================
   ACTIVITY LOG
========================================================= */

$medicineDescription =
    "Medicine ID " .
    $id .
    ": " .
    $medicine["medicine_name"];

if (
    !empty($medicine["strength"])
) {
    $medicineDescription .=
        " - " .
        $medicine["strength"];
}

if (
    !empty($medicine["form"])
) {
    $medicineDescription .=
        " - " .
        $medicine["form"];
}

if ($status === 1) {

    logActivity(
        $conn,
        "ACTIVATE_MEDICINE",
        $medicineDescription
    );

} else {

    logActivity(
        $conn,
        "DEACTIVATE_MEDICINE",
        $medicineDescription
    );

}

/* =========================================================
   REDIRECT
========================================================= */

header("Location: index.php");
exit;

?>