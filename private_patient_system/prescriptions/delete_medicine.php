<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


/* =========================================================
   REQUEST METHOD
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST" &&
    $_SERVER["REQUEST_METHOD"] !== "GET"
) {

    die("Invalid request.");

}


/* =========================================================
   GET MEDICINE ID
========================================================= */

$medicineId = isset($_POST["id"])
    ? intval($_POST["id"])
    : (
        isset($_GET["id"])
        ? intval($_GET["id"])
        : 0
    );


if ($medicineId <= 0) {

    die("Invalid prescription medicine ID.");

}


/* =========================================================
   GET MEDICINE RECORD
========================================================= */

$stmt = $conn->prepare("
    SELECT
        p.id,
        p.prescription_header_id,
        p.patient_id,
        p.medicine_name,
        ph.prescribed_date
    FROM prescriptions p
    LEFT JOIN prescription_headers ph
        ON ph.id = p.prescription_header_id
    WHERE p.id = ?
    LIMIT 1
");


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$stmt->bind_param(
    "i",
    $medicineId
);


$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();

    $conn->close();

    die(
        "Prescription medicine not found."
    );

}


$medicine = $result->fetch_assoc();


$stmt->close();


/* =========================================================
   GET ACTUAL VALUES
========================================================= */

$headerId = intval(
    $medicine["prescription_header_id"]
);

$patientId = intval(
    $medicine["patient_id"]
);

$medicineName = trim(
    $medicine["medicine_name"]
);

$prescribedDate = isset(
    $medicine["prescribed_date"]
)
    ? trim(
        $medicine["prescribed_date"]
    )
    : "";


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


$patientStmt->execute();


$patientResult =
    $patientStmt->get_result();


if ($patientResult->num_rows === 0) {

    $patientStmt->close();

    $conn->close();

    die(
        "Patient not found."
    );

}


$patientData =
    $patientResult->fetch_assoc();


$patientDisplayId =
    $patientData["patient_id"];


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
    trim($patientName)
);


$patientStmt->close();


/* =========================================================
   DELETE MEDICINE
========================================================= */

$deleteStmt = $conn->prepare("
    DELETE FROM prescriptions
    WHERE id = ?
");


if (!$deleteStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$deleteStmt->bind_param(
    "i",
    $medicineId
);


if (!$deleteStmt->execute()) {

    $error =
        $deleteStmt->error;

    $deleteStmt->close();

    $conn->close();

    die(
        "Failed to delete prescription medicine: " .
        htmlspecialchars($error)
    );

}


$deleteStmt->close();


/* =========================================================
   ACTIVITY LOG
========================================================= */

$activityDescription =
    "Deleted prescription medicine: " .
    $patientDisplayId .
    " - " .
    $patientName .
    " - " .
    $medicineName;


if ($prescribedDate !== "") {

    $activityDescription .=
        " - " .
        $prescribedDate;

}


logActivity(
    $conn,
    "DELETE_PRESCRIPTION",
    $activityDescription
);


/* =========================================================
   CHECK REMAINING MEDICINES
========================================================= */

$countStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM prescriptions
    WHERE prescription_header_id = ?
");


if (!$countStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$countStmt->bind_param(
    "i",
    $headerId
);


$countStmt->execute();


$countResult =
    $countStmt->get_result();


$countData =
    $countResult->fetch_assoc();


$remainingMedicines =
    intval(
        $countData["total"]
    );


$countStmt->close();


/* =========================================================
   DELETE EMPTY PRESCRIPTION HEADER
========================================================= */

if ($remainingMedicines === 0) {

    $headerStmt = $conn->prepare("
        DELETE FROM prescription_headers
        WHERE id = ?
    ");


    if (!$headerStmt) {

        die(
            "Database error: " .
            $conn->error
        );

    }


    $headerStmt->bind_param(
        "i",
        $headerId
    );


    if (!$headerStmt->execute()) {

        $error =
            $headerStmt->error;

        $headerStmt->close();

        $conn->close();

        die(
            "Failed to delete empty prescription header: " .
            htmlspecialchars($error)
        );

    }


    $headerStmt->close();


    /* =====================================================
       IMPORTANT:
       history.php expects ?id=PATIENT_DATABASE_ID
    ===================================================== */

    $conn->close();

    header(
        "Location: history.php?id=" .
        urlencode($patientId)
    );

    exit;
}


/* =========================================================
   REDIRECT BACK TO PRESCRIPTION MANAGEMENT
========================================================= */

$conn->close();

header(
    "Location: manage_prescription.php?prescription_header_id=" .
    urlencode($headerId)
);

exit;

?>