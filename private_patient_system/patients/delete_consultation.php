<?php

require_once "../config/database.php";
require_once "../config/auth.php";


/*
|--------------------------------------------------------------------------
| ONLY POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}


/*
|--------------------------------------------------------------------------
| GET CONSULTATION ID
|--------------------------------------------------------------------------
*/

$consultation_id = isset($_POST['consultation_id'])
    ? intval($_POST['consultation_id'])
    : 0;

if ($consultation_id <= 0) {
    die("Invalid consultation ID.");
}


/*
|--------------------------------------------------------------------------
| GET PATIENT ID
|--------------------------------------------------------------------------
*/

$sql = "SELECT patient_id
        FROM consultations
        WHERE id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $consultation_id);

if (!$stmt->execute()) {
    $stmt->close();
    die("Database error: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("Consultation not found.");
}

$data = $result->fetch_assoc();

$patient_id = intval($data['patient_id']);

$stmt->close();


/*
|--------------------------------------------------------------------------
| DELETE CONSULTATION
|--------------------------------------------------------------------------
*/

$sql = "DELETE FROM consultations
        WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $consultation_id);

if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();

    die("Error deleting consultation: " . $error);
}


/*
|--------------------------------------------------------------------------
| CHECK IF DELETE WAS SUCCESSFUL
|--------------------------------------------------------------------------
*/

if ($stmt->affected_rows <= 0) {
    $stmt->close();
    $conn->close();

    die("Consultation was not deleted.");
}

$stmt->close();
$conn->close();


/*
|--------------------------------------------------------------------------
| RETURN TO PATIENT PROFILE
|--------------------------------------------------------------------------
*/

header(
    "Location: view.php?id=" . urlencode($patient_id)
);

exit;

?>