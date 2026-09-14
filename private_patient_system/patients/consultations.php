<?php

require_once "../config/database.php";
require_once "../config/auth.php";


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Consultation";
$pageSubtitle = "Search Patient";
$basePath = "../";
$activePage = "consultations";


/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$patients = null;


if ($search !== '') {

    $searchTerm = "%" . $search . "%";


    /* =====================================================
       SEARCH PATIENTS
    ===================================================== */

    $sql = "
        SELECT
            id,
            patient_id,
            last_name,
            first_name,
            middle_name,
            birthdate,
            sex,
            philhealth_no,
            contact_no
        FROM patients
        WHERE
            patient_id LIKE ?
            OR last_name LIKE ?
            OR first_name LIKE ?
            OR middle_name LIKE ?
            OR philhealth_no LIKE ?
            OR contact_no LIKE ?
        ORDER BY
            last_name ASC,
            first_name ASC
        LIMIT 100
    ";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        die(
            "Database error: " .
            $conn->error
        );

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


    $patients = $stmt->get_result();

}


/* =========================================================
   SHARED HEADER
========================================================= */

include __DIR__ .
    "/../includes/header.php";


/* =========================================================
   SHARED NAVIGATION
========================================================= */

include __DIR__ .
    "/../includes/navigation.php";

?>


<style>

/* =========================================================
   CONSULTATION SEARCH PAGE
========================================================= */

.consultation-search-page {

    max-width: 1100px;

    margin: 0 auto;

}


/* =========================================================
   PAGE TITLE
========================================================= */

.consultation-search-page .page-title {

    margin-bottom: 20px;

}


/* =========================================================
   SEARCH CARD
========================================================= */

.consultation-search-card {

    background: #ffffff;

    border: 1px solid #e1e5e9;

    border-radius: 8px;

    padding: 25px;

    margin-bottom: 20px;

}


.consultation-search-subtitle {

    margin: 0 0 20px 0;

    color: #777777;

    font-size: 13px;

}


/* =========================================================
   SEARCH FORM
========================================================= */

.consultation-search-form {

    display: flex;

    gap: 10px;

}


.consultation-search-input {

    flex: 1;

    height: 44px;

    padding: 0 14px;

    border: 1px solid #d5dbe1;

    border-radius: 6px;

    outline: none;

    font-family: inherit;

    font-size: 14px;

}


.consultation-search-input:focus {

    border-color: #1f4e78;

    box-shadow:
        0 0 0 2px
        rgba(31, 78, 120, 0.08);

}


.consultation-search-button {

    height: 44px;

    padding: 0 22px;

    background: #1f4e78;

    color: #ffffff;

    border: 1px solid #1f4e78;

    border-radius: 6px;

    font-family: inherit;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;

}


.consultation-search-button:hover {

    background: #173a5c;

    border-color: #173a5c;

}


.consultation-clear-button {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    height: 44px;

    padding: 0 17px;

    background: #ffffff;

    color: #555555;

    border: 1px solid #d5dbe1;

    border-radius: 6px;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

}


.consultation-clear-button:hover {

    background: #f4f6f8;

}


/* =========================================================
   SEARCH HELP
========================================================= */

.consultation-search-help {

    margin-top: 10px;

    color: #888888;

    font-size: 11px;

}


/* =========================================================
   RESULTS CARD
========================================================= */

.consultation-results-card {

    background: #ffffff;

    border: 1px solid #e1e5e9;

    border-radius: 8px;

    overflow: hidden;

}


/* =========================================================
   RESULTS HEADER
========================================================= */

.consultation-results-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 17px 22px;

    border-bottom: 1px solid #e5e8eb;

}


.consultation-results-title {

    margin: 0;

    color: #1f4e78;

    font-size: 17px;

    font-weight: 700;

}


.consultation-results-count {

    color: #777777;

    font-size: 12px;

}


/* =========================================================
   PATIENT RESULT
========================================================= */

.consultation-patient-result {

    display: block;

    padding: 18px 22px;

    border-bottom: 1px solid #edf0f2;

    color: inherit;

    text-decoration: none;

    transition: 0.15s ease;

}


.consultation-patient-result:last-child {

    border-bottom: none;

}


.consultation-patient-result:hover {

    background: #f7f9fb;

}


/* =========================================================
   RESULT CONTENT
========================================================= */

.consultation-result-content {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

}


.consultation-result-left {

    min-width: 0;

}


.consultation-result-name {

    margin: 0 0 7px 0;

    color: #1f4e78;

    font-size: 16px;

    font-weight: 700;

}


.consultation-result-details {

    display: flex;

    flex-wrap: wrap;

    gap: 7px 20px;

    color: #666666;

    font-size: 12px;

}


.consultation-result-detail strong {

    color: #444444;

}


.consultation-result-arrow {

    flex-shrink: 0;

    color: #1f4e78;

    font-size: 20px;

    font-weight: 600;

}


/* =========================================================
   INITIAL MESSAGE
========================================================= */

.consultation-empty {

    text-align: center;

    padding: 60px 20px;

}


.consultation-empty-title {

    color: #555555;

    font-size: 17px;

    font-weight: 600;

    margin-bottom: 8px;

}


.consultation-empty-text {

    color: #888888;

    font-size: 13px;

}


/* =========================================================
   NO RESULTS
========================================================= */

.consultation-no-results {

    text-align: center;

    padding: 55px 20px;

}


.consultation-no-results-title {

    color: #555555;

    font-size: 16px;

    font-weight: 600;

    margin-bottom: 7px;

}


.consultation-no-results-text {

    color: #888888;

    font-size: 13px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

    .consultation-search-form {

        flex-direction: column;

    }


    .consultation-search-button,
    .consultation-clear-button {

        width: 100%;

    }


    .consultation-result-content {

        align-items: flex-start;

    }


    .consultation-result-details {

        flex-direction: column;

        gap: 4px;

    }

}

</style>


<main class="main-container">

    <div class="consultation-search-page">


        <!-- =================================================
             PAGE TITLE
        ================================================== -->

        <div class="page-title">

            <h2>

                Consultation

            </h2>

        </div>


        <!-- =================================================
             SEARCH
        ================================================== -->

        <div class="consultation-search-card">


            <p class="consultation-search-subtitle">

                Search for a patient to start a consultation.

            </p>


            <form
                method="GET"
                action="consultations.php"
                class="consultation-search-form"
            >


                <input
                    type="text"
                    name="search"
                    class="consultation-search-input"
                    value="<?php
                        echo htmlspecialchars($search);
                    ?>"
                    placeholder="Search Patient ID, Name, PhilHealth No., or Contact No."
                    autocomplete="off"
                    autofocus
                >


                <button
                    type="submit"
                    class="consultation-search-button"
                >

                    Search

                </button>


                <?php if ($search !== '') { ?>

                    <a
                        href="consultations.php"
                        class="consultation-clear-button"
                    >

                        Clear

                    </a>

                <?php } ?>


            </form>


            <div class="consultation-search-help">

                Search by Patient ID, Last Name, First Name,
                Middle Name, PhilHealth No., or Contact No.

            </div>


        </div>



        <?php if ($search === '') { ?>


            <!-- =================================================
                 INITIAL STATE
            ================================================== -->

            <div class="consultation-results-card">


                <div class="consultation-empty">


                    <div class="consultation-empty-title">

                        Search for a patient

                    </div>


                    <div class="consultation-empty-text">

                        Enter the patient's name or any available
                        patient information above.

                    </div>


                </div>


            </div>


        <?php } else { ?>


            <!-- =================================================
                 SEARCH RESULTS
            ================================================== -->

            <div class="consultation-results-card">


                <div class="consultation-results-header">


                    <h2 class="consultation-results-title">

                        Patient Results

                    </h2>


                    <div class="consultation-results-count">

                        <?php

                        echo $patients
                            ? $patients->num_rows
                            : 0;

                        ?>

                        result(s)

                    </div>


                </div>



                <?php if (
                    $patients &&
                    $patients->num_rows > 0
                ) { ?>


                    <?php while (
                        $patient =
                        $patients->fetch_assoc()
                    ) { ?>


                        <?php

                        /* =================================================
                           FULL NAME
                        ================================================== */

                        $fullName =
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

                        $fullName .=
                            " " .
                            $patient['last_name'];

                        ?>


                        <!-- =================================================
                             CLICKABLE PATIENT
                        ================================================== -->

                        <a
                            href="consultation.php?patient_id=<?php
                                echo (int)$patient['id'];
                            ?>"
                            class="consultation-patient-result"
                        >


                            <div class="consultation-result-content">


                                <div class="consultation-result-left">


                                    <h3 class="consultation-result-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $fullName
                                        );

                                        ?>

                                    </h3>


                                    <div
                                        class="consultation-result-details"
                                    >


                                        <!-- PATIENT ID -->

                                        <div
                                            class="consultation-result-detail"
                                        >

                                            Patient ID:

                                            <strong>

                                                <?php

                                                echo htmlspecialchars(
                                                    $patient['patient_id']
                                                );

                                                ?>

                                            </strong>

                                        </div>



                                        <!-- PHILHEALTH -->

                                        <?php if (
                                            !empty(
                                                $patient[
                                                    'philhealth_no'
                                                ]
                                            )
                                        ) { ?>


                                            <div
                                                class="consultation-result-detail"
                                            >

                                                PhilHealth:

                                                <strong>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $patient[
                                                            'philhealth_no'
                                                        ]
                                                    );

                                                    ?>

                                                </strong>

                                            </div>


                                        <?php } ?>



                                        <!-- CONTACT -->

                                        <?php if (
                                            !empty(
                                                $patient[
                                                    'contact_no'
                                                ]
                                            )
                                        ) { ?>


                                            <div
                                                class="consultation-result-detail"
                                            >

                                                Contact:

                                                <strong>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $patient[
                                                            'contact_no'
                                                        ]
                                                    );

                                                    ?>

                                                </strong>

                                            </div>


                                        <?php } ?>



                                        <!-- SEX -->

                                        <div
                                            class="consultation-result-detail"
                                        >

                                            Sex:

                                            <strong>

                                                <?php

                                                echo !empty(
                                                    $patient['sex']
                                                )
                                                    ? htmlspecialchars(
                                                        $patient['sex']
                                                    )
                                                    : "-";

                                                ?>

                                            </strong>

                                        </div>


                                    </div>


                                </div>


                                <!-- ARROW -->

                                <div
                                    class="consultation-result-arrow"
                                >

                                    →

                                </div>


                            </div>


                        </a>


                    <?php } ?>


                <?php } else { ?>


                    <!-- =================================================
                         NO RESULTS
                    ================================================== -->

                    <div class="consultation-no-results">


                        <div class="consultation-no-results-title">

                            No patient found.

                        </div>


                        <div class="consultation-no-results-text">

                            Try searching using another
                            Patient ID, name, PhilHealth
                            number, or contact number.

                        </div>


                    </div>


                <?php } ?>


            </div>


        <?php } ?>


    </div>

</main>


<?php

/* =========================================================
   CLOSE DATABASE
========================================================= */

if (isset($stmt)) {

    $stmt->close();

}

$conn->close();


/* =========================================================
   FOOTER
========================================================= */

include __DIR__ .
    "/../includes/footer.php";

?>