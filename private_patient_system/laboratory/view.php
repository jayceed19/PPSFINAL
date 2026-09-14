<?php

require_once "../config/database.php";
require_once "../config/auth.php";


/* =========================================================
   GET PARAMETERS
========================================================= */

$patientId = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

$testDate = isset($_GET['date'])
    ? trim($_GET['date'])
    : "";

$currentPage = isset($_GET['page'])
    ? intval($_GET['page'])
    : 1;


if ($patientId <= 0) {
    die("Invalid patient ID.");
}


if ($testDate === "") {
    die("Laboratory date is required.");
}


if ($currentPage < 1) {
    $currentPage = 1;
}


/* =========================================================
   VALIDATE DATE
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
   GET PATIENT
========================================================= */

$patientSql = "
    SELECT
        id,
        patient_id,
        last_name,
        first_name,
        middle_name,
        birthdate,
        sex,
        address,
        contact_no
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

$patientResult =
    $patientStmt->get_result();

$patient =
    $patientResult->fetch_assoc();

$patientStmt->close();


if (!$patient) {
    die("Patient not found.");
}


/* =========================================================
   PATIENT NAME
========================================================= */

$fullName =
    strtoupper(
        trim($patient['last_name'])
        . ", "
        . trim($patient['first_name'])
    );

if (!empty($patient['middle_name'])) {

    $fullName .=
        " " .
        strtoupper(
            trim($patient['middle_name'])
        );

}


/* =========================================================
   AGE
========================================================= */

$age = "-";

if (!empty($patient['birthdate'])) {

    try {

        $birthDate =
            new DateTime(
                $patient['birthdate']
            );

        $today =
            new DateTime();

        $age =
            $birthDate->diff(
                $today
            )->y;

    } catch (Exception $e) {

        $age = "-";

    }

}


/* =========================================================
   GET LABORATORY RESULTS
========================================================= */

$labSql = "
    SELECT
        id,
        test_name,
        result,
        unit,
        reference_range,
        remarks,
        facility_type,
        health_care_institution
    FROM laboratory
    WHERE patient_id = ?
      AND test_date = ?
      AND TRIM(result) <> ''
    ORDER BY id ASC
";

$labStmt =
    $conn->prepare($labSql);

if (!$labStmt) {
    die(
        "Database error: " .
        $conn->error
    );
}

$labStmt->bind_param(
    "is",
    $patientId,
    $testDate
);

$labStmt->execute();

$labResult =
    $labStmt->get_result();

$laboratoryResults =
    array();

while (
    $row =
    $labResult->fetch_assoc()
) {

    $laboratoryResults[] =
        $row;

}

$labStmt->close();


/* =========================================================
   CHECK RESULTS
========================================================= */

if (
    count($laboratoryResults) === 0
) {

    die(
        "No laboratory results found for this date."
    );

}


/* =========================================================
   LABORATORY CATEGORIES
========================================================= */

$categories = array(

    "CHEMISTRY" => array(

        "RANDOM BLOOD SUGAR",
        "FASTING BLOOD SUGAR",
        "CREATININE",
        "HBA1C",
        "VLDL",
        "URIC ACID",
        "SGPT",
        "AST / SGOT",
        "ORAL GLUCOSE TOLERANCE TEST"

    ),

    "LIPID" => array(

        "TOTAL CHOLESTEROL",
        "TRIGLYCERIDES",
        "HDL CHOLESTEROL",
        "LDL CHOLESTEROL"

    ),

    "CBC" => array(

        "HEMOGLOBIN",
        "HEMATOCRIT",
        "RED BLOOD CELL COUNT",
        "WHITE BLOOD CELL COUNT",
        "PLATELET COUNT",
        "MCV",
        "MCH",
        "MCHC",
        "RDW",
        "NEUTROPHILS",
        "LYMPHOCYTES",
        "MONOCYTES",
        "EOSINOPHILS",
        "BASOPHILS"

    ),

    "URINALYSIS" => array(

        "COLOR",
        "APPEARANCE",
        "SPECIFIC GRAVITY",
        "PH",
        "ALBUMIN",
        "GLUCOSE",
        "KETONES",
        "BLOOD",
        "NITRITE",
        "LEUKOCYTE ESTERASE",
        "RBC",
        "WBC",
        "EPITHELIAL CELLS",
        "BACTERIA",
        "MUCUS",
        "PUS CELLS"

    ),

    "RADIOLOGY" => array(

        "CHEST X-RAY",
        "ELECTROCARDIOGRAM (ECG)"

    ),

    "STOOL" => array(

        "FECAL OCCULT BLOOD",
        "FECALYSIS"

    ),

    "OTHER TESTS" => array(

        "PAP SMEAR",
        "PPD TEST (TUBERCULOSIS)",
        "SPUTUM MICROSCOPY"

    )

);


/* =========================================================
   CATEGORY DISPLAY NAMES
========================================================= */

$categoryDisplayNames = array(

    "CHEMISTRY" =>
        "Clinical Chemistry",

    "LIPID" =>
        "Lipid Profile",

    "CBC" =>
        "Hematology",

    "URINALYSIS" =>
        "Urinalysis",

    "RADIOLOGY" =>
        "Radiology",

    "STOOL" =>
        "Stool",

    "OTHER TESTS" =>
        "Others"

);


/* =========================================================
   REFERENCE RANGES
========================================================= */

$referenceRanges = array(

    /* =====================================================
       CLINICAL CHEMISTRY
    ====================================================== */

    "FASTING BLOOD SUGAR" =>
        "70-105 mg/dL",

    "RANDOM BLOOD SUGAR" =>
        "70-140 mg/dL",

    "CREATININE" =>
        "Male: 0.74-1.35 mg/dL | Female: 0.59-1.04 mg/dL",

    "URIC ACID" =>
        "2.5-7.7 mg/dL",

    "SGPT / ALT" =>
        "0-40 U/L",

    "AST / SGOT" =>
        "0-40 U/L",

    "HBA1C" =>
        "<5.7%",

    "ORAL GLUCOSE TOLERANCE TEST" =>
        "See OGTT parameters",

    "VLDL" =>
        "10-40 mg/dL",


    /* =====================================================
       LIPID PROFILE
    ====================================================== */

    "TOTAL CHOLESTEROL" =>
        "100-200 mg/dL",

    "TRIGLYCERIDES" =>
        "44-148 mg/dL",

    "HDL" =>
        "30-70 mg/dL",

    "LDL" =>
        "Less than 130 mg/dL",


    /* =====================================================
       HEMATOLOGY
    ====================================================== */

    "HEMOGLOBIN" =>
        "Male: 140-180 g/L | Female: 120-160 g/L",

    "HEMATOCRIT" =>
        "Male: 0.40-0.54 | Female: 0.37-0.47",

    "WBC COUNT" =>
        "5-10 x10^9/L",

    "SEGMENTER" =>
        "0.50-0.70",

    "LYMPHOCYTE" =>
        "0.20-0.40",

    "EOSINOPHIL" =>
        "0.01-0.05",

    "MONOCYTE" =>
        "0.02-0.06",

    "BASOPHIL" =>
        "0.00-0.01",

    "PLATELET COUNT" =>
        "Male: 150-450 x10^9/L | Female: 150-450 x10^9/L",


    /* =====================================================
       URINALYSIS
    ====================================================== */

    "COLOR" =>
        "Light yellow",

    "APPEARANCE" =>
        "Clear",

    "PH" =>
        "5.0-8.0",

    "SPECIFIC GRAVITY" =>
        "1.005-1.030",

    "ALBUMIN" =>
        "Negative",

    "GLUCOSE" =>
        "Negative",

    "SUGAR" =>
        "Negative",

    "KETONES" =>
        "Negative",

    "BLOOD" =>
        "Negative",

    "NITRITE" =>
        "Negative",

    "LEUKOCYTE ESTERASE" =>
        "Negative",

    "EPITHELIAL CELLS" =>
        "Few",

    "MUCUS THREADS" =>
        "Few",

    "MUCUS" =>
        "Few",

    "RBC CELLS" =>
        "0-1 /HPF",

    "RBC" =>
        "0-1 /HPF",

    "PUS CELLS" =>
        "0-5 /HPF",

    "WBC" =>
        "0-5 /HPF",

    "BACTERIA" =>
        "Few",

    "AMORPHOUS URATES" =>
        "None",

    "AMORPHOUS PHOSPHATES" =>
        "None",


    /* =====================================================
       STOOL
    ====================================================== */

    "FECAL OCCULT BLOOD" =>
        "Negative",

    "FECALYSIS" =>
        "Negative / Normal",


    /* =====================================================
       OTHERS
    ====================================================== */

    "PAP SMEAR" =>
        "Negative for intraepithelial lesion or malignancy",

    "PPD TEST (TUBERCULOSIS)" =>
        "Negative",

    "PPD TEST" =>
        "Negative",

    "SPUTUM MICROSCOPY" =>
        "Negative"
);


/* =========================================================
   REFERENCE RANGE ALIAS
========================================================= */

$referenceAliases = array(

    "SGPT" =>
        "SGPT / ALT",

    "HDL CHOLESTEROL" =>
        "HDL",

    "LDL CHOLESTEROL" =>
        "LDL",

    "WHITE BLOOD CELL COUNT" =>
        "WBC COUNT",

    "NEUTROPHILS" =>
        "SEGMENTER",

    "LYMPHOCYTES" =>
        "LYMPHOCYTE",

    "MONOCYTES" =>
        "MONOCYTE",

    "EOSINOPHILS" =>
        "EOSINOPHIL",

    "BASOPHILS" =>
        "BASOPHIL",

    "WBC" =>
        "WBC COUNT",

    "RBC" =>
        "RBC CELLS",

    "GLUCOSE" =>
        "SUGAR",

    "MUCUS" =>
        "MUCUS THREADS"
);


/* =========================================================
   GROUP RESULTS BY CATEGORY
========================================================= */

$groupedResults =
    array();


foreach (
    $categories
    as $categoryName => $testList
) {

    $groupedResults[
        $categoryName
    ] = array();


    foreach (
        $laboratoryResults
        as $lab
    ) {

        $testName =
            strtoupper(
                trim(
                    $lab['test_name']
                )
            );


        if (
            in_array(
                $testName,
                $testList
            )
        ) {

            $groupedResults[
                $categoryName
            ][] =
                $lab;

        }

    }

}


/* =========================================================
   CUSTOM / OTHER TESTS
========================================================= */

$knownTests =
    array();


foreach (
    $categories
    as $testList
) {

    foreach (
        $testList
        as $testName
    ) {

        $knownTests[] =
            strtoupper(
                trim($testName)
            );

    }

}


foreach (
    $laboratoryResults
    as $lab
) {

    $testName =
        strtoupper(
            trim(
                $lab['test_name']
            )
        );


    if (
        !in_array(
            $testName,
            $knownTests
        )
    ) {

        $groupedResults[
            "OTHER TESTS"
        ][] =
            $lab;

    }

}


/* =========================================================
   REMOVE EMPTY CATEGORIES
========================================================= */

$activeCategories =
    array();


foreach (
    $groupedResults
    as $categoryName =>
    $categoryResults
) {

    if (
        count(
            $categoryResults
        ) > 0
    ) {

        $activeCategories[
            $categoryName
        ] =
            $categoryResults;

    }

}


/* =========================================================
   CHECK ACTIVE CATEGORIES
========================================================= */

$totalPages =
    count(
        $activeCategories
    );


if ($totalPages === 0) {

    die(
        "No laboratory results found for this date."
    );

}


/* =========================================================
   VALIDATE CURRENT PAGE
========================================================= */

if (
    $currentPage > $totalPages
) {

    $currentPage =
        $totalPages;

}


/* =========================================================
   GET CURRENT CATEGORY
========================================================= */

$activeCategoryNames =
    array_keys(
        $activeCategories
    );

$currentCategoryName =
    $activeCategoryNames[
        $currentPage - 1
    ];

$currentCategoryResults =
    $activeCategories[
        $currentCategoryName
    ];


/* =========================================================
   GET FACILITY INFORMATION
========================================================= */

$facilityLabels =
    array();


foreach (
    $currentCategoryResults
    as $categoryResult
) {

    $currentFacilityType = "";
    $currentInstitution = "";


    if (
        isset(
            $categoryResult[
                'facility_type'
            ]
        )
    ) {

        $currentFacilityType =
            strtoupper(
                trim(
                    $categoryResult[
                        'facility_type'
                    ]
                )
            );

    }


    if (
        isset(
            $categoryResult[
                'health_care_institution'
            ]
        )
    ) {

        $currentInstitution =
            trim(
                $categoryResult[
                    'health_care_institution'
                ]
            );

    }


    if (
        $currentFacilityType ===
        "WITHIN_FACILITY"
    ) {

        $label =
            "Within Facility: DCMD Clinic";

    }

    elseif (
        $currentFacilityType ===
        "ACCREDITED_FACILITY"
    ) {

        if (
            $currentInstitution !== ""
        ) {

            $label =
                "Accredited Diagnostic Facilities: "
                . $currentInstitution;

        } else {

            $label =
                "Accredited Diagnostic Facilities";

        }

    }

    else {

        $label = "";

    }


    if (
        $label !== ""
        &&
        !in_array(
            $label,
            $facilityLabels
        )
    ) {

        $facilityLabels[] =
            $label;

    }

}


/* =========================================================
   DISPLAY FACILITY
========================================================= */

$facilityDisplay = "";


if (
    count($facilityLabels) > 0
) {

    $facilityDisplay =
        implode(
            " | ",
            $facilityLabels
        );

}


/* =========================================================
   DISPLAY CATEGORY
========================================================= */

$displayCategoryName =
    isset(
        $categoryDisplayNames[
            $currentCategoryName
        ]
    )
        ? $categoryDisplayNames[
            $currentCategoryName
        ]
        : $currentCategoryName;


/* =========================================================
   PREVIOUS / NEXT PAGE
========================================================= */

$previousPage =
    $currentPage - 1;

$nextPage =
    $currentPage + 1;


/* =========================================================
   FORMAT DATE
========================================================= */

$formattedDate =
    date(
        "F d, Y",
        strtotime(
            $testDate
        )
    );


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle =
    "Laboratory Results";

$pageSubtitle =
    "Laboratory Results";

$basePath =
    "../";

$activePage =
    "laboratory";


/* =========================================================
   SHARED HEADER
========================================================= */

include "../includes/header.php";


/* =========================================================
   SHARED NAVIGATION
========================================================= */

include "../includes/navigation.php";

?>

<style>

/* =========================================================
   MAIN PAGE
========================================================= */

.lab-record-page {
    width: 100%;
    max-width: 1180px;
    min-height: 720px;
    margin: 24px auto 50px;
    padding: 0 20px;
    box-sizing: border-box;
}


/* =========================================================
   PAGE TOP
========================================================= */

.lab-page-top {
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 14px;
}

.lab-page-heading h2 {
    margin: 0;
    color: #172033;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: -0.3px;
}

.lab-page-heading p {
    margin: 4px 0 0;
    color: #7a8699;
    font-size: 12px;
}

.lab-page-actions {
    display: flex;
    gap: 8px;
}

.lab-top-btn {
    height: 34px;
    padding: 0 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid #d1d8e2;
    box-sizing: border-box;
}

.lab-back-btn {
    background: #ffffff;
    color: #475569;
}

.lab-back-btn:hover {
    background: #f4f7fa;
    border-color: #bfc9d6;
}


/* =========================================================
   MAIN CARD
========================================================= */

.lab-record-card {
    width: 100%;
    height: 650px;
    background: #ffffff;
    border: 1px solid #d9e0e8;
    border-radius: 8px;
    overflow: hidden;
    box-shadow:
        0 2px 8px
        rgba(
            15,
            23,
            42,
            0.04
        );
    display: flex;
    flex-direction: column;
}


/* =========================================================
   RECORD HEADER
========================================================= */

.lab-record-header {
    height: 65px;
    flex-shrink: 0;
    padding: 0 22px;
    background: #f8fafc;
    border-bottom: 1px solid #dfe5ec;
    display: flex;
    align-items: center;
    box-sizing: border-box;
}

.record-header-main {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.record-title {
    color: #172033;
    font-size: 16px;
    font-weight: 700;
    margin: 0;
}

.record-subtitle {
    margin-top: 4px;
    color: #7a8699;
    font-size: 11px;
}

.record-status {
    height: 26px;
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    border-radius: 5px;
    background: #edf6ff;
    color: #145b91;
    border: 1px solid #d1e5f5;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}


/* =========================================================
   PATIENT INFORMATION
========================================================= */

.patient-information {
    height: 105px;
    flex-shrink: 0;
    padding: 14px 22px;
    border-bottom: 1px solid #e3e8ee;
    box-sizing: border-box;
}

.patient-info-title {
    margin-bottom: 10px;
    color: #7b8798;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.patient-grid {
    display: grid;
    grid-template-columns:
        2fr
        0.85fr
        0.85fr
        2.4fr
        1.35fr;
    gap: 0;
}

.info-item {
    min-width: 0;
    height: 48px;
    padding: 0 16px;
    border-right: 1px solid #e5e9ee;
    box-sizing: border-box;
}

.info-item:first-child {
    padding-left: 0;
}

.info-item:last-child {
    padding-right: 0;
    border-right: none;
}

.info-label {
    display: block;
    margin-bottom: 4px;
    color: #98a1af;
    font-size: 8px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    color: #263244;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.35;
    word-break: break-word;
}


/* =========================================================
   CATEGORY NAVIGATION
========================================================= */

.category-navigation {
    height: 50px;
    flex-shrink: 0;
    padding: 0 22px;
    background: #ffffff;
    border-bottom: 1px solid #dfe5ec;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    box-sizing: border-box;
}

.category-position {
    color: #8994a4;
    font-size: 11px;
    font-weight: 600;
}

.category-position strong {
    color: #334155;
    font-weight: 800;
}

.category-nav-buttons {
    display: flex;
    align-items: center;
    gap: 7px;
}

.category-nav-btn {
    height: 30px;
    min-width: 88px;
    padding: 0 12px;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    background: #ffffff;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
    box-sizing: border-box;
    cursor: pointer;
    transition: none;
}

.category-nav-btn:hover {
    background: #f5f8fb;
    border-color: #b9c5d3;
    color: #1e293b;
}

.category-nav-btn.disabled {
    opacity: 0.4;
    pointer-events: none;
}


/* =========================================================
   CATEGORY AREA
========================================================= */

.category-section {
    height: 376px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.category-header {
    min-height: 44px;
    flex-shrink: 0;
    padding: 8px 22px;
    background: #f1f5f8;
    border-bottom: 1px solid #dce3ea;
    color: #344256;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.7px;
    text-transform: uppercase;
    box-sizing: border-box;
}

.category-title {
    min-width: 0;
}

.category-facility {
    flex-shrink: 0;
    max-width: 55%;
    padding: 5px 9px;
    border-radius: 4px;
    background: #ffffff;
    border: 1px solid #d3dce6;
    color: #526174;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0;
    text-transform: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


/* =========================================================
   TABLE
========================================================= */

.table-container {
    width: 100%;
    height: 332px;
    overflow-y: auto;
    overflow-x: hidden;
}

.results-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.results-table thead {
    position: sticky;
    top: 0;
    z-index: 2;
}

.results-table thead th {
    height: 38px;
    padding: 0 14px;
    background: #fafbfc;
    color: #7b8798;
    border-bottom: 1px solid #e3e8ee;
    text-align: left;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    white-space: nowrap;
    box-sizing: border-box;
}

.results-table th:nth-child(1) {
    width: 23%;
}

.results-table th:nth-child(2) {
    width: 15%;
}

.results-table th:nth-child(3) {
    width: 22%;
}

.results-table th:nth-child(4) {
    width: 27%;
}

.results-table th:nth-child(5) {
    width: 13%;
}

.results-table tbody tr {
    min-height: 45px;
    border-bottom: 1px solid #edf0f3;
}

.results-table tbody tr:hover {
    background: #f8fafc;
}

.results-table tbody tr:last-child {
    border-bottom: none;
}


/* =========================================================
   TABLE BODY CONTENT
========================================================= */

.results-table td {
    padding: 8px 14px;
    color: #374151;
    font-size: 12px;
    vertical-align: middle;
    box-sizing: border-box;
}

.test-name {
    color: #263244;
    font-weight: 700;
    font-size: 12px;
}

.result-value {
    color: #111827;
    font-weight: 700;
    font-size: 12px;
}

.result-badge {
    display: inline-block;
    min-width: 55px;
    color: #1f2937;
}

.reference-value {
    color: #667386;
    line-height: 1.35;
    font-size: 11px;
}

.remarks-value {
    color: #596579;
    line-height: 1.45;
    word-break: break-word;
    font-size: 11px;
}


/* =========================================================
   SPECIAL RADIOLOGY DISPLAY
========================================================= */

.radiology-details {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.radiology-line {
    line-height: 1.45;
}

.radiology-label {
    color: #7b8798;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-right: 5px;
}

.radiology-text {
    color: #596579;
    font-size: 11px;
}


/* =========================================================
   ACTION BUTTONS
========================================================= */

.action-buttons {
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.edit-result-btn,
.delete-result-btn {
    height: 28px;
    padding: 0 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    box-sizing: border-box;
}

.edit-result-btn {
    background: #ffffff;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.edit-result-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.delete-result-btn {
    background: #fff8f7;
    color: #b42318;
    border: 1px solid #f0c9c5;
    cursor: pointer;
}

.delete-result-btn:hover {
    background: #fceeed;
    border-color: #e4aaa5;
}


/* =========================================================
   RECORD FOOTER
========================================================= */

.record-footer {
    height: 54px;
    flex-shrink: 0;
    padding: 0 22px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    box-sizing: border-box;
}

.footer-note {
    color: #7b8798;
    font-size: 10px;
}

.footer-count {
    color: #344256;
    font-size: 10px;
    font-weight: 700;
}


/* =========================================================
   BOTTOM ACTION
========================================================= */

.bottom-actions {
    height: 42px;
    display: flex;
    justify-content: flex-end;
    margin-top: 13px;
}

.add-new-btn {
    height: 36px;
    padding: 0 15px;
    background: #145f91;
    color: #ffffff;
    border: 1px solid #145f91;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    box-sizing: border-box;
}

.add-new-btn:hover {
    background: #0f4f79;
    border-color: #0f4f79;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 850px) {

    .lab-record-page {
        padding: 0 14px;
        margin-top: 20px;
    }

    .lab-record-card {
        height: 690px;
    }

    .patient-information {
        height: 145px;
    }

    .patient-grid {
        grid-template-columns:
            1fr 1fr;
        gap: 14px 0;
    }

    .info-item {
        height: 42px;
        padding: 0 12px;
        border-right: none;
    }

    .info-item:first-child {
        padding-left: 0;
    }

    .info-item:last-child {
        padding-right: 0;
    }

    .category-section {
        height: 410px;
    }

    .table-container {
        height: 366px;
        overflow-x: auto;
    }

    .results-table {
        min-width: 760px;
    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 550px) {

    .lab-record-page {
        padding: 0 10px;
    }

    .lab-page-top {
        height: auto;
        min-height: 70px;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
    }

    .lab-page-actions {
        width: 100%;
    }

    .lab-top-btn {
        width: 100%;
    }

    .lab-record-card {
        height: 735px;
    }

    .lab-record-header {
        height: 75px;
        padding: 0 16px;
    }

    .record-header-main {
        align-items: flex-start;
        flex-direction: column;
        justify-content: center;
    }

    .record-status {
        display: none;
    }

    .patient-information {
        height: 190px;
        padding: 14px 16px;
    }

    .patient-grid {
        grid-template-columns: 1fr;
        gap: 11px;
    }

    .info-item {
        height: auto;
        min-height: 30px;
        padding: 0 !important;
    }

    .category-navigation {
        height: 70px;
        padding: 0 16px;
        flex-direction: column;
        align-items: stretch;
        justify-content: center;
    }

    .category-nav-buttons {
        width: 100%;
    }

    .category-nav-btn {
        flex: 1;
    }

    .category-section {
        height: 345px;
    }

    .category-header {
        min-height: 50px;
        padding: 8px 16px;
    }

    .category-facility {
        max-width: 55%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-container {
        height: 295px;
    }

    .record-footer {
        height: 55px;
        padding: 0 16px;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        gap: 4px;
    }

    .bottom-actions {
        justify-content: stretch;
    }

    .add-new-btn {
        width: 100%;
    }

}


/* =========================================================
   PRINT
========================================================= */

@media print {

    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    body {
        background: #ffffff !important;
    }

    .header,
    .navigation,
    .footer,
    .lab-page-top,
    .category-navigation,
    .bottom-actions,
    .action-column,
    .action-buttons {
        display: none !important;
    }

    .lab-record-page {
        width: auto;
        max-width: none;
        min-height: 0;
        margin: 0;
        padding: 0;
    }

    .lab-record-card {
        width: 100%;
        height: auto;
        border: none;
        box-shadow: none;
        overflow: visible;
        display: block;
    }

    .lab-record-header {
        height: auto;
        padding: 0 0 12px;
        background: #ffffff !important;
        border-bottom: 2px solid #172033;
    }

    .patient-information {
        height: auto;
        padding: 14px 0;
    }

    .category-section {
        height: auto;
        overflow: visible;
    }

    .category-header {
        height: auto;
        padding: 8px 0;
        background: #eeeeee !important;
        border-bottom: 1px solid #333333;
    }

    .table-container {
        height: auto;
        overflow: visible;
    }

    .results-table {
        min-width: 0;
    }

    .results-table thead th {
        background: #eeeeee !important;
    }

    .record-footer {
        height: auto;
        padding: 12px 0;
    }

}

</style>


<div class="lab-record-page">


    <!-- =====================================================
         PAGE TOP
    ====================================================== -->

    <div class="lab-page-top">

        <div class="lab-page-heading">

            <h2>
                Laboratory Results
            </h2>

            <p>
                Patient laboratory records on file
            </p>

        </div>


        <div class="lab-page-actions">

            <a
                href="index.php?id=<?php echo $patientId; ?>"
                class="lab-top-btn lab-back-btn"
            >
                ← Back to Laboratory
            </a>

        </div>

    </div>


    <!-- =====================================================
         MAIN CARD
    ====================================================== -->

    <div class="lab-record-card">


        <!-- =================================================
             RECORD HEADER
        ================================================== -->

        <div class="lab-record-header">

            <div class="record-header-main">

                <div>

                    <div class="record-title">
                        Laboratory Examination Record
                    </div>

                    <div class="record-subtitle">
                        Laboratory results submitted for patient record
                    </div>

                </div>

                <div class="record-status">
                    Record on File
                </div>

            </div>

        </div>


        <!-- =================================================
             PATIENT INFORMATION
        ================================================== -->

        <div class="patient-information">

            <div class="patient-info-title">
                Patient Information
            </div>

            <div class="patient-grid">


                <div class="info-item">

                    <span class="info-label">
                        Patient Name
                    </span>

                    <div class="info-value">

                        <?php
                        echo htmlspecialchars(
                            $fullName
                        );
                        ?>

                    </div>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Patient ID
                    </span>

                    <div class="info-value">

                        <?php
                        echo htmlspecialchars(
                            $patient['patient_id']
                        );
                        ?>

                    </div>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Age / Sex
                    </span>

                    <div class="info-value">

                        <?php
                        echo htmlspecialchars(
                            $age
                        );
                        ?>

                        /

                        <?php

                        echo !empty(
                            $patient['sex']
                        )
                            ? htmlspecialchars(
                                strtoupper(
                                    $patient['sex']
                                )
                            )
                            : "-";

                        ?>

                    </div>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Address
                    </span>

                    <div class="info-value">

                        <?php

                        echo !empty(
                            $patient['address']
                        )
                            ? htmlspecialchars(
                                $patient['address']
                            )
                            : "-";

                        ?>

                    </div>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Laboratory Date
                    </span>

                    <div class="info-value">

                        <?php
                        echo htmlspecialchars(
                            $formattedDate
                        );
                        ?>

                    </div>

                </div>


            </div>

        </div>


        <!-- =================================================
             CATEGORY NAVIGATION
        ================================================== -->

        <div class="category-navigation">


            <div class="category-position">

                Category

                <strong>
                    <?php
                    echo $currentPage;
                    ?>
                </strong>

                of

                <strong>
                    <?php
                    echo $totalPages;
                    ?>
                </strong>

            </div>


            <div class="category-nav-buttons">


                <?php if ($previousPage >= 1): ?>

                    <a
                        href="view.php?id=<?php
                            echo $patientId;
                        ?>&date=<?php
                            echo urlencode(
                                $testDate
                            );
                        ?>&page=<?php
                            echo $previousPage;
                        ?>"
                        class="category-nav-btn category-scroll-link"
                    >
                        ← Previous
                    </a>

                <?php else: ?>

                    <span class="category-nav-btn disabled">
                        ← Previous
                    </span>

                <?php endif; ?>


                <?php if (
                    $nextPage <= $totalPages
                ): ?>

                    <a
                        href="view.php?id=<?php
                            echo $patientId;
                        ?>&date=<?php
                            echo urlencode(
                                $testDate
                            );
                        ?>&page=<?php
                            echo $nextPage;
                        ?>"
                        class="category-nav-btn category-scroll-link"
                    >
                        Next →
                    </a>

                <?php else: ?>

                    <span class="category-nav-btn disabled">
                        Next →
                    </span>

                <?php endif; ?>


            </div>

        </div>


        <!-- =================================================
             CURRENT CATEGORY
        ================================================== -->

        <div class="category-section">


            <div class="category-header">


                <div class="category-title">

                    <?php
                    echo htmlspecialchars(
                        $displayCategoryName
                    );
                    ?>

                </div>


                <?php if (
                    $facilityDisplay !== ""
                ): ?>

                    <div
                        class="category-facility"
                        title="<?php
                            echo htmlspecialchars(
                                $facilityDisplay
                            );
                        ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $facilityDisplay
                        );
                        ?>

                    </div>

                <?php endif; ?>


            </div>


            <div class="table-container">


                <table class="results-table">


                    <thead>

                        <tr>

                            <th>
                                Laboratory Test
                            </th>

                            <th>
                                Result
                            </th>

                            <th>
                                Reference Range
                            </th>

                            <th>
                                Remarks
                            </th>

                            <th class="action-column">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach (
                            $currentCategoryResults
                            as $lab
                        ): ?>


                            <?php

                            $testName =
                                strtoupper(
                                    trim(
                                        $lab['test_name']
                                    )
                                );


                            $remarks =
                                isset(
                                    $lab['remarks']
                                )
                                    ? trim(
                                        $lab['remarks']
                                    )
                                    : "";


                            /* =================================================
                               GET REFERENCE RANGE
                            ================================================= */

                            $referenceKey =
                                $testName;


                            if (
                                isset(
                                    $referenceAliases[
                                        $testName
                                    ]
                                )
                            ) {

                                $referenceKey =
                                    $referenceAliases[
                                        $testName
                                    ];

                            }


                            $displayReferenceRange = "";


                            /* First use saved database value */

                            if (
                                isset(
                                    $lab[
                                        'reference_range'
                                    ]
                                )
                                &&
                                trim(
                                    $lab[
                                        'reference_range'
                                    ]
                                ) !== ""
                            ) {

                                $displayReferenceRange =
                                    trim(
                                        $lab[
                                            'reference_range'
                                        ]
                                    );

                            }


                            /* If blank, use default reference range */

                            elseif (
                                isset(
                                    $referenceRanges[
                                        $referenceKey
                                    ]
                                )
                            ) {

                                $displayReferenceRange =
                                    $referenceRanges[
                                        $referenceKey
                                    ];

                            }


                            /* =================================================
                               CHEST X-RAY
                            ================================================= */

                            $xrayFindings = "";
                            $xrayImpression = "";


                            if (
                                $testName ===
                                "CHEST X-RAY"
                            ) {

                                $remarksParts =
                                    explode(
                                        "|",
                                        $remarks
                                    );


                                foreach (
                                    $remarksParts
                                    as $part
                                ) {

                                    $part =
                                        trim(
                                            $part
                                        );


                                    if (
                                        stripos(
                                            $part,
                                            "FINDINGS:"
                                        ) === 0
                                    ) {

                                        $xrayFindings =
                                            trim(
                                                substr(
                                                    $part,
                                                    strlen(
                                                        "FINDINGS:"
                                                    )
                                                )
                                            );

                                    }


                                    if (
                                        stripos(
                                            $part,
                                            "IMPRESSION:"
                                        ) === 0
                                    ) {

                                        $xrayImpression =
                                            trim(
                                                substr(
                                                    $part,
                                                    strlen(
                                                        "IMPRESSION:"
                                                    )
                                                )
                                            );

                                    }

                                }

                            }


                            /* =================================================
                               ECG
                            ================================================= */

                            $ecgFinding = "";


                            if (
                                $testName ===
                                "ELECTROCARDIOGRAM (ECG)"
                            ) {

                                $ecgFinding =
                                    $remarks;

                            }

                            ?>


                            <tr>


                                <td class="test-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $testName
                                    );
                                    ?>

                                </td>


                                <td class="result-value">


                                    <?php if (
                                        $testName ===
                                        "CHEST X-RAY"
                                    ): ?>


                                        <span
                                            class="result-badge"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $lab['result']
                                            );
                                            ?>

                                        </span>


                                    <?php elseif (
                                        $testName ===
                                        "ELECTROCARDIOGRAM (ECG)"
                                    ): ?>


                                        <span
                                            class="result-badge"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $lab['result']
                                            );
                                            ?>

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="result-badge"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $lab['result']
                                            );
                                            ?>

                                            <?php

                                            if (
                                                isset(
                                                    $lab['unit']
                                                )
                                                &&
                                                trim(
                                                    $lab['unit']
                                                ) !== ""
                                            ) {

                                                echo " " .
                                                    htmlspecialchars(
                                                        $lab['unit']
                                                    );

                                            }

                                            ?>

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <td class="reference-value">

                                    <?php

                                    if (
                                        $displayReferenceRange !== ""
                                    ) {

                                        echo nl2br(
                                            htmlspecialchars(
                                                $displayReferenceRange
                                            )
                                        );

                                    } else {

                                        echo "—";

                                    }

                                    ?>

                                </td>


                                <td class="remarks-value">


                                    <?php if (
                                        $testName ===
                                        "CHEST X-RAY"
                                    ): ?>


                                        <?php if (
                                            $xrayFindings !== ""
                                            ||
                                            $xrayImpression !== ""
                                        ): ?>


                                            <div
                                                class="radiology-details"
                                            >


                                                <?php if (
                                                    $xrayFindings !== ""
                                                ): ?>

                                                    <div
                                                        class="radiology-line"
                                                    >

                                                        <span
                                                            class="radiology-label"
                                                        >
                                                            Findings:
                                                        </span>

                                                        <span
                                                            class="radiology-text"
                                                        >

                                                            <?php
                                                            echo nl2br(
                                                                htmlspecialchars(
                                                                    $xrayFindings
                                                                )
                                                            );
                                                            ?>

                                                        </span>

                                                    </div>

                                                <?php endif; ?>


                                                <?php if (
                                                    $xrayImpression !== ""
                                                ): ?>

                                                    <div
                                                        class="radiology-line"
                                                    >

                                                        <span
                                                            class="radiology-label"
                                                        >
                                                            Impression:
                                                        </span>

                                                        <span
                                                            class="radiology-text"
                                                        >

                                                            <?php
                                                            echo nl2br(
                                                                htmlspecialchars(
                                                                    $xrayImpression
                                                                )
                                                            );
                                                            ?>

                                                        </span>

                                                    </div>

                                                <?php endif; ?>


                                            </div>


                                        <?php else: ?>

                                            —

                                        <?php endif; ?>


                                    <?php elseif (
                                        $testName ===
                                        "ELECTROCARDIOGRAM (ECG)"
                                    ): ?>


                                        <?php if (
                                            $ecgFinding !== ""
                                        ): ?>

                                            <div
                                                class="radiology-details"
                                            >

                                                <div
                                                    class="radiology-line"
                                                >

                                                    <span
                                                        class="radiology-label"
                                                    >
                                                        ECG Finding:
                                                    </span>

                                                    <span
                                                        class="radiology-text"
                                                    >

                                                        <?php
                                                        echo nl2br(
                                                            htmlspecialchars(
                                                                $ecgFinding
                                                            )
                                                        );
                                                        ?>

                                                    </span>

                                                </div>

                                            </div>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>


                                    <?php elseif (
                                        $remarks !== ""
                                    ): ?>


                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $remarks
                                            )
                                        );
                                        ?>


                                    <?php else: ?>

                                        —

                                    <?php endif; ?>


                                </td>


                                <td class="action-column">


                                    <div
                                        class="action-buttons"
                                    >


                                        <a
                                            href="edit.php?id=<?php
                                                echo (int)
                                                    $lab['id'];
                                            ?>"
                                            class="edit-result-btn"
                                        >
                                            Edit
                                        </a>


                                        <form
                                            method="POST"
                                            action="delete.php"
                                            style="margin: 0;"
                                            onsubmit="return confirmDelete();"
                                        >


                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php
                                                    echo (int)
                                                        $lab['id'];
                                                ?>"
                                            >


                                            <button
                                                type="submit"
                                                class="delete-result-btn"
                                            >
                                                Delete
                                            </button>


                                        </form>


                                    </div>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>


                </table>


            </div>


        </div>


        <!-- =================================================
             RECORD FOOTER
        ================================================== -->

        <div class="record-footer">


            <div class="footer-note">

                Laboratory results submitted for clinic record purposes.

            </div>


            <div class="footer-count">

                <?php
                echo count(
                    $currentCategoryResults
                );
                ?>

                Test(s) in this category

            </div>


        </div>


    </div>
</div>


<script>

/* =========================================================
   DELETE CONFIRMATION
========================================================= */

function confirmDelete() {

    return confirm(

        "Are you sure you want to delete this laboratory result?\n\n" +

        "This action cannot be undone."

    );

}


/* =========================================================
   STABLE CATEGORY NAVIGATION
========================================================= */

(function () {


    var isLoading = false;


    /* =====================================================
       LOAD CATEGORY
    ===================================================== */

    function loadCategory(
        url,
        addHistory
    ) {

        if (isLoading) {
            return;
        }


        isLoading = true;


        var pageScroll =
            window.pageYOffset ||
            document.documentElement.scrollTop ||
            0;


        var oldTableContainer =
            document.querySelector(
                ".table-container"
            );

        var tableScroll = 0;


        if (oldTableContainer) {

            tableScroll =
                oldTableContainer.scrollTop;

        }


        fetch(
            url,
            {
                method: "GET",

                headers: {
                    "X-Requested-With":
                        "XMLHttpRequest"
                },

                credentials:
                    "same-origin"
            }
        )


        .then(
            function (response) {

                if (!response.ok) {

                    throw new Error(
                        "Unable to load laboratory category."
                    );

                }

                return response.text();

            }
        )


        .then(
            function (html) {


                var parser =
                    new DOMParser();


                var newDocument =
                    parser.parseFromString(
                        html,
                        "text/html"
                    );


                var newNavigation =
                    newDocument.querySelector(
                        ".category-navigation"
                    );

                var newCategorySection =
                    newDocument.querySelector(
                        ".category-section"
                    );

                var currentNavigation =
                    document.querySelector(
                        ".category-navigation"
                    );

                var currentCategorySection =
                    document.querySelector(
                        ".category-section"
                    );


                if (
                    !newNavigation ||
                    !newCategorySection ||
                    !currentNavigation ||
                    !currentCategorySection
                ) {

                    throw new Error(
                        "Laboratory category content not found."
                    );

                }


                currentNavigation.replaceWith(
                    newNavigation
                );


                currentCategorySection.replaceWith(
                    newCategorySection
                );


                if (addHistory) {

                    history.pushState(
                        {
                            laboratoryCategory:
                                true
                        },
                        "",
                        url
                    );

                }


                window.scrollTo(
                    0,
                    pageScroll
                );


                var newTableContainer =
                    document.querySelector(
                        ".table-container"
                    );


                if (newTableContainer) {

                    newTableContainer.scrollTop =
                        tableScroll;

                }


                bindCategoryLinks();

                isLoading = false;

            }
        )


        .catch(
            function (error) {

                console.error(
                    error
                );

                isLoading = false;

            }
        );

    }


    /* =====================================================
       BIND NEXT / PREVIOUS BUTTONS
    ===================================================== */

    function bindCategoryLinks() {


        var links =
            document.querySelectorAll(
                ".category-scroll-link"
            );


        for (
            var i = 0;
            i < links.length;
            i++
        ) {


            links[i].addEventListener(
                "click",
                function (event) {


                    event.preventDefault();


                    var url =
                        this.href;


                    loadCategory(
                        url,
                        true
                    );

                }
            );

        }

    }


    /* =====================================================
       BROWSER BACK / FORWARD
    ===================================================== */

    window.addEventListener(
        "popstate",
        function () {

            loadCategory(
                window.location.href,
                false
            );

        }
    );


    /* =====================================================
       INITIALIZE
    ===================================================== */

    bindCategoryLinks();


})();

</script>


<?php

$conn->close();

include "../includes/footer.php";

?>