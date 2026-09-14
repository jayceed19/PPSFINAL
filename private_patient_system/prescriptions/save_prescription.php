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


/*
|--------------------------------------------------------------------------
| GET IDS
|--------------------------------------------------------------------------
*/

$consultationId = isset($_POST["consultation_id"])
    ? (int) $_POST["consultation_id"]
    : 0;

$patientId = isset($_POST["patient_id"])
    ? (int) $_POST["patient_id"]
    : 0;


if ($consultationId <= 0 || $patientId <= 0) {

    die(
        "Invalid consultation or patient ID."
    );

}


/*
|--------------------------------------------------------------------------
| VERIFY CONSULTATION
|--------------------------------------------------------------------------
*/

$checkSql = "

    SELECT

        id,
        patient_id

    FROM consultations

    WHERE id = ?

    LIMIT 1

";


$checkStmt = $conn->prepare(
    $checkSql
);


if (!$checkStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$checkStmt->bind_param(
    "i",
    $consultationId
);


$checkStmt->execute();


$checkResult =
    $checkStmt->get_result();


if ($checkResult->num_rows === 0) {

    $checkStmt->close();

    die(
        "Consultation not found."
    );

}


$checkData =
    $checkResult->fetch_assoc();


$checkStmt->close();


/*
|--------------------------------------------------------------------------
| VERIFY PATIENT
|--------------------------------------------------------------------------
*/

if (
    (int) $checkData["patient_id"]
    !== $patientId
) {

    die(
        "Patient does not match the selected consultation."
    );

}


/*
|--------------------------------------------------------------------------
| GET PATIENT INFORMATION
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$medicineNames =
    isset($_POST["medicine_name"])
        ? $_POST["medicine_name"]
        : array();


$strengths =
    isset($_POST["strength"])
        ? $_POST["strength"]
        : array();


$quantities =
    isset($_POST["quantity"])
        ? $_POST["quantity"]
        : array();


/*
|--------------------------------------------------------------------------
| SIG / DIRECTIONS
|--------------------------------------------------------------------------
*/

$sigs =
    isset($_POST["sig"])
        ? $_POST["sig"]
        : array();


$breakfasts =
    isset($_POST["breakfast"])
        ? $_POST["breakfast"]
        : array();


$lunches =
    isset($_POST["lunch"])
        ? $_POST["lunch"]
        : array();


$dinners =
    isset($_POST["dinner"])
        ? $_POST["dinner"]
        : array();



/*
|--------------------------------------------------------------------------
| VALIDATE MEDICINE
|--------------------------------------------------------------------------
*/

if (

    !is_array($medicineNames) ||

    count($medicineNames) === 0

) {

    die(
        "Please enter at least one medicine."
    );

}


/*
|--------------------------------------------------------------------------
| PRESCRIPTION DATE
|--------------------------------------------------------------------------
|
| ONE PATIENT + ONE DATE = ONE PRESCRIPTION
|
|--------------------------------------------------------------------------
*/

$prescribedDate =
    date("Y-m-d");


/*
|--------------------------------------------------------------------------
| START TRANSACTION
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();


$headerId = 0;


$savedCount = 0;


/*
|--------------------------------------------------------------------------
| FIND EXISTING PRESCRIPTION HEADER
|--------------------------------------------------------------------------
*/

$headerSelectSql = "

    SELECT id

    FROM prescription_headers

    WHERE patient_id = ?

      AND prescribed_date = ?

    LIMIT 1

    FOR UPDATE

";


$headerSelectStmt =
    $conn->prepare(
        $headerSelectSql
    );


if (!$headerSelectStmt) {

    $conn->rollback();

    die(
        "Database error: " .
        $conn->error
    );

}


$headerSelectStmt->bind_param(
    "is",
    $patientId,
    $prescribedDate
);


if (!$headerSelectStmt->execute()) {

    $headerSelectStmt->close();

    $conn->rollback();

    die(
        "Unable to check today's prescription."
    );

}


$headerResult =
    $headerSelectStmt->get_result();


/*
|--------------------------------------------------------------------------
| USE EXISTING HEADER
|--------------------------------------------------------------------------
*/

if ($headerResult->num_rows > 0) {

    $headerData =
        $headerResult->fetch_assoc();


    $headerId =
        (int) $headerData["id"];

}


/*
|--------------------------------------------------------------------------
| CREATE NEW HEADER
|--------------------------------------------------------------------------
*/

else {

    $headerInsertSql = "

        INSERT INTO prescription_headers

        (
            patient_id,
            prescribed_date
        )

        VALUES

        (
            ?,
            ?
        )

    ";


    $headerInsertStmt =
        $conn->prepare(
            $headerInsertSql
        );


    if (!$headerInsertStmt) {

        $headerSelectStmt->close();

        $conn->rollback();

        die(
            "Database error: " .
            $conn->error
        );

    }


    $headerInsertStmt->bind_param(
        "is",
        $patientId,
        $prescribedDate
    );


    if (
        !$headerInsertStmt->execute()
    ) {

        /*
        |--------------------------------------------------------------------------
        | TRY EXISTING HEADER AGAIN
        |--------------------------------------------------------------------------
        */

        $retrySql = "

            SELECT id

            FROM prescription_headers

            WHERE patient_id = ?

              AND prescribed_date = ?

            LIMIT 1

        ";


        $retryStmt =
            $conn->prepare(
                $retrySql
            );


        if (!$retryStmt) {

            $headerInsertStmt->close();

            $headerSelectStmt->close();

            $conn->rollback();

            die(
                "Unable to find today's prescription."
            );

        }


        $retryStmt->bind_param(
            "is",
            $patientId,
            $prescribedDate
        );


        $retryStmt->execute();


        $retryResult =
            $retryStmt->get_result();


        if (
            $retryResult->num_rows > 0
        ) {

            $retryData =
                $retryResult->fetch_assoc();


            $headerId =
                (int) $retryData["id"];

        }

        else {

            $error =
                $headerInsertStmt->error;


            $retryStmt->close();

            $headerInsertStmt->close();

            $headerSelectStmt->close();

            $conn->rollback();

            die(
                "Unable to create prescription: " .
                $error
            );

        }


        $retryStmt->close();

    }

    else {

        $headerId =
            (int) $conn->insert_id;

    }


    $headerInsertStmt->close();

}


$headerSelectStmt->close();


/*
|--------------------------------------------------------------------------
| VERIFY HEADER ID
|--------------------------------------------------------------------------
*/

if ($headerId <= 0) {

    $conn->rollback();

    die(
        "Unable to create or find today's prescription."
    );

}


/*
|--------------------------------------------------------------------------
| PREPARE MEDICINE INSERT
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| We now save:
|
| prescription_header_id
| consultation_id
| patient_id
| medicine_name
| strength
| quantity
| sig
| breakfast
| lunch
| dinner
| prescribed_date
|
| Old columns:
|
| dosage
| frequency
| duration
| instructions
|
| are intentionally left untouched.
|
|--------------------------------------------------------------------------
*/

$insertSql = "

    INSERT INTO prescriptions

    (

        prescription_header_id,

        consultation_id,

        patient_id,

        medicine_name,

        strength,

        quantity,

        sig,

        breakfast,

        lunch,

        dinner,

        prescribed_date

    )

    VALUES

    (

        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?

    )

";


$insertStmt =
    $conn->prepare(
        $insertSql
    );


if (!$insertStmt) {

    $conn->rollback();

    die(
        "Database error: " .
        $conn->error
    );

}


/*
|--------------------------------------------------------------------------
| SAVE EACH MEDICINE
|--------------------------------------------------------------------------
*/

for (

    $i = 0;

    $i < count($medicineNames);

    $i++

) {


    /*
    |--------------------------------------------------------------------------
    | MEDICINE NAME
    |--------------------------------------------------------------------------
    */

    $medicineName =
        isset($medicineNames[$i])

            ? trim($medicineNames[$i])

            : "";


    /*
    |--------------------------------------------------------------------------
    | SKIP EMPTY ROW
    |--------------------------------------------------------------------------
    */

    if ($medicineName === "") {

        continue;

    }


    /*
    |--------------------------------------------------------------------------
    | STRENGTH
    |--------------------------------------------------------------------------
    */

    $strength =
        isset($strengths[$i])

            ? trim($strengths[$i])

            : "";


    /*
    |--------------------------------------------------------------------------
    | QUANTITY
    |--------------------------------------------------------------------------
    */

    $quantityRaw =
        isset($quantities[$i])

            ? trim($quantities[$i])

            : "";


    if ($quantityRaw === "") {

        /*
        |--------------------------------------------------------------------------
        | Keep NULL when no quantity was entered.
        |--------------------------------------------------------------------------
        */

        $quantity = null;

    }

    else {

        if (
            !ctype_digit($quantityRaw)
        ) {

            $insertStmt->close();

            $conn->rollback();

            $conn->close();

            die(
                "Quantity must contain numbers only."
            );

        }


        $quantity =
            (int) $quantityRaw;


        if ($quantity < 1) {

            $insertStmt->close();

            $conn->rollback();

            $conn->close();

            die(
                "Quantity must be at least 1."
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | SIG / DIRECTIONS
    |--------------------------------------------------------------------------
    */

    $sig =
        isset($sigs[$i])

            ? trim($sigs[$i])

            : "";


    /*
    |--------------------------------------------------------------------------
    | LIMIT SIG TO 255 CHARACTERS
    |--------------------------------------------------------------------------
    */

    if (strlen($sig) > 255) {

        $sig =
            substr(
                $sig,
                0,
                255
            );

    }


    /*
    |--------------------------------------------------------------------------
    | BREAKFAST
    |--------------------------------------------------------------------------
    */

    $breakfast =
        isset($breakfasts[$i])

            ? trim($breakfasts[$i])

            : "";


    /*
    |--------------------------------------------------------------------------
    | LUNCH
    |--------------------------------------------------------------------------
    */

    $lunch =
        isset($lunches[$i])

            ? trim($lunches[$i])

            : "";


    /*
    |--------------------------------------------------------------------------
    | DINNER
    |--------------------------------------------------------------------------
    */

    $dinner =
        isset($dinners[$i])

            ? trim($dinners[$i])

            : "";


    /*
    |--------------------------------------------------------------------------
    | CLEAN TEXT
    |--------------------------------------------------------------------------
    */

    $medicineName =
        preg_replace(
            '/\s+/',
            ' ',
            $medicineName
        );


    $strength =
        preg_replace(
            '/\s+/',
            ' ',
            $strength
        );


    $sig =
        preg_replace(
            '/\s+/',
            ' ',
            $sig
        );


    $breakfast =
        preg_replace(
            '/\s+/',
            ' ',
            $breakfast
        );


    $lunch =
        preg_replace(
            '/\s+/',
            ' ',
            $lunch
        );


    $dinner =
        preg_replace(
            '/\s+/',
            ' ',
            $dinner
        );


    /*
    |--------------------------------------------------------------------------
    | UPPERCASE TEXT
    |--------------------------------------------------------------------------
    */

    $medicineName =
        strtoupper(
            $medicineName
        );


    $strength =
        strtoupper(
            $strength
        );


    $sig =
        strtoupper(
            $sig
        );


    $breakfast =
        strtoupper(
            $breakfast
        );


    $lunch =
        strtoupper(
            $lunch
        );


    $dinner =
        strtoupper(
            $dinner
        );


    /*
    |--------------------------------------------------------------------------
    | BIND PARAMETERS
    |--------------------------------------------------------------------------
    |
    | TOTAL = 11 VARIABLES
    |
    | 1  headerId
    | 2  consultationId
    | 3  patientId
    | 4  medicineName
    | 5  strength
    | 6  quantity
    | 7  sig
    | 8  breakfast
    | 9  lunch
    | 10 dinner
    | 11 prescribedDate
    |
    | Types:
    |
    | i i i s s i s s s s s
    |
    | = "iiississsss"
    |
    |--------------------------------------------------------------------------
    */

    $insertStmt->bind_param(

        "iiississsss",

        $headerId,

        $consultationId,

        $patientId,

        $medicineName,

        $strength,

        $quantity,

        $sig,

        $breakfast,

        $lunch,

        $dinner,

        $prescribedDate

    );


    /*
    |--------------------------------------------------------------------------
    | EXECUTE
    |--------------------------------------------------------------------------
    */

    if (
        !$insertStmt->execute()
    ) {

        $error =
            $insertStmt->error;


        $insertStmt->close();

        $conn->rollback();

        $conn->close();

        die(
            "Unable to save prescription: " .
            $error
        );

    }


    $savedCount++;

}


/*
|--------------------------------------------------------------------------
| CHECK SAVED MEDICINE
|--------------------------------------------------------------------------
*/

if ($savedCount <= 0) {

    $insertStmt->close();

    $conn->rollback();

    $conn->close();

    die(
        "Please enter at least one medicine."
    );

}


/*
|--------------------------------------------------------------------------
| COMMIT
|--------------------------------------------------------------------------
*/

$conn->commit();


$insertStmt->close();


/*
|--------------------------------------------------------------------------
| ACTIVITY LOG
|--------------------------------------------------------------------------
*/

$activityDescription =

    "Added prescription: " .

    $patientDisplayId .

    " - " .

    $patientName .

    " - " .

    $savedCount .

    " medicine(s) - " .

    $prescribedDate;


logActivity(

    $conn,

    "PRESCRIPTION",

    $activityDescription

);


$conn->close();


/*
|--------------------------------------------------------------------------
| REDIRECT TO PRINT
|--------------------------------------------------------------------------
*/

header(

    "Location: print_prescription.php?prescription_header_id=" .

    $headerId

);


exit;

?>