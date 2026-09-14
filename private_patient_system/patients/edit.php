<?php

require_once "../config/database.php";
require_once "../config/auth.php";

// =========================================================
// GET PATIENT ID
// =========================================================

$id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($id <= 0) {
    die("Invalid patient ID.");
}

// =========================================================
// GET PATIENT DATA
// =========================================================

$sql = "SELECT * FROM patients WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    die("Patient not found.");
}

$patient = $result->fetch_assoc();

$stmt->close();

// =========================================================
// DEFAULT STATUS
// =========================================================

$currentStatus = !empty($patient['status'])
    ? $patient['status']
    : 'Active';

// =========================================================
// PHILHEALTH NUMBER
// =========================================================

$philhealthNo = isset($patient['philhealth_no'])
    ? $patient['philhealth_no']
    : '';

// =========================================================
// CIVIL STATUS
// =========================================================

$civilStatus = isset($patient['civil_status'])
    ? trim($patient['civil_status'])
    : '';

// =========================================================
// PHILHEALTH / YAKAP STATUS
// =========================================================

$philhealthYakapStatus = isset(
    $patient['philhealth_yakap_status']
)
    ? trim($patient['philhealth_yakap_status'])
    : '';

// =========================================================
// EMAIL
// =========================================================

$email = isset($patient['email'])
    ? trim($patient['email'])
    : '';

// =========================================================
// PAGE SETTINGS
// =========================================================

$pageTitle = "Edit Patient";
$pageSubtitle = "Edit Patient Information";
$basePath = "../";
$activePage = "patients";

// =========================================================
// SHARED HEADER
// =========================================================

require_once "../includes/header.php";

// =========================================================
// SHARED NAVIGATION
// =========================================================

require_once "../includes/navigation.php";

?>

<style>

/* =========================================================
   EDIT PATIENT PAGE
========================================================= */

.edit-patient-card {
    max-width: 1050px;
    margin: 0 auto 20px;
}

/* =========================================================
   CARD HEADER
========================================================= */

.edit-patient-card .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 18px;
    margin-bottom: 5px;
    border-bottom: 1px solid #e5e9ee;
}

.edit-patient-card .card-header h2 {
    margin: 0;
    color: #1f4e78;
    font-size: 23px;
    font-weight: 700;
    line-height: 1.3;
}

.edit-patient-card .card-header p {
    margin: 5px 0 0;
    color: #6c757d;
    font-size: 13px;
}

/* =========================================================
   SECTION TITLE
========================================================= */

.edit-patient-card .section-title {
    margin-top: 28px;
    margin-bottom: 17px;
    padding: 9px 12px;
    background: #f1f5f9;
    border-left: 4px solid #1f4e78;
    border-bottom: 1px solid #e1e6eb;
    color: #1f4e78;
    font-size: 15px;
    font-weight: 700;
}

.edit-patient-card .section-title:first-child {
    margin-top: 0;
}

/* =========================================================
   FORM GRID
========================================================= */

.edit-patient-card .form-grid {
    display: grid;
    grid-template-columns:
        repeat(2, minmax(0, 1fr));
    gap: 18px 22px;
}

/* =========================================================
   FORM GROUP
========================================================= */

.edit-patient-card .form-group {
    display: flex;
    flex-direction: column;
}

/* =========================================================
   FULL WIDTH
========================================================= */

.edit-patient-card .form-group-full {
    grid-column: 1 / -1;
}

/* =========================================================
   LABEL
========================================================= */

.edit-patient-card .form-group label {
    margin-bottom: 7px;
    color: #343a40;
    font-size: 13px;
    font-weight: 600;
}

/* =========================================================
   REQUIRED
========================================================= */

.edit-patient-card .required {
    color: #dc3545;
    font-weight: 700;
}

/* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

.edit-patient-card input,
.edit-patient-card select,
.edit-patient-card textarea {
    width: 100%;
    min-height: 40px;
    padding: 9px 11px;
    background: #ffffff;
    border: 1px solid #cfd6dd;
    border-radius: 5px;
    color: #333;
    font-family:
        Arial,
        Helvetica,
        sans-serif;
    font-size: 13px;
    outline: none;
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease,
        background 0.15s ease;
}

/* =========================================================
   INPUT FOCUS
========================================================= */

.edit-patient-card input:focus,
.edit-patient-card select:focus,
.edit-patient-card textarea:focus {
    border-color: #1f4e78;
    box-shadow:
        0 0 0 2px
        rgba(31, 78, 120, 0.10);
}

/* =========================================================
   DISABLED FIELD
========================================================= */

.edit-patient-card input:disabled,
.edit-patient-card select:disabled {
    background: #f1f3f5;
    color: #6c757d;
    cursor: not-allowed;
}

/* =========================================================
   TEXTAREA
========================================================= */

.edit-patient-card textarea {
    min-height: 85px;
    resize: vertical;
    line-height: 1.5;
}

/* =========================================================
   SELECT
========================================================= */

.edit-patient-card select {
    cursor: pointer;
}

/* =========================================================
   DATE
========================================================= */

.edit-patient-card input[type="date"] {
    cursor: pointer;
}

/* =========================================================
   PATIENT ID BOX
========================================================= */

.patient-id-display {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 15px 16px;
    background: #f5f7fa;
    border: 1px solid #e1e6eb;
    border-radius: 6px;
    margin-bottom: 8px;
}

.patient-id-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.patient-id-label {
    color: #6c757d;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.patient-id-number {
    color: #1f4e78;
    font-size: 20px;
    font-weight: 700;
}

.patient-id-note {
    color: #6c757d;
    font-size: 12px;
}

/* =========================================================
   FIELD HELP
========================================================= */

.field-note {
    margin-top: 6px;
    color: #6c757d;
    font-size: 11px;
    line-height: 1.4;
}

/* =========================================================
   STATUS NOTE
========================================================= */

.status-note {
    margin-top: 6px;
    color: #6c757d;
    font-size: 11px;
    line-height: 1.4;
}

/* =========================================================
   YAKAP STATUS NOTE
========================================================= */

.yakap-status-note {
    margin-top: 6px;
    color: #6c757d;
    font-size: 11px;
    line-height: 1.4;
}

/* =========================================================
   FORM ACTIONS
========================================================= */

.edit-patient-card .form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 9px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e9ee;
}

/* =========================================================
   BUTTONS
========================================================= */

.edit-patient-card .form-actions .btn {
    min-height: 38px;
    padding: 9px 17px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

/* =========================================================
   CANCEL BUTTON
========================================================= */

.edit-patient-card .btn-cancel {
    background: #e9ecef;
    color: #343a40;
    border: 1px solid #d5d9dd;
}

.edit-patient-card .btn-cancel:hover {
    background: #dee2e6;
}

/* =========================================================
   UPDATE BUTTON
========================================================= */

.edit-patient-card .btn-update {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #1f4e78;
    color: #ffffff;
    border: 1px solid #1f4e78;
    cursor: pointer;
    transition:
        background 0.15s ease,
        border-color 0.15s ease,
        opacity 0.15s ease;
}

.edit-patient-card .btn-update:hover {
    background: #173a5c;
    border-color: #173a5c;
}

.edit-patient-card .btn-update:disabled {
    background: #6c8daa;
    border-color: #6c8daa;
    cursor: not-allowed;
    opacity: 0.85;
}

/* =========================================================
   BUTTON SPINNER
========================================================= */

.update-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.40);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: updateSpin 0.7s linear infinite;
    display: none;
}

.btn-update.loading .update-spinner {
    display: inline-block;
}

@keyframes updateSpin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

/* =========================================================
   LOADING OVERLAY
========================================================= */

.patient-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.88);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    backdrop-filter: blur(2px);
}

.patient-loading-overlay.show {
    display: flex;
}

.patient-loading-box {
    width: 300px;
    padding: 28px 30px;
    background: #ffffff;
    border: 1px solid #dce3ea;
    border-radius: 8px;
    box-shadow:
        0 10px 35px rgba(0, 0, 0, 0.15);
    text-align: center;
}

.patient-loading-spinner {
    width: 38px;
    height: 38px;
    margin: 0 auto 15px;
    border: 3px solid #dce4ec;
    border-top-color: #1f4e78;
    border-radius: 50%;
    animation: patientLoadingSpin 0.8s linear infinite;
}

.patient-loading-title {
    margin: 0;
    color: #1f4e78;
    font-size: 16px;
    font-weight: 700;
}

.patient-loading-text {
    margin: 6px 0 0;
    color: #6c757d;
    font-size: 12px;
    line-height: 1.5;
}

@keyframes patientLoadingSpin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .edit-patient-card .form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .edit-patient-card .form-group-full {
        grid-column: auto;
    }

    .edit-patient-card .card-header h2 {
        font-size: 20px;
    }

    .patient-id-display {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }

    .edit-patient-card .form-actions {
        justify-content: stretch;
        flex-wrap: wrap;
    }

    .edit-patient-card .form-actions .btn {
        flex: 1;
        min-width: 110px;
        text-align: center;
    }

    .patient-loading-box {
        width: calc(100% - 40px);
        max-width: 300px;
    }
}

</style>

<!-- =========================================================
     LOADING OVERLAY
========================================================== -->

<div
    class="patient-loading-overlay"
    id="patientLoadingOverlay"
>

    <div class="patient-loading-box">

        <div class="patient-loading-spinner"></div>

        <p class="patient-loading-title">
            Updating Patient
        </p>

        <p class="patient-loading-text">
            Please wait while the information is being saved.
        </p>

    </div>

</div>

<main class="main-container">

    <div class="card edit-patient-card">

        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->

        <div class="card-header">

            <div>

                <h2>
                    Edit Patient Information
                </h2>

                <p>
                    Update the patient's information below.
                </p>

            </div>

        </div>

        <form
            action="update.php"
            method="POST"
            id="editPatientForm"
        >

            <!-- =================================================
                 HIDDEN DATABASE ID
            ================================================== -->

            <input
                type="hidden"
                name="id"
                value="<?php echo (int) $id; ?>"
            >

            <!-- =================================================
                 PATIENT IDENTIFICATION
            ================================================== -->

            <div class="section-title">
                Patient Identification
            </div>

            <div class="patient-id-display">

                <div class="patient-id-info">

                    <span class="patient-id-label">
                        Patient ID
                    </span>

                    <span class="patient-id-number">
                        <?php
                        echo htmlspecialchars(
                            $patient['patient_id']
                        );
                        ?>
                    </span>

                </div>

                <div class="patient-id-note">
                    Patient ID cannot be changed.
                </div>

            </div>

            <!-- =================================================
                 PERSONAL INFORMATION
            ================================================== -->

            <div class="section-title">
                Personal Information
            </div>

            <div class="form-grid">

                <!-- LAST NAME -->

                <div class="form-group">

                    <label>
                        Last Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="last_name"
                        class="uppercase-field"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['last_name']
                            );
                        ?>"
                        autocomplete="family-name"
                        required
                    >

                </div>

                <!-- FIRST NAME -->

                <div class="form-group">

                    <label>
                        First Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="first_name"
                        class="uppercase-field"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['first_name']
                            );
                        ?>"
                        autocomplete="given-name"
                        required
                    >

                </div>

                <!-- MIDDLE NAME -->

                <div class="form-group">

                    <label>
                        Middle Name
                    </label>

                    <input
                        type="text"
                        name="middle_name"
                        class="uppercase-field"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['middle_name']
                            );
                        ?>"
                    >

                </div>

                <!-- SEX -->

                <div class="form-group">

                    <label>
                        Sex
                        <span class="required">*</span>
                    </label>

                    <select
                        name="sex"
                        required
                    >

                        <option value="">
                            Select Sex
                        </option>

                        <option
                            value="Male"
                            <?php
                            echo (
                                $patient['sex'] == 'Male'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Male
                        </option>

                        <option
                            value="Female"
                            <?php
                            echo (
                                $patient['sex'] == 'Female'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Female
                        </option>

                    </select>

                </div>

                <!-- BIRTHDATE -->

                <div class="form-group">

                    <label>
                        Birthdate
                        <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        name="birthdate"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['birthdate']
                            );
                        ?>"
                        max="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>

                <!-- CIVIL STATUS -->

                <div class="form-group">

                    <label>
                        Civil Status
                        <span class="required">*</span>
                    </label>

                    <select
                        name="civil_status"
                        required
                    >

                        <option value="">
                            Select Civil Status
                        </option>

                        <option
                            value="Single"
                            <?php
                            echo (
                                $civilStatus == 'Single'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Single
                        </option>

                        <option
                            value="Married"
                            <?php
                            echo (
                                $civilStatus == 'Married'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Married
                        </option>

                        <option
                            value="Widowed"
                            <?php
                            echo (
                                $civilStatus == 'Widowed'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Widowed
                        </option>

                        <option
                            value="Separated"
                            <?php
                            echo (
                                $civilStatus == 'Separated'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Separated
                        </option>

                        <option
                            value="Annulled"
                            <?php
                            echo (
                                $civilStatus == 'Annulled'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Annulled
                        </option>

                        <option
                            value="Other"
                            <?php
                            echo (
                                $civilStatus == 'Other'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Other
                        </option>

                    </select>

                </div>

                <!-- PATIENT STATUS -->

                <div class="form-group">

                    <label>
                        Patient Status
                        <span class="required">*</span>
                    </label>

                    <select
                        name="status"
                        required
                    >

                        <option
                            value="Active"
                            <?php
                            echo (
                                $currentStatus == 'Active'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Active
                        </option>

                        <option
                            value="Inactive"
                            <?php
                            echo (
                                $currentStatus == 'Inactive'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Inactive
                        </option>

                        <option
                            value="Deceased"
                            <?php
                            echo (
                                $currentStatus == 'Deceased'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Deceased
                        </option>

                    </select>

                    <div class="status-note">
                        Select Deceased for patients who are no longer living.
                    </div>

                </div>

                <!-- PHILHEALTH NUMBER -->

                <div class="form-group">

                    <label>
                        PhilHealth No.
                    </label>

                    <input
                        type="text"
                        name="philhealth_no"
                        id="philhealth_no"
                        maxlength="30"
                        inputmode="numeric"
                        autocomplete="off"
                        value="<?php
                            echo htmlspecialchars(
                                $philhealthNo
                            );
                        ?>"
                    >

                    <div class="field-note">
                        Optional — leave blank if not available.
                    </div>

                </div>

                <!-- PHILHEALTH / YAKAP STATUS -->

                <div class="form-group">

                    <label>
                        PhilHealth / YAKAP Status

                        <span
                            class="required"
                            id="yakapRequired"
                        >
                            *
                        </span>

                    </label>

                    <select
                        name="philhealth_yakap_status"
                        id="philhealth_yakap_status"
                    >

                        <option value="">
                            Select Status
                        </option>

                        <option
                            value="REGISTERED"
                            <?php
                            echo (
                                $philhealthYakapStatus
                                == 'REGISTERED'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            REGISTERED
                        </option>

                        <option
                            value="NOT YET REGISTERED"
                            <?php
                            echo (
                                $philhealthYakapStatus
                                == 'NOT YET REGISTERED'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            NOT YET REGISTERED
                        </option>

                    </select>

                    <div class="yakap-status-note">
                        Manual CIC checking only. Select the appropriate status based on the actual CIC result.
                    </div>

                </div>

            </div>

            <!-- =================================================
                 CONTACT INFORMATION
            ================================================== -->

            <div class="section-title">
                Contact Information
            </div>

            <div class="form-grid">

                <!-- CONTACT NUMBER -->

                <div class="form-group">

                    <label>
                        Contact Number
                    </label>

                    <input
                        type="text"
                        name="contact_no"
                        maxlength="11"
                        inputmode="numeric"
                        autocomplete="tel"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['contact_no']
                            );
                        ?>"
                    >

                </div>

                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        maxlength="150"
                        autocomplete="email"
                        value="<?php
                            echo htmlspecialchars(
                                $email
                            );
                        ?>"
                    >

                    <div class="field-note">
                        Optional.
                    </div>

                </div>

                <!-- EMERGENCY CONTACT -->

                <div class="form-group">

                    <label>
                        Emergency Contact
                    </label>

                    <input
                        type="text"
                        name="emergency_contact"
                        class="uppercase-field"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['emergency_contact']
                            );
                        ?>"
                    >

                </div>

                <!-- EMERGENCY CONTACT NUMBER -->

                <div class="form-group">

                    <label>
                        Emergency Contact Number
                    </label>

                    <input
                        type="text"
                        name="emergency_contact_no"
                        maxlength="11"
                        inputmode="numeric"
                        autocomplete="tel"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['emergency_contact_no']
                            );
                        ?>"
                    >

                </div>

                <!-- ADDRESS -->

                <div class="form-group form-group-full">

                    <label>
                        Address
                        <span class="required">*</span>
                    </label>

                    <textarea
                        name="address"
                        id="address"
                        class="uppercase-field"
                        required
                    ><?php
                        echo htmlspecialchars(
                            $patient['address']
                        );
                    ?></textarea>

                    <div class="field-note">
                        Required — enter
                        <strong>-</strong>
                        if no address is available.
                    </div>

                </div>

            </div>

            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <div class="form-actions">

                <a
                    href="view.php?id=<?php echo (int) $id; ?>"
                    class="btn btn-cancel"
                    id="cancelButton"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-update"
                    id="updateButton"
                >

                    <span
                        class="update-spinner"
                        id="updateSpinner"
                    ></span>

                    <span id="updateButtonText">
                        Update Patient
                    </span>

                </button>

            </div>

        </form>

    </div>

</main>

<script>

/*
|--------------------------------------------------------------------------
| UPPERCASE WHILE TYPING
|--------------------------------------------------------------------------
*/

function uppercaseWhileTyping(field) {

    field.value =
        field.value.toUpperCase();

}

/*
|--------------------------------------------------------------------------
| CLEAN FIELD
|--------------------------------------------------------------------------
*/

function cleanUppercaseField(field) {

    field.value =
        field.value
        .replace(/\s+/g, " ")
        .trim()
        .toUpperCase();

}

/*
|--------------------------------------------------------------------------
| APPLY UPPERCASE FIELDS
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll(".uppercase-field")
    .forEach(function(field) {

        field.value =
            field.value.toUpperCase();

        field.addEventListener(
            "input",
            function() {

                uppercaseWhileTyping(this);

            }
        );

        field.addEventListener(
            "blur",
            function() {

                cleanUppercaseField(this);

            }
        );

    });

/*
|--------------------------------------------------------------------------
| PHILHEALTH NUMBER
|--------------------------------------------------------------------------
*/

var philhealthField =
    document.querySelector(
        'input[name="philhealth_no"]'
    );

if (philhealthField) {

    philhealthField.addEventListener(
        "input",
        function() {

            this.value =
                this.value
                .replace(/[^0-9]/g, '')
                .slice(0, 30);

            updateYakapStatus();

        }
    );

}

/*
|--------------------------------------------------------------------------
| YAKAP STATUS
|--------------------------------------------------------------------------
*/

var yakapStatusField =
    document.getElementById(
        "philhealth_yakap_status"
    );

var yakapRequired =
    document.getElementById(
        "yakapRequired"
    );

function updateYakapStatus() {

    if (
        !philhealthField ||
        !yakapStatusField
    ) {
        return;
    }

    var philhealthValue =
        philhealthField.value
        .replace(/[^0-9]/g, '');

    /*
    -----------------------------------------------------------
    NO PHILHEALTH NUMBER
    -----------------------------------------------------------
    */

    if (
        philhealthValue === ""
    ) {

        yakapStatusField.value = "";
        yakapStatusField.disabled = true;
        yakapStatusField.required = false;

        if (yakapRequired) {

            yakapRequired.style.display =
                "none";

        }

    }

    /*
    -----------------------------------------------------------
    WITH PHILHEALTH NUMBER
    -----------------------------------------------------------
    */

    else {

        yakapStatusField.disabled = false;
        yakapStatusField.required = true;

        if (yakapRequired) {

            yakapRequired.style.display =
                "inline";

        }

    }

}

/*
|--------------------------------------------------------------------------
| CONTACT NUMBER NUMERIC
|--------------------------------------------------------------------------
*/

var contactField =
    document.querySelector(
        'input[name="contact_no"]'
    );

if (contactField) {

    contactField.addEventListener(
        "input",
        function() {

            this.value =
                this.value
                .replace(/[^0-9]/g, '')
                .slice(0, 11);

        }
    );

}

/*
|--------------------------------------------------------------------------
| EMERGENCY CONTACT NUMBER NUMERIC
|--------------------------------------------------------------------------
*/

var emergencyContactNoField =
    document.querySelector(
        'input[name="emergency_contact_no"]'
    );

if (emergencyContactNoField) {

    emergencyContactNoField.addEventListener(
        "input",
        function() {

            this.value =
                this.value
                .replace(/[^0-9]/g, '')
                .slice(0, 11);

        }
    );

}

/*
|--------------------------------------------------------------------------
| INITIAL YAKAP STATUS
|--------------------------------------------------------------------------
*/

updateYakapStatus();

/*
|--------------------------------------------------------------------------
| FORM SUBMIT + LOADING EFFECT
|--------------------------------------------------------------------------
*/

var editPatientForm =
    document.getElementById(
        "editPatientForm"
    );

var updateButton =
    document.getElementById(
        "updateButton"
    );

var updateButtonText =
    document.getElementById(
        "updateButtonText"
    );

var patientLoadingOverlay =
    document.getElementById(
        "patientLoadingOverlay"
    );

var cancelButton =
    document.getElementById(
        "cancelButton"
    );

editPatientForm.addEventListener(
    "submit",
    function(event) {

        /*
        -------------------------------------------------------
        STOP DEFAULT SUBMIT TEMPORARILY
        -------------------------------------------------------
        */

        event.preventDefault();

        /*
        -------------------------------------------------------
        ADDRESS
        -------------------------------------------------------
        */

        var address =
            document.getElementById(
                "address"
            );

        if (
            address.value.trim() === ""
        ) {

            address.value = "-";

        }

        /*
        -------------------------------------------------------
        CLEAN ALL UPPERCASE FIELDS
        -------------------------------------------------------
        */

        document
            .querySelectorAll(
                ".uppercase-field"
            )
            .forEach(
                function(field) {

                    field.value =
                        field.value
                        .replace(/\s+/g, " ")
                        .trim()
                        .toUpperCase();

                }
            );

        /*
        -------------------------------------------------------
        CLEAN PHILHEALTH NUMBER
        -------------------------------------------------------
        */

        if (philhealthField) {

            philhealthField.value =
                philhealthField.value
                .replace(/[^0-9]/g, '')
                .slice(0, 30);

        }

        /*
        -------------------------------------------------------
        VALIDATE PHILHEALTH / YAKAP STATUS
        -------------------------------------------------------
        */

        var philhealthValue =
            philhealthField
                ? philhealthField.value
                : "";

        var yakapValue =
            yakapStatusField
                ? yakapStatusField.value
                : "";

        if (
            philhealthValue !== "" &&
            yakapValue === ""
        ) {

            alert(
                "Please select the PhilHealth / YAKAP registration status."
            );

            if (yakapStatusField) {

                yakapStatusField.disabled = false;
                yakapStatusField.focus();

            }

            return false;

        }

        /*
        -------------------------------------------------------
        NO PHILHEALTH = NO YAKAP STATUS
        -------------------------------------------------------
        */

        if (
            philhealthValue === "" &&
            yakapStatusField
        ) {

            yakapStatusField.value = "";

        }

        /*
        -------------------------------------------------------
        SHOW LOADING
        -------------------------------------------------------
        */

        if (updateButton) {

            updateButton.disabled = true;

            updateButton.classList.add(
                "loading"
            );

        }

        if (updateButtonText) {

            updateButtonText.textContent =
                "Updating...";

        }

        if (cancelButton) {

            cancelButton.style.pointerEvents =
                "none";

            cancelButton.style.opacity =
                "0.6";

        }

        if (patientLoadingOverlay) {

            patientLoadingOverlay.classList.add(
                "show"
            );

        }

        /*
        -------------------------------------------------------
        ALLOW BROWSER TO RENDER LOADING FIRST
        -------------------------------------------------------
        */

        setTimeout(function() {

            editPatientForm.submit();

        }, 250);

    }
);

</script>

<?php

// =========================================================
// SHARED FOOTER
// =========================================================

require_once "../includes/footer.php";

$conn->close();

?>