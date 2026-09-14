<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/
function cleanText($value)
{
    return trim($value);
}


/*
|--------------------------------------------------------------------------
| BP STATUS
|--------------------------------------------------------------------------
|
| This is for screening / alert purposes only.
| It does NOT diagnose hypertension.
|
*/
function getBPStatus($blood_pressure)
{
    if ($blood_pressure === "") {
        return null;
    }

    if (!preg_match(
        "/^([0-9]{2,3})\/([0-9]{2,3})$/",
        $blood_pressure,
        $matches
    )) {
        return null;
    }

    $systolic = intval($matches[1]);
    $diastolic = intval($matches[2]);


    /*
    |--------------------------------------------------------------------------
    | Very High BP / Urgent Alert
    |--------------------------------------------------------------------------
    */
    if (
        $systolic >= 180 ||
        $diastolic >= 120
    ) {
        return "Very High BP / Urgent Alert";
    }


    /*
    |--------------------------------------------------------------------------
    | High BP
    |--------------------------------------------------------------------------
    */
    if (
        $systolic >= 140 ||
        $diastolic >= 90
    ) {
        return "High BP";
    }


    /*
    |--------------------------------------------------------------------------
    | Elevated
    |--------------------------------------------------------------------------
    */
    if (
        $systolic >= 120 &&
        $systolic <= 129 &&
        $diastolic < 80
    ) {
        return "Elevated";
    }


    /*
    |--------------------------------------------------------------------------
    | Low BP
    |--------------------------------------------------------------------------
    */
    if (
        $systolic < 90 ||
        $diastolic < 60
    ) {
        return "Low BP";
    }


    /*
    |--------------------------------------------------------------------------
    | Normal
    |--------------------------------------------------------------------------
    */
    if (
        $systolic < 120 &&
        $diastolic < 80
    ) {
        return "Normal";
    }


    /*
    |--------------------------------------------------------------------------
    | Other elevated combinations
    |--------------------------------------------------------------------------
    */
    if (
        $systolic >= 130 ||
        $diastolic >= 80
    ) {
        return "High BP";
    }


    return "Normal";
}


/*
|--------------------------------------------------------------------------
| Only POST requests are allowed
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}


/*
|--------------------------------------------------------------------------
| GET POST DATA
|--------------------------------------------------------------------------
*/
$patient_id = isset($_POST["patient_id"])
    ? intval($_POST["patient_id"])
    : 0;

$visit_date = isset($_POST["visit_date"])
    ? cleanText($_POST["visit_date"])
    : "";

$chief_complaint = isset($_POST["chief_complaint"])
    ? cleanText($_POST["chief_complaint"])
    : "";

$other_chief_complaint = isset($_POST["other_chief_complaint"])
    ? cleanText($_POST["other_chief_complaint"])
    : "";

$history_illness = isset($_POST["history_illness"])
    ? cleanText($_POST["history_illness"])
    : "";

$blood_pressure = isset($_POST["blood_pressure"])
    ? cleanText($_POST["blood_pressure"])
    : "";

$temperature = isset($_POST["temperature"]) && $_POST["temperature"] !== ""
    ? floatval($_POST["temperature"])
    : null;

$pulse_rate = isset($_POST["pulse_rate"]) && $_POST["pulse_rate"] !== ""
    ? intval($_POST["pulse_rate"])
    : null;

$respiratory_rate = isset($_POST["respiratory_rate"]) && $_POST["respiratory_rate"] !== ""
    ? intval($_POST["respiratory_rate"])
    : null;

$weight = isset($_POST["weight"]) && $_POST["weight"] !== ""
    ? floatval($_POST["weight"])
    : null;

$height = isset($_POST["height"]) && $_POST["height"] !== ""
    ? floatval($_POST["height"])
    : null;

$assessment = isset($_POST["assessment"])
    ? cleanText($_POST["assessment"])
    : "";

$management = isset($_POST["management"])
    ? cleanText($_POST["management"])
    : "";

$follow_up_date = isset($_POST["follow_up_date"])
    ? cleanText($_POST["follow_up_date"])
    : "";

$remarks = isset($_POST["remarks"])
    ? cleanText($_POST["remarks"])
    : "";


/*
|--------------------------------------------------------------------------
| Required Fields
|--------------------------------------------------------------------------
*/
if ($patient_id <= 0) {
    die("Invalid patient ID.");
}

if ($visit_date === "") {
    die("Visit date is required.");
}

if ($chief_complaint === "") {
    die("Chief complaint is required.");
}

if ($history_illness === "") {
    die("History of present illness is required.");
}

if ($assessment === "") {
    die("Assessment is required.");
}


/*
|--------------------------------------------------------------------------
| Handle OTHER Chief Complaint
|--------------------------------------------------------------------------
*/
if ($chief_complaint === "OTHERS") {

    if ($other_chief_complaint === "") {
        die("Please specify the other chief complaint.");
    }

    $chief_complaint = $other_chief_complaint;
}


/*
|--------------------------------------------------------------------------
| Validate Visit Date
|--------------------------------------------------------------------------
*/
$dateObject = DateTime::createFromFormat(
    "Y-m-d",
    $visit_date
);

if (
    !$dateObject ||
    $dateObject->format("Y-m-d") !== $visit_date
) {
    die("Invalid visit date.");
}


/*
|--------------------------------------------------------------------------
| Validate Follow-up Date
|--------------------------------------------------------------------------
*/
if ($follow_up_date !== "") {

    $followDateObject = DateTime::createFromFormat(
        "Y-m-d",
        $follow_up_date
    );

    if (
        !$followDateObject ||
        $followDateObject->format("Y-m-d") !== $follow_up_date
    ) {
        die("Invalid follow-up date.");
    }
}


/*
|--------------------------------------------------------------------------
| Validate Blood Pressure
|--------------------------------------------------------------------------
*/
if ($blood_pressure !== "") {

    if (
        !preg_match(
            "/^[0-9]{2,3}\/[0-9]{2,3}$/",
            $blood_pressure
        )
    ) {
        die(
            "Invalid blood pressure format. Example: 120/80."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Extract BP values
    |--------------------------------------------------------------------------
    */
    $bpParts = explode(
        "/",
        $blood_pressure
    );

    $systolic = intval($bpParts[0]);
    $diastolic = intval($bpParts[1]);


    /*
    |--------------------------------------------------------------------------
    | Validate reasonable BP range
    |--------------------------------------------------------------------------
    */
    if (
        $systolic < 50 ||
        $systolic > 300 ||
        $diastolic < 30 ||
        $diastolic > 200
    ) {
        die(
            "Invalid blood pressure value."
        );
    }
}


/*
|--------------------------------------------------------------------------
| Calculate BP Status
|--------------------------------------------------------------------------
*/
$bp_status = getBPStatus(
    $blood_pressure
);


/*
|--------------------------------------------------------------------------
| Validate Temperature
|--------------------------------------------------------------------------
*/
if ($temperature !== null) {

    if (
        $temperature < 20 ||
        $temperature > 50
    ) {
        die("Invalid temperature.");
    }
}


/*
|--------------------------------------------------------------------------
| Validate Pulse Rate
|--------------------------------------------------------------------------
*/
if ($pulse_rate !== null) {

    if (
        $pulse_rate < 20 ||
        $pulse_rate > 250
    ) {
        die("Invalid pulse rate.");
    }
}


/*
|--------------------------------------------------------------------------
| Validate Respiratory Rate
|--------------------------------------------------------------------------
*/
if ($respiratory_rate !== null) {

    if (
        $respiratory_rate < 5 ||
        $respiratory_rate > 100
    ) {
        die("Invalid respiratory rate.");
    }
}


/*
|--------------------------------------------------------------------------
| Validate Weight
|--------------------------------------------------------------------------
*/
if ($weight !== null) {

    if (
        $weight <= 0 ||
        $weight > 500
    ) {
        die("Invalid weight.");
    }
}


/*
|--------------------------------------------------------------------------
| Validate Height
|--------------------------------------------------------------------------
*/
if ($height !== null) {

    if (
        $height <= 0 ||
        $height > 300
    ) {
        die("Invalid height.");
    }
}


/*
|--------------------------------------------------------------------------
| Calculate BMI
|--------------------------------------------------------------------------
|
| Height is entered in centimeters.
|
*/
$bmi = null;
$bmi_status = null;

if (
    $weight !== null &&
    $height !== null &&
    $height > 0
) {

    $height_meters = $height / 100;

    $bmi = $weight /
        (
            $height_meters *
            $height_meters
        );

    $bmi = round($bmi, 2);


    /*
    |--------------------------------------------------------------------------
    | BMI Status
    |--------------------------------------------------------------------------
    */
    if ($bmi < 18.5) {

        $bmi_status = "Underweight";

    } elseif ($bmi < 25) {

        $bmi_status = "Normal Weight";

    } elseif ($bmi < 30) {

        $bmi_status = "Overweight";

    } else {

        $bmi_status = "Obese";
    }
}


/*
|--------------------------------------------------------------------------
| Check Patient
|--------------------------------------------------------------------------
*/
$stmtPatient = $conn->prepare("
    SELECT
        id,
        first_name,
        middle_name,
        last_name,
        status
    FROM patients
    WHERE id = ?
    LIMIT 1
");

if (!$stmtPatient) {

    die(
        "Database error: " .
        $conn->error
    );
}

$stmtPatient->bind_param(
    "i",
    $patient_id
);

$stmtPatient->execute();

$resultPatient =
    $stmtPatient->get_result();

if ($resultPatient->num_rows === 0) {

    $stmtPatient->close();

    die("Patient not found.");
}

$patient =
    $resultPatient->fetch_assoc();

$stmtPatient->close();


/*
|--------------------------------------------------------------------------
| Prevent Consultation for Deceased Patient
|--------------------------------------------------------------------------
*/
if (
    isset($patient["status"]) &&
    strtolower(
        trim($patient["status"])
    ) === "deceased"
) {

    die(
        "Cannot create a consultation for a deceased patient."
    );
}


/*
|--------------------------------------------------------------------------
| Prevent Exact Duplicate Consultation
|--------------------------------------------------------------------------
*/
$stmtDuplicate = $conn->prepare("
    SELECT id
    FROM consultations
    WHERE patient_id = ?
      AND visit_date = ?
      AND chief_complaint = ?
      AND history_illness = ?
      AND assessment = ?
    LIMIT 1
");

if (!$stmtDuplicate) {

    die(
        "Database error: " .
        $conn->error
    );
}

$stmtDuplicate->bind_param(
    "issss",
    $patient_id,
    $visit_date,
    $chief_complaint,
    $history_illness,
    $assessment
);

$stmtDuplicate->execute();

$resultDuplicate =
    $stmtDuplicate->get_result();

if ($resultDuplicate->num_rows > 0) {

    $stmtDuplicate->close();

    die(
        "A consultation with the same visit date, " .
        "chief complaint, history, and assessment already exists."
    );
}

$stmtDuplicate->close();


/*
|--------------------------------------------------------------------------
| Follow-up Status
|--------------------------------------------------------------------------
*/
$follow_up_status = "PENDING";

if ($follow_up_date === "") {
    $follow_up_status = "NONE";
}


/*
|--------------------------------------------------------------------------
| Insert Consultation
|--------------------------------------------------------------------------
|
| Current fields:
|
| patient_id
| visit_date
| chief_complaint
| history_illness
| blood_pressure
| bp_status
| temperature
| pulse_rate
| respiratory_rate
| weight
| height
| bmi
| bmi_status
| assessment
| management
| follow_up_date
| remarks
| follow_up_status
|
| Oxygen Saturation is no longer used.
|
*/
$sql = "
    INSERT INTO consultations (
        patient_id,
        visit_date,
        chief_complaint,
        history_illness,
        blood_pressure,
        bp_status,
        temperature,
        pulse_rate,
        respiratory_rate,
        weight,
        height,
        bmi,
        bmi_status,
        assessment,
        management,
        follow_up_date,
        remarks,
        follow_up_status
    )
    VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )
";


/*
|--------------------------------------------------------------------------
| Prepare Insert
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare($sql);

if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );
}


/*
|--------------------------------------------------------------------------
| Bind Parameters
|--------------------------------------------------------------------------
|
| 1  patient_id       = i
| 2  visit_date       = s
| 3  chief_complaint  = s
| 4  history_illness  = s
| 5  blood_pressure   = s
| 6  bp_status        = s
| 7  temperature      = d
| 8  pulse_rate       = i
| 9  respiratory_rate = i
| 10 weight           = d
| 11 height           = d
| 12 bmi              = d
| 13 bmi_status       = s
| 14 assessment       = s
| 15 management       = s
| 16 follow_up_date   = s
| 17 remarks          = s
| 18 follow_up_status = s
|
| TOTAL = 18
|
*/
$stmt->bind_param(
    "isssssdiidddssssss",
    $patient_id,
    $visit_date,
    $chief_complaint,
    $history_illness,
    $blood_pressure,
    $bp_status,
    $temperature,
    $pulse_rate,
    $respiratory_rate,
    $weight,
    $height,
    $bmi,
    $bmi_status,
    $assessment,
    $management,
    $follow_up_date,
    $remarks,
    $follow_up_status
);


/*
|--------------------------------------------------------------------------
| Execute
|--------------------------------------------------------------------------
*/
if (!$stmt->execute()) {

    $error = $stmt->error;

    $stmt->close();

    die(
        "Unable to save consultation: " .
        $error
    );
}


$consultation_id =
    $stmt->insert_id;

$stmt->close();


/*
|--------------------------------------------------------------------------
| Get Consultation Count
|--------------------------------------------------------------------------
*/
$stmtCount = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM consultations
    WHERE patient_id = ?
");

$consultation_count = 1;

if ($stmtCount) {

    $stmtCount->bind_param(
        "i",
        $patient_id
    );

    $stmtCount->execute();

    $resultCount =
        $stmtCount->get_result();

    if (
        $rowCount =
        $resultCount->fetch_assoc()
    ) {

        $consultation_count =
            intval(
                $rowCount["total"]
            );
    }

    $stmtCount->close();
}


/*
|--------------------------------------------------------------------------
| Activity Log
|--------------------------------------------------------------------------
*/
$patient_name = trim(
    $patient["last_name"] .
    ", " .
    $patient["first_name"] .
    " " .
    $patient["middle_name"]
);

$activity_description =
    "Created consultation for patient " .
    $patient_name .
    " (Patient ID: " .
    $patient_id .
    ")" .
    " on " .
    $visit_date;

if (function_exists("logActivity")) {

    logActivity(
        $conn,
        "CREATE",
        $activity_description
    );
}


/*
|--------------------------------------------------------------------------
| Close Database
|--------------------------------------------------------------------------
*/
$conn->close();

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link
        rel="icon"
        type="image/png"
        href="../asset/images/DCMDLOGO.png?v=1"
    >

    <title>
        Consultation Saved
    </title>


    <style>

        body {

            font-family:
                Arial,
                sans-serif;

            background:
                #f4f6f8;

            margin: 0;

            padding:
                40px 20px;
        }


        .success-box {

            max-width:
                600px;

            margin:
                50px auto;

            background:
                #ffffff;

            padding:
                35px;

            border-radius:
                10px;

            box-shadow:
                0 3px 15px
                rgba(0,0,0,0.10);

            text-align:
                center;
        }


        .success-icon {

            font-size:
                55px;

            color:
                #198754;

            margin-bottom:
                10px;
        }


        h2 {

            margin-top:
                0;

            color:
                #198754;
        }


        p {

            color:
                #555;

            line-height:
                1.6;
        }


        .info {

            background:
                #f8f9fa;

            padding:
                15px;

            border-radius:
                8px;

            margin:
                20px 0;

            text-align:
                left;
        }


        .info strong {

            color:
                #333;
        }


        .bp-normal {
            color: #198754;
            font-weight: bold;
        }


        .bp-elevated {
            color: #d39e00;
            font-weight: bold;
        }


        .bp-high {
            color: #dc3545;
            font-weight: bold;
        }


        .bp-urgent {
            color: #b02a37;
            font-weight: bold;
        }


        .bp-low {
            color: #0d6efd;
            font-weight: bold;
        }


        .buttons {

            margin-top:
                25px;
        }


        .btn {

            display:
                inline-block;

            padding:
                11px 18px;

            margin:
                5px;

            border-radius:
                6px;

            text-decoration:
                none;

            color:
                #ffffff;

            font-weight:
                bold;
        }


        .btn-primary {

            background:
                #0d6efd;
        }


        .btn-success {

            background:
                #198754;
        }


        .btn:hover {

            opacity:
                0.9;
        }

    </style>

</head>


<body>


<div class="success-box">


    <div class="success-icon">
        ✓
    </div>


    <h2>
        Consultation Saved Successfully
    </h2>


    <p>
        The consultation record has been
        successfully saved.
    </p>


    <div class="info">


        <p>

            <strong>
                Patient:
            </strong>

            <?php
            echo htmlspecialchars(
                $patient_name
            );
            ?>

        </p>


        <p>

            <strong>
                Visit Date:
            </strong>

            <?php
            echo htmlspecialchars(
                $visit_date
            );
            ?>

        </p>


        <p>

            <strong>
                Consultation No.:
            </strong>

            <?php
            echo $consultation_count;
            ?>

        </p>


        <?php if ($blood_pressure !== "") { ?>


            <p>

                <strong>
                    Blood Pressure:
                </strong>

                <?php
                echo htmlspecialchars(
                    $blood_pressure
                );
                ?>

            </p>


            <p>

                <strong>
                    BP Status:
                </strong>

                <?php

                $bpClass = "bp-normal";

                if ($bp_status === "High BP") {
                    $bpClass = "bp-high";
                } elseif (
                    $bp_status ===
                    "Very High BP / Urgent Alert"
                ) {
                    $bpClass = "bp-urgent";
                } elseif (
                    $bp_status === "Elevated"
                ) {
                    $bpClass = "bp-elevated";
                } elseif (
                    $bp_status === "Low BP"
                ) {
                    $bpClass = "bp-low";
                }

                ?>

                <span class="<?php echo $bpClass; ?>">

                    <?php
                    echo htmlspecialchars(
                        $bp_status
                    );
                    ?>

                </span>

            </p>


        <?php } ?>


        <?php if ($bmi !== null) { ?>


            <p>

                <strong>
                    BMI:
                </strong>

                <?php
                echo number_format(
                    $bmi,
                    2
                );
                ?>

            </p>


            <p>

                <strong>
                    BMI Status:
                </strong>

                <?php
                echo htmlspecialchars(
                    $bmi_status
                );
                ?>

            </p>


        <?php } ?>


    </div>


    <div class="buttons">


        <a
            href="view.php?id=<?php echo $patient_id; ?>"
            class="btn btn-primary"
        >
            View Patient Profile
        </a>


        <a
            href="consultation.php?patient_id=<?php echo $patient_id; ?>"
            class="btn btn-success"
        >
            New Consultation
        </a>


    </div>


</div>


</body>

</html>