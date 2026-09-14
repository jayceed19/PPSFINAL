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
   GET TOTAL CONSULTATIONS FOR YEAR
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
   HEADER
========================================================= */

require_once "../includes/header.php";

require_once "../includes/navigation.php";

?>

<style>

.report-container {
    padding: 25px;
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.report-title h2 {
    margin: 0;
    font-size: 25px;
}

.report-title p {
    margin: 5px 0 0;
    color: #666;
}

.year-filter {
    display: flex;
    align-items: center;
    gap: 10px;
}

.year-filter label {
    font-weight: 600;
}

.year-filter select {
    padding: 9px 35px 9px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}

.year-filter button {
    padding: 9px 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    background: #1f4e79;
    color: white;
    font-weight: 600;
}

.year-filter button:hover {
    opacity: 0.9;
}


/* =========================================================
   SUMMARY CARDS
========================================================= */

.summary-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.summary-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.summary-card-title {
    color: #666;
    font-size: 14px;
    margin-bottom: 8px;
}

.summary-card-value {
    font-size: 28px;
    font-weight: 700;
}


/* =========================================================
   REPORT TABLE
========================================================= */

.report-table-container {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.report-table-header {
    padding: 18px 20px;
    border-bottom: 1px solid #ddd;
}

.report-table-header h3 {
    margin: 0;
    font-size: 18px;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
}

.report-table th,
.report-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    text-align: left;
}

.report-table th {
    background: #f5f5f5;
    font-weight: 700;
}

.report-table td:last-child,
.report-table th:last-child {
    text-align: center;
}

.report-table tr:last-child td {
    border-bottom: none;
}

.empty-message {
    text-align: center;
    padding: 35px;
    color: #777;
}


/* =========================================================
   PRINT
========================================================= */

@media print {

    .year-filter,
    .sidebar,
    .navigation,
    nav,
    .no-print {
        display: none !important;
    }

    .report-container {
        padding: 0;
    }

    .summary-cards {
        grid-template-columns: repeat(3, 1fr);
    }

    .report-table-container {
        border: 1px solid #000;
    }

}

@media (max-width: 800px) {

    .report-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .summary-cards {
        grid-template-columns: 1fr;
    }

}

</style>


<div class="report-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="report-header">

        <div class="report-title">

            <h2>
                Diagnosis Report
            </h2>

            <p>
                Diagnosis and case summary for
                <?php echo htmlspecialchars($selectedYear); ?>
            </p>

        </div>


        <form
            method="GET"
            class="year-filter"
        >

            <label for="year">
                Year:
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
                        if ($year == $selectedYear) {
                            echo "selected";
                        }
                        ?>
                    >
                        <?php echo $year; ?>
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
         SUMMARY
    ====================================================== -->

    <div class="summary-cards">


        <div class="summary-card">

            <div class="summary-card-title">
                Total Consultations
            </div>

            <div class="summary-card-value">
                <?php
                echo number_format(
                    $totalConsultations
                );
                ?>
            </div>

        </div>


        <div class="summary-card">

            <div class="summary-card-title">
                Total Cases with Diagnosis
            </div>

            <div class="summary-card-value">
                <?php
                echo number_format(
                    $totalCases
                );
                ?>
            </div>

        </div>


        <div class="summary-card">

            <div class="summary-card-title">
                Different Diagnoses
            </div>

            <div class="summary-card-value">
                <?php
                echo number_format(
                    $totalDiagnoses
                );
                ?>
            </div>

        </div>


    </div>



    <!-- =====================================================
         DIAGNOSIS TABLE
    ====================================================== -->

    <div class="report-table-container">


        <div class="report-table-header">

            <h3>
                Diagnosis Summary -
                <?php echo htmlspecialchars($selectedYear); ?>
            </h3>

        </div>


        <?php if (count($diagnoses) > 0) { ?>


            <table class="report-table">

                <thead>

                    <tr>

                        <th style="width: 70px;">
                            #
                        </th>

                        <th>
                            Diagnosis
                        </th>

                        <th style="width: 180px;">
                            Number of Cases
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

                    ?>

                        <tr>

                            <td>
                                <?php
                                echo $number;
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $diagnosis["diagnosis"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo number_format(
                                    intval(
                                        $diagnosis["total_cases"]
                                    )
                                );
                                ?>
                            </td>

                        </tr>

                    <?php

                        $number++;
                    }

                    ?>

                </tbody>

            </table>


        <?php } else { ?>


            <div class="empty-message">

                No diagnosis records found for
                <?php
                echo htmlspecialchars(
                    $selectedYear
                );
                ?>.

            </div>


        <?php } ?>


    </div>


</div>


<?php

require_once "../includes/footer.php";

?>