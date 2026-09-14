<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


/* =========================================================
   POST ONLY
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: ../dashboard.php"
    );

    exit;
}


/* =========================================================
   GET FORM VALUES
========================================================= */

$medicineId = isset($_POST["id"])
    ? (int) $_POST["id"]
    : 0;

$headerId = isset($_POST["prescription_header_id"])
    ? (int) $_POST["prescription_header_id"]
    : 0;

$patientId = isset($_POST["patient_id"])
    ? (int) $_POST["patient_id"]
    : 0;

$medicineName = isset($_POST["medicine_name"])
    ? trim($_POST["medicine_name"])
    : "";

$strength = isset($_POST["strength"])
    ? trim($_POST["strength"])
    : "";

$quantity = isset($_POST["quantity"])
    ? trim($_POST["quantity"])
    : "";

$sig = isset($_POST["sig"])
    ? trim($_POST["sig"])
    : "";

$breakfast = isset($_POST["breakfast"])
    ? trim($_POST["breakfast"])
    : "";

$lunch = isset($_POST["lunch"])
    ? trim($_POST["lunch"])
    : "";

$dinner = isset($_POST["dinner"])
    ? trim($_POST["dinner"])
    : "";


/* =========================================================
   CLEAN TEXT
========================================================= */

function cleanText($value)
{
    $value = trim($value);

    $value = preg_replace(
        '/\s+/',
        ' ',
        $value
    );

    return strtoupper($value);
}


$medicineName = cleanText(
    $medicineName
);

$strength = cleanText(
    $strength
);

$sig = cleanText(
    $sig
);

$breakfast = cleanText(
    $breakfast
);

$lunch = cleanText(
    $lunch
);

$dinner = cleanText(
    $dinner
);


/* =========================================================
   LIMIT SIG LENGTH
========================================================= */

if (strlen($sig) > 255) {
    $sig = substr($sig, 0, 255);
}


/* =========================================================
   VALIDATE IDs
========================================================= */

if (
    $medicineId <= 0
    ||
    $headerId <= 0
    ||
    $patientId <= 0
) {

    die(
        "Invalid medicine information."
    );
}


/* =========================================================
   VALIDATE MEDICINE NAME
========================================================= */

if ($medicineName === "") {

    die(
        "Medicine name is required."
    );
}


/* =========================================================
   VALIDATE QUANTITY
========================================================= */

$quantityValue = null;

if ($quantity !== "") {

    if (
        !preg_match(
            '/^[0-9]+$/',
            $quantity
        )
    ) {

        die(
            "Quantity must contain numbers only."
        );
    }


    $quantityValue =
        (int) $quantity;


    if ($quantityValue < 1) {

        die(
            "Quantity must be at least 1."
        );
    }
}


/* =========================================================
   VERIFY MEDICINE
========================================================= */

$verifySql = "

    SELECT
        id,
        prescription_header_id,
        patient_id

    FROM prescriptions

    WHERE id = ?
    AND prescription_header_id = ?
    AND patient_id = ?

    LIMIT 1
";


$verifyStmt =
    $conn->prepare(
        $verifySql
    );


if (!$verifyStmt) {

    die(
        "Database error: " .
        $conn->error
    );
}


$verifyStmt->bind_param(
    "iii",
    $medicineId,
    $headerId,
    $patientId
);


$verifyStmt->execute();


$verifyResult =
    $verifyStmt->get_result();


if (
    $verifyResult->num_rows == 0
) {

    $verifyStmt->close();

    $conn->close();

    die(
        "Medicine record not found or does not belong to this prescription."
    );
}


$verifyStmt->close();


/* =========================================================
   VERIFY PRESCRIPTION HEADER
========================================================= */

$headerSql = "

    SELECT
        id,
        patient_id,
        prescribed_date

    FROM prescription_headers

    WHERE id = ?
    AND patient_id = ?

    LIMIT 1
";


$headerStmt =
    $conn->prepare(
        $headerSql
    );


if (!$headerStmt) {

    die(
        "Database error: " .
        $conn->error
    );
}


$headerStmt->bind_param(
    "ii",
    $headerId,
    $patientId
);


$headerStmt->execute();


$headerResult =
    $headerStmt->get_result();


if (
    $headerResult->num_rows == 0
) {

    $headerStmt->close();

    $conn->close();

    die(
        "Prescription not found."
    );
}


$headerData =
    $headerResult->fetch_assoc();


$headerStmt->close();


/* =========================================================
   GET PATIENT INFORMATION
========================================================= */

$patientSql = "

    SELECT
        id,
        patient_id,
        first_name,
        middle_name,
        last_name

    FROM patients

    WHERE id = ?

    LIMIT 1
";


$patientStmt =
    $conn->prepare(
        $patientSql
    );


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


if ($patientResult->num_rows == 0) {

    $patientStmt->close();

    $conn->close();

    die(
        "Patient not found."
    );
}


$patientData =
    $patientResult->fetch_assoc();


$patientStmt->close();


$patientDisplayId =
    $patientData["patient_id"];


$patientName = trim(
    $patientData["first_name"] . " " .
    $patientData["middle_name"] . " " .
    $patientData["last_name"]
);


$patientName = strtoupper(
    preg_replace(
        '/\s+/',
        ' ',
        $patientName
    )
);


/* =========================================================
   UPDATE MEDICINE
========================================================= */

$updateSql = "

    UPDATE prescriptions

    SET

        medicine_name = ?,
        strength = ?,
        quantity = ?,
        sig = ?,
        breakfast = ?,
        lunch = ?,
        dinner = ?

    WHERE id = ?
    AND prescription_header_id = ?
    AND patient_id = ?

    LIMIT 1
";


$updateStmt =
    $conn->prepare(
        $updateSql
    );


if (!$updateStmt) {

    die(
        "Database error: " .
        $conn->error
    );
}


/*
    Bind:

    s = medicine name
    s = strength
    i = quantity
    s = sig
    s = breakfast
    s = lunch
    s = dinner
    i = medicine id
    i = header id
    i = patient id
*/

$updateStmt->bind_param(
    "ssissssiii",
    $medicineName,
    $strength,
    $quantityValue,
    $sig,
    $breakfast,
    $lunch,
    $dinner,
    $medicineId,
    $headerId,
    $patientId
);


if (!$updateStmt->execute()) {

    $error =
        $updateStmt->error;

    $updateStmt->close();

    $conn->close();

    die(
        "Unable to update medicine: " .
        htmlspecialchars($error)
    );
}


$updateStmt->close();


/* =========================================================
   ACTIVITY LOG
========================================================= */

$activityDescription =
    "Updated prescription medicine: " .
    $patientDisplayId .
    " - " .
    $patientName .
    " - " .
    $medicineName .
    " - " .
    $headerData["prescribed_date"];


logActivity(
    $conn,
    "PRESCRIPTION",
    $activityDescription
);


/* =========================================================
   CLOSE DATABASE
========================================================= */

$conn->close();


/* =========================================================
   REDIRECT BACK TO MANAGE PRESCRIPTION
========================================================= */

header(
    "Location: manage_prescription.php?prescription_header_id=" .
    $headerId
);

exit;

?>