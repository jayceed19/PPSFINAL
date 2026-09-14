<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


/* =========================================================
   ONLY POST REQUEST
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    die("Invalid request.");

}


/* =========================================================
   GET LABORATORY ID
========================================================= */

$labId = isset($_POST["id"])
    ? intval($_POST["id"])
    : 0;


if ($labId <= 0) {

    die("Invalid laboratory result ID.");

}


/* =========================================================
   GET TEST NAME
========================================================= */

$testName = isset($_POST["test_name"])
    ? trim($_POST["test_name"])
    : "";


/* =========================================================
   GET RESULT
========================================================= */

$testResult = isset($_POST["result"])
    ? trim($_POST["result"])
    : "";


/* =========================================================
   GET X-RAY FINDINGS
========================================================= */

$xrayFindings = isset($_POST["xray_findings"])
    ? trim($_POST["xray_findings"])
    : "";


/* =========================================================
   GET X-RAY IMPRESSION
========================================================= */

$xrayImpression = isset($_POST["xray_impression"])
    ? trim($_POST["xray_impression"])
    : "";


/* =========================================================
   GET ECG FINDING
========================================================= */

$ecgFinding = isset($_POST["ecg_finding"])
    ? trim($_POST["ecg_finding"])
    : "";


/* =========================================================
   GET REMARKS
========================================================= */

$remarks = isset($_POST["remarks"])
    ? trim($_POST["remarks"])
    : "";


/* =========================================================
   CLEAN TEXT FUNCTION
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


/* =========================================================
   CLEAN VALUES
========================================================= */

$testName = cleanText($testName);

$testResult = cleanText($testResult);


/*
 * Do not uppercase findings/impression.
 * This allows the user to keep normal sentence formatting.
 */

$xrayFindings = trim($xrayFindings);

$xrayImpression = trim($xrayImpression);

$ecgFinding = trim($ecgFinding);

$remarks = trim($remarks);


/* =========================================================
   VALIDATE TEST NAME
========================================================= */

if ($testName === "") {

    die("Laboratory test name is required.");

}


/* =========================================================
   VALIDATE RESULT
========================================================= */

if ($testResult === "") {

    die("Laboratory result is required.");

}


/* =========================================================
   GET EXISTING LABORATORY RECORD
========================================================= */

$sql = "

    SELECT

        l.id,
        l.patient_id,
        l.test_date,
        l.test_name,
        l.result,
        l.remarks,
        l.facility_type,
        l.health_care_institution,

        p.patient_id AS patient_code,
        p.first_name,
        p.middle_name,
        p.last_name

    FROM laboratory l

    INNER JOIN patients p
        ON p.id = l.patient_id

    WHERE l.id = ?

    LIMIT 1

";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$stmt->bind_param(
    "i",
    $labId
);


$stmt->execute();


$result = $stmt->get_result();


$lab = $result->fetch_assoc();


$stmt->close();


/* =========================================================
   CHECK RECORD
========================================================= */

if (!$lab) {

    $conn->close();

    die(
        "Laboratory result not found."
    );

}


/* =========================================================
   GET ORIGINAL TEST NAME
========================================================= */

$originalTestName = strtoupper(
    trim($lab["test_name"])
);


/* =========================================================
   CHECK TEST TYPE
========================================================= */

$isXray =
    $originalTestName === "CHEST X-RAY";


$isECG =
    $originalTestName === "ELECTROCARDIOGRAM (ECG)" ||
    $originalTestName === "ECG";


$isImaging =
    $isXray ||
    $isECG;


/* =========================================================
   FORCE ORIGINAL TEST NAME
========================================================= */

/*
 * Laboratory test name should not actually be changed
 * from the edit page.
 *
 * This also prevents changing the category accidentally.
 */

$testName = $originalTestName;


/* =========================================================
   VALIDATE X-RAY
========================================================= */

if ($isXray) {


    /* -----------------------------------------------------
       VALID RESULT
    ----------------------------------------------------- */

    if (
        $testResult !== "ESSENTIALLY NORMAL" &&
        $testResult !== "WITH FINDING"
    ) {

        $conn->close();

        die(
            "Invalid Chest X-ray result."
        );

    }


    /* -----------------------------------------------------
       ESSENTIALLY NORMAL
       No findings / impression
    ----------------------------------------------------- */

    if (
        $testResult === "ESSENTIALLY NORMAL"
    ) {

        $xrayFindings = "";

        $xrayImpression = "";

        $remarks = "";

    }


    /* -----------------------------------------------------
       WITH FINDING
    ----------------------------------------------------- */

    if (
        $testResult === "WITH FINDING"
    ) {


        if (
            trim($xrayFindings) === ""
        ) {

            $conn->close();

            die(
                "Please enter the X-ray findings."
            );

        }


        if (
            trim($xrayImpression) === ""
        ) {

            $conn->close();

            die(
                "Please enter the X-ray impression."
            );

        }


        /*
         * Database currently has only one remarks column.
         *
         * Store the two X-ray fields together using:
         *
         * FINDINGS: ...
         * | IMPRESSION: ...
         */

        $remarks =
            "FINDINGS: " .
            trim($xrayFindings) .
            " | IMPRESSION: " .
            trim($xrayImpression);

    }

}


/* =========================================================
   VALIDATE ECG
========================================================= */

elseif ($isECG) {


    /* -----------------------------------------------------
       VALID RESULT
    ----------------------------------------------------- */

    if (
        $testResult !== "ESSENTIALLY NORMAL" &&
        $testResult !== "WITH FINDING"
    ) {

        $conn->close();

        die(
            "Invalid ECG result."
        );

    }


    /* -----------------------------------------------------
       ESSENTIALLY NORMAL
       No ECG finding
    ----------------------------------------------------- */

    if (
        $testResult === "ESSENTIALLY NORMAL"
    ) {

        $ecgFinding = "";

        $remarks = "";

    }


    /* -----------------------------------------------------
       WITH FINDING
    ----------------------------------------------------- */

    if (
        $testResult === "WITH FINDING"
    ) {


        if (
            trim($ecgFinding) === ""
        ) {

            $conn->close();

            die(
                "Please enter the ECG finding."
            );

        }


        /*
         * ECG finding is stored in the existing
         * remarks column because there is no separate
         * ecg_finding column in the database.
         */

        $remarks =
            trim($ecgFinding);

    }

}


/* =========================================================
   NON-IMAGING LABORATORY TEST
========================================================= */

else {

    /*
     * Regular laboratory tests use the result field.
     *
     * Keep any manually supplied remarks if present.
     */

    $remarks = trim($remarks);

}


/* =========================================================
   GET EXISTING FACILITY
========================================================= */

$facilityType = isset(
    $lab["facility_type"]
)
    ? trim($lab["facility_type"])
    : "";


$healthCareInstitution = isset(
    $lab["health_care_institution"]
)
    ? trim($lab["health_care_institution"])
    : "";


/* =========================================================
   NORMALIZE FACILITY
========================================================= */

/*
|----------------------------------------------------------------
| WITHIN_FACILITY
| = DCMD Clinic
|
| ACCREDITED_FACILITY
| = Laboratory name
|----------------------------------------------------------------
*/

if (
    $facilityType === "" ||
    $facilityType === "WITHIN_FACILITY"
) {

    $facilityType =
        "WITHIN_FACILITY";

    $healthCareInstitution =
        "DCMD CLINIC";

}

elseif (
    $facilityType === "ACCREDITED_FACILITY"
) {


    if (
        trim($healthCareInstitution) === ""
    ) {

        $conn->close();

        die(
            "Laboratory name is missing."
        );

    }


    $healthCareInstitution =
        cleanText(
            $healthCareInstitution
        );

}

else {

    $conn->close();

    die(
        "Invalid laboratory facility."
    );

}


/* =========================================================
   UPDATE LABORATORY RESULT
========================================================= */

$updateSql = "

    UPDATE laboratory

    SET

        test_name = ?,
        result = ?,
        remarks = ?,
        facility_type = ?,
        health_care_institution = ?

    WHERE id = ?

    LIMIT 1

";


$updateStmt =
    $conn->prepare($updateSql);


if (!$updateStmt) {

    $conn->close();

    die(
        "Database error: " .
        $conn->error
    );

}


/* =========================================================
   BIND PARAMETERS
========================================================= */

$updateStmt->bind_param(
    "sssssi",
    $testName,
    $testResult,
    $remarks,
    $facilityType,
    $healthCareInstitution,
    $labId
);


/* =========================================================
   EXECUTE UPDATE
========================================================= */

if (!$updateStmt->execute()) {

    $errorMessage =
        $updateStmt->error;


    $updateStmt->close();

    $conn->close();

    die(
        "Failed to update laboratory result." .
        "<br><br>" .
        htmlspecialchars(
            $errorMessage
        )
    );

}


/* =========================================================
   CLOSE UPDATE STATEMENT
========================================================= */

$updateStmt->close();


/* =========================================================
   PATIENT INFORMATION FOR ACTIVITY LOG
========================================================= */

$patientCode =
    isset($lab["patient_code"])
        ? $lab["patient_code"]
        : "";


$patientFullName = trim(
    $lab["first_name"] .
    " " .
    $lab["middle_name"] .
    " " .
    $lab["last_name"]
);


$patientFullName = preg_replace(
    '/\s+/',
    ' ',
    $patientFullName
);


$patientFullName =
    strtoupper(
        trim($patientFullName)
    );


/* =========================================================
   ACTIVITY LOG
========================================================= */

$activityDescription =
    "Updated laboratory result: " .
    $patientCode .
    " - " .
    $patientFullName .
    " - " .
    $testName .
    " - " .
    $lab["test_date"];


logActivity(
    $conn,
    "LABORATORY",
    $activityDescription
);


/* =========================================================
   REDIRECT BACK TO LABORATORY RESULTS
========================================================= */

$patientId =
    (int)$lab["patient_id"];


$testDate =
    $lab["test_date"];


$conn->close();


header(
    "Location: view.php?id=" .
    $patientId .
    "&date=" .
    urlencode($testDate) .
    "&updated=1"
);

exit;

?>