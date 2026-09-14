<?php

require_once "../config/database.php";
require_once "../config/auth.php";


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Prescription";
$pageSubtitle = "Search Patient";
$basePath = "../";
$activePage = "prescriptions";


/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";

$patients = array();


/* =========================================================
   SEARCH PATIENT
========================================================= */

if ($search !== "") {

    $searchTerm = "%" . $search . "%";

    $sql = "
        SELECT
            id,
            patient_id,
            first_name,
            middle_name,
            last_name,
            sex,
            contact_no,
            philhealth_no
        FROM patients
        WHERE
            patient_id LIKE ?
            OR last_name LIKE ?
            OR first_name LIKE ?
            OR middle_name LIKE ?
            OR philhealth_no LIKE ?
            OR contact_no LIKE ?
        ORDER BY last_name ASC, first_name ASC
        LIMIT 100
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

    while ($row = $result->fetch_assoc()) {

        $patients[] = $row;

    }

    $stmt->close();
}

?>


<?php include __DIR__ . "/../includes/header.php"; ?>

<?php include __DIR__ . "/../includes/navigation.php"; ?>


<style>

/* =========================================================
   PRESCRIPTION SEARCH PAGE
========================================================= */

.prescription-search-page {

    width: calc(100% - 40px);

    max-width: 1200px;

    margin: 0 auto;

    padding: 20px 0 40px;

    box-sizing: border-box;

}


/* =========================================================
   PAGE TITLE
   SAME STYLE / HEIGHT AS LABORATORY
========================================================= */

.prescription-search-page .page-title {

    margin-bottom: 20px;

}


/* =========================================================
   SEARCH CARD
========================================================= */

.prescription-search-card {

    background: #ffffff;

    border: 1px solid #e1e5e9;

    border-radius: 8px;

    padding: 20px;

    margin-bottom: 20px;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.04);

}


.prescription-search-form {

    display: flex;

    gap: 10px;

    align-items: center;

}


.prescription-search-input {

    flex: 1;

    height: 42px;

    padding: 0 13px;

    border: 1px solid #cfd5db;

    border-radius: 6px;

    font-size: 14px;

    outline: none;

    box-sizing: border-box;

}


.prescription-search-input:focus {

    border-color: #1f4e78;

    box-shadow:
        0 0 0 2px
        rgba(31, 78, 120, 0.08);

}


.prescription-search-button {

    height: 42px;

    padding: 0 20px;

    border: none;

    border-radius: 6px;

    background: #1f4e78;

    color: #ffffff;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;

}


.prescription-search-button:hover {

    background: #173b5d;

}


/* =========================================================
   RESULTS CARD
========================================================= */

.prescription-results-card {

    background: #ffffff;

    border: 1px solid #e1e5e9;

    border-radius: 8px;

    overflow: hidden;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.04);

}


/* =========================================================
   RESULTS HEADER
========================================================= */

.prescription-results-header {

    padding: 16px 20px;

    border-bottom: 1px solid #e5e7eb;

    background: #f8fafc;

}


.prescription-results-header h2 {

    margin: 0;

    font-size: 16px;

    color: #1f2937;

}


.prescription-results-header span {

    display: block;

    margin-top: 3px;

    font-size: 12px;

    color: #6b7280;

}


/* =========================================================
   PATIENT RESULT CONTAINER
========================================================= */

.prescription-patient-result {

    margin: 14px 16px;

    padding: 16px 18px;

    background: #ffffff;

    border: 1px solid #e1e5e9;

    border-radius: 8px;

    box-sizing: border-box;

}


.prescription-patient-result:last-child {

    margin-bottom: 16px;

}


/* =========================================================
   PATIENT TOP
========================================================= */

.prescription-patient-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;

    margin-bottom: 12px;

}


.prescription-patient-name {

    font-size: 16px;

    font-weight: 700;

    color: #1f2937;

    margin-bottom: 6px;

}


.prescription-patient-details {

    display: flex;

    flex-wrap: wrap;

    gap: 6px 16px;

    font-size: 12px;

    color: #6b7280;

}


.prescription-patient-details strong {

    color: #374151;

}


/* =========================================================
   CONSULTATION SECTION
========================================================= */

.prescription-consultation-section {

    margin-top: 8px;

}


.prescription-consultation-title {

    font-size: 13px;

    font-weight: 700;

    color: #374151;

    margin-bottom: 7px;

}


/* =========================================================
   CONSULTATION LIST
========================================================= */

.prescription-consultation-list {

    border: 1px solid #e5e7eb;

    border-radius: 6px;

    overflow: hidden;

}


.prescription-consultation-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 11px 13px;

    background: #ffffff;

    border-bottom: 1px solid #e5e7eb;

}


.prescription-consultation-row:last-child {

    border-bottom: none;

}


.prescription-consultation-info {

    min-width: 0;

    flex: 1;

}


.prescription-consultation-date {

    font-size: 13px;

    font-weight: 700;

    color: #1f2937;

    margin-bottom: 3px;

}


.prescription-consultation-complaint {

    font-size: 12px;

    color: #6b7280;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

}


.prescription-select-button {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 130px;

    height: 34px;

    padding: 0 14px;

    background: #1f4e78;

    color: #ffffff;

    text-decoration: none;

    border-radius: 5px;

    font-size: 12px;

    font-weight: 600;

    flex-shrink: 0;

}


.prescription-select-button:hover {

    background: #173b5d;

}


/* =========================================================
   NO CONSULTATION
========================================================= */

.prescription-no-consultation {

    padding: 11px 13px;

    background: #f9fafb;

    border: 1px solid #e5e7eb;

    border-radius: 6px;

    color: #6b7280;

    font-size: 12px;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.prescription-empty-state {

    padding: 55px 20px;

    text-align: center;

}


.prescription-empty-state h3 {

    margin: 0 0 7px;

    font-size: 16px;

    color: #374151;

}


.prescription-empty-state p {

    margin: 0;

    font-size: 13px;

    color: #6b7280;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

    .prescription-search-page {

        width: calc(100% - 20px);

        padding: 15px 0 30px;

    }


    .prescription-search-form {

        flex-direction: column;

        align-items: stretch;

    }


    .prescription-search-button {

        width: 100%;

    }


    .prescription-patient-result {

        margin: 10px;

        padding: 14px;

    }


    .prescription-patient-top {

        flex-direction: column;

        gap: 10px;

    }


    .prescription-consultation-row {

        flex-direction: column;

        align-items: stretch;

    }


    .prescription-select-button {

        width: 100%;

    }

}

</style>


<main class="main-container">

    <div class="prescription-search-page">


        <!-- =================================================
             PAGE TITLE
             SAME AS LABORATORY
        ================================================== -->

        <div class="page-title">

            <h2>Prescription</h2>

        </div>


        <!-- =================================================
             SEARCH
        ================================================== -->

        <div class="prescription-search-card">

            <form
                method="GET"
                action=""
                class="prescription-search-form"
            >

                <input
                    type="text"
                    name="search"
                    class="prescription-search-input"
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search Patient ID, Name, PhilHealth No., or Contact No."
                    autocomplete="off"
                >


                <button
                    type="submit"
                    class="prescription-search-button"
                >
                    Search Patient
                </button>

            </form>

        </div>


        <?php if ($search === ""): ?>


            <!-- =================================================
                 INITIAL STATE
            ================================================== -->

            <div class="prescription-results-card">

                <div class="prescription-empty-state">

                    <h3>
                        Search for a Patient
                    </h3>

                    <p>
                        Enter a Patient ID, name, PhilHealth number,
                        or contact number to continue.
                    </p>

                </div>

            </div>


        <?php elseif (count($patients) === 0): ?>


            <!-- =================================================
                 NO PATIENT FOUND
            ================================================== -->

            <div class="prescription-results-card">

                <div class="prescription-empty-state">

                    <h3>
                        No Patient Found
                    </h3>

                    <p>
                        No patient record matched
                        "<?php echo htmlspecialchars($search); ?>".
                    </p>

                </div>

            </div>


        <?php else: ?>


            <!-- =================================================
                 PATIENT RESULTS
            ================================================== -->

            <div class="prescription-results-card">


                <div class="prescription-results-header">

                    <h2>
                        Patient Results
                    </h2>

                    <span>

                        <?php echo count($patients); ?>

                        patient(s) found

                    </span>

                </div>


                <?php foreach ($patients as $patient): ?>


                    <?php

                    /* =============================================
                       BUILD PATIENT NAME
                    ============================================== */

                    $patientName =
                        $patient["first_name"];

                    if (!empty($patient["middle_name"])) {

                        $patientName .=
                            " " .
                            $patient["middle_name"];

                    }

                    $patientName .=
                        " " .
                        $patient["last_name"];


                    /* =============================================
                       GET CONSULTATIONS
                    ============================================== */

                    $consultationSql = "

                        SELECT
                            id,
                            visit_date,
                            chief_complaint

                        FROM consultations

                        WHERE patient_id = ?

                        ORDER BY
                            visit_date DESC,
                            id DESC

                        LIMIT 20

                    ";


                    $consultationStmt =
                        $conn->prepare(
                            $consultationSql
                        );


                    $consultations = array();


                    if ($consultationStmt) {

                        $consultationStmt->bind_param(
                            "i",
                            $patient["id"]
                        );


                        $consultationStmt->execute();


                        $consultationResult =
                            $consultationStmt->get_result();


                        while (
                            $consultation =
                            $consultationResult->fetch_assoc()
                        ) {

                            $consultations[] =
                                $consultation;

                        }


                        $consultationStmt->close();

                    }

                    ?>


                    <!-- =============================================
                         PATIENT
                    ============================================== -->

                    <div class="prescription-patient-result">


                        <div class="prescription-patient-top">

                            <div>

                                <div class="prescription-patient-name">

                                    <?php

                                    echo htmlspecialchars(
                                        $patientName
                                    );

                                    ?>

                                </div>


                                <div
                                    class="prescription-patient-details"
                                >


                                    <!-- PATIENT ID -->

                                    <span>

                                        <strong>
                                            Patient ID:
                                        </strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $patient["patient_id"]
                                        );

                                        ?>

                                    </span>


                                    <!-- PHILHEALTH -->

                                    <?php
                                    if (
                                        !empty(
                                            $patient[
                                                "philhealth_no"
                                            ]
                                        )
                                    ):
                                    ?>

                                        <span>

                                            <strong>
                                                PhilHealth:
                                            </strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $patient[
                                                    "philhealth_no"
                                                ]
                                            );

                                            ?>

                                        </span>

                                    <?php endif; ?>


                                    <!-- CONTACT -->

                                    <?php
                                    if (
                                        !empty(
                                            $patient[
                                                "contact_no"
                                            ]
                                        )
                                    ):
                                    ?>

                                        <span>

                                            <strong>
                                                Contact:
                                            </strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $patient[
                                                    "contact_no"
                                                ]
                                            );

                                            ?>

                                        </span>

                                    <?php endif; ?>


                                    <!-- SEX -->

                                    <?php
                                    if (
                                        !empty(
                                            $patient["sex"]
                                        )
                                    ):
                                    ?>

                                        <span>

                                            <strong>
                                                Sex:
                                            </strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $patient["sex"]
                                            );

                                            ?>

                                        </span>

                                    <?php endif; ?>


                                </div>

                            </div>

                        </div>


                        <!-- =========================================
                             CONSULTATIONS
                        ========================================== -->

                        <div
                            class="prescription-consultation-section"
                        >


                            <div
                                class="prescription-consultation-title"
                            >

                                Consultations

                            </div>


                            <?php
                            if (
                                count($consultations) > 0
                            ):
                            ?>


                                <div
                                    class="prescription-consultation-list"
                                >


                                    <?php
                                    foreach (
                                        $consultations
                                        as $consultation
                                    ):
                                    ?>


                                        <div
                                            class="prescription-consultation-row"
                                        >


                                            <div
                                                class="prescription-consultation-info"
                                            >


                                                <!-- DATE -->

                                                <div
                                                    class="prescription-consultation-date"
                                                >

                                                    <?php

                                                    $visitDate =
                                                        $consultation[
                                                            "visit_date"
                                                        ];


                                                    if (
                                                        !empty(
                                                            $visitDate
                                                        )
                                                    ) {

                                                        echo date(
                                                            "F d, Y",
                                                            strtotime(
                                                                $visitDate
                                                            )
                                                        );

                                                    } else {

                                                        echo "No date";

                                                    }

                                                    ?>

                                                </div>


                                                <!-- CHIEF COMPLAINT -->

                                                <div
                                                    class="prescription-consultation-complaint"
                                                >

                                                    <?php

                                                    if (
                                                        isset(
                                                            $consultation[
                                                                "chief_complaint"
                                                            ]
                                                        )
                                                    ) {

                                                        $complaint =
                                                            trim(
                                                                $consultation[
                                                                    "chief_complaint"
                                                                ]
                                                            );

                                                    } else {

                                                        $complaint =
                                                            "";

                                                    }


                                                    if (
                                                        $complaint ===
                                                        ""
                                                    ) {

                                                        echo
                                                            "No chief complaint recorded.";

                                                    } else {

                                                        echo htmlspecialchars(
                                                            $complaint
                                                        );

                                                    }

                                                    ?>

                                                </div>


                                            </div>


                                            <!-- NEW PRESCRIPTION -->

                                            <a
                                                href="prescription.php?consultation_id=<?php echo (int)$consultation["id"]; ?>"
                                                class="prescription-select-button"
                                            >

                                                New Prescription

                                            </a>


                                        </div>


                                    <?php endforeach; ?>


                                </div>


                            <?php else: ?>


                                <!-- =================================
                                     NO CONSULTATION
                                ================================== -->

                                <div
                                    class="prescription-no-consultation"
                                >

                                    No consultation found for this patient.
                                    A prescription must be associated
                                    with a consultation.

                                </div>


                            <?php endif; ?>


                        </div>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </div>

</main>


<?php

$conn->close();

include __DIR__ . "/../includes/footer.php";

?>