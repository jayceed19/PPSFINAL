<?php

require_once "../config/database.php";


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Consultation Report";
$pageSubtitle = "Consultation Reports";

$basePath = "../";
$activePage = "reports";


/* =========================================================
   DATE FILTER
========================================================= */

$fromDate = isset($_GET["from_date"])
    ? trim($_GET["from_date"])
    : "";

$toDate = isset($_GET["to_date"])
    ? trim($_GET["to_date"])
    : "";


/* =========================================================
   PAGINATION SETTINGS
========================================================= */

$recordsPerPage = 20;

$currentPage = isset($_GET["page"])
    ? intval($_GET["page"])
    : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}


/* =========================================================
   DATE CONDITION
========================================================= */

$dateCondition = "";


if ($fromDate != "") {

    $safeFromDate = $conn->real_escape_string($fromDate);

    $dateCondition .=
        " AND c.visit_date >= '" .
        $safeFromDate .
        "'";
}


if ($toDate != "") {

    $safeToDate = $conn->real_escape_string($toDate);

    $dateCondition .=
        " AND c.visit_date <= '" .
        $safeToDate .
        "'";
}


/* =========================================================
   TOTAL CONSULTATIONS
========================================================= */

$totalConsultations = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM consultations c
    WHERE 1=1
    " . $dateCondition;


$result = $conn->query($sql);


if ($result) {

    $row = $result->fetch_assoc();

    $totalConsultations = (int)$row["total"];
}


/* =========================================================
   MALE CONSULTATIONS
========================================================= */

$maleConsultations = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM consultations c

    INNER JOIN patients p
        ON p.id = c.patient_id

    WHERE UPPER(TRIM(p.sex)) = 'MALE'
    " . $dateCondition;


$result = $conn->query($sql);


if ($result) {

    $row = $result->fetch_assoc();

    $maleConsultations = (int)$row["total"];
}


/* =========================================================
   FEMALE CONSULTATIONS
========================================================= */

$femaleConsultations = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM consultations c

    INNER JOIN patients p
        ON p.id = c.patient_id

    WHERE UPPER(TRIM(p.sex)) = 'FEMALE'
    " . $dateCondition;


$result = $conn->query($sql);


if ($result) {

    $row = $result->fetch_assoc();

    $femaleConsultations = (int)$row["total"];
}


/* =========================================================
   WITH FOLLOW-UP
   Same-day follow-up is NOT counted.
========================================================= */

$withFollowUp = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM consultations c

    WHERE c.follow_up_date IS NOT NULL
    AND c.follow_up_date != ''
    AND DATE(c.follow_up_date) != DATE(c.visit_date)

    " . $dateCondition;


$result = $conn->query($sql);


if ($result) {

    $row = $result->fetch_assoc();

    $withFollowUp = (int)$row["total"];
}


/* =========================================================
   WITHOUT FOLLOW-UP
   Includes:
   - NULL follow-up date
   - blank follow-up date
   - same-day follow-up date
========================================================= */

$withoutFollowUp = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM consultations c

    WHERE
    (
        c.follow_up_date IS NULL
        OR c.follow_up_date = ''
        OR DATE(c.follow_up_date) = DATE(c.visit_date)
    )

    " . $dateCondition;


$result = $conn->query($sql);


if ($result) {

    $row = $result->fetch_assoc();

    $withoutFollowUp = (int)$row["total"];
}


/* =========================================================
   TOTAL PAGES
========================================================= */

$totalPages = 0;


if ($totalConsultations > 0) {

    $totalPages = ceil(
        $totalConsultations /
        $recordsPerPage
    );
}


if ($currentPage > $totalPages && $totalPages > 0) {

    $currentPage = $totalPages;
}


/* =========================================================
   OFFSET
========================================================= */

$offset =
    ($currentPage - 1) *
    $recordsPerPage;


/* =========================================================
   CONSULTATION RECORDS
========================================================= */

$consultations = array();

$sql = "
    SELECT

        c.id,
        c.patient_id,
        c.visit_date,
        c.chief_complaint,
        c.assessment,
        c.follow_up_date,
        c.follow_up_status,

        p.patient_id AS patient_code,
        p.last_name,
        p.first_name,
        p.middle_name,
        p.birthdate,
        p.sex

    FROM consultations c

    INNER JOIN patients p
        ON p.id = c.patient_id

    WHERE 1=1

    " . $dateCondition . "

    ORDER BY
        c.visit_date DESC,
        c.id DESC

    LIMIT " .
    intval($offset) .
    ", " .
    intval($recordsPerPage);


$result = $conn->query($sql);


if ($result) {

    while ($row = $result->fetch_assoc()) {

        $consultations[] = $row;
    }
}


/* =========================================================
   AGE FUNCTION
========================================================= */

function calculateConsultationAge($birthdate)
{

    if (empty($birthdate)) {

        return "-";
    }


    try {

        $birthDate = new DateTime($birthdate);

        $today = new DateTime();

        return $today->diff($birthDate)->y;

    } catch (Exception $e) {

        return "-";
    }
}


/* =========================================================
   DATE FUNCTION
========================================================= */

function displayConsultationDate($date)
{

    if (empty($date)) {

        return "-";
    }


    $timestamp = strtotime($date);


    if (!$timestamp) {

        return "-";
    }


    return date("M d, Y", $timestamp);
}


/* =========================================================
   FOLLOW-UP STATUS FUNCTION
========================================================= */

function getConsultationFollowUpStatus(
    $visitDate,
    $followUpDate,
    $followUpStatus
) {

    /* -------------------------------------------------
       CHECK SAVED FOLLOW-UP STATUS FIRST
       Final statuses must not be overridden by date.
    ------------------------------------------------- */

    $savedStatus = strtoupper(
        trim($followUpStatus)
    );


    if ($savedStatus == "COMPLETED") {

        return "COMPLETED";

    }


    if ($savedStatus == "CONFIRMED") {

        return "CONFIRMED";

    }


    if ($savedStatus == "CANCELLED") {

        return "CANCELLED";

    }


    if ($savedStatus == "NO SHOW") {

        return "NO SHOW";

    }


    /* -------------------------------------------------
       NO FOLLOW-UP
    ------------------------------------------------- */

    if (empty($followUpDate)) {

        return "NO FOLLOW-UP";
    }


    try {

        $visit = new DateTime($visitDate);

        $followUp = new DateTime($followUpDate);


        /* -------------------------------------------------
           SAME-DAY FOLLOW-UP
        ------------------------------------------------- */

        if (
            $visit->format("Y-m-d")
            ==
            $followUp->format("Y-m-d")
        ) {

            return "NO FOLLOW-UP";
        }


        /* -------------------------------------------------
           TODAY
        ------------------------------------------------- */

        $today = new DateTime();

        $today->setTime(0, 0, 0);

        $followUp->setTime(0, 0, 0);


        /* -------------------------------------------------
           DATE DIFFERENCE
        ------------------------------------------------- */

        $difference =
            (int)$today
                ->diff($followUp)
                ->format("%r%a");


        /* -------------------------------------------------
           OVERDUE
        ------------------------------------------------- */

        if ($difference < 0) {

            return "OVERDUE";
        }


        /* -------------------------------------------------
           DUE SOON
        ------------------------------------------------- */

        if ($difference <= 7) {

            return "DUE SOON";
        }


        /* -------------------------------------------------
           SCHEDULED
        ------------------------------------------------- */

        return "SCHEDULED";

    } catch (Exception $e) {

        return "NO FOLLOW-UP";
    }
}


/* =========================================================
   HEADER
========================================================= */

include __DIR__ . "/../includes/header.php";


/* =========================================================
   NAVIGATION
========================================================= */

include __DIR__ . "/../includes/navigation.php";

?>


<style>

/* =========================================================
   FILTER
========================================================= */

.consultation-report-filter {
    display: grid;
    grid-template-columns: 1fr 1fr auto auto;
    gap: 12px;
    align-items: end;
}


.report-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}


.report-field label {
    font-size: 13px;
    font-weight: 600;
    color: #555;
}


.report-field input {
    height: 40px;
    border: 1px solid #d5dbe1;
    border-radius: 5px;
    padding: 0 11px;
    font-size: 14px;
    background: #fff;
}


/* =========================================================
   SUMMARY
========================================================= */

.report-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}


.report-summary-card {
    border: 1px solid #e2e6ea;
    background: #fff;
    padding: 18px 20px;
    border-radius: 6px;
}


.report-summary-card .label {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 8px;
}


.report-summary-card .value {
    font-size: 28px;
    font-weight: 700;
    color: #1f4e78;
}


.report-summary-card .small-text {
    margin-top: 5px;
    font-size: 12px;
    color: #777;
}


/* =========================================================
   REPORT SECTIONS
========================================================= */

.report-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}


.report-section-subtitle {
    color: #777;
    font-size: 13px;
    margin-bottom: 18px;
}


/* =========================================================
   STATISTICS
========================================================= */

.report-stat-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}


.report-stat-row {
    display: grid;
    grid-template-columns: 120px 1fr 55px;
    align-items: center;
    gap: 10px;
}


.report-stat-label {
    font-size: 13px;
    color: #555;
}


.report-stat-bar {
    height: 8px;
    background: #edf0f2;
    border-radius: 10px;
    overflow: hidden;
}


.report-stat-fill {
    height: 100%;
    background: #1f4e78;
    border-radius: 10px;
}


.report-stat-value {
    text-align: right;
    font-size: 13px;
    font-weight: 700;
    color: #333;
}


/* =========================================================
   TABLE
========================================================= */

.report-table-count {
    font-size: 13px;
    color: #777;
}


.consultation-table {
    width: 100%;
    border-collapse: collapse;
}


.consultation-table th {
    white-space: nowrap;
}


.consultation-table td {
    vertical-align: top;
}


.visit-date-text {
    white-space: nowrap;
    font-weight: 600;
}


.patient-id-text {
    color: #1f4e78;
    font-weight: 700;
    white-space: nowrap;
}


.patient-name-text {
    font-weight: 600;
    min-width: 160px;
}


.complaint-text {
    min-width: 180px;
    max-width: 280px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}


.assessment-text {
    min-width: 180px;
    max-width: 280px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}


.followup-date-text {
    white-space: nowrap;
}


/* =========================================================
   STATUS
========================================================= */

.consultation-status {
    display: inline-block;
    padding: 4px 9px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}


.status-overdue {
    background: #fde2e2;
    color: #b42318;
}


.status-due {
    background: #fff0c2;
    color: #8a6100;
}


.status-scheduled {
    background: #e2efff;
    color: #1f4e78;
}


.status-confirmed {
    background: #e6f4ea;
    color: #287d3c;
}


.status-cancelled {
    background: #fde2e2;
    color: #b42318;
}


.status-completed {
    background: #dff3ea;
    color: #176b4d;
}


.status-no-show {
    background: #fff0d9;
    color: #9a5b00;
}


.status-none {
    background: #edf0f2;
    color: #666;
}


/* =========================================================
   PAGINATION
========================================================= */

.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #e5e7eb;
    flex-wrap: wrap;
}


.pagination-link {
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border: 1px solid #d5dbe1;
    border-radius: 5px;
    background: #ffffff;
    color: #444;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    box-sizing: border-box;
}


.pagination-link:hover {
    background: #f5f7f9;
}


.pagination-link.active {
    background: #1f4e78;
    border-color: #1f4e78;
    color: #ffffff;
}


.pagination-link.disabled {
    color: #aaa;
    background: #f5f5f5;
    cursor: not-allowed;
}


.pagination-info {
    width: 100%;
    text-align: center;
    margin-top: 7px;
    color: #777;
    font-size: 12px;
}


/* =========================================================
   PRINT
========================================================= */

.print-only {
    display: none;
}


@media print {

    @page {
        size: A4 landscape;
        margin: 12mm;
    }


    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }


    body {
        background: #fff !important;
        color: #000 !important;
    }


    .sidebar,
    .navigation,
    nav,
    .consultation-report-filter-card,
    .no-print,
    button,
    a,
    .pagination-container {
        display: none !important;
    }


    .main-container {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }


    .card {
        border: none !important;
        box-shadow: none !important;
        margin-bottom: 15px !important;
        padding: 0 !important;
    }


    .print-only {
        display: block !important;
        text-align: center !important;
        margin-bottom: 15px !important;
        padding-bottom: 10px !important;
        border-bottom: 2px solid #000 !important;
    }


    .print-only h1 {
        margin: 0 0 4px 0 !important;
        font-size: 22px !important;
        color: #000 !important;
    }


    .print-only p {
        margin: 0 !important;
        font-size: 11px !important;
        color: #333 !important;
    }


    .report-summary-grid {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 8px !important;
        margin-bottom: 12px !important;
    }


    .report-summary-card {
        border: 1px solid #999 !important;
        border-radius: 0 !important;
        padding: 8px 10px !important;
        box-shadow: none !important;
    }


    .report-summary-card .label {
        font-size: 8px !important;
        color: #444 !important;
        margin-bottom: 4px !important;
    }


    .report-summary-card .value {
        font-size: 18px !important;
        color: #000 !important;
    }


    .report-summary-card .small-text {
        margin-top: 3px !important;
        font-size: 8px !important;
        color: #555 !important;
    }


    .report-section-title {
        font-size: 13px !important;
        color: #000 !important;
        margin-bottom: 3px !important;
    }


    .report-section-subtitle {
        font-size: 9px !important;
        color: #555 !important;
        margin-bottom: 8px !important;
    }


    .report-stat-list {
        gap: 6px !important;
    }


    .report-stat-row {
        grid-template-columns: 100px 1fr 40px !important;
        gap: 7px !important;
    }


    .report-stat-label,
    .report-stat-value {
        font-size: 9px !important;
        color: #000 !important;
    }


    .report-stat-bar {
        height: 5px !important;
        background: #eeeeee !important;
        border: 1px solid #aaa !important;
        border-radius: 0 !important;
    }


    .report-stat-fill {
        background: #777 !important;
        border-radius: 0 !important;
    }


    .table-container {
        width: 100% !important;
        overflow: visible !important;
    }


    .consultation-table {
        width: 100% !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
    }


    .consultation-table th,
    .consultation-table td {
        border: 1px solid #999 !important;
        padding: 5px 6px !important;
        font-size: 8px !important;
        line-height: 1.2 !important;
        color: #000 !important;
        vertical-align: top !important;
    }


    .consultation-table th {
        background: #eeeeee !important;
        font-weight: 800 !important;
        text-align: left !important;
    }


    .consultation-table th:nth-child(1),
    .consultation-table td:nth-child(1) {
        width: 8% !important;
    }


    .consultation-table th:nth-child(2),
    .consultation-table td:nth-child(2) {
        width: 7% !important;
    }


    .consultation-table th:nth-child(3),
    .consultation-table td:nth-child(3) {
        width: 12% !important;
    }


    .consultation-table th:nth-child(4),
    .consultation-table td:nth-child(4) {
        width: 5% !important;
    }


    .consultation-table th:nth-child(5),
    .consultation-table td:nth-child(5) {
        width: 6% !important;
    }


    .consultation-table th:nth-child(6),
    .consultation-table td:nth-child(6) {
        width: 17% !important;
    }


    .consultation-table th:nth-child(7),
    .consultation-table td:nth-child(7) {
        width: 17% !important;
    }


    .consultation-table th:nth-child(8),
    .consultation-table td:nth-child(8) {
        width: 10% !important;
    }


    .consultation-table th:nth-child(9),
    .consultation-table td:nth-child(9) {
        width: 10% !important;
    }


    .visit-date-text,
    .patient-id-text,
    .followup-date-text {
        white-space: normal !important;
    }


    .patient-name-text,
    .complaint-text,
    .assessment-text {
        min-width: 0 !important;
        max-width: none !important;
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
    }


    .consultation-status {
        padding: 2px 4px !important;
        border-radius: 0 !important;
        font-size: 7px !important;
        white-space: normal !important;
        background: #eeeeee !important;
        color: #000 !important;
        border: 1px solid #999 !important;
    }


    .report-table-count {
        font-size: 8px !important;
        color: #555 !important;
    }


    .section-header {
        margin-bottom: 6px !important;
    }


    .consultation-table tr {
        page-break-inside: avoid !important;
    }

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .report-summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }


    .consultation-report-filter {
        grid-template-columns: 1fr 1fr;
    }

}


@media (max-width: 600px) {

    .report-summary-grid {
        grid-template-columns: 1fr;
    }


    .consultation-report-filter {
        grid-template-columns: 1fr;
    }


    .pagination-link {
        min-width: 34px;
        height: 34px;
    }

}

</style>


<main class="main-container">


    <!-- =====================================================
         PAGE TITLE
    ====================================================== -->

    <div class="page-title">

        <h2>
            Consultation Report
        </h2>

        <p>
            Consultation activity and clinical visit report.
        </p>

    </div>


    <!-- =====================================================
         FILTER
    ====================================================== -->

    <div class="card consultation-report-filter-card">

        <div class="card-title">
            Report Period
        </div>


        <form
            method="GET"
            class="consultation-report-filter"
        >

            <div class="report-field">

                <label for="from_date">
                    From Date
                </label>

                <input
                    type="date"
                    id="from_date"
                    name="from_date"
                    value="<?php
                        echo htmlspecialchars($fromDate);
                    ?>"
                >

            </div>


            <div class="report-field">

                <label for="to_date">
                    To Date
                </label>

                <input
                    type="date"
                    id="to_date"
                    name="to_date"
                    value="<?php
                        echo htmlspecialchars($toDate);
                    ?>"
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary"
            >
                Generate Report
            </button>


            <?php

            if (
                $fromDate != ""
                ||
                $toDate != ""
            ) {

            ?>

                <a
                    href="consultations.php"
                    class="btn btn-secondary"
                >
                    Clear
                </a>

            <?php

            }

            ?>

        </form>

    </div>


    <!-- =====================================================
         PRINT HEADER
    ====================================================== -->

    <div class="print-only">

        <h1>
            Consultation Report
        </h1>


        <p>

            <?php

            if (
                $fromDate != ""
                &&
                $toDate != ""
            ) {

                echo "Period: "
                    . displayConsultationDate($fromDate)
                    . " - "
                    . displayConsultationDate($toDate);

            } elseif ($fromDate != "") {

                echo "From: "
                    . displayConsultationDate($fromDate);

            } elseif ($toDate != "") {

                echo "Up to: "
                    . displayConsultationDate($toDate);

            } else {

                echo "All Consultation Records";
            }

            ?>

        </p>

    </div>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="card">

        <div class="report-section-title">
            Consultation Summary
        </div>


        <div class="report-section-subtitle">

            <?php

            if (
                $fromDate != ""
                &&
                $toDate != ""
            ) {

                echo "Consultations from "
                    . htmlspecialchars(
                        displayConsultationDate($fromDate)
                    )
                    . " to "
                    . htmlspecialchars(
                        displayConsultationDate($toDate)
                    );

            } elseif ($fromDate != "") {

                echo "Consultations from "
                    . htmlspecialchars(
                        displayConsultationDate($fromDate)
                    );

            } elseif ($toDate != "") {

                echo "Consultations up to "
                    . htmlspecialchars(
                        displayConsultationDate($toDate)
                    );

            } else {

                echo "All recorded consultations";
            }

            ?>

        </div>


        <div class="report-summary-grid">


            <div class="report-summary-card">

                <div class="label">
                    Total Consultations
                </div>

                <div class="value">
                    <?php echo $totalConsultations; ?>
                </div>

                <div class="small-text">
                    Clinical visits
                </div>

            </div>


            <div class="report-summary-card">

                <div class="label">
                    Male
                </div>

                <div class="value">
                    <?php echo $maleConsultations; ?>
                </div>

                <div class="small-text">
                    Male consultations
                </div>

            </div>


            <div class="report-summary-card">

                <div class="label">
                    Female
                </div>

                <div class="value">
                    <?php echo $femaleConsultations; ?>
                </div>

                <div class="small-text">
                    Female consultations
                </div>

            </div>


            <div class="report-summary-card">

                <div class="label">
                    With Follow-up
                </div>

                <div class="value">
                    <?php echo $withFollowUp; ?>
                </div>

                <div class="small-text">
                    Scheduled follow-ups
                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         FOLLOW-UP OVERVIEW
    ====================================================== -->

    <div class="card">

        <div class="report-section-title">
            Follow-up Overview
        </div>


        <div class="report-section-subtitle">
            Follow-up records within the selected consultation
            period
        </div>


        <?php

        $followUpTotal =
            $withFollowUp +
            $withoutFollowUp;


        $followUpPercent = 0;

        if ($followUpTotal > 0) {

            $followUpPercent =
                ($withFollowUp / $followUpTotal) * 100;
        }


        $noFollowUpPercent = 0;

        if ($followUpTotal > 0) {

            $noFollowUpPercent =
                ($withoutFollowUp / $followUpTotal) * 100;
        }

        ?>


        <div class="report-stat-list">


            <div class="report-stat-row">

                <div class="report-stat-label">
                    With Follow-up
                </div>


                <div class="report-stat-bar">

                    <div
                        class="report-stat-fill"
                        style="<?php
                            echo 'width: ' .
                                $followUpPercent .
                                '%;';
                        ?>"
                    ></div>

                </div>


                <div class="report-stat-value">
                    <?php echo $withFollowUp; ?>
                </div>

            </div>


            <div class="report-stat-row">

                <div class="report-stat-label">
                    No Follow-up
                </div>


                <div class="report-stat-bar">

                    <div
                        class="report-stat-fill"
                        style="<?php
                            echo 'width: ' .
                                $noFollowUpPercent .
                                '%;';
                        ?>"
                    ></div>

                </div>


                <div class="report-stat-value">
                    <?php echo $withoutFollowUp; ?>
                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         CONSULTATION RECORDS
    ====================================================== -->

    <div class="card">


        <div class="section-header">

            <div>

                <div class="report-section-title">
                    Consultation Records
                </div>


                <div class="report-table-count">

                    <?php

                    $startRecord = 0;

                    $endRecord = 0;


                    if ($totalConsultations > 0) {

                        $startRecord =
                            $offset + 1;


                        $endRecord =
                            $offset +
                            count($consultations);


                        if (
                            $endRecord >
                            $totalConsultations
                        ) {

                            $endRecord =
                                $totalConsultations;
                        }

                    }


                    echo number_format(
                        $startRecord
                    );

                    echo " - ";

                    echo number_format(
                        $endRecord
                    );

                    echo " of ";

                    echo number_format(
                        $totalConsultations
                    );

                    echo " consultation record(s)";

                    ?>

                </div>

            </div>


            <button
                type="button"
                class="btn btn-primary no-print"
                onclick="window.print()"
            >
                Print Report
            </button>

        </div>


        <div class="table-container">

            <table class="consultation-table">


                <thead>

                    <tr>

                        <th>Visit Date</th>

                        <th>Patient ID</th>

                        <th>Patient Name</th>

                        <th>Age</th>

                        <th>Sex</th>

                        <th>Chief Complaint</th>

                        <th>Assessment</th>

                        <th>Follow-up</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                <?php

                if (count($consultations) > 0) {

                    foreach (
                        $consultations
                        as $consultation
                    ) {


                        /* =================================
                           AGE
                        ================================= */

                        $age =
                            calculateConsultationAge(
                                $consultation["birthdate"]
                            );


                        /* =================================
                           FULL NAME
                        ================================= */

                        $fullName =
                            $consultation["last_name"]
                            . ", "
                            . $consultation["first_name"];


                        if (
                            !empty(
                                $consultation["middle_name"]
                            )
                        ) {

                            $fullName .=
                                " " .
                                $consultation["middle_name"];
                        }


                        /* =================================
                           FOLLOW-UP STATUS
                        ================================= */

                        $followUpStatus =
                            getConsultationFollowUpStatus(
                                $consultation["visit_date"],
                                $consultation["follow_up_date"],
                                $consultation["follow_up_status"]
                            );


                        /* =================================
                           STATUS CLASS
                        ================================= */

                        $statusClass = "status-none";


                        if (
                            $followUpStatus == "OVERDUE"
                        ) {

                            $statusClass =
                                "status-overdue";

                        } elseif (
                            $followUpStatus == "DUE SOON"
                        ) {

                            $statusClass =
                                "status-due";

                        } elseif (
                            $followUpStatus == "SCHEDULED"
                        ) {

                            $statusClass =
                                "status-scheduled";

                        } elseif (
                            $followUpStatus == "CONFIRMED"
                        ) {

                            $statusClass =
                                "status-confirmed";

                        } elseif (
                            $followUpStatus == "CANCELLED"
                        ) {

                            $statusClass =
                                "status-cancelled";

                        } elseif (
                            $followUpStatus == "COMPLETED"
                        ) {

                            $statusClass =
                                "status-completed";

                        } elseif (
                            $followUpStatus == "NO SHOW"
                        ) {

                            $statusClass =
                                "status-no-show";
                        }

                ?>


                    <tr>


                        <!-- VISIT DATE -->

                        <td>

                            <span class="visit-date-text">

                                <?php

                                echo htmlspecialchars(
                                    displayConsultationDate(
                                        $consultation["visit_date"]
                                    )
                                );

                                ?>

                            </span>

                        </td>


                        <!-- PATIENT ID -->

                        <td>

                            <span class="patient-id-text">

                                <?php

                                echo htmlspecialchars(
                                    $consultation["patient_code"]
                                );

                                ?>

                            </span>

                        </td>


                        <!-- PATIENT NAME -->

                        <td>

                            <span class="patient-name-text">

                                <?php

                                echo htmlspecialchars(
                                    $fullName
                                );

                                ?>

                            </span>

                        </td>


                        <!-- AGE -->

                        <td>

                            <?php
                            echo $age;
                            ?>

                        </td>


                        <!-- SEX -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $consultation["sex"]
                            );

                            ?>

                        </td>


                        <!-- CHIEF COMPLAINT -->

                        <td>

                            <div class="complaint-text">

                                <?php

                                if (
                                    !empty(
                                        $consultation[
                                            "chief_complaint"
                                        ]
                                    )
                                ) {

                                    echo nl2br(
                                        htmlspecialchars(
                                            $consultation[
                                                "chief_complaint"
                                            ]
                                        )
                                    );

                                } else {

                                    echo "-";
                                }

                                ?>

                            </div>

                        </td>


                        <!-- ASSESSMENT -->

                        <td>

                            <div class="assessment-text">

                                <?php

                                if (
                                    !empty(
                                        $consultation[
                                            "assessment"
                                        ]
                                    )
                                ) {

                                    echo nl2br(
                                        htmlspecialchars(
                                            $consultation[
                                                "assessment"
                                            ]
                                        )
                                    );

                                } else {

                                    echo "-";
                                }

                                ?>

                            </div>

                        </td>


                        <!-- FOLLOW-UP DATE -->

                        <td>

                            <span class="followup-date-text">

                                <?php

                                echo htmlspecialchars(
                                    displayConsultationDate(
                                        $consultation[
                                            "follow_up_date"
                                        ]
                                    )
                                );

                                ?>

                            </span>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <span
                                class="consultation-status <?php echo $statusClass; ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $followUpStatus
                                );

                                ?>

                            </span>

                        </td>


                    </tr>


                <?php

                    }

                } else {

                ?>


                    <tr>

                        <td
                            colspan="9"
                            style="
                                text-align: center;
                                padding: 45px;
                                color: #777;
                            "
                        >

                            No consultation records
                            found for the selected period.

                        </td>

                    </tr>


                <?php

                }

                ?>


                </tbody>

            </table>

        </div>


        <!-- =================================================
             PAGINATION
        ================================================== -->

        <?php

        if ($totalPages > 1) {

        ?>

            <div class="pagination-container">


                <!-- PREVIOUS -->

                <?php

                if ($currentPage > 1) {

                    $previousPage =
                        $currentPage - 1;

                ?>

                    <a
                        class="pagination-link"
                        href="?page=<?php
                            echo $previousPage;
                        ?>&from_date=<?php
                            echo urlencode($fromDate);
                        ?>&to_date=<?php
                            echo urlencode($toDate);
                        ?>"
                    >
                        Previous
                    </a>

                <?php

                } else {

                ?>

                    <span class="pagination-link disabled">
                        Previous
                    </span>

                <?php

                }


                /* =========================================
                   PAGE NUMBER RANGE
                ========================================== */

                $startPage =
                    max(
                        1,
                        $currentPage - 2
                    );


                $endPage =
                    min(
                        $totalPages,
                        $currentPage + 2
                    );


                /* =========================================
                   FIRST PAGE
                ========================================== */

                if ($startPage > 1) {

                ?>

                    <a
                        class="pagination-link"
                        href="?page=1&from_date=<?php
                            echo urlencode($fromDate);
                        ?>&to_date=<?php
                            echo urlencode($toDate);
                        ?>"
                    >
                        1
                    </a>


                    <?php

                    if ($startPage > 2) {

                    ?>

                        <span class="pagination-link disabled">
                            ...
                        </span>

                    <?php

                    }

                }


                /* =========================================
                   PAGE NUMBERS
                ========================================== */

                for (
                    $pageNumber = $startPage;
                    $pageNumber <= $endPage;
                    $pageNumber++
                ) {

                ?>

                    <a
                        class="
                            pagination-link
                            <?php

                            if (
                                $pageNumber ==
                                $currentPage
                            ) {

                                echo "active";
                            }

                            ?>
                        "
                        href="?page=<?php
                            echo $pageNumber;
                        ?>&from_date=<?php
                            echo urlencode($fromDate);
                        ?>&to_date=<?php
                            echo urlencode($toDate);
                        ?>"
                    >

                        <?php
                        echo $pageNumber;
                        ?>

                    </a>

                <?php

                }


                /* =========================================
                   LAST PAGE
                ========================================== */

                if ($endPage < $totalPages) {

                    if (
                        $endPage <
                        ($totalPages - 1)
                    ) {

                    ?>

                        <span class="pagination-link disabled">
                            ...
                        </span>

                    <?php

                    }

                ?>

                    <a
                        class="pagination-link"
                        href="?page=<?php
                            echo $totalPages;
                        ?>&from_date=<?php
                            echo urlencode($fromDate);
                        ?>&to_date=<?php
                            echo urlencode($toDate);
                        ?>"
                    >

                        <?php
                        echo $totalPages;
                        ?>

                    </a>

                <?php

                }


                /* =========================================
                   NEXT
                ========================================== */

                if (
                    $currentPage <
                    $totalPages
                ) {

                    $nextPage =
                        $currentPage + 1;

                ?>

                    <a
                        class="pagination-link"
                        href="?page=<?php
                            echo $nextPage;
                        ?>&from_date=<?php
                            echo urlencode($fromDate);
                        ?>&to_date=<?php
                            echo urlencode($toDate);
                        ?>"
                    >
                        Next
                    </a>

                <?php

                } else {

                ?>

                    <span class="pagination-link disabled">
                        Next
                    </span>

                <?php

                }


                ?>

                <div class="pagination-info">

                    Page

                    <?php
                    echo $currentPage;
                    ?>

                    of

                    <?php
                    echo $totalPages;
                    ?>

                </div>


            </div>

        <?php

        }

        ?>


    </div>


</main>


<?php

/* =========================================================
   FOOTER
========================================================= */

include __DIR__ . "/../includes/footer.php";


$conn->close();

?>