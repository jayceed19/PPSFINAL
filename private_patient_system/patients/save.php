<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


// =========================================================
// KUNIN ANG DATA MULA SA FORM
// =========================================================

$last_name = isset($_POST['last_name'])
    ? trim($_POST['last_name'])
    : '';

$first_name = isset($_POST['first_name'])
    ? trim($_POST['first_name'])
    : '';

$middle_name = isset($_POST['middle_name'])
    ? trim($_POST['middle_name'])
    : '';

$birthdate = isset($_POST['birthdate'])
    ? $_POST['birthdate']
    : '';

$sex = isset($_POST['sex'])
    ? $_POST['sex']
    : '';

$civil_status = isset($_POST['civil_status'])
    ? trim($_POST['civil_status'])
    : '';

$philhealth_no = isset($_POST['philhealth_no'])
    ? trim($_POST['philhealth_no'])
    : '';

$philhealth_yakap_status = isset($_POST['philhealth_yakap_status'])
    ? trim($_POST['philhealth_yakap_status'])
    : '';

$email = isset($_POST['email'])
    ? trim($_POST['email'])
    : '';

$address = isset($_POST['address'])
    ? trim($_POST['address'])
    : '';

$contact_no = isset($_POST['contact_no'])
    ? trim($_POST['contact_no'])
    : '';

$emergency_contact = isset($_POST['emergency_contact'])
    ? trim($_POST['emergency_contact'])
    : '';

$emergency_contact_no = isset($_POST['emergency_contact_no'])
    ? trim($_POST['emergency_contact_no'])
    : '';

$date_registered = isset($_POST['date_registered'])
    ? $_POST['date_registered']
    : date('Y-m-d');


// =========================================================
// CHECK REQUIRED FIELDS
// =========================================================

if (
    $last_name == '' ||
    $first_name == '' ||
    $birthdate == '' ||
    $sex == '' ||
    $civil_status == ''
) {
    die("Please complete all required fields.");
}


// =========================================================
// VALIDATE CIVIL STATUS
// =========================================================

$allowedCivilStatus = array(
    'Single',
    'Married',
    'Widowed',
    'Separated',
    'Annulled',
    'Other'
);

if (!in_array($civil_status, $allowedCivilStatus)) {

    die("Invalid civil status.");

}


// =========================================================
// CLEAN PHILHEALTH NUMBER
// =========================================================

$philhealth_no = preg_replace(
    '/[^0-9]/',
    '',
    $philhealth_no
);


// =========================================================
// LIMIT PHILHEALTH NUMBER
// =========================================================

$philhealth_no = substr(
    $philhealth_no,
    0,
    30
);


// =========================================================
// HANDLE YAKAP / CIC STATUS
// =========================================================
// Only two choices:
// REGISTERED
// NOT YET REGISTERED
//
// If there is NO PhilHealth Number,
// YAKAP status will be NULL.
// =========================================================

if ($philhealth_no == '') {

    $philhealth_yakap_status = null;

} else {

    if (
        $philhealth_yakap_status != 'REGISTERED' &&
        $philhealth_yakap_status != 'NOT YET REGISTERED'
    ) {

        die(
            "Please select the PhilHealth / YAKAP registration status."
        );

    }

}


// =========================================================
// VALIDATE EMAIL
// =========================================================
// Email is OPTIONAL.
// If entered, validate the format.
// =========================================================

if ($email != '') {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        die("Please enter a valid email address.");

    }

    $email = substr(
        $email,
        0,
        150
    );

} else {

    $email = null;

}


// =========================================================
// CLEAN CONTACT NUMBERS
// =========================================================

$contact_no = preg_replace(
    '/[^0-9]/',
    '',
    $contact_no
);

$emergency_contact_no = preg_replace(
    '/[^0-9]/',
    '',
    $emergency_contact_no
);


// =========================================================
// LIMIT CONTACT NUMBERS
// =========================================================

$contact_no = substr(
    $contact_no,
    0,
    11
);

$emergency_contact_no = substr(
    $emergency_contact_no,
    0,
    11
);


// =========================================================
// AUTOMATIC PATIENT ID
// =========================================================
// IMPORTANT:
// HINDI NA GAGAMIT NG MAX(id)
//
// Gagamit tayo ng MAX(patient_id)
// para hindi maapektuhan ng internal database ID.
//
// Example:
// 0001
// 0002
// 0003
// Next = 0004
// =========================================================

$result = $conn->query("
    SELECT MAX(
        CAST(patient_id AS UNSIGNED)
    ) AS last_patient_id
    FROM patients
");

if (!$result) {

    die(
        "Error checking patient ID: " .
        $conn->error
    );

}

$row = $result->fetch_assoc();

$last_patient_id = $row['last_patient_id'];


// =========================================================
// SET NEXT PATIENT ID
// =========================================================

if (
    $last_patient_id === null ||
    $last_patient_id === ''
) {

    $next_id = 1;

} else {

    $next_id = intval($last_patient_id) + 1;

}


// =========================================================
// FORMAT PATIENT ID
// =========================================================
// 1    = 0001
// 2    = 0002
// 10   = 0010
// 100  = 0100
// 1000 = 1000
// =========================================================

$patient_id = str_pad(
    $next_id,
    4,
    '0',
    STR_PAD_LEFT
);


// =========================================================
// CHECK IF PATIENT ID ALREADY EXISTS
// =========================================================

$checkStmt = $conn->prepare("
    SELECT id
    FROM patients
    WHERE patient_id = ?
    LIMIT 1
");

if (!$checkStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}

$checkStmt->bind_param(
    "s",
    $patient_id
);

$checkStmt->execute();

$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {

    $checkStmt->close();

    die(
        "Patient ID " .
        htmlspecialchars($patient_id) .
        " already exists."
    );

}

$checkStmt->close();


// =========================================================
// SAVE PATIENT
// =========================================================

$sql = "
    INSERT INTO patients (
        patient_id,
        last_name,
        first_name,
        middle_name,
        birthdate,
        sex,
        civil_status,
        philhealth_no,
        philhealth_yakap_status,
        address,
        contact_no,
        email,
        emergency_contact,
        emergency_contact_no,
        date_registered
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


// =========================================================
// BIND PARAMETERS
// =========================================================

$stmt->bind_param(
    "sssssssssssssss",
    $patient_id,
    $last_name,
    $first_name,
    $middle_name,
    $birthdate,
    $sex,
    $civil_status,
    $philhealth_no,
    $philhealth_yakap_status,
    $address,
    $contact_no,
    $email,
    $emergency_contact,
    $emergency_contact_no,
    $date_registered
);


// =========================================================
// CHECK IF SAVED
// =========================================================

if ($stmt->execute()) {


    // =====================================================
    // ACTIVITY LOG
    // =====================================================

    $patientName = trim(
        $first_name . ' ' .
        $middle_name . ' ' .
        $last_name
    );

    $activityDescription =
        "Added patient: " .
        $patient_id .
        " - " .
        $patientName;

    logActivity(
        $conn,
        "ADD_PATIENT",
        $activityDescription
    );

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <link
        rel="icon"
        type="image/png"
        href="../asset/images/DCMDLOGO.png?v=1"
    >

    <title>
        Patient Saved
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                sans-serif;

            background: #f4f6f9;

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;

        }


        .box {

            background: white;

            width: 450px;

            padding: 40px;

            border-radius: 12px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.10);

        }


        h2 {

            color: #1f4e78;

            margin-bottom: 10px;

        }


        .success {

            color: #28a745;

            font-weight: bold;

        }


        .label {

            margin-top: 25px;

            color: #777;

        }


        .patient-id {

            font-size: 38px;

            font-weight: bold;

            color: #1f4e78;

            margin: 10px 0 20px;

        }


        .patient-name {

            font-size: 18px;

            font-weight: bold;

            margin-bottom: 25px;

        }


        a {

            display: inline-block;

            padding: 12px 18px;

            background: #1f4e78;

            color: white;

            text-decoration: none;

            border-radius: 6px;

            margin: 5px;

        }


        a:hover {

            background: #173a5c;

        }

    </style>

</head>


<body>


    <div class="box">


        <h2>
            Patient Successfully Registered!
        </h2>


        <p class="success">
            Patient record has been saved.
        </p>


        <p class="label">
            Patient ID
        </p>


        <div class="patient-id">

            <?php

            echo htmlspecialchars(
                $patient_id
            );

            ?>

        </div>


        <div class="patient-name">

            <?php

            echo htmlspecialchars(
                $first_name . ' ' .
                $middle_name . ' ' .
                $last_name
            );

            ?>

        </div>


        <a href="add.php">
            Add Another Patient
        </a>


        <a href="index.php">
            View Patients
        </a>


    </div>


</body>

</html>


<?php

} else {

    echo
        "Error saving patient: " .
        $stmt->error;

}


$stmt->close();

$conn->close();

?>