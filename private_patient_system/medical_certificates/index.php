<?php

require_once "../config/database.php";
require_once "../config/auth.php";


/* =========================================================
   GET PATIENT ID
========================================================= */

$patientId = isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;


if ($patientId <= 0) {

    die("Invalid patient ID.");

}


/* =========================================================
   GET PATIENT
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        patient_id,
        first_name,
        middle_name,
        last_name,
        birthdate,
        sex,
        address
    FROM patients
    WHERE id = ?
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
    $patientId
);


if (!$stmt->execute()) {

    $stmt->close();

    die("Unable to retrieve patient.");

}


$result = $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();

    die("Patient not found.");

}


$patient = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   PATIENT NAME
========================================================= */

$fullName = trim(

    $patient["first_name"] . " " .
    $patient["middle_name"] . " " .
    $patient["last_name"]

);


$fullName = preg_replace(
    '/\s+/',
    ' ',
    $fullName
);


/* =========================================================
   AGE
========================================================= */

$age = "";

if (!empty($patient["birthdate"])) {

    try {

        $birthDate = new DateTime(
            $patient["birthdate"]
        );

        $today = new DateTime();

        $age = $today->diff(
            $birthDate
        )->y;

    } catch (Exception $e) {

        $age = "";

    }

}


/* =========================================================
   GET MEDICAL CERTIFICATE HISTORY
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        certificate_date,
        diagnosis
    FROM medical_certificates
    WHERE patient_id = ?
    ORDER BY certificate_date DESC, id DESC
");


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$stmt->bind_param(
    "i",
    $patientId
);


if (!$stmt->execute()) {

    $stmt->close();

    die("Unable to retrieve medical certificate history.");

}


$certificates = $stmt->get_result();

$stmt->close();


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Medical Certificate History";
$pageSubtitle = "Medical certificates of patient";
$basePath = "../";
$activePage = "patients";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($pageTitle); ?>
    </title>

    <?php include "../includes/header.php"; ?>

</head>


<body>


<?php include "../includes/navigation.php"; ?>


<main class="main-content">


    <div class="page-container">


        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->

        <div class="page-header">


            <div>

                <h1>
                    Medical Certificate History
                </h1>

                <p>
                    <?php
                    echo htmlspecialchars(
                        $fullName
                    );
                    ?>
                </p>

            </div>


            <div class="page-header-actions">


                <!-- BACK TO MEDICAL CERTIFICATE -->

                <a
                    href="add.php?patient_id=<?php echo $patientId; ?>"
                    class="btn btn-secondary"
                >
                    ← Back
                </a>


            </div>


        </div>



        <!-- =====================================================
             PATIENT INFORMATION
        ====================================================== -->

        <div class="card patient-info-card">


            <div class="card-header">

                <h2>
                    Patient Information
                </h2>

            </div>


            <div class="patient-info-grid">


                <!-- PATIENT ID -->

                <div class="info-item">

                    <span class="info-label">
                        Patient ID
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $patient["patient_id"]
                        );

                        ?>

                    </strong>

                </div>



                <!-- PATIENT NAME -->

                <div class="info-item">

                    <span class="info-label">
                        Patient Name
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            strtoupper($fullName)
                        );

                        ?>

                    </strong>

                </div>



                <!-- AGE -->

                <div class="info-item">

                    <span class="info-label">
                        Age
                    </span>

                    <strong>

                        <?php

                        if ($age !== "") {

                            echo htmlspecialchars(
                                $age
                            );

                        } else {

                            echo "-";

                        }

                        ?>

                    </strong>

                </div>



                <!-- SEX -->

                <div class="info-item">

                    <span class="info-label">
                        Sex
                    </span>

                    <strong>

                        <?php

                        if (!empty($patient["sex"])) {

                            echo htmlspecialchars(
                                strtoupper(
                                    $patient["sex"]
                                )
                            );

                        } else {

                            echo "-";

                        }

                        ?>

                    </strong>

                </div>


            </div>


        </div>



        <!-- =====================================================
             CERTIFICATE HISTORY
        ====================================================== -->

        <div class="card">


            <div class="card-header">


                <div>

                    <h2>
                        Certificate History
                    </h2>


                    <p class="card-subtitle">

                        <?php
                        echo $certificates->num_rows;
                        ?>

                        certificate(s) found

                    </p>

                </div>


            </div>



            <?php if ($certificates->num_rows > 0): ?>


                <div class="table-responsive">


                    <table class="data-table">


                        <thead>

                            <tr>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Diagnosis
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while (
                            $certificate =
                            $certificates->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- =================================================
                                     DATE
                                ================================================== -->

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $certificate[
                                                "certificate_date"
                                            ]
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            date(
                                                "M d, Y",
                                                strtotime(
                                                    $certificate[
                                                        "certificate_date"
                                                    ]
                                                )
                                            )
                                        );

                                    } else {

                                        echo "-";

                                    }

                                    ?>

                                </td>



                                <!-- =================================================
                                     DIAGNOSIS
                                ================================================== -->

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $certificate["diagnosis"]
                                        )
                                    ) {

                                        $diagnosis =
                                            $certificate[
                                                "diagnosis"
                                            ];

                                        echo nl2br(
                                            htmlspecialchars(
                                                $diagnosis
                                            )
                                        );

                                    } else {

                                    ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php

                                    }

                                    ?>

                                </td>



                                <!-- =================================================
                                     ACTIONS
                                ================================================== -->

                                <td>

                                    <div class="action-buttons">


                                        <a
                                            href="print.php?id=<?php echo intval($certificate["id"]); ?>"
                                            class="btn btn-small btn-primary"
                                            target="_blank"
                                        >
                                            View / Print
                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <!-- =================================================
                     EMPTY STATE
                ================================================== -->

                <div class="empty-state">


                    <div class="empty-icon">
                        📄
                    </div>


                    <h3>
                        No Medical Certificates
                    </h3>


                    <p>
                        This patient does not have any
                        medical certificate records yet.
                    </p>


                    <a
                        href="add.php?patient_id=<?php echo $patientId; ?>"
                        class="btn btn-primary"
                    >
                        + Create Medical Certificate
                    </a>


                </div>


            <?php endif; ?>


        </div>


    </div>


</main>


<?php include "../includes/footer.php"; ?>



<style>

/* =========================================================
   PAGE CONTAINER
========================================================= */

.page-container {

    max-width: 1200px;

    margin: 0 auto;

    padding: 25px;

}


/* =========================================================
   PAGE HEADER
========================================================= */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 20px;

}


.page-header h1 {

    margin: 0;

    color: #1f4e78;

}


.page-header p {

    margin: 5px 0 0;

    color: #666;

}


.page-header-actions {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

}


/* =========================================================
   CARDS
========================================================= */

.card {

    background: #ffffff;

    border-radius: 10px;

    padding: 20px;

    margin-bottom: 20px;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.08);

}


.card-header {

    margin-bottom: 18px;

}


.card-header h2 {

    margin: 0;

}


.card-subtitle {

    margin: 5px 0 0;

    color: #777;

    font-size: 14px;

}


/* =========================================================
   PATIENT INFORMATION
========================================================= */

.patient-info-grid {

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 20px;

}


.info-item {

    display: flex;

    flex-direction: column;

    gap: 5px;

}


.info-label {

    font-size: 13px;

    color: #777;

}


.info-item strong {

    color: #222;

}


/* =========================================================
   TABLE
========================================================= */

.table-responsive {

    width: 100%;

    overflow-x: auto;

}


.data-table {

    width: 100%;

    border-collapse: collapse;

    min-width: 650px;

}


.data-table th {

    background: #12355b;

    color: #ffffff;

    text-align: left;

    padding: 13px 12px;

    font-size: 14px;

}


.data-table td {

    padding: 13px 12px;

    border-bottom:
        1px solid #e5e5e5;

    color: #222;

    vertical-align: top;

}


.data-table tbody tr:hover {

    background: #f8fafc;

}


/* =========================================================
   BUTTONS
========================================================= */

.btn {

    display: inline-block;

    padding: 9px 15px;

    border-radius: 6px;

    text-decoration: none;

    border: none;

    cursor: pointer;

    font-size: 14px;

    font-weight: 600;

}


.btn-primary {

    background: #12355b;

    color: #ffffff;

}


.btn-primary:hover {

    background: #0d2947;

}


.btn-secondary {

    background: #e9ecef;

    color: #222;

}


.btn-secondary:hover {

    background: #dfe3e6;

}


.btn-small {

    padding: 7px 11px;

    font-size: 13px;

}


.action-buttons {

    display: flex;

    gap: 6px;

    flex-wrap: wrap;

}


.text-muted {

    color: #999;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    text-align: center;

    padding: 50px 20px;

}


.empty-icon {

    font-size: 40px;

    margin-bottom: 10px;

}


.empty-state h3 {

    margin: 5px 0;

}


.empty-state p {

    color: #777;

    margin-bottom: 20px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .patient-info-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }


    .page-header {

        align-items: flex-start;

        flex-direction: column;

    }

}


@media (max-width: 600px) {

    .page-container {

        padding: 15px;

    }


    .patient-info-grid {

        grid-template-columns: 1fr;

    }


    .page-header-actions {

        width: 100%;

        flex-direction: column;

    }


    .page-header-actions .btn {

        width: 100%;

        text-align: center;

    }

}

</style>


</body>

</html>