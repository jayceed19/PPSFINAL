<?php

require_once "../config/database.php";

/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Patients";
$pageSubtitle = "Patient Management";

$basePath = "../";
$activePage = "patients";

/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

/* =========================================================
   QUERY PATIENTS
========================================================= */

if ($search != '') {

    $searchTerm = "%" . $search . "%";

    $sql = "
        SELECT *
        FROM patients
        WHERE patient_id LIKE ?
        OR last_name LIKE ?
        OR first_name LIKE ?
        OR middle_name LIKE ?
        OR contact_no LIKE ?
        OR philhealth_no LIKE ?
        ORDER BY id DESC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $sql = "
        SELECT *
        FROM patients
        ORDER BY id DESC
    ";

    $result = $conn->query($sql);
}

/* =========================================================
   FUNCTION FOR AGE
========================================================= */

function calculateAge($birthdate)
{
    if (empty($birthdate)) {
        return '-';
    }

    $birthDate = new DateTime($birthdate);
    $today = new DateTime();

    return $today->diff($birthDate)->y;
}

/* =========================================================
   SHARED HEADER
========================================================= */

include __DIR__ . "/../includes/header.php";

/* =========================================================
   SHARED NAVIGATION
========================================================= */

include __DIR__ . "/../includes/navigation.php";

?>

<style>

/* =========================================================
   PATIENT PAGE TOP
========================================================= */

.patient-page-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
}


/* =========================================================
   PATIENT PAGE TITLE
========================================================= */

.patient-page-title {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}


/* =========================================================
   SEARCH RESULT TEXT
========================================================= */

.patient-search-result {
    color: #777;
    font-size: 13px;
    white-space: nowrap;
}


/* =========================================================
   NEW PATIENT BUTTON
========================================================= */

.patient-page-action {
    flex-shrink: 0;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

    .patient-page-top {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .patient-page-title {
        flex-wrap: wrap;
    }

    .patient-search-result {
        white-space: normal;
    }

    .patient-page-action {
        align-self: flex-start;
    }

}

</style>


<main class="main-container">


    <!-- =====================================================
         PAGE TITLE + ACTION BUTTON
    ====================================================== -->

    <div class="patient-page-top">


        <div class="patient-page-title">

            <div class="page-title">

                <h2>
                    Patient List
                </h2>

            </div>


            <?php if ($search != '') { ?>

                <span class="patient-search-result">

                    — Search results for
                    "<strong>
                        <?php
                        echo htmlspecialchars($search);
                        ?>
                    </strong>"

                </span>

            <?php } ?>

        </div>


        <div class="patient-page-action">

            <a
                href="add.php"
                class="btn btn-primary"
            >
                + New Patient
            </a>

        </div>


    </div>


    <!-- =====================================================
         SEARCH
    ====================================================== -->

    <div class="card">

        <div class="card-title">
            Search Patients
        </div>

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                placeholder="Search patient name, ID, contact number, or PhilHealth No..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button
                type="submit"
                class="btn btn-primary"
            >
                Search
            </button>


            <?php if ($search != '') { ?>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Clear
                </a>

            <?php } ?>

        </form>

    </div>


    <!-- =====================================================
         PATIENT TABLE
    ====================================================== -->

    <div class="card">

        <div class="card-title">
            Patients
        </div>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>
                            Patient ID
                        </th>

                        <th>
                            Patient Name
                        </th>

                        <th>
                            Age
                        </th>

                        <th>
                            Sex
                        </th>

                        <th>
                            Contact
                        </th>

                        <th>
                            Date Registered
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                if (
                    $result &&
                    $result->num_rows > 0
                ) {

                    while (
                        $patient =
                        $result->fetch_assoc()
                    ) {

                        /* =====================================
                           AGE
                        ====================================== */

                        $age = calculateAge(
                            $patient['birthdate']
                        );


                        /* =====================================
                           FULL NAME
                        ====================================== */

                        $fullName =
                            $patient['last_name'] .
                            ", " .
                            $patient['first_name'];

                        if (
                            !empty(
                                $patient['middle_name']
                            )
                        ) {

                            $fullName .=
                                " " .
                                $patient['middle_name'];
                        }


                        /* =====================================
                           PATIENT STATUS
                        ====================================== */

                        $patientStatus =
                            !empty(
                                $patient['status']
                            )
                                ? $patient['status']
                                : 'Active';


                        /* =====================================
                           STATUS CLASS
                        ====================================== */

                        if (
                            $patientStatus ==
                            'Active'
                        ) {

                            $statusClass =
                                'status-active';

                        }

                        elseif (
                            $patientStatus ==
                            'Inactive'
                        ) {

                            $statusClass =
                                'status-inactive';

                        }

                        elseif (
                            $patientStatus ==
                            'Deceased'
                        ) {

                            $statusClass =
                                'status-deceased';

                        }

                        else {

                            $statusClass =
                                'status-inactive';
                        }

                ?>

                    <tr>

                        <!-- PATIENT ID -->

                        <td>

                            <strong
                                style="
                                    color:#1f4e78;
                                "
                            >

                                <?php

                                echo htmlspecialchars(
                                    $patient['patient_id']
                                );

                                ?>

                            </strong>

                        </td>


                        <!-- PATIENT NAME -->

                        <td>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $fullName
                                );

                                ?>

                            </strong>

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
                                $patient['sex']
                            );

                            ?>

                        </td>


                        <!-- CONTACT -->

                        <td>

                            <?php

                            echo !empty(
                                $patient['contact_no']
                            )

                                ? htmlspecialchars(
                                    $patient['contact_no']
                                )

                                : '-';

                            ?>

                        </td>


                        <!-- DATE REGISTERED -->

                        <td>

                            <?php

                            if (
                                !empty(
                                    $patient[
                                        'date_registered'
                                    ]
                                )
                            ) {

                                echo date(
                                    "M d, Y",
                                    strtotime(
                                        $patient[
                                            'date_registered'
                                        ]
                                    )
                                );

                            } else {

                                echo '-';

                            }

                            ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <span
                                class="status <?php
                                    echo $statusClass;
                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $patientStatus
                                );

                                ?>

                            </span>

                        </td>


                        <!-- ACTION -->

                        <td>

                            <a
                                href="view.php?id=<?php
                                    echo $patient['id'];
                                ?>"
                                class="btn btn-primary"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="8"
                            style="
                                text-align:center;
                                padding:45px;
                                color:#777;
                            "
                        >

                            <?php

                            if ($search != '') {

                            ?>

                                No patient found for

                                "<strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $search
                                    );

                                    ?>

                                </strong>".

                            <?php

                            } else {

                            ?>

                                No registered patients yet.

                            <?php

                            }

                            ?>

                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>

</main>


<?php

/* =========================================================
   FOOTER
========================================================= */

include __DIR__ . "/../includes/footer.php";

/* =========================================================
   CLOSE DATABASE
========================================================= */

$conn->close();

?>