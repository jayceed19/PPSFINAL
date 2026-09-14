<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


// =========================================================
// GET FORM DATA
// =========================================================

$id = isset($_POST['id'])
    ? intval($_POST['id'])
    : 0;

$first_name = isset($_POST['first_name'])
    ? trim($_POST['first_name'])
    : '';

$middle_name = isset($_POST['middle_name'])
    ? trim($_POST['middle_name'])
    : '';

$last_name = isset($_POST['last_name'])
    ? trim($_POST['last_name'])
    : '';

$birthdate = isset($_POST['birthdate'])
    ? trim($_POST['birthdate'])
    : '';

$sex = isset($_POST['sex'])
    ? trim($_POST['sex'])
    : '';

$civil_status = isset($_POST['civil_status'])
    ? trim($_POST['civil_status'])
    : '';

$status = isset($_POST['status'])
    ? trim($_POST['status'])
    : '';

$philhealth_no = isset($_POST['philhealth_no'])
    ? trim($_POST['philhealth_no'])
    : '';

$philhealth_yakap_status = isset($_POST['philhealth_yakap_status'])
    ? trim($_POST['philhealth_yakap_status'])
    : '';

$contact_no = isset($_POST['contact_no'])
    ? trim($_POST['contact_no'])
    : '';

$email = isset($_POST['email'])
    ? trim($_POST['email'])
    : '';

$emergency_contact = isset($_POST['emergency_contact'])
    ? trim($_POST['emergency_contact'])
    : '';

$emergency_contact_no = isset($_POST['emergency_contact_no'])
    ? trim($_POST['emergency_contact_no'])
    : '';

$address = isset($_POST['address'])
    ? trim($_POST['address'])
    : '';


// =========================================================
// BASIC VALIDATION
// =========================================================

if ($id <= 0) {
    die("Invalid patient ID.");
}

if ($first_name == '' || $last_name == '') {
    die("First name and last name are required.");
}

if ($birthdate == '') {
    die("Birthdate is required.");
}

if ($sex == '') {
    die("Sex is required.");
}

if ($civil_status == '') {
    die("Civil status is required.");
}

if ($status == '') {
    die("Patient status is required.");
}


// =========================================================
// FORMAL TEXT FORMAT
// =========================================================

$first_name = preg_replace(
    '/\s+/',
    ' ',
    $first_name
);

$first_name = strtoupper(
    trim($first_name)
);


$middle_name = preg_replace(
    '/\s+/',
    ' ',
    $middle_name
);

$middle_name = strtoupper(
    trim($middle_name)
);


$last_name = preg_replace(
    '/\s+/',
    ' ',
    $last_name
);

$last_name = strtoupper(
    trim($last_name)
);


$emergency_contact = preg_replace(
    '/\s+/',
    ' ',
    $emergency_contact
);

$emergency_contact = strtoupper(
    trim($emergency_contact)
);


$address = preg_replace(
    '/\s+/',
    ' ',
    $address
);

$address = strtoupper(
    trim($address)
);


// =========================================================
// SEX VALIDATION
// =========================================================

if (
    $sex != 'Male' &&
    $sex != 'Female'
) {
    die("Invalid sex.");
}


// =========================================================
// CIVIL STATUS VALIDATION
// =========================================================

$allowedCivilStatus = array(
    'Single',
    'Married',
    'Widowed',
    'Separated',
    'Annulled',
    'Other'
);

if (!in_array(
    $civil_status,
    $allowedCivilStatus,
    true
)) {
    die("Invalid civil status.");
}


// =========================================================
// PATIENT STATUS VALIDATION
// =========================================================

if (
    $status != 'Active' &&
    $status != 'Inactive' &&
    $status != 'Deceased'
) {
    die("Invalid patient status.");
}


// =========================================================
// PHONE NUMBER FORMAT
// =========================================================

$contact_no = preg_replace(
    '/[^0-9]/',
    '',
    $contact_no
);

$contact_no = substr(
    $contact_no,
    0,
    11
);


$emergency_contact_no = preg_replace(
    '/[^0-9]/',
    '',
    $emergency_contact_no
);

$emergency_contact_no = substr(
    $emergency_contact_no,
    0,
    11
);


// =========================================================
// PHILHEALTH NUMBER FORMAT
// =========================================================
// Optional.
// Numbers only.
// Maximum 12 digits.
// Blank is allowed.
// =========================================================

$philhealth_no = preg_replace(
    '/[^0-9]/',
    '',
    $philhealth_no
);

$philhealth_no = substr(
    $philhealth_no,
    0,
    12
);


// =========================================================
// PHILHEALTH / YAKAP STATUS
// =========================================================
// If PhilHealth number is blank,
// status will be NULL.
// If PhilHealth number exists,
// status must be REGISTERED or NOT YET REGISTERED.
// =========================================================

if ($philhealth_no == '') {

    $philhealth_yakap_status = null;

} else {

    if (
        $philhealth_yakap_status != 'REGISTERED' &&
        $philhealth_yakap_status != 'NOT YET REGISTERED'
    ) {
        die(
            "PhilHealth/YAKAP registration status is required."
        );
    }
}


// =========================================================
// EMAIL VALIDATION
// =========================================================
// Optional field.
// =========================================================

if ($email != '') {

    if (!filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )) {
        die("Invalid email address.");
    }

    if (strlen($email) > 150) {
        die("Email address is too long.");
    }
}


// =========================================================
// BIRTHDATE VALIDATION
// =========================================================

$date_check = DateTime::createFromFormat(
    'Y-m-d',
    $birthdate
);

if (
    !$date_check ||
    $date_check->format('Y-m-d') !== $birthdate
) {
    die("Invalid birthdate.");
}


// =========================================================
// PREVENT FUTURE BIRTHDATE
// =========================================================

$today = date('Y-m-d');

if ($birthdate > $today) {
    die("Birthdate cannot be in the future.");
}


// =========================================================
// ADDRESS
// =========================================================

if ($address == '') {
    $address = '-';
}


// =========================================================
// CHECK PATIENT
// ALSO GET ACTUAL PATIENT ID
// =========================================================

$check_sql = "
    SELECT
        id,
        patient_id
    FROM patients
    WHERE id = ?
";

$check_stmt = $conn->prepare(
    $check_sql
);

if (!$check_stmt) {
    die(
        "Database error: " .
        $conn->error
    );
}


$check_stmt->bind_param(
    "i",
    $id
);

$check_stmt->execute();

$check_stmt->store_result();


if ($check_stmt->num_rows == 0) {

    $check_stmt->close();

    die("Patient not found.");
}


// =========================================================
// GET PATIENT ID
// =========================================================

$check_stmt->bind_result(
    $patient_db_id,
    $patient_id
);

$check_stmt->fetch();

$check_stmt->close();


// =========================================================
// UPDATE PATIENT
// =========================================================

$sql = "
    UPDATE patients SET

        first_name = ?,
        middle_name = ?,
        last_name = ?,
        birthdate = ?,
        sex = ?,
        civil_status = ?,
        philhealth_no = ?,
        philhealth_yakap_status = ?,
        status = ?,
        contact_no = ?,
        email = ?,
        emergency_contact = ?,
        emergency_contact_no = ?,
        address = ?

    WHERE id = ?
";


$stmt = $conn->prepare(
    $sql
);

if (!$stmt) {
    die(
        "Database error: " .
        $conn->error
    );
}


// =========================================================
// BIND VALUES
// =========================================================

$stmt->bind_param(
    "ssssssssssssssi",
    $first_name,
    $middle_name,
    $last_name,
    $birthdate,
    $sex,
    $civil_status,
    $philhealth_no,
    $philhealth_yakap_status,
    $status,
    $contact_no,
    $email,
    $emergency_contact,
    $emergency_contact_no,
    $address,
    $id
);


// =========================================================
// EXECUTE UPDATE
// =========================================================

if (!$stmt->execute()) {

    die(
        "Error updating patient: " .
        $stmt->error
    );
}


// =========================================================
// ACTIVITY LOG
// =========================================================

$patientName = trim(
    $first_name .
    ' ' .
    $middle_name .
    ' ' .
    $last_name
);

$activityDescription =
    "Updated patient: " .
    $patient_id .
    " - " .
    $patientName;


logActivity(
    $conn,
    "EDIT_PATIENT",
    $activityDescription
);


// =========================================================
// CLOSE STATEMENT
// =========================================================

$stmt->close();


// =========================================================
// CLOSE DATABASE
// =========================================================

$conn->close();


// =========================================================
// RETURN TO PATIENT PROFILE
// =========================================================

header(
    "Location: view.php?id=" .
    $id
);

exit;

?>