<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/* =========================================================
   GET PATIENT ID
========================================================= */

$patientId = isset($_GET["patient_id"])
    ? (int)$_GET["patient_id"]
    : 0;

if ($patientId <= 0) {
    die("Invalid patient ID.");
}


/* =========================================================
   GET PATIENT INFORMATION
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
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $patientId);
$stmt->execute();

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

$fullName = preg_replace('/\s+/', ' ', $fullName);


/* =========================================================
   AGE
========================================================= */

$age = "";

if (!empty($patient["birthdate"])) {

    try {

        $birthDate = new DateTime($patient["birthdate"]);
        $today = new DateTime();

        $age = $today->diff($birthDate)->y;

    } catch (Exception $e) {

        $age = "";

    }
}


/* =========================================================
   SEX
========================================================= */

$sex = "";

if (!empty($patient["sex"])) {
    $sex = strtoupper($patient["sex"]);
}


/* =========================================================
   ADDRESS
========================================================= */

$address = "";

if (!empty($patient["address"])) {
    $address = $patient["address"];
}


/* =========================================================
   CERTIFICATE DATE
========================================================= */

$certificateDate = date("F d, Y");


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Medical Certificate";
$pageSubtitle = "Create Medical Certificate";
$basePath = "../";
$activePage = "patients";

include "../includes/header.php";
include "../includes/navigation.php";

?>

<style>

/* =========================================================
   MEDICAL CERTIFICATE PAGE
========================================================= */

.medcert-page {

    width: calc(100% - 40px);

    max-width: 1100px;

    margin: 0 auto;

    padding: 25px 0 45px;

}


/* =========================================================
   PAGE HEADER
========================================================= */

.medcert-header {

    margin-bottom: 20px;

}

.medcert-header h1 {

    margin: 0;

    color: #1f4e78;

    font-size: 28px;

}

.medcert-header p {

    margin: 5px 0 0;

    color: #666;

}


/* =========================================================
   CARD
========================================================= */

.medcert-card {

    background: #ffffff;

    border-radius: 10px;

    border: 1px solid #e2e6ea;

    box-shadow: 0 3px 12px rgba(0,0,0,0.06);

    margin-bottom: 20px;

    overflow: hidden;

}


/* =========================================================
   CARD TITLE
========================================================= */

.card-title {

    padding: 16px 20px;

    background: #1f4e78;

    color: #ffffff;

    font-size: 17px;

    font-weight: bold;

}


/* =========================================================
   FORM BODY
========================================================= */

.form-body {

    padding: 22px;

}


/* =========================================================
   PATIENT INFORMATION
========================================================= */

.patient-information {

    border: 1px solid #d9dee3;

    border-radius: 8px;

    background: #fafafa;

    margin-bottom: 22px;

    overflow: hidden;

}

.patient-information-title {

    background: #f0f3f6;

    border-bottom: 1px solid #d9dee3;

    padding: 12px 15px;

    font-size: 15px;

    font-weight: bold;

    color: #333;

}

.patient-information-body {

    padding: 15px;

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 12px;

}

.info-box {

    background: #ffffff;

    border: 1px solid #e1e5e9;

    border-radius: 6px;

    padding: 10px 12px;

}

.info-label {

    font-size: 11px;

    color: #777;

    margin-bottom: 4px;

    text-transform: uppercase;

}

.info-value {

    font-size: 14px;

    font-weight: 600;

    color: #222;

    word-break: break-word;

}


/* =========================================================
   DIAGNOSIS
========================================================= */

.form-group {

    margin-bottom: 20px;

}

.form-group label {

    display: block;

    margin-bottom: 8px;

    font-weight: 600;

    color: #333;

}

.required {

    color: #d93025;

}

.form-control {

    width: 100%;

    box-sizing: border-box;

    border: 1px solid #cfd6dd;

    border-radius: 6px;

    padding: 12px;

    font-size: 14px;

    color: #222;

    background: #ffffff;

    outline: none;

}

.form-control:focus {

    border-color: #1f4e78;

    box-shadow: 0 0 0 2px rgba(31,78,120,0.10);

}

textarea.form-control {

    min-height: 130px;

    resize: vertical;

}


/* =========================================================
   NOTE
========================================================= */

.form-note {

    margin-top: 8px;

    color: #777;

    font-size: 12px;

}


/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    margin-top: 25px;

}

.btn {

    display: inline-block;

    border: none;

    border-radius: 6px;

    padding: 11px 18px;

    font-size: 14px;

    font-weight: 600;

    text-decoration: none;

    cursor: pointer;

}

.btn-back {

    background: #6c757d;

    color: #ffffff;

}

.btn-save {

    background: #1f4e78;

    color: #ffffff;

}

.btn:hover {

    opacity: 0.9;

}


/* =========================================================
   PREVIEW NOTE
========================================================= */

.preview-note {

    background: #f7f9fb;

    border-left: 4px solid #1f4e78;

    padding: 12px 14px;

    margin-bottom: 20px;

    color: #555;

    font-size: 13px;

    line-height: 1.5;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 800px) {

    .patient-information-body {

        grid-template-columns: 1fr 1fr;

    }

}

@media (max-width: 550px) {

    .medcert-page {

        width: calc(100% - 20px);

    }

    .patient-information-body {

        grid-template-columns: 1fr;

    }

    .form-actions {

        flex-direction: column;

        align-items: stretch;

    }

    .btn {

        text-align: center;

    }

}


/* =========================================================
   PRINT
========================================================= */

@media print {

    body {

        background: #ffffff !important;

    }

    .medcert-page {

        width: 100%;

        max-width: none;

        margin: 0;

        padding: 0;

    }

    .medcert-header,

    .patient-information,

    .form-actions,

    .preview-note,

    .card-title {

        display: none !important;

    }

}

</style>


<div class="medcert-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="medcert-header">

        <h1>
            Medical Certificate
        </h1>

        <p>
            Create a medical certificate for this patient.
        </p>

    </div>


    <!-- =====================================================
         MEDICAL CERTIFICATE FORM
    ====================================================== -->

    <div class="medcert-card">


        <div class="card-title">
            Medical Certificate
        </div>


        <div class="form-body">


            <!-- =================================================
                 INFORMATION NOTE
            ================================================== -->

            <div class="preview-note">

                Patient information is automatically retrieved
                from the patient's record. Only the diagnosis
                needs to be entered before saving the certificate.

            </div>


            <!-- =================================================
                 PATIENT INFORMATION
            ================================================== -->

            <div class="patient-information">

                <div class="patient-information-title">

                    Patient Information

                </div>


                <div class="patient-information-body">


                    <!-- PATIENT ID -->

                    <div class="info-box">

                        <div class="info-label">
                            Patient ID
                        </div>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                $patient["patient_id"]
                            );
                            ?>

                        </div>

                    </div>


                    <!-- PATIENT NAME -->

                    <div class="info-box">

                        <div class="info-label">
                            Patient Name
                        </div>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                strtoupper($fullName)
                            );
                            ?>

                        </div>

                    </div>


                    <!-- AGE -->

                    <div class="info-box">

                        <div class="info-label">
                            Age
                        </div>

                        <div class="info-value">

                            <?php

                            if ($age !== "") {

                                echo htmlspecialchars($age);

                            } else {

                                echo "-";

                            }

                            ?>

                        </div>

                    </div>


                    <!-- SEX -->

                    <div class="info-box">

                        <div class="info-label">
                            Sex
                        </div>

                        <div class="info-value">

                            <?php

                            if ($sex !== "") {

                                echo htmlspecialchars($sex);

                            } else {

                                echo "-";

                            }

                            ?>

                        </div>

                    </div>


                    <!-- BIRTHDATE -->

                    <div class="info-box">

                        <div class="info-label">
                            Birthdate
                        </div>

                        <div class="info-value">

                            <?php

                            if (!empty($patient["birthdate"])) {

                                echo htmlspecialchars(
                                    date(
                                        "F d, Y",
                                        strtotime(
                                            $patient["birthdate"]
                                        )
                                    )
                                );

                            } else {

                                echo "-";

                            }

                            ?>

                        </div>

                    </div>


                    <!-- CERTIFICATE DATE -->

                    <div class="info-box">

                        <div class="info-label">
                            Certificate Date
                        </div>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                $certificateDate
                            );
                            ?>

                        </div>

                    </div>


                    <!-- ADDRESS -->

                    <div class="info-box" style="grid-column: 1 / -1;">

                        <div class="info-label">
                            Address
                        </div>

                        <div class="info-value">

                            <?php

                            if ($address !== "") {

                                echo htmlspecialchars($address);

                            } else {

                                echo "-";

                            }

                            ?>

                        </div>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
                action="save.php"
                onsubmit="return validateMedicalCertificate();"
            >


                <!-- PATIENT ID -->

                <input
                    type="hidden"
                    name="patient_id"
                    value="<?php echo (int)$patientId; ?>"
                >


                <!-- =================================================
                     DIAGNOSIS ONLY
                ================================================== -->

                <div class="form-group">

                    <label for="diagnosis">

                        Diagnosis
                        <span class="required">*</span>

                    </label>


                    <textarea
                        name="diagnosis"
                        id="diagnosis"
                        class="form-control"
                        placeholder="Enter the patient's diagnosis..."
                        required
                    ></textarea>


                    <div class="form-note">

                        Enter the diagnosis that will appear on
                        the medical certificate.

                    </div>

                </div>


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="form-actions">


                    <a
                        href="../patients/view.php?id=<?php echo (int)$patientId; ?>"
                        class="btn btn-back"
                    >
                        ← Back to Patient
                    </a>


                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Save Medical Certificate
                    </button>


                </div>


            </form>


        </div>

    </div>


</div>


<script>

/* =========================================================
   DIAGNOSIS VALIDATION
========================================================= */

function validateMedicalCertificate()
{
    var diagnosis =
        document.getElementById("diagnosis").value.trim();

    if (diagnosis === "") {

        alert("Please enter the diagnosis.");

        document.getElementById("diagnosis").focus();

        return false;

    }

    return true;
}

</script>


<?php include "../includes/footer.php"; ?>