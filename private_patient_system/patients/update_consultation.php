<?php

require_once "../config/database.php";
require_once "../config/auth.php";


/* =========================================================
   ONLY POST REQUEST
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    die("Invalid request.");

}


/* =========================================================
   HELPER FUNCTIONS
========================================================= */

function cleanText($value)
{
    $value = trim($value);
    $value = preg_replace('/\s+/', ' ', $value);

    return $value;
}


function cleanTextarea($value)
{
    return trim($value);
}


function validDate($date)
{
    if ($date === '') {
        return false;
    }

    $d = DateTime::createFromFormat(
        'Y-m-d',
        $date
    );

    return $d &&
        $d->format('Y-m-d') === $date;
}


/* =========================================================
   BLOOD PRESSURE STATUS
========================================================= */

function getBPStatus($bloodPressure)
{
    $bloodPressure = trim($bloodPressure);

    if ($bloodPressure === '') {
        return null;
    }

    if (
        !preg_match(
            '/^([0-9]{2,3})\/([0-9]{2,3})$/',
            $bloodPressure,
            $matches
        )
    ) {
        return null;
    }

    $systolic = intval($matches[1]);
    $diastolic = intval($matches[2]);


    /*
        Simplified BP screening categories:

        Low:
        <90 systolic OR <60 diastolic

        Normal:
        <120 systolic AND <80 diastolic

        Elevated:
        120-129 systolic AND <80 diastolic

        High:
        130+ systolic OR 80+ diastolic

        Very High / Urgent Alert:
        180+ systolic OR 120+ diastolic
    */


    if (
        $systolic >= 180 ||
        $diastolic >= 120
    ) {

        return "Very High BP / Urgent Alert";

    }


    if (
        $systolic < 90 ||
        $diastolic < 60
    ) {

        return "Low BP";

    }


    if (
        $systolic >= 120 &&
        $systolic <= 129 &&
        $diastolic < 80
    ) {

        return "Elevated";

    }


    if (
        $systolic < 120 &&
        $diastolic < 80
    ) {

        return "Normal";

    }


    if (
        $systolic >= 130 ||
        $diastolic >= 80
    ) {

        return "High BP";

    }


    return "Normal";
}


/* =========================================================
   GET POST DATA
========================================================= */

$consultationId = isset($_POST['consultation_id'])
    ? intval($_POST['consultation_id'])
    : 0;


$visitDate = isset($_POST['visit_date'])
    ? trim($_POST['visit_date'])
    : "";


$chiefComplaint = isset($_POST['chief_complaint'])
    ? strtoupper(
        cleanText($_POST['chief_complaint'])
    )
    : "";


$otherChiefComplaint = isset(
    $_POST['other_chief_complaint']
)
    ? cleanText(
        $_POST['other_chief_complaint']
    )
    : "";


$historyIllness = isset(
    $_POST['history_illness']
)
    ? cleanTextarea(
        $_POST['history_illness']
    )
    : "";


$bloodPressure = isset(
    $_POST['blood_pressure']
)
    ? strtoupper(
        cleanText($_POST['blood_pressure'])
    )
    : "";


$temperature = isset(
    $_POST['temperature']
)
    ? trim($_POST['temperature'])
    : "";


$pulseRate = isset(
    $_POST['pulse_rate']
)
    ? trim($_POST['pulse_rate'])
    : "";


$respiratoryRate = isset(
    $_POST['respiratory_rate']
)
    ? trim($_POST['respiratory_rate'])
    : "";


$weight = isset(
    $_POST['weight']
)
    ? trim($_POST['weight'])
    : "";


$height = isset(
    $_POST['height']
)
    ? trim($_POST['height'])
    : "";


$assessment = isset(
    $_POST['assessment']
)
    ? cleanTextarea(
        $_POST['assessment']
    )
    : "";


$medication = isset(
    $_POST['medication']
)
    ? cleanTextarea(
        $_POST['medication']
    )
    : "";


$management = isset(
    $_POST['management']
)
    ? cleanTextarea(
        $_POST['management']
    )
    : "";


$followUpDate = isset(
    $_POST['follow_up_date']
)
    ? trim($_POST['follow_up_date'])
    : "";


$remarks = isset(
    $_POST['remarks']
)
    ? cleanTextarea(
        $_POST['remarks']
    )
    : "";


/* =========================================================
   BASIC VALIDATION
========================================================= */

if ($consultationId <= 0) {

    die("Invalid consultation ID.");

}


if ($visitDate === '') {

    die("Visit date is required.");

}


if (!validDate($visitDate)) {

    die("Invalid visit date.");

}


/* =========================================================
   PREVENT FUTURE VISIT DATE
========================================================= */

$today = date('Y-m-d');

if ($visitDate > $today) {

    die(
        "Visit date cannot be in the future."
    );

}


/* =========================================================
   REQUIRED FIELDS
========================================================= */

if ($chiefComplaint === '') {

    die(
        "Chief complaint is required."
    );

}


if ($historyIllness === '') {

    die(
        "History of present illness is required."
    );

}


if ($assessment === '') {

    die(
        "Assessment / diagnosis is required."
    );

}


/* =========================================================
   HANDLE OTHERS
========================================================= */

if ($chiefComplaint === 'OTHERS') {

    if ($otherChiefComplaint === '') {

        die(
            "Please specify the other chief complaint."
        );

    }

    $chiefComplaint =
        strtoupper(
            cleanText(
                $otherChiefComplaint
            )
        );

}


/* =========================================================
   BLOOD PRESSURE VALIDATION
========================================================= */

if ($bloodPressure !== '') {

    if (
        !preg_match(
            '/^[0-9]{2,3}\/[0-9]{2,3}$/',
            $bloodPressure
        )
    ) {

        die(
            "Invalid blood pressure format. Use format like 120/80."
        );

    }


    /*
        Extract systolic and diastolic
        values for range validation.
    */

    $bpParts =
        explode(
            '/',
            $bloodPressure
        );


    $systolic =
        intval($bpParts[0]);


    $diastolic =
        intval($bpParts[1]);


    /*
        Reasonable input limits.
    */

    if (
        $systolic < 50 ||
        $systolic > 300
    ) {

        die(
            "Invalid systolic blood pressure. Enter a value between 50 and 300."
        );

    }


    if (
        $diastolic < 30 ||
        $diastolic > 200
    ) {

        die(
            "Invalid diastolic blood pressure. Enter a value between 30 and 200."
        );

    }

}


/* =========================================================
   CALCULATE BP STATUS
========================================================= */

$bpStatus =
    getBPStatus(
        $bloodPressure
    );


/* =========================================================
   TEMPERATURE VALIDATION
========================================================= */

if ($temperature !== '') {

    if (!is_numeric($temperature)) {

        die(
            "Invalid temperature."
        );

    }


    $temperatureValue =
        floatval($temperature);


    if (
        $temperatureValue < 20 ||
        $temperatureValue > 50
    ) {

        die(
            "Temperature must be between 20 and 50 °C."
        );

    }


    $temperature =
        number_format(
            $temperatureValue,
            1,
            '.',
            ''
        );

}


/* =========================================================
   PULSE RATE VALIDATION
========================================================= */

if ($pulseRate !== '') {

    if (!ctype_digit($pulseRate)) {

        die(
            "Invalid pulse rate."
        );

    }


    $pulseValue =
        intval($pulseRate);


    if (
        $pulseValue < 20 ||
        $pulseValue > 250
    ) {

        die(
            "Pulse rate must be between 20 and 250 bpm."
        );

    }


    $pulseRate =
        $pulseValue;

}


/* =========================================================
   RESPIRATORY RATE VALIDATION
========================================================= */

if ($respiratoryRate !== '') {

    if (!ctype_digit($respiratoryRate)) {

        die(
            "Invalid respiratory rate."
        );

    }


    $respiratoryValue =
        intval($respiratoryRate);


    if (
        $respiratoryValue < 5 ||
        $respiratoryValue > 100
    ) {

        die(
            "Respiratory rate must be between 5 and 100 breaths/min."
        );

    }


    $respiratoryRate =
        $respiratoryValue;

}


/* =========================================================
   WEIGHT VALIDATION
========================================================= */

if ($weight !== '') {

    if (!is_numeric($weight)) {

        die(
            "Invalid weight."
        );

    }


    $weightValue =
        floatval($weight);


    if (
        $weightValue <= 0 ||
        $weightValue > 500
    ) {

        die(
            "Weight must be greater than 0 and not more than 500 kg."
        );

    }


    $weight =
        number_format(
            $weightValue,
            2,
            '.',
            ''
        );

}


/* =========================================================
   HEIGHT VALIDATION
========================================================= */

if ($height !== '') {

    if (!is_numeric($height)) {

        die(
            "Invalid height."
        );

    }


    $heightValue =
        floatval($height);


    if (
        $heightValue <= 0 ||
        $heightValue > 300
    ) {

        die(
            "Height must be greater than 0 and not more than 300 cm."
        );

    }


    $height =
        number_format(
            $heightValue,
            2,
            '.',
            ''
        );

}


/* =========================================================
   CALCULATE BMI
========================================================= */

$bmi = null;

$bmiStatus = null;


if (
    $weight !== '' &&
    $height !== ''
) {

    $weightForBMI =
        floatval($weight);


    $heightForBMI =
        floatval($height);


    if ($heightForBMI > 0) {

        $heightMeters =
            $heightForBMI / 100;


        $bmiValue =
            $weightForBMI /
            (
                $heightMeters *
                $heightMeters
            );


        $bmiValue =
            round(
                $bmiValue,
                2
            );


        $bmi =
            number_format(
                $bmiValue,
                2,
                '.',
                ''
            );


        if ($bmiValue < 18.5) {

            $bmiStatus =
                "Underweight";

        }

        elseif ($bmiValue < 25) {

            $bmiStatus =
                "Normal Weight";

        }

        elseif ($bmiValue < 30) {

            $bmiStatus =
                "Overweight";

        }

        else {

            $bmiStatus =
                "Obese";

        }

    }

}


/* =========================================================
   FOLLOW-UP DATE VALIDATION
========================================================= */

if ($followUpDate !== '') {

    if (!validDate($followUpDate)) {

        die(
            "Invalid follow-up date."
        );

    }


    if ($followUpDate < $visitDate) {

        die(
            "Follow-up date cannot be earlier than the visit date."
        );

    }

}


/* =========================================================
   GET EXISTING CONSULTATION + PATIENT
========================================================= */

$sql = "

    SELECT

        c.id,

        c.patient_id,

        c.visit_date,

        c.follow_up_date,

        c.follow_up_status,

        p.patient_id AS display_patient_id,

        p.first_name,

        p.middle_name,

        p.last_name,

        p.status AS patient_status

    FROM consultations c

    INNER JOIN patients p

        ON p.id = c.patient_id

    WHERE c.id = ?

    LIMIT 1

";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$stmt->bind_param(
    "i",
    $consultationId
);


$stmt->execute();


$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();

    die(
        "Consultation record not found."
    );

}


$existing =
    $result->fetch_assoc();


$stmt->close();


/* =========================================================
   PATIENT INFORMATION
========================================================= */

$patientId =
    intval(
        $existing['patient_id']
    );


$displayPatientId =
    $existing['display_patient_id'];


$patientName =
    strtoupper(
        trim(
            $existing['last_name'] .
            ", " .
            $existing['first_name'] .
            " " .
            $existing['middle_name']
        )
    );


/* =========================================================
   PATIENT STATUS CHECK
========================================================= */

if (
    isset($existing['patient_status']) &&
    strtoupper(
        $existing['patient_status']
    ) === 'DECEASED'
) {

    die(
        "Consultation cannot be updated because the patient is marked as deceased."
    );

}


/* =========================================================
   DUPLICATE CONSULTATION CHECK
========================================================= */

$duplicateSql = "

    SELECT id

    FROM consultations

    WHERE patient_id = ?

      AND visit_date = ?

      AND chief_complaint = ?

      AND history_illness = ?

      AND assessment = ?

      AND id <> ?

    LIMIT 1

";


$duplicateStmt =
    $conn->prepare(
        $duplicateSql
    );


if (!$duplicateStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$duplicateStmt->bind_param(
    "issssi",
    $patientId,
    $visitDate,
    $chiefComplaint,
    $historyIllness,
    $assessment,
    $consultationId
);


$duplicateStmt->execute();


$duplicateResult =
    $duplicateStmt->get_result();


if ($duplicateResult->num_rows > 0) {

    $duplicateStmt->close();

    die(
        "A similar consultation already exists for this patient on " .
        htmlspecialchars($visitDate) .
        "."
    );

}


$duplicateStmt->close();


/* =========================================================
   FOLLOW-UP STATUS
========================================================= */

$oldFollowUpDate =
    isset(
        $existing['follow_up_date']
    )
    ? $existing['follow_up_date']
    : null;


$oldFollowUpStatus =
    isset(
        $existing['follow_up_status']
    )
    ? $existing['follow_up_status']
    : "PENDING";


if ($oldFollowUpDate != $followUpDate) {

    if ($followUpDate !== '') {

        $followUpStatus =
            "PENDING";

    }

    else {

        $followUpStatus =
            "NONE";

    }

}

else {

    $followUpStatus =
        $oldFollowUpStatus;


    if ($followUpStatus === '') {

        $followUpStatus =
            ($followUpDate !== '')
            ? "PENDING"
            : "NONE";

    }

}


/* =========================================================
   PREPARE NULL VALUES
========================================================= */

$dbTemperature =
    ($temperature === '')
    ? null
    : $temperature;


$dbPulseRate =
    ($pulseRate === '')
    ? null
    : $pulseRate;


$dbRespiratoryRate =
    ($respiratoryRate === '')
    ? null
    : $respiratoryRate;


$dbWeight =
    ($weight === '')
    ? null
    : $weight;


$dbHeight =
    ($height === '')
    ? null
    : $height;


$dbBMI =
    ($bmi === null || $bmi === '')
    ? null
    : $bmi;


$dbBMIStatus =
    ($bmiStatus === null || $bmiStatus === '')
    ? null
    : $bmiStatus;


$dbBPStatus =
    ($bpStatus === null || $bpStatus === '')
    ? null
    : $bpStatus;


$dbFollowUpDate =
    ($followUpDate === '')
    ? null
    : $followUpDate;


/* =========================================================
   UPDATE CONSULTATION
========================================================= */

$updateSql = "

    UPDATE consultations

    SET

        visit_date = ?,

        chief_complaint = ?,

        history_illness = ?,

        blood_pressure = ?,

        bp_status = ?,

        temperature = ?,

        pulse_rate = ?,

        respiratory_rate = ?,

        weight = ?,

        height = ?,

        bmi = ?,

        bmi_status = ?,

        assessment = ?,

        medication = ?,

        management = ?,

        follow_up_date = ?,

        remarks = ?,

        follow_up_status = ?

    WHERE id = ?

    LIMIT 1

";


$updateStmt =
    $conn->prepare(
        $updateSql
    );


if (!$updateStmt) {

    die(
        "Unable to prepare update: " .
        $conn->error
    );

}


/*
    18 fields + consultation ID
*/

$updateStmt->bind_param(
    "ssssssssssssssssssi",
    $visitDate,
    $chiefComplaint,
    $historyIllness,
    $bloodPressure,
    $dbBPStatus,
    $dbTemperature,
    $dbPulseRate,
    $dbRespiratoryRate,
    $dbWeight,
    $dbHeight,
    $dbBMI,
    $dbBMIStatus,
    $assessment,
    $medication,
    $management,
    $dbFollowUpDate,
    $remarks,
    $followUpStatus,
    $consultationId
);


/* =========================================================
   EXECUTE UPDATE
========================================================= */

if (!$updateStmt->execute()) {

    $error =
        $updateStmt->error;


    $updateStmt->close();


    die(
        "Unable to update consultation. " .
        htmlspecialchars($error)
    );

}


$updateStmt->close();


/* =========================================================
   CLOSE DATABASE
========================================================= */

$conn->close();


/* =========================================================
   LOADING PAGE
========================================================= */

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
        Updating Consultation
    </title>


    <style>

        * {

            box-sizing: border-box;

        }


        body {

            margin: 0;

            padding: 0;

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f7fb;

            color: #1f2937;

            display: flex;

            align-items: center;

            justify-content: center;

        }


        .loading-card {

            width: 90%;

            max-width: 430px;

            background: #ffffff;

            border-radius: 12px;

            padding: 40px 30px;

            text-align: center;

            box-shadow:
                0 5px 25px
                rgba(0, 0, 0, 0.08);

        }


        .spinner {

            width: 55px;

            height: 55px;

            margin: 0 auto 20px;

            border: 5px solid #e5e7eb;

            border-top: 5px solid #0d6efd;

            border-radius: 50%;

            animation:
                spin 0.8s linear infinite;

        }


        @keyframes spin {

            from {

                transform:
                    rotate(0deg);

            }

            to {

                transform:
                    rotate(360deg);

            }

        }


        .loading-card h2 {

            margin:
                0 0 10px;

            font-size: 22px;

            color: #1f2937;

        }


        .loading-card p {

            margin: 0;

            color: #6b7280;

            font-size: 15px;

            line-height: 1.5;

        }


        .patient-name {

            margin-top: 15px;

            font-weight: 700;

            color: #0d6efd;

        }

    </style>


    <script>

        setTimeout(
            function () {

                window.location.href =
                    "consultation_view.php?id=<?php echo (int) $consultationId; ?>";

            },
            800
        );

    </script>

</head>


<body>


    <div class="loading-card">


        <div class="spinner"></div>


        <h2>

            Saving Consultation

        </h2>


        <p>

            Please wait while the updated consultation
            record is being loaded.

        </p>


        <div class="patient-name">

            <?php

            echo htmlspecialchars(
                $patientName
            );

            ?>

        </div>


    </div>


</body>

</html>