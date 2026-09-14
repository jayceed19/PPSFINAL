<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


/* =========================================================
   ONLY POST REQUEST
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    die("Invalid request.");

}


/* =========================================================
   GET PATIENT ID
========================================================= */

$patientId = isset($_POST['patient_id'])
    ? intval($_POST['patient_id'])
    : 0;

if ($patientId <= 0) {

    die("Invalid patient ID.");

}


/* =========================================================
   GET LABORATORY DATE
========================================================= */

$testDate = isset($_POST['test_date'])
    ? trim($_POST['test_date'])
    : "";

if ($testDate === "") {

    die("Laboratory date is required.");

}


/* =========================================================
   VALIDATE DATE FORMAT
========================================================= */

$dateObject = DateTime::createFromFormat(
    'Y-m-d',
    $testDate
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $testDate
) {

    die("Invalid laboratory date.");

}


/* =========================================================
   GET COMMON TEST ARRAYS
========================================================= */

$testNames = isset($_POST['test_name'])
    ? $_POST['test_name']
    : array();

$results = isset($_POST['result'])
    ? $_POST['result']
    : array();

$testCategories = isset($_POST['test_category'])
    ? $_POST['test_category']
    : array();


/* =========================================================
   GET FACILITY ARRAYS
========================================================= */

$facilityTypes = isset($_POST['facility_type'])
    ? $_POST['facility_type']
    : array();


/* =========================================================
   GET HEALTH CARE INSTITUTION ARRAYS
========================================================= */

$healthCareInstitutions = isset(
    $_POST['health_care_institution']
)
    ? $_POST['health_care_institution']
    : array();


/* =========================================================
   GET UNIT AND REFERENCE RANGE
========================================================= */

$units = isset($_POST['unit'])
    ? $_POST['unit']
    : array();

$referenceRanges = isset($_POST['reference_range'])
    ? $_POST['reference_range']
    : array();


/* =========================================================
   GET X-RAY DATA
========================================================= */

$xrayFindings = isset($_POST['xray_findings'])
    ? trim($_POST['xray_findings'])
    : "";

$xrayImpression = isset($_POST['xray_impression'])
    ? trim($_POST['xray_impression'])
    : "";


/* =========================================================
   GET ECG DATA
========================================================= */

$ecgFinding = isset($_POST['ecg_finding'])
    ? trim($_POST['ecg_finding'])
    : "";


/* =========================================================
   GET OTHER TEST ARRAYS
========================================================= */

$otherTestNames = isset($_POST['other_test_name'])
    ? $_POST['other_test_name']
    : array();

$otherResults = isset($_POST['other_result'])
    ? $_POST['other_result']
    : array();

$otherUnits = isset($_POST['other_unit'])
    ? $_POST['other_unit']
    : array();

$otherReferenceRanges = isset(
    $_POST['other_reference_range']
)
    ? $_POST['other_reference_range']
    : array();


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
   GET CATEGORY FACILITY
========================================================= */

function getCategoryFacility(
    $category,
    $facilityTypes,
    $healthCareInstitutions
) {

    /* =====================================================
       DEFAULT FACILITY
    ===================================================== */

    $facilityType = "WITHIN_FACILITY";
    $laboratoryName = "";


    /* =====================================================
       GET SELECTED FACILITY
    ===================================================== */

    if (
        is_array($facilityTypes) &&
        isset($facilityTypes[$category])
    ) {

        $facilityType = cleanText(
            $facilityTypes[$category]
        );

    }


    /* =====================================================
       WITHIN FACILITY
    ===================================================== */

    if (
        $facilityType === "WITHIN_FACILITY"
    ) {

        return array(
            "WITHIN_FACILITY",
            "DCMD CLINIC"
        );

    }


    /* =====================================================
       ACCREDITED DIAGNOSTIC FACILITY
    ===================================================== */

    if (
        $facilityType === "ACCREDITED_FACILITY"
    ) {

        if (
            is_array($healthCareInstitutions) &&
            isset($healthCareInstitutions[$category])
        ) {

            $laboratoryName = cleanText(
                $healthCareInstitutions[$category]
            );

        }


        if ($laboratoryName === "") {

            throw new Exception(
                "Please enter the Laboratory Name for " .
                $category .
                "."
            );

        }


        return array(
            "ACCREDITED_FACILITY",
            $laboratoryName
        );

    }


    /* =====================================================
       INVALID FACILITY
    ===================================================== */

    throw new Exception(
        "Invalid laboratory facility selected for " .
        $category .
        "."
    );

}


/* =========================================================
   VERIFY PATIENT
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

$patientStmt = $conn->prepare($patientSql);

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

$patientResult = $patientStmt->get_result();

if ($patientResult->num_rows === 0) {

    $patientStmt->close();

    $conn->close();

    die("Patient not found.");

}


$patientData = $patientResult->fetch_assoc();

$patientDisplayId = $patientData['patient_id'];

$patientFullName = trim(
    $patientData['first_name'] .
    ' ' .
    $patientData['middle_name'] .
    ' ' .
    $patientData['last_name']
);

$patientFullName = strtoupper(
    preg_replace(
        '/\s+/',
        ' ',
        $patientFullName
    )
);

$patientStmt->close();


/* =========================================================
   BEGIN TRANSACTION
========================================================= */

$conn->begin_transaction();

try {


    /* =====================================================
       INSERT STATEMENT
    ===================================================== */

    $insertSql = "

        INSERT INTO laboratory

        (
            patient_id,
            test_date,
            test_name,
            result,
            unit,
            reference_range,
            remarks,
            facility_type,
            health_care_institution
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
            ?
        )

    ";

    $insertStmt = $conn->prepare($insertSql);

    if (!$insertStmt) {

        throw new Exception(
            "Database error: " .
            $conn->error
        );

    }


    /* =====================================================
       SAVED COUNT
    ===================================================== */

    $savedCount = 0;


    /* =====================================================
       X-RAY VALIDATION
    ===================================================== */

    $cleanXrayFindings = cleanText(
        $xrayFindings
    );

    $cleanXrayImpression = cleanText(
        $xrayImpression
    );


    if (
        $cleanXrayFindings !== "" &&
        $cleanXrayImpression === ""
    ) {

        throw new Exception(
            "Please enter the X-ray Impression."
        );

    }


    if (
        $cleanXrayFindings === "" &&
        $cleanXrayImpression !== ""
    ) {

        throw new Exception(
            "Please enter the X-ray Findings."
        );

    }


    /* =====================================================
       ECG VALIDATION
    ===================================================== */

    $ecgResultValue = "";

    if (
        isset($testNames) &&
        is_array($testNames)
    ) {

        $totalTestNames = count($testNames);

        for (
            $i = 0;
            $i < $totalTestNames;
            $i++
        ) {

            $currentTestName = isset(
                $testNames[$i]
            )
                ? cleanText($testNames[$i])
                : "";


            if (
                $currentTestName ===
                "ELECTROCARDIOGRAM (ECG)"
            ) {

                if (
                    isset($results[$i])
                ) {

                    $ecgResultValue =
                        cleanText(
                            $results[$i]
                        );

                }

                break;

            }

        }

    }


    $cleanECGFinding = cleanText(
        $ecgFinding
    );


    if (
        $ecgResultValue ===
        "WITH FINDING"
    ) {

        if ($cleanECGFinding === "") {

            throw new Exception(
                "Please enter the ECG Finding."
            );

        }

    }

    elseif (
        $ecgResultValue ===
        "ESSENTIALLY NORMAL"
    ) {

        $cleanECGFinding = "";

    }

    elseif (
        $ecgResultValue !== ""
    ) {

        throw new Exception(
            "Invalid ECG result."
        );

    }


    /* =====================================================
       SAVE COMMON LABORATORY TESTS
    ===================================================== */

    if (is_array($testNames)) {

        $totalTests = count($testNames);

        for (
            $i = 0;
            $i < $totalTests;
            $i++
        ) {


            /* =============================================
               TEST NAME
            ============================================= */

            $testName = isset(
                $testNames[$i]
            )
                ? cleanText(
                    $testNames[$i]
                )
                : "";


            /* =============================================
               RESULT
            ============================================= */

            $testResult = isset(
                $results[$i]
            )
                ? cleanText(
                    $results[$i]
                )
                : "";


            /* =============================================
               CATEGORY
            ============================================= */

            $testCategory = isset(
                $testCategories[$i]
            )
                ? cleanText(
                    $testCategories[$i]
                )
                : "";


            /* =============================================
               UNIT
            ============================================= */

            $unit = isset(
                $units[$i]
            )
                ? cleanText(
                    $units[$i]
                )
                : "";


            /* =============================================
               REFERENCE RANGE
            ============================================= */

            $referenceRange = isset(
                $referenceRanges[$i]
            )
                ? cleanText(
                    $referenceRanges[$i]
                )
                : "";


            /* =============================================
               SKIP COMPLETELY EMPTY TEST
            ============================================= */

            if (
                $testName === ""
            ) {

                continue;

            }


            /* =============================================
               VALID CATEGORIES
            ============================================= */

            $validCategories = array(

                "CBC",

                "HEMATOLOGY",

                "LIPID",

                "CHEMISTRY",

                "RADIOLOGY",

                "STOOL",

                "URINALYSIS",

                "OTHERS"

            );


            if (
                !in_array(
                    $testCategory,
                    $validCategories
                )
            ) {

                throw new Exception(
                    "Invalid laboratory category for " .
                    $testName .
                    "."
                );

            }


            /* =============================================
               FACILITY CATEGORY
            ============================================= */

            $facilityCategory = $testCategory;


            /*
             * CBC and Hematology belong to the
             * HEMATOLOGY facility.
             */

            if (
                $testCategory === "CBC" ||
                $testCategory === "HEMATOLOGY"
            ) {

                $facilityCategory = "HEMATOLOGY";

            }


            /*
             * Radiology has separate facilities
             * for X-ray and ECG.
             */

            if (
                $testName === "CHEST X-RAY"
            ) {

                $facilityCategory = "X_RAY";

            }

            elseif (
                $testName ===
                "ELECTROCARDIOGRAM (ECG)"
            ) {

                $facilityCategory = "ECG";

            }


            /* =============================================
               GET FACILITY
            ============================================= */

            $facilityData =
                getCategoryFacility(
                    $facilityCategory,
                    $facilityTypes,
                    $healthCareInstitutions
                );


            $facilityType =
                $facilityData[0];

            $healthCareInstitution =
                $facilityData[1];


            /* =============================================
               DEFAULT REMARKS
            ============================================= */

            $currentRemarks = "";


            /* =============================================
               CHEST X-RAY
            ============================================= */

            if (
                $testName ===
                "CHEST X-RAY"
            ) {

                /*
                 * X-ray does not use the normal result field.
                 *
                 * Findings + Impression are stored together
                 * inside the existing remarks column.
                 */

                if (
                    $cleanXrayFindings === "" &&
                    $cleanXrayImpression === ""
                ) {

                    continue;

                }


                $testResult =
                    "WITH FINDING";


                $referenceRange =
                    "";


                $currentRemarks =
                    "FINDINGS: " .
                    $cleanXrayFindings .
                    " | IMPRESSION: " .
                    $cleanXrayImpression;

            }


            /* =============================================
               ECG
            ============================================= */

            elseif (
                $testName ===
                "ELECTROCARDIOGRAM (ECG)"
            ) {

                /*
                 * ECG has no reference range.
                 */

                $referenceRange = "";


                /*
                 * ECG WITH FINDING
                 */

                if (
                    $testResult ===
                    "WITH FINDING"
                ) {

                    $currentRemarks =
                        $cleanECGFinding;

                }


                /*
                 * ECG ESSENTIALLY NORMAL
                 */

                elseif (
                    $testResult ===
                    "ESSENTIALLY NORMAL"
                ) {

                    $currentRemarks = "";

                }


                /*
                 * ECG not selected
                 */

                elseif (
                    $testResult === ""
                ) {

                    continue;

                }

            }


            /* =============================================
               NORMAL LABORATORY TEST
            ============================================= */

            else {

                /*
                 * Skip blank result.
                 *
                 * This allows users to leave tests blank
                 * when they were not performed.
                 */

                if (
                    $testResult === ""
                ) {

                    continue;

                }

            }


            /* =============================================
               INSERT TEST
            ============================================= */

            $insertStmt->bind_param(

                "issssssss",

                $patientId,

                $testDate,

                $testName,

                $testResult,

                $unit,

                $referenceRange,

                $currentRemarks,

                $facilityType,

                $healthCareInstitution

            );


            if (
                !$insertStmt->execute()
            ) {

                throw new Exception(
                    "Failed to save laboratory test: " .
                    $testName .
                    "<br><br>" .
                    $insertStmt->error
                );

            }


            $savedCount++;

        }

    }


    /* =====================================================
       SAVE OTHER LABORATORY TESTS
    ===================================================== */

    if (is_array($otherTestNames)) {

        $totalOtherTests =
            count($otherTestNames);

        for (
            $i = 0;
            $i < $totalOtherTests;
            $i++
        ) {


            /* =============================================
               OTHER TEST NAME
            ============================================= */

            $otherName = isset(
                $otherTestNames[$i]
            )
                ? cleanText(
                    $otherTestNames[$i]
                )
                : "";


            /* =============================================
               OTHER RESULT
            ============================================= */

            $otherResult = isset(
                $otherResults[$i]
            )
                ? cleanText(
                    $otherResults[$i]
                )
                : "";


            /* =============================================
               OTHER UNIT
            ============================================= */

            $otherUnit = isset(
                $otherUnits[$i]
            )
                ? cleanText(
                    $otherUnits[$i]
                )
                : "";


            /* =============================================
               OTHER REFERENCE RANGE
            ============================================= */

            $otherReferenceRange =
                isset(
                    $otherReferenceRanges[$i]
                )
                    ? cleanText(
                        $otherReferenceRanges[$i]
                    )
                    : "";


            /* =============================================
               COMPLETELY BLANK ROW
            ============================================= */

            if (
                $otherName === "" &&
                $otherResult === ""
            ) {

                continue;

            }


            /* =============================================
               RESULT WITHOUT TEST NAME
            ============================================= */

            if (
                $otherResult !== "" &&
                $otherName === ""
            ) {

                throw new Exception(
                    "An OTHER laboratory result was entered without a laboratory test name."
                );

            }


            /* =============================================
               TEST NAME WITHOUT RESULT
            ============================================= */

            if (
                $otherName !== "" &&
                $otherResult === ""
            ) {

                continue;

            }


            /* =============================================
               OTHER FACILITY
            ============================================= */

            $facilityData =
                getCategoryFacility(
                    "OTHERS",
                    $facilityTypes,
                    $healthCareInstitutions
                );


            $facilityType =
                $facilityData[0];

            $healthCareInstitution =
                $facilityData[1];


            /* =============================================
               OTHER REMARKS
            ============================================= */

            $otherRemarks = "";


            /* =============================================
               INSERT OTHER TEST
            ============================================= */

            $insertStmt->bind_param(

                "issssssss",

                $patientId,

                $testDate,

                $otherName,

                $otherResult,

                $otherUnit,

                $otherReferenceRange,

                $otherRemarks,

                $facilityType,

                $healthCareInstitution

            );


            if (
                !$insertStmt->execute()
            ) {

                throw new Exception(
                    "Failed to save OTHER laboratory test: " .
                    $otherName .
                    "<br><br>" .
                    $insertStmt->error
                );

            }


            $savedCount++;

        }

    }


    /* =====================================================
       CHECK IF SOMETHING WAS SAVED
    ===================================================== */

    if ($savedCount === 0) {

        throw new Exception(
            "No laboratory result was entered."
        );

    }


    /* =====================================================
       CLOSE STATEMENT
    ===================================================== */

    $insertStmt->close();


    /* =====================================================
       COMMIT
    ===================================================== */

    $conn->commit();


    /* =====================================================
       ACTIVITY LOG
    ===================================================== */

    $activityDescription =
        "Added laboratory record: " .
        $patientDisplayId .
        " - " .
        $patientFullName .
        " - " .
        $savedCount .
        " test(s) - " .
        $testDate;


    logActivity(
        $conn,
        "LABORATORY",
        $activityDescription
    );


    /* =====================================================
       REDIRECT
    ===================================================== */

    header(
        "Location: index.php?id=" .
        $patientId .
        "&saved=1&count=" .
        $savedCount
    );

    exit;

}


catch (Exception $e) {


    /* =====================================================
       ROLLBACK
    ===================================================== */

    $conn->rollback();

    $conn->close();


    /* =====================================================
       ERROR
    ===================================================== */

    die(

        "Unable to save laboratory results." .
        "<br><br>" .
        htmlspecialchars(
            $e->getMessage()
        )

    );

}

?>