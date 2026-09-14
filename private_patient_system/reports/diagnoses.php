<?php

require_once "../config/database.php";
require_once "../config/auth.php";


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Diagnosis Report";
$pageSubtitle = "Yearly diagnosis and case summary";
$basePath = "../";
$activePage = "diagnoses";


/* =========================================================
   SELECT YEAR
========================================================= */

$currentYear = date("Y");

$selectedYear = isset($_GET["year"])
    ? intval($_GET["year"])
    : intval($currentYear);


if ($selectedYear < 2000 || $selectedYear > 2100) {
    $selectedYear = intval($currentYear);
}


/* =========================================================
   GET DIAGNOSIS SUMMARY
========================================================= */

$sql = "
    SELECT
        TRIM(UPPER(assessment)) AS diagnosis,
        COUNT(*) AS total_cases
    FROM consultations
    WHERE YEAR(visit_date) = ?
      AND assessment IS NOT NULL
      AND TRIM(assessment) <> ''
    GROUP BY TRIM(UPPER(assessment))
    ORDER BY total_cases DESC, diagnosis ASC
";


$stmt = $conn->prepare($sql);


if (!$stmt) {
    die("Database error: " . $conn->error);
}


$stmt->bind_param(
    "i",
    $selectedYear
);


$stmt->execute();


$result = $stmt->get_result();


$diagnoses = array();

$totalCases = 0;


while ($row = $result->fetch_assoc()) {

    $diagnoses[] = $row;

    $totalCases += intval(
        $row["total_cases"]
    );
}


$stmt->close();


/* =========================================================
   TOTAL DIFFERENT DIAGNOSES
========================================================= */

$totalDiagnoses = count($diagnoses);


/* =========================================================
   TOTAL CONSULTATIONS FOR YEAR
========================================================= */

$totalStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM consultations
    WHERE YEAR(visit_date) = ?
");


if (!$totalStmt) {
    die("Database error: " . $conn->error);
}


$totalStmt->bind_param(
    "i",
    $selectedYear
);


$totalStmt->execute();


$totalResult = $totalStmt->get_result();


$totalData = $totalResult->fetch_assoc();


$totalConsultations = intval(
    $totalData["total"]
);


$totalStmt->close();


/* =========================================================
   MOST COMMON DIAGNOSIS
========================================================= */

$mostCommonDiagnosis = "";

$mostCommonCases = 0;


if (count($diagnoses) > 0) {

    $mostCommonDiagnosis =
        $diagnoses[0]["diagnosis"];

    $mostCommonCases =
        intval(
            $diagnoses[0]["total_cases"]
        );
}


/* =========================================================
   MOST COMMON DIAGNOSIS PERCENTAGE
========================================================= */

$mostCommonPercentage = 0;


if ($totalCases > 0) {

    $mostCommonPercentage =
        ($mostCommonCases / $totalCases) * 100;
}


/* =========================================================
   HEADER
========================================================= */

require_once "../includes/header.php";

require_once "../includes/navigation.php";

?>

<style>

/* =========================================================
   MAIN CONTAINER
========================================================= */

.report-container {
    padding: 28px;
    max-width: 1500px;
    margin: 0 auto;
}


/* =========================================================
   PAGE TITLE + YEAR FILTER
========================================================= */

.report-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
    margin-bottom: 20px;
}


.report-container .page-title {
    margin: 0;
    flex-shrink: 0;
}


.report-container .page-title h2 {
    margin: 0;
}


/* =========================================================
   YEAR FILTER
========================================================= */

.year-filter {
    display: flex;
    align-items: center;
    gap: 9px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    flex-shrink: 0;
}

.year-filter label {
    font-size: 13px;
    font-weight: 700;
    color: #4b5563;
    padding-left: 6px;
}

.year-filter select {
    height: 38px;
    min-width: 95px;
    padding: 0 30px 0 11px;
    border: 1px solid #d1d5db;
    border-radius: 7px;
    background: #ffffff;
    color: #1f2937;
    font-size: 14px;
    outline: none;
}

.year-filter select:focus {
    border-color: #1f4e79;
}

.year-filter button {
    height: 38px;
    padding: 0 16px;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    background: #1f4e79;
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
}

.year-filter button:hover {
    opacity: 0.92;
}


/* =========================================================
   PAGE DESCRIPTION
========================================================= */

.report-description {
    margin: 0 0 20px;
    color: #6b7280;
    font-size: 14px;
}


/* =========================================================
   MOST COMMON DIAGNOSIS
========================================================= */

.most-common-card {
    position: relative;
    background: linear-gradient(
        135deg,
        #1f4e79 0%,
        #2d6a9f 100%
    );
    color: #ffffff;
    border-radius: 14px;
    padding: 25px 28px;
    margin-bottom: 22px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(31,78,121,0.20);
}

.most-common-card:after {
    content: "";
    position: absolute;
    width: 180px;
    height: 180px;
    right: -55px;
    top: -70px;
    border: 25px solid rgba(255,255,255,0.08);
    border-radius: 50%;
}

.most-common-label {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    opacity: 0.85;
    margin-bottom: 8px;
}

.most-common-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 25px;
    position: relative;
    z-index: 1;
}

.most-common-name {
    font-size: 30px;
    font-weight: 800;
    line-height: 1.2;
    word-break: break-word;
}

.most-common-description {
    margin-top: 7px;
    font-size: 13px;
    opacity: 0.85;
}

.most-common-number {
    text-align: right;
    min-width: 150px;
}

.most-common-number strong {
    display: block;
    font-size: 38px;
    line-height: 1;
    font-weight: 800;
}

.most-common-number span {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    opacity: 0.85;
}


/* =========================================================
   SUMMARY CARDS
========================================================= */

.summary-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}

.summary-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 11px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.035);
}

.summary-card-title {
    color: #6b7280;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 9px;
}

.summary-card-value {
    font-size: 28px;
    font-weight: 800;
    color: #1f2937;
}

.summary-card-subtitle {
    margin-top: 5px;
    color: #9ca3af;
    font-size: 12px;
}


/* =========================================================
   TABLE CONTAINER
========================================================= */

.report-table-container {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 9px rgba(0,0,0,0.035);
}

.report-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 18px 22px;
    border-bottom: 1px solid #e5e7eb;
}

.report-table-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 700;
}

.report-table-header p {
    margin: 4px 0 0;
    color: #9ca3af;
    font-size: 12px;
}

.print-button {
    padding: 8px 14px;
    border: 1px solid #d1d5db;
    border-radius: 7px;
    background: #ffffff;
    color: #374151;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
}

.print-button:hover {
    background: #f9fafb;
}


/* =========================================================
   TABLE
========================================================= */

.report-table {
    width: 100%;
    border-collapse: collapse;
}

.report-table th,
.report-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #f0f0f0;
    text-align: left;
}

.report-table th {
    background: #f8fafc;
    color: #6b7280;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.report-table td {
    color: #374151;
    font-size: 13px;
}

.report-table tbody tr:hover {
    background: #fafcff;
}

.report-table tr:last-child td {
    border-bottom: none;
}


/* =========================================================
   RANK
========================================================= */

.rank-cell {
    width: 70px;
    text-align: center !important;
}

.rank-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #f3f4f6;
    color: #4b5563;
    font-weight: 800;
    font-size: 12px;
}

.rank-top {
    background: #1f4e79;
    color: #ffffff;
}


/* =========================================================
   DIAGNOSIS NAME
========================================================= */

.diagnosis-name {
    font-weight: 700;
    color: #1f2937;
    word-break: break-word;
}


/* =========================================================
   CASE COUNT
========================================================= */

.case-count {
    width: 150px;
    text-align: center !important;
}

.case-number {
    font-size: 15px;
    font-weight: 800;
    color: #1f4e79;
}


/* =========================================================
   PERCENTAGE
========================================================= */

.percentage-cell {
    width: 220px;
}

.percentage-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.progress-container {
    flex: 1;
    height: 7px;
    background: #edf1f5;
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: #1f4e79;
    border-radius: 10px;
    min-width: 2px;
}

.percentage-value {
    width: 48px;
    text-align: right;
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-message {
    text-align: center;
    padding: 55px 25px;
    color: #9ca3af;
}

.empty-message strong {
    display: block;
    color: #6b7280;
    font-size: 16px;
    margin-bottom: 5px;
}


/* =========================================================
   REPORT FOOTER
========================================================= */

.report-footer {
    padding: 15px 20px;
    background: #fafafa;
    border-top: 1px solid #eeeeee;
    color: #9ca3af;
    font-size: 11px;
}


/* =========================================================
   PRINT
========================================================= */

@media print {

    @page {
        size: A4 landscape;
        margin: 12mm;
    }


    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }


    html,
    body {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }


    body {
        font-family: Arial, Helvetica, sans-serif !important;
        color: #000000 !important;
        font-size: 11px !important;
    }


    /* -----------------------------------------------------
       HIDE SYSTEM UI
    ----------------------------------------------------- */

    .year-filter,
    .print-button,
    .sidebar,
    .navigation,
    nav,
    .no-print,
    header,
    footer {
        display: none !important;
    }


    /* -----------------------------------------------------
       MAIN CONTAINER
    ----------------------------------------------------- */

    .report-container {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }


    /* -----------------------------------------------------
       PAGE TOPBAR
    ----------------------------------------------------- */

    .report-topbar {
        display: block !important;
        margin: 0 0 10px 0 !important;
        padding: 0 0 10px 0 !important;
        border-bottom: 2px solid #000000 !important;
        text-align: center !important;
    }


    .report-container .page-title {
        margin: 0 !important;
        padding: 0 !important;
    }


    .report-container .page-title h2 {
        margin: 0 !important;
        padding: 0 !important;
        font-size: 22px !important;
        line-height: 1.2 !important;
        font-weight: 800 !important;
        color: #000000 !important;
        text-transform: uppercase !important;
    }


    .report-description {
        margin: 4px 0 12px 0 !important;
        padding: 0 !important;
        text-align: center !important;
        font-size: 11px !important;
        color: #333333 !important;
    }


    /* -----------------------------------------------------
       MOST COMMON DIAGNOSIS
    ----------------------------------------------------- */

    .most-common-card {
        display: block !important;
        position: relative !important;
        background: #ffffff !important;
        color: #000000 !important;
        border: 1px solid #000000 !important;
        border-radius: 0 !important;
        padding: 10px 14px !important;
        margin: 0 0 10px 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
    }


    .most-common-card:after {
        display: none !important;
    }


    .most-common-label {
        margin: 0 0 5px 0 !important;
        font-size: 9px !important;
        line-height: 1 !important;
        letter-spacing: 0.8px !important;
        font-weight: 800 !important;
        color: #333333 !important;
        opacity: 1 !important;
    }


    .most-common-content {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        position: relative !important;
        z-index: 1 !important;
    }


    .most-common-name {
        font-size: 17px !important;
        line-height: 1.2 !important;
        font-weight: 800 !important;
        color: #000000 !important;
        word-break: break-word !important;
    }


    .most-common-description {
        margin-top: 3px !important;
        font-size: 9px !important;
        color: #555555 !important;
        opacity: 1 !important;
    }


    .most-common-number {
        min-width: 100px !important;
        text-align: right !important;
    }


    .most-common-number strong {
        display: block !important;
        font-size: 23px !important;
        line-height: 1 !important;
        font-weight: 800 !important;
        color: #000000 !important;
    }


    .most-common-number span {
        display: block !important;
        margin-top: 3px !important;
        font-size: 9px !important;
        color: #444444 !important;
        opacity: 1 !important;
    }


    /* -----------------------------------------------------
       SUMMARY CARDS
    ----------------------------------------------------- */

    .summary-cards {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 8px !important;
        margin: 0 0 10px 0 !important;
    }


    .summary-card {
        background: #ffffff !important;
        border: 1px solid #999999 !important;
        border-radius: 0 !important;
        padding: 9px 12px !important;
        box-shadow: none !important;
    }


    .summary-card-title {
        margin: 0 0 4px 0 !important;
        font-size: 8px !important;
        line-height: 1.1 !important;
        font-weight: 800 !important;
        color: #444444 !important;
    }


    .summary-card-value {
        font-size: 19px !important;
        line-height: 1.1 !important;
        font-weight: 800 !important;
        color: #000000 !important;
    }


    .summary-card-subtitle {
        margin-top: 3px !important;
        font-size: 8px !important;
        line-height: 1.1 !important;
        color: #666666 !important;
    }


    /* -----------------------------------------------------
       TABLE CONTAINER
    ----------------------------------------------------- */

    .report-table-container {
        background: #ffffff !important;
        border: 1px solid #000000 !important;
        border-radius: 0 !important;
        overflow: visible !important;
        box-shadow: none !important;
    }


    /* -----------------------------------------------------
       TABLE HEADER
    ----------------------------------------------------- */

    .report-table-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 8px 10px !important;
        border-bottom: 1px solid #000000 !important;
    }


    .report-table-header h3 {
        margin: 0 !important;
        font-size: 13px !important;
        line-height: 1.2 !important;
        font-weight: 800 !important;
        color: #000000 !important;
    }


    .report-table-header p {
        margin: 2px 0 0 0 !important;
        font-size: 8px !important;
        color: #555555 !important;
    }


    /* -----------------------------------------------------
       TABLE
    ----------------------------------------------------- */

    .report-table {
        width: 100% !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
    }


    .report-table th,
    .report-table td {
        padding: 6px 8px !important;
        border-bottom: 1px solid #bdbdbd !important;
        border-right: 1px solid #d0d0d0 !important;
        text-align: left !important;
        vertical-align: middle !important;
    }


    .report-table th:last-child,
    .report-table td:last-child {
        border-right: none !important;
    }


    .report-table th {
        background: #eeeeee !important;
        color: #000000 !important;
        font-size: 8px !important;
        line-height: 1.1 !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
    }


    .report-table td {
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 9px !important;
        line-height: 1.2 !important;
    }


    .report-table tbody tr:hover {
        background: #ffffff !important;
    }


    .report-table tr:last-child td {
        border-bottom: none !important;
    }


    /* -----------------------------------------------------
       COLUMN WIDTHS
    ----------------------------------------------------- */

    .report-table th:nth-child(1),
    .report-table td:nth-child(1) {
        width: 55px !important;
    }


    .report-table th:nth-child(2),
    .report-table td:nth-child(2) {
        width: auto !important;
    }


    .report-table th:nth-child(3),
    .report-table td:nth-child(3) {
        width: 80px !important;
    }


    .report-table th:nth-child(4),
    .report-table td:nth-child(4) {
        width: 180px !important;
    }


    /* -----------------------------------------------------
       RANK
    ----------------------------------------------------- */

    .rank-cell {
        width: 55px !important;
        text-align: center !important;
    }


    .rank-number {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 22px !important;
        height: 22px !important;
        border-radius: 50% !important;
        background: #eeeeee !important;
        color: #000000 !important;
        border: 1px solid #999999 !important;
        font-size: 8px !important;
        font-weight: 800 !important;
    }


    .rank-top {
        background: #dddddd !important;
        color: #000000 !important;
        border-color: #000000 !important;
    }


    /* -----------------------------------------------------
       DIAGNOSIS
    ----------------------------------------------------- */

    .diagnosis-name {
        font-size: 9px !important;
        line-height: 1.25 !important;
        font-weight: 700 !important;
        color: #000000 !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
    }


    /* -----------------------------------------------------
       CASE COUNT
    ----------------------------------------------------- */

    .case-count {
        width: 80px !important;
        text-align: center !important;
    }


    .case-number {
        font-size: 10px !important;
        font-weight: 800 !important;
        color: #000000 !important;
    }


    /* -----------------------------------------------------
       PERCENTAGE
    ----------------------------------------------------- */

    .percentage-cell {
        width: 180px !important;
    }


    .percentage-wrapper {
        display: flex !important;
        align-items: center !important;
        gap: 7px !important;
    }


    .progress-container {
        flex: 1 !important;
        height: 6px !important;
        background: #eeeeee !important;
        border: 1px solid #999999 !important;
        border-radius: 0 !important;
        overflow: hidden !important;
    }


    .progress-bar {
        height: 100% !important;
        background: #777777 !important;
        border-radius: 0 !important;
        min-width: 2px !important;
    }


    .percentage-value {
        width: 38px !important;
        text-align: right !important;
        font-size: 8px !important;
        font-weight: 700 !important;
        color: #000000 !important;
    }


    /* -----------------------------------------------------
       REPORT FOOTER
    ----------------------------------------------------- */

    .report-footer {
        padding: 6px 10px !important;
        background: #ffffff !important;
        border-top: 1px solid #999999 !important;
        color: #444444 !important;
        font-size: 8px !important;
        line-height: 1.2 !important;
    }


    .report-footer strong {
        color: #000000 !important;
    }


    /* -----------------------------------------------------
       EMPTY MESSAGE
    ----------------------------------------------------- */

    .empty-message {
        text-align: center !important;
        padding: 30px 15px !important;
        color: #555555 !important;
        font-size: 10px !important;
    }


    .empty-message strong {
        display: block !important;
        margin-bottom: 4px !important;
        color: #000000 !important;
        font-size: 13px !important;
    }


    /* -----------------------------------------------------
       PAGE BREAK
    ----------------------------------------------------- */

    .report-table-container {
        page-break-inside: auto !important;
    }


    .report-table tr {
        page-break-inside: avoid !important;
        page-break-after: auto !important;
    }


    .summary-card,
    .most-common-card {
        page-break-inside: avoid !important;
    }

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .report-topbar {
        align-items: flex-start;
    }

    .year-filter {
        width: auto;
    }

    .year-filter select {
        flex: 1;
    }

    .summary-cards {
        grid-template-columns: 1fr;
    }

    .most-common-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .most-common-number {
        text-align: left;
    }

    .percentage-cell {
        width: 180px;
    }

}


@media (max-width: 650px) {

    .report-container {
        padding: 15px;
    }

    .report-topbar {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .year-filter {
        width: 100%;
    }

    .year-filter select {
        flex: 1;
    }

    .most-common-name {
        font-size: 22px;
    }

    .most-common-number strong {
        font-size: 30px;
    }

    .report-table {
        min-width: 650px;
    }

    .report-table-container {
        overflow-x: auto;
    }

}

</style>


<div class="report-container">


    <!-- =====================================================
         PAGE TITLE + YEAR FILTER
    ====================================================== -->

    <div class="report-topbar">


        <div class="page-title">

            <h2>
                Diagnosis Report
            </h2>

        </div>


        <form
            method="GET"
            class="year-filter"
        >

            <label for="year">
                YEAR
            </label>


            <select
                name="year"
                id="year"
            >

                <?php

                for (
                    $year = intval($currentYear);
                    $year >= 2000;
                    $year--
                ) {

                ?>

                    <option
                        value="<?php echo $year; ?>"
                        <?php

                        if (
                            $year == $selectedYear
                        ) {
                            echo "selected";
                        }

                        ?>
                    >

                        <?php
                        echo $year;
                        ?>

                    </option>

                <?php

                }

                ?>

            </select>


            <button type="submit">
                View Report
            </button>

        </form>

    </div>


    <!-- =====================================================
         PAGE DESCRIPTION
    ====================================================== -->

    <p class="report-description">

        Yearly diagnosis and case summary for
        <?php echo htmlspecialchars($selectedYear); ?>

    </p>



    <!-- =====================================================
         MOST COMMON DIAGNOSIS
    ====================================================== -->

    <?php if ($totalCases > 0) { ?>

        <div class="most-common-card">

            <div class="most-common-label">
                Most Common Diagnosis
            </div>


            <div class="most-common-content">

                <div>

                    <div class="most-common-name">

                        <?php
                        echo htmlspecialchars(
                            $mostCommonDiagnosis
                        );
                        ?>

                    </div>


                    <div class="most-common-description">

                        Most frequently recorded diagnosis
                        for <?php echo $selectedYear; ?>

                    </div>

                </div>


                <div class="most-common-number">

                    <strong>

                        <?php
                        echo number_format(
                            $mostCommonCases
                        );
                        ?>

                    </strong>


                    <span>

                        cases
                        /
                        <?php
                        echo number_format(
                            $mostCommonPercentage,
                            1
                        ); ?>%

                        of diagnosed cases

                    </span>

                </div>

            </div>

        </div>

    <?php } ?>



    <!-- =====================================================
         SUMMARY CARDS
    ====================================================== -->

    <div class="summary-cards">


        <div class="summary-card">

            <div class="summary-card-title">
                TOTAL CONSULTATIONS
            </div>


            <div class="summary-card-value">

                <?php
                echo number_format(
                    $totalConsultations
                );
                ?>

            </div>


            <div class="summary-card-subtitle">

                All consultations in
                <?php echo $selectedYear; ?>

            </div>

        </div>



        <div class="summary-card">

            <div class="summary-card-title">
                TOTAL DIAGNOSED CASES
            </div>


            <div class="summary-card-value">

                <?php
                echo number_format(
                    $totalCases
                );
                ?>

            </div>


            <div class="summary-card-subtitle">

                Consultations with recorded assessment

            </div>

        </div>



        <div class="summary-card">

            <div class="summary-card-title">
                DIFFERENT DIAGNOSES
            </div>


            <div class="summary-card-value">

                <?php
                echo number_format(
                    $totalDiagnoses
                );
                ?>

            </div>


            <div class="summary-card-subtitle">

                Unique recorded diagnoses

            </div>

        </div>


    </div>



    <!-- =====================================================
         DIAGNOSIS TABLE
    ====================================================== -->

    <div class="report-table-container">


        <div class="report-table-header">

            <div>

                <h3>
                    Diagnosis Ranking
                </h3>


                <p>
                    Ranked from highest to lowest number of cases
                </p>

            </div>


            <button
                type="button"
                class="print-button no-print"
                onclick="window.print();"
            >
                Print Report
            </button>

        </div>



        <?php if (count($diagnoses) > 0) { ?>


            <table class="report-table">

                <thead>

                    <tr>

                        <th class="rank-cell">
                            Rank
                        </th>


                        <th>
                            Diagnosis
                        </th>


                        <th class="case-count">
                            Cases
                        </th>


                        <th class="percentage-cell">
                            Percentage
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php

                    $number = 1;


                    foreach (
                        $diagnoses
                        as $diagnosis
                    ) {


                        $cases = intval(
                            $diagnosis["total_cases"]
                        );


                        $percentage = 0;


                        if ($totalCases > 0) {

                            $percentage =
                                (
                                    $cases /
                                    $totalCases
                                ) * 100;

                        }


                        /*
                         * Bar width is based on
                         * the highest diagnosis.
                         */

                        $barWidth = 0;


                        if ($mostCommonCases > 0) {

                            $barWidth =
                                (
                                    $cases /
                                    $mostCommonCases
                                ) * 100;

                        }


                        if ($barWidth > 100) {

                            $barWidth = 100;

                        }

                    ?>


                        <tr>


                            <!-- RANK -->

                            <td class="rank-cell">

                                <span
                                    class="
                                        rank-number
                                        <?php

                                        if (
                                            $number === 1
                                        ) {

                                            echo "rank-top";

                                        }

                                        ?>
                                    "
                                >

                                    <?php
                                    echo $number;
                                    ?>

                                </span>

                            </td>



                            <!-- DIAGNOSIS -->

                            <td>

                                <div class="diagnosis-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $diagnosis["diagnosis"]
                                    );
                                    ?>

                                </div>

                            </td>



                            <!-- CASES -->

                            <td class="case-count">

                                <span class="case-number">

                                    <?php
                                    echo number_format(
                                        $cases
                                    );
                                    ?>

                                </span>

                            </td>



                            <!-- PERCENTAGE -->

                            <td class="percentage-cell">

                                <div class="percentage-wrapper">


                                    <div class="progress-container">

                                        <div
                                            class="progress-bar"
                                            <?php
                                            echo 'style="width:' .
                                                $barWidth .
                                                '%;"';
                                            ?>
                                        ></div>

                                    </div>


                                    <div class="percentage-value">

                                        <?php
                                        echo number_format(
                                            $percentage,
                                            1
                                        );
                                        ?>%

                                    </div>


                                </div>

                            </td>


                        </tr>


                    <?php

                        $number++;

                    }

                    ?>

                </tbody>

            </table>



            <div class="report-footer">

                Showing

                <strong>

                    <?php
                    echo number_format(
                        $totalDiagnoses
                    );
                    ?>

                </strong>

                different diagnoses recorded in

                <?php
                echo $selectedYear;
                ?>.

            </div>


        <?php } else { ?>


            <div class="empty-message">

                <strong>
                    No Diagnosis Records Found
                </strong>


                No consultation with a recorded diagnosis
                was found for

                <?php
                echo $selectedYear;
                ?>.

            </div>


        <?php } ?>


    </div>


</div>


<?php

require_once "../includes/footer.php";

?>