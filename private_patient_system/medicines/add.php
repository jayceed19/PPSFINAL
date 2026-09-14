<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";

/* =========================================================
   ADMINISTRATOR ONLY
========================================================= */

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "Administrator"
) {
    http_response_code(403);
    die("Access denied.");
}

/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Add Medicine";
$pageSubtitle = "Add a medicine to the master list";
$basePath = "../";
$activePage = "medicines";

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

.medicine-form-container {
    max-width: 850px;
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
   CARD
========================================================= */

.form-card {
    background: white;
    border: 1px solid #d9e2ec;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* =========================================================
   INFO BOX
========================================================= */

.info-box {
    margin-bottom: 20px;
    padding: 13px 15px;
    background: #eef6ff;
    border-left: 4px solid #1f4e78;
    border-radius: 5px;
    color: #444;
    font-size: 13px;
    line-height: 1.5;
}

/* =========================================================
   FORM
========================================================= */

.form-grid {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1fr;
    gap: 18px;
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

.form-group input,
.form-group select {
    width: 100%;
    box-sizing: border-box;
    padding: 11px 12px;
    border: 1px solid #cbd5df;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    outline: none;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #1f4e78;
    box-shadow: 0 0 0 2px rgba(31,78,120,0.10);
}

.form-help {
    margin-top: 5px;
    font-size: 12px;
    color: #777;
}

/* =========================================================
   REQUIRED
========================================================= */

.required {
    color: #dc3545;
}

/* =========================================================
   ACTIONS
========================================================= */

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e1e7ed;
    flex-wrap: wrap;
}

.form-actions-left,
.form-actions-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

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

.btn-primary {
    background: #1f4e78;
    color: white;
}

.btn-primary:hover {
    background: #173a5c;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .medicine-form-container {
        padding: 0 12px 30px;
    }

    .form-card {
        padding: 18px;
    }

    .form-grid {
        grid-template-columns: 1fr;
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

<div class="medicine-form-container">

    <!-- =====================================================
         PAGE TOP
    ====================================================== -->

    <div class="page-top">

        <div>

            <h2>
                Add Medicine
            </h2>

            <p>
                Add a new medicine to the master list.
            </p>

        </div>

    </div>


    <!-- =====================================================
         INFO
    ====================================================== -->

    <div class="info-box">

        <strong>Medicine Master List:</strong>

        Enter the medicine name, strength, and form.
        The medicine will become available in the Prescription
        medicine search after it is saved as active.

    </div>


    <!-- =====================================================
         FORM CARD
    ====================================================== -->

    <div class="form-card">

        <form
            method="POST"
            action="save.php"
            autocomplete="off"
        >

            <div class="form-grid">

                <!-- =================================================
                     MEDICINE NAME
                ================================================== -->

                <div class="form-group">

                    <label for="medicine_name">
                        Medicine Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="medicine_name"
                        name="medicine_name"
                        maxlength="150"
                        placeholder="e.g. PARACETAMOL"
                        required
                    >

                    <div class="form-help">
                        Enter the generic or standard medicine name.
                    </div>

                </div>


                <!-- =================================================
                     STRENGTH
                ================================================== -->

                <div class="form-group">

                    <label for="strength">
                        Strength
                    </label>

                    <input
                        type="text"
                        id="strength"
                        name="strength"
                        maxlength="100"
                        placeholder="e.g. 500 MG"
                    >

                    <div class="form-help">
                        Example: 500 MG, 250 MG/5 ML.
                    </div>

                </div>


                <!-- =================================================
                     FORM
                ================================================== -->

                <div class="form-group">

                    <label for="form">
                        Form
                    </label>

                    <input
                        type="text"
                        id="form"
                        name="form"
                        maxlength="50"
                        placeholder="e.g. TABLET"
                    >

                    <div class="form-help">
                        Example: TABLET, CAPSULE, SYRUP.
                    </div>

                </div>

            </div>


            <!-- =====================================================
                 ACTIONS
            ====================================================== -->

            <div class="form-actions">

                <div class="form-actions-left">

                    <a
                        href="index.php"
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
                        Save Medicine
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const textFields = document.querySelectorAll(
            "#medicine_name, #strength, #form"
        );

        textFields.forEach(
            function (field) {

                field.addEventListener(
                    "input",
                    function () {

                        field.value =
                            field.value.toUpperCase();

                    }
                );

            }
        );

        document.querySelector("form").addEventListener(
            "submit",
            function (event) {

                const medicineName =
                    document.getElementById(
                        "medicine_name"
                    );

                if (
                    !medicineName.value.trim()
                ) {

                    event.preventDefault();

                    alert(
                        "Please enter the medicine name."
                    );

                    medicineName.focus();

                    return;

                }

                medicineName.value =
                    medicineName.value
                        .trim()
                        .replace(/\s+/g, " ")
                        .toUpperCase();

                document.getElementById(
                    "strength"
                ).value =
                    document.getElementById(
                        "strength"
                    ).value
                        .trim()
                        .replace(/\s+/g, " ")
                        .toUpperCase();

                document.getElementById(
                    "form"
                ).value =
                    document.getElementById(
                        "form"
                    ).value
                        .trim()
                        .replace(/\s+/g, " ")
                        .toUpperCase();

            }
        );

    }
);

</script>

<?php

include "../includes/footer.php";

?>
