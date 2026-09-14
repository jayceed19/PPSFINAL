<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/* =========================================================
   GET CONSULTATION ID
========================================================= */

$consultationId = isset($_GET["consultation_id"])
    ? (int) $_GET["consultation_id"]
    : 0;

if ($consultationId <= 0) {
    die("Invalid consultation ID.");
}

/* =========================================================
   GET CONSULTATION + PATIENT
========================================================= */

$sql = "
    SELECT
        c.id AS consultation_id,
        c.patient_id,
        c.visit_date,
        c.chief_complaint,
        c.follow_up_date,

        p.id AS patient_database_id,
        p.patient_id AS patient_number,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.birthdate,
        p.sex,
        p.address,
        p.contact_no,
        p.philhealth_no

    FROM consultations c

    INNER JOIN patients p
        ON c.patient_id = p.id

    WHERE c.id = ?

    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $consultationId
);

$stmt->execute();

$result = $stmt->get_result();

$consultation = $result->fetch_assoc();

$stmt->close();

if (!$consultation) {
    die("Consultation not found.");
}

/* =========================================================
   PATIENT INFORMATION
========================================================= */

$patientId = (int) $consultation["patient_id"];

/* =========================================================
   GET ACTIVE MEDICINES
========================================================= */

$medicineList = array();

$medicineStmt = $conn->prepare("
    SELECT
        id,
        medicine_name,
        strength,
        form
    FROM medicines
    WHERE is_active = 1
    ORDER BY medicine_name ASC, strength ASC
");

if ($medicineStmt) {

    $medicineStmt->execute();

    $medicineResult = $medicineStmt->get_result();

    while ($medicineRow = $medicineResult->fetch_assoc()) {

        $medicineList[] = $medicineRow;
    }

    $medicineStmt->close();
}

/* =========================================================
   FULL NAME
========================================================= */

$fullName = trim(
    $consultation["first_name"] . " " .
    $consultation["middle_name"] . " " .
    $consultation["last_name"]
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

if (!empty($consultation["birthdate"])) {

    try {

        $birthDate = new DateTime(
            $consultation["birthdate"]
        );

        $today = new DateTime();

        $age = $birthDate->diff($today)->y;

    } catch (Exception $e) {

        $age = "";
    }
}

/* =========================================================
   OTHER PATIENT INFORMATION
========================================================= */

$sex = !empty($consultation["sex"])
    ? $consultation["sex"]
    : "";

$address = !empty($consultation["address"])
    ? $consultation["address"]
    : "";

$contactNo = !empty($consultation["contact_no"])
    ? $consultation["contact_no"]
    : "";

$philhealthNo = !empty($consultation["philhealth_no"])
    ? $consultation["philhealth_no"]
    : "";

$visitDate = !empty($consultation["visit_date"])
    ? date(
        "F j, Y",
        strtotime($consultation["visit_date"])
    )
    : "";

$chiefComplaint = !empty(
    $consultation["chief_complaint"]
)
    ? $consultation["chief_complaint"]
    : "";

/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "New Prescription";
$pageSubtitle = "Prescription Management";
$basePath = "../";
$activePage = "prescriptions";

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
   MAIN CONTAINER
========================================================= */

.prescription-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px 40px;
}

/* =========================================================
   PAGE TOP
========================================================= */

.page-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.page-top h2 {
    margin: 0;
    color: #1f4e78;
    font-size: 26px;
}

.page-top p {
    margin: 5px 0 0;
    color: #666;
    font-size: 14px;
}

/* =========================================================
   BUTTONS
========================================================= */

.btn {
    display: inline-block;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-primary {
    background: #1f4e78;
    color: white;
}

.btn-primary:hover {
    background: #173a5c;
}

.btn-success {
    background: #198754;
    color: white;
}

.btn-success:hover {
    background: #157347;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #bb2d3b;
}

/* =========================================================
   PATIENT CARD
========================================================= */

.patient-card {
    background: white;
    border: 1px solid #d9e2ec;
    border-radius: 10px;
    padding: 22px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.patient-card-title {
    color: #1f4e78;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 15px;
    border-bottom: 2px solid #1f4e78;
    padding-bottom: 8px;
}

.patient-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px 25px;
}

.info-item {
    min-width: 0;
}

.info-label {
    display: block;
    font-size: 12px;
    color: #777;
    margin-bottom: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.info-value {
    font-size: 15px;
    color: #222;
    word-break: break-word;
}

/* =========================================================
   PRESCRIPTION CARD
========================================================= */

.prescription-card {
    background: white;
    border: 1px solid #d9e2ec;
    border-radius: 10px;
    padding: 22px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.section-title {
    color: #1f4e78;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 18px;
}

/* =========================================================
   NOTICE
========================================================= */

.notice {
    background: #eef6ff;
    border-left: 4px solid #1f4e78;
    padding: 13px 15px;
    border-radius: 5px;
    color: #444;
    font-size: 13px;
    margin-bottom: 20px;
    line-height: 1.5;
}

/* =========================================================
   MEDICINE ROW
========================================================= */

.medicine-row {
    border: 1px solid #d7dee7;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 18px;
    background: #fafcff;
}

.medicine-row-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    gap: 10px;
}

.medicine-number {
    font-size: 16px;
    font-weight: 700;
    color: #1f4e78;
}

/* =========================================================
   FORM GRID
========================================================= */

.form-grid {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 13px;
    font-weight: 700;
    color: #444;
    margin-bottom: 6px;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 11px;
    border: 1px solid #cbd5df;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    outline: none;
}

 .form-group input:focus {
    border-color: #1f4e78;
    box-shadow:
        0 0 0 2px
        rgba(31,78,120,0.10);
}

.medicine-form-input {
    background: #f3f6f9 !important;
    color: #555;
    cursor: not-allowed;
}

.medicine-selection-status {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
}

/* =========================================================
   MEDICINE SEARCH
========================================================= */

.medicine-search-group {
    min-width: 0;
}

.medicine-search-wrapper {
    position: relative;
    width: 100%;
}

.medicine-search-input {
    width: 100%;
    box-sizing: border-box;
}

.medicine-suggestions {
    position: absolute;
    top: calc(100% + 3px);
    left: 0;
    right: 0;

    background: white;

    border: 1px solid #cbd5df;
    border-radius: 6px;

    box-shadow:
        0 4px 12px
        rgba(0,0,0,0.12);

    z-index: 9999;

    max-height: 240px;

    overflow-y: auto;

    display: none;
}

.medicine-suggestion {
    padding: 11px 12px;

    cursor: pointer;

    border-bottom:
        1px solid #eeeeee;

    font-size: 14px;

    color: #222;

    background: white;
}

.medicine-suggestion:last-child {
    border-bottom: none;
}

.medicine-suggestion:hover {
    background: #eef6ff;
}

.medicine-suggestion-name {
    font-weight: 700;
    color: #1f4e78;
}

.medicine-suggestion-details {
    font-size: 12px;
    color: #777;
    margin-top: 3px;
}

.medicine-help {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
}

/* =========================================================
   SIG
========================================================= */

.sig-group {
    margin-bottom: 20px;
}

.sig-group label {
    font-size: 13px;
    font-weight: 700;
    color: #1f4e78;
    margin-bottom: 6px;
    display: block;
}

.sig-group input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 11px;
    border: 1px solid #cbd5df;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    outline: none;
}

.sig-group input:focus {
    border-color: #1f4e78;
    box-shadow:
        0 0 0 2px
        rgba(31,78,120,0.10);
}

.sig-help {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
}

/* =========================================================
   MEAL GRID
========================================================= */

.meal-title {
    color: #1f4e78;
    font-size: 15px;
    font-weight: 700;
    margin: 5px 0 10px;
}

.meal-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 10px;
}

.meal-help {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
}

/* =========================================================
   REMOVE BUTTON
========================================================= */

.remove-button {
    padding: 7px 11px;
    font-size: 13px;
}

/* =========================================================
   ADD MEDICINE
========================================================= */

.add-medicine-container {
    margin-top: 5px;
    margin-bottom: 25px;
}

/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    flex-wrap: wrap;
}

.form-actions-left,
.form-actions-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 900px) {

    .patient-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .form-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .meal-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {

    .prescription-container {
        padding: 0 12px 30px;
    }

    .patient-grid,
    .form-grid,
    .meal-grid {
        grid-template-columns: 1fr;
    }

    .medicine-row {
        padding: 15px;
    }

    .page-top {
        align-items: flex-start;
    }

    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .form-actions-left,
    .form-actions-right {
        width: 100%;
    }

    .form-actions .btn {
        width: 100%;
        text-align: center;
    }
}

</style>


<div class="prescription-container">

    <!-- =====================================================
         PAGE TOP
    ====================================================== -->

    <div class="page-top">

        <div>

            <h2>
                New Prescription
            </h2>

            <p>
                Create prescription for this consultation.
            </p>

        </div>

        <div>

            <a
                href="../patients/consultation_view.php?id=<?php echo $consultationId; ?>"
                class="btn btn-secondary"
            >
                ← Back to Consultation
            </a>

        </div>

    </div>


    <!-- =====================================================
         PATIENT INFORMATION
    ====================================================== -->

    <div class="patient-card">

        <div class="patient-card-title">
            Patient Information
        </div>

        <div class="patient-grid">

            <!-- PATIENT ID -->

            <div class="info-item">

                <span class="info-label">
                    Patient ID
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $consultation["patient_number"]
                    );

                    ?>

                </div>

            </div>


            <!-- PATIENT NAME -->

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


            <!-- AGE / SEX -->

            <div class="info-item">

                <span class="info-label">
                    Age / Sex
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $age
                    );

                    if (
                        $age !== ""
                        &&
                        $sex !== ""
                    ) {

                        echo " / ";

                    }

                    echo htmlspecialchars(
                        $sex
                    );

                    ?>

                </div>

            </div>


            <!-- VISIT DATE -->

            <div class="info-item">

                <span class="info-label">
                    Visit Date
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $visitDate
                    );

                    ?>

                </div>

            </div>


            <!-- CONTACT -->

            <div class="info-item">

                <span class="info-label">
                    Contact No.
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $contactNo
                    );

                    ?>

                </div>

            </div>


            <!-- PHILHEALTH -->

            <div class="info-item">

                <span class="info-label">
                    PhilHealth No.
                </span>

                <div class="info-value">

                    <?php

                    if ($philhealthNo !== "") {

                        echo htmlspecialchars(
                            $philhealthNo
                        );

                    } else {

                        echo "-";

                    }

                    ?>

                </div>

            </div>


            <!-- ADDRESS -->

            <div class="info-item">

                <span class="info-label">
                    Address
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $address
                    );

                    ?>

                </div>

            </div>


            <!-- CHIEF COMPLAINT -->

            <div class="info-item">

                <span class="info-label">
                    Chief Complaint
                </span>

                <div class="info-value">

                    <?php

                    if ($chiefComplaint !== "") {

                        echo htmlspecialchars(
                            $chiefComplaint
                        );

                    } else {

                        echo "-";

                    }

                    ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         PRESCRIPTION FORM
    ====================================================== -->

    <div class="prescription-card">

        <div class="section-title">
            Prescription Details
        </div>


        <div class="notice">

            <strong>Prescription Rule:</strong>

            All medicines saved for the same patient and the same date
            will be grouped into a single prescription.

            <br>
            <br>

            <strong>Medicine Search:</strong>

            Type the medicine name and select it from the list.
            The available strength and form will be filled in automatically.

            <br>
            <br>

            <strong>SIG / Directions:</strong>

            Enter the exact instructions for taking the medicine.

            Example:

            <strong>
                1 TAB EVERY 8 HOURS FOR 3 DAYS
            </strong>

            <br>
            <br>

            <strong>Dose per Meal:</strong>

            Enter the dose to be taken at each meal.

            Examples:

            <strong>1 TAB</strong>,
            <strong>1 CAP</strong>,
            <strong>5 ML</strong>.

            Leave blank if there is no dose for that meal.

        </div>


        <form
            method="POST"
            action="save_prescription.php"
            id="prescriptionForm"
        >

            <!-- CONSULTATION ID -->

            <input
                type="hidden"
                name="consultation_id"
                value="<?php echo $consultationId; ?>"
            >


            <!-- PATIENT ID -->

            <input
                type="hidden"
                name="patient_id"
                value="<?php echo $patientId; ?>"
            >


            <div id="medicineContainer">


                <!-- =================================================
                     MEDICINE #1
                ================================================== -->

                <div class="medicine-row">

                    <div class="medicine-row-header">

                        <div class="medicine-number">
                            Medicine #1
                        </div>

                    </div>


                    <!-- MEDICINE / STRENGTH / FORM / QUANTITY -->

                    <div class="form-grid">


                        <!-- MEDICINE NAME -->

                        <div class="form-group medicine-search-group">

                            <label>
                                Medicine Name
                            </label>

                            <div class="medicine-search-wrapper">

                                <input
                                    type="text"
                                    name="medicine_name[]"
                                    class="uppercase-field medicine-search-input"
                                    placeholder="Search medicine..."
                                    autocomplete="off"
                                    required
                                >

                                <div class="medicine-suggestions"></div>

                            </div>

                            <div class="medicine-help">
                                Type the medicine name and select from the list.
                            </div>

                        </div>


                        <!-- STRENGTH -->

                        <div class="form-group">

                            <label>
                                Strength
                            </label>

                            <input
                                type="text"
                                name="strength[]"
                                class="uppercase-field medicine-strength"
                                placeholder="e.g. 500 MG"
                            >

                        </div>


                        <!-- FORM -->

                        <div class="form-group">

                            <label>
                                Form
                            </label>

                            <input
                                type="text"
                                name="medicine_form[]"
                                class="medicine-form-input"
                                placeholder="Auto-filled"
                                readonly
                            >

                            <div class="medicine-selection-status">
                                Auto-filled from the selected medicine.
                            </div>

                        </div>


                        <!-- QUANTITY -->

                        <div class="form-group">

                            <label>
                                Quantity
                            </label>

                            <input
                                type="number"
                                name="quantity[]"
                                placeholder="e.g. 10"
                                min="1"
                                step="1"
                                inputmode="numeric"
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         SIG / DIRECTIONS
                    ================================================== -->

                    <div class="sig-group">

                        <label>
                            SIG / Directions
                        </label>

                        <input
                            type="text"
                            name="sig[]"
                            class="uppercase-field"
                            maxlength="255"
                            placeholder="e.g. 1 TAB EVERY 8 HOURS FOR 3 DAYS"
                        >

                        <div class="sig-help">
                            Enter the exact instructions for how and when the medicine should be taken.
                        </div>

                    </div>


                    <!-- =================================================
                         DOSE PER MEAL
                    ================================================== -->

                    <div class="meal-title">
                        Dose per Meal
                    </div>


                    <div class="meal-grid">


                        <!-- BREAKFAST -->

                        <div class="form-group">

                            <label>
                                Breakfast
                            </label>

                            <input
                                type="text"
                                name="breakfast[]"
                                class="uppercase-field"
                                placeholder="e.g. 1 TAB"
                            >

                            <div class="meal-help">
                                Example:
                                1 TAB
                            </div>

                        </div>


                        <!-- LUNCH -->

                        <div class="form-group">

                            <label>
                                Lunch
                            </label>

                            <input
                                type="text"
                                name="lunch[]"
                                class="uppercase-field"
                                placeholder="e.g. 1 TAB"
                            >

                            <div class="meal-help">
                                Example:
                                1 TAB
                            </div>

                        </div>


                        <!-- DINNER -->

                        <div class="form-group">

                            <label>
                                Dinner
                            </label>

                            <input
                                type="text"
                                name="dinner[]"
                                class="uppercase-field"
                                placeholder="e.g. 1 TAB"
                            >

                            <div class="meal-help">
                                Example:
                                1 TAB
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 ADD MEDICINE
            ====================================================== -->

            <div class="add-medicine-container">

                <button
                    type="button"
                    class="btn btn-success"
                    id="addMedicineBtn"
                >
                    + Add Medicine
                </button>

            </div>


            <!-- =====================================================
                 ACTIONS
            ====================================================== -->

            <div class="form-actions">

                <div class="form-actions-left">

                    <a
                        href="../patients/consultation_view.php?id=<?php echo $consultationId; ?>"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>


                <div class="form-actions-right">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Prescription
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>


<script>

const medicineList =
    <?php echo json_encode($medicineList); ?>;


document.addEventListener(
    "DOMContentLoaded",
    function () {

        const container =
            document.getElementById(
                "medicineContainer"
            );

        const addButton =
            document.getElementById(
                "addMedicineBtn"
            );


        /* =====================================================
           UPPERCASE TEXT FIELDS
        ====================================================== */

        function applyUppercase(field) {

            if (!field) {
                return;
            }

            field.addEventListener(
                "input",
                function () {

                    const start =
                        field.selectionStart;

                    const end =
                        field.selectionEnd;

                    field.value =
                        field.value.toUpperCase();

                    try {

                        field.setSelectionRange(
                            start,
                            end
                        );

                    } catch (e) {
                    }

                }
            );
        }


        /* =====================================================
           MEDICINE SEARCH
        ====================================================== */

        function applyMedicineSearch(row) {

            const input =
                row.querySelector(
                    ".medicine-search-input"
                );

            const strengthInput =
                row.querySelector(
                    ".medicine-strength"
                );

            const formInput =
                row.querySelector(
                    'input[name="medicine_form[]"]'
                );

            const suggestions =
                row.querySelector(
                    ".medicine-suggestions"
                );


            if (
                !input ||
                !strengthInput ||
                !formInput ||
                !suggestions
            ) {

                return;

            }


            let selectedMedicineName = "";
            let selectedStrength = "";
            let activeIndex = -1;


            function hideSuggestions() {

                suggestions.innerHTML = "";
                suggestions.style.display = "none";
                activeIndex = -1;

            }


            function medicineText(medicine) {

                return (
                    (medicine.medicine_name || "") +
                    " " +
                    (medicine.strength || "") +
                    " " +
                    (medicine.form || "")
                ).trim().toLowerCase();

            }


            function showSuggestions(searchText, showAll) {

                const search =
                    String(searchText || "")
                        .trim()
                        .toLowerCase();


                suggestions.innerHTML = "";
                activeIndex = -1;


                if (
                    !showAll &&
                    search === ""
                ) {

                    hideSuggestions();
                    return;

                }


                const matches =
                    medicineList.filter(
                        function (medicine) {

                            if (showAll && search === "") {
                                return true;
                            }

                            return medicineText(
                                medicine
                            ).indexOf(search) !== -1;

                        }
                    );


                if (matches.length === 0) {

                    const noResult =
                        document.createElement(
                            "div"
                        );

                    noResult.className =
                        "medicine-suggestion";

                    noResult.style.color =
                        "#777";

                    noResult.textContent =
                        search === ""
                            ? "No medicines available."
                            : "No medicine found. Please ask an Administrator to add it.";

                    suggestions.appendChild(
                        noResult
                    );

                    suggestions.style.display =
                        "block";

                    return;

                }


                matches.forEach(
                    function (medicine, index) {

                        const item =
                            document.createElement(
                                "div"
                            );

                        item.className =
                            "medicine-suggestion";

                        item.setAttribute(
                            "data-index",
                            index
                        );


                        const name =
                            document.createElement(
                                "div"
                            );

                        name.className =
                            "medicine-suggestion-name";

                        name.textContent =
                            medicine.medicine_name || "";


                        const details =
                            document.createElement(
                                "div"
                            );

                        details.className =
                            "medicine-suggestion-details";


                        let detailsText = "";


                        if (medicine.strength) {

                            detailsText +=
                                medicine.strength;

                        }


                        if (medicine.form) {

                            if (
                                detailsText !== ""
                            ) {

                                detailsText +=
                                    " • ";

                            }

                            detailsText +=
                                medicine.form;

                        }


                        details.textContent =
                            detailsText;


                        item.appendChild(
                            name
                        );


                        if (
                            detailsText !== ""
                        ) {

                            item.appendChild(
                                details
                            );

                        }


                        item.addEventListener(
                            "mousedown",
                            function (event) {

                                event.preventDefault();

                                selectMedicine(
                                    medicine
                                );

                            }
                        );


                        suggestions.appendChild(
                            item
                        );

                    }
                );


                suggestions.style.display =
                    "block";

            }


            function selectMedicine(medicine) {

                selectedMedicineName =
                    (medicine.medicine_name || "")
                        .trim()
                        .toUpperCase();

                selectedStrength =
                    (medicine.strength || "")
                        .trim()
                        .toUpperCase();


                input.value =
                    selectedMedicineName;

                strengthInput.value =
                    selectedStrength;

                formInput.value =
                    (medicine.form || "")
                        .trim()
                        .toUpperCase();


                input.setAttribute(
                    "data-medicine-selected",
                    "1"
                );


                hideSuggestions();

            }


            input.addEventListener(
                "input",
                function () {

                    const currentValue =
                        input.value
                            .trim()
                            .toUpperCase();


                    if (
                        currentValue !==
                        selectedMedicineName
                    ) {

                        input.removeAttribute(
                            "data-medicine-selected"
                        );

                        if (
                            selectedMedicineName !== ""
                        ) {

                            strengthInput.value = "";
                            formInput.value = "";

                        }

                    }


                    showSuggestions(
                        input.value,
                        false
                    );

                }
            );


            input.addEventListener(
                "focus",
                function () {

                    showSuggestions(
                        input.value,
                        true
                    );

                }
            );


            input.addEventListener(
                "keydown",
                function (event) {

                    const items =
                        suggestions.querySelectorAll(
                            ".medicine-suggestion"
                        );


                    if (
                        suggestions.style.display !==
                            "block" ||
                        items.length === 0
                    ) {

                        return;

                    }


                    if (
                        event.key === "ArrowDown"
                    ) {

                        event.preventDefault();

                        activeIndex++;

                        if (
                            activeIndex >=
                            items.length
                        ) {

                            activeIndex = 0;

                        }

                        updateActiveSuggestion(
                            items
                        );

                    }


                    else if (
                        event.key === "ArrowUp"
                    ) {

                        event.preventDefault();

                        activeIndex--;

                        if (
                            activeIndex < 0
                        ) {

                            activeIndex =
                                items.length - 1;

                        }

                        updateActiveSuggestion(
                            items
                        );

                    }


                    else if (
                        event.key === "Enter"
                    ) {

                        if (
                            activeIndex >= 0 &&
                            activeIndex < items.length
                        ) {

                            event.preventDefault();

                            const selectedItem =
                                items[
                                    activeIndex
                                ];

                            selectedItem.dispatchEvent(
                                new MouseEvent(
                                    "mousedown",
                                    {
                                        bubbles: true
                                    }
                                )
                            );

                        }

                    }


                    else if (
                        event.key === "Escape"
                    ) {

                        hideSuggestions();

                    }

                }
            );


            function updateActiveSuggestion(
                items
            ) {

                items.forEach(
                    function (item, index) {

                        if (
                            index ===
                            activeIndex
                        ) {

                            item.style.background =
                                "#eef6ff";

                        } else {

                            item.style.background =
                                "white";

                        }

                    }
                );

            }


            input.addEventListener(
                "blur",
                function () {

                    setTimeout(
                        function () {

                            hideSuggestions();

                        },
                        200
                    );

                }
            );

        }


        /* =====================================================
           APPLY UPPERCASE + SEARCH TO ROW
        ====================================================== */

        function applyUppercaseToRow(row) {

            const fields =
                row.querySelectorAll(
                    ".uppercase-field"
                );


            fields.forEach(
                function (field) {

                    applyUppercase(
                        field
                    );

                }
            );


            applyMedicineSearch(
                row
            );

        }


        /* =====================================================
           QUANTITY NUMBERS ONLY
        ====================================================== */

        function applyQuantityValidation(row) {

            const quantityInputs =
                row.querySelectorAll(
                    'input[name="quantity[]"]'
                );


            quantityInputs.forEach(
                function (input) {

                    input.addEventListener(
                        "input",
                        function () {

                            input.value =
                                input.value.replace(
                                    /[^0-9]/g,
                                    ""
                                );

                        }
                    );

                }
            );

        }


        /* =====================================================
           UPDATE MEDICINE NUMBERS
        ====================================================== */

        function updateMedicineNumbers() {

            const rows =
                container.querySelectorAll(
                    ".medicine-row"
                );


            rows.forEach(
                function (row, index) {

                    const numberLabel =
                        row.querySelector(
                            ".medicine-number"
                        );


                    if (numberLabel) {

                        numberLabel.textContent =
                            "Medicine #" +
                            (index + 1);

                    }

                }
            );

        }


        /* =====================================================
           ADD MEDICINE ROW
        ====================================================== */

        function addMedicineRow() {

            const rows =
                container.querySelectorAll(
                    ".medicine-row"
                );


            if (rows.length === 0) {

                return;

            }


            const firstRow =
                rows[0];


            const newRow =
                firstRow.cloneNode(true);


            /* CLEAR ALL INPUTS */

            newRow.querySelectorAll(
                "input"
            ).forEach(
                function (input) {

                    input.value = "";

                }
            );


            /* CLEAR SEARCH SUGGESTIONS */

            const suggestionBox =
                newRow.querySelector(
                    ".medicine-suggestions"
                );


            if (suggestionBox) {

                suggestionBox.innerHTML =
                    "";

                suggestionBox.style.display =
                    "none";

            }


            /* ADD REMOVE BUTTON */

            const header =
                newRow.querySelector(
                    ".medicine-row-header"
                );


            if (header) {

                const removeButton =
                    document.createElement(
                        "button"
                    );


                removeButton.type =
                    "button";


                removeButton.className =
                    "btn btn-danger remove-button";


                removeButton.textContent =
                    "Remove";


                removeButton.addEventListener(
                    "click",
                    function () {

                        const currentRows =
                            container.querySelectorAll(
                                ".medicine-row"
                            );


                        if (
                            currentRows.length > 1
                        ) {

                            newRow.remove();

                            updateMedicineNumbers();

                        }

                    }
                );


                header.appendChild(
                    removeButton
                );

            }


            container.appendChild(
                newRow
            );


            /* APPLY FUNCTIONS */

            applyUppercaseToRow(
                newRow
            );

            applyQuantityValidation(
                newRow
            );

            updateMedicineNumbers();

        }


        /* =====================================================
           ADD MEDICINE BUTTON
        ====================================================== */

        addButton.addEventListener(
            "click",
            function () {

                addMedicineRow();

            }
        );


        /* =====================================================
           INITIAL ROW FUNCTIONS
        ====================================================== */

        const firstRow =
            container.querySelector(
                ".medicine-row"
            );


        if (firstRow) {

            applyUppercaseToRow(
                firstRow
            );

            applyQuantityValidation(
                firstRow
            );

        }


        /* =====================================================
           FORM VALIDATION
        ====================================================== */

        document
            .getElementById(
                "prescriptionForm"
            )
            .addEventListener(
                "submit",
                function (event) {


                    const medicineNames =
                        container.querySelectorAll(
                            'input[name="medicine_name[]"]'
                        );


                    let hasMedicine =
                        false;


                    medicineNames.forEach(
                        function (input) {

                            if (
                                input.value.trim()
                                !== ""
                            ) {

                                hasMedicine =
                                    true;

                            }

                        }
                    );


                    if (!hasMedicine) {

                        event.preventDefault();

                        alert(
                            "Please enter at least one medicine."
                        );

                        return;

                    }


                    /* =================================================
                       VALIDATE EACH MEDICINE
                    ================================================== */

                    const rows =
                        container.querySelectorAll(
                            ".medicine-row"
                        );


                    let invalidRow =
                        false;


                    rows.forEach(
                        function (row) {

                            const medicineInput =
                                row.querySelector(
                                    'input[name="medicine_name[]"]'
                                );


                            if (
                                medicineInput
                                &&
                                medicineInput.value.trim()
                                === ""
                            ) {

                                invalidRow =
                                    true;

                            }


                            /* FINAL UPPERCASE */

                            row.querySelectorAll(
                                ".uppercase-field"
                            ).forEach(
                                function (field) {

                                    field.value =
                                        field.value
                                            .trim()
                                            .replace(
                                                /\s+/g,
                                                " "
                                            )
                                            .toUpperCase();

                                }
                            );


                            /* FINAL QUANTITY CLEANING */

                            const quantityInput =
                                row.querySelector(
                                    'input[name="quantity[]"]'
                                );


                            if (quantityInput) {

                                quantityInput.value =
                                    quantityInput.value.replace(
                                        /[^0-9]/g,
                                        ""
                                    );

                            }

                        }
                    );


                    if (invalidRow) {

                        event.preventDefault();

                        alert(
                            "Please enter the Medicine Name for every medicine row."
                        );

                        return;

                    }

                }
            );


        /* =====================================================
           INITIAL NUMBER
        ====================================================== */

        updateMedicineNumbers();

    }

);

</script>


<?php

include "../includes/footer.php";

?>