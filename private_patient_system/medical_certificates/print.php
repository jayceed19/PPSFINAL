<?php

require_once "../config/database.php";
require_once "../config/auth.php";


/* =========================================================
   GET CERTIFICATE ID
========================================================= */

$certificateId = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;


if ($certificateId <= 0) {

    die("Invalid medical certificate ID.");

}


/* =========================================================
   GET MEDICAL CERTIFICATE + PATIENT
========================================================= */

$stmt = $conn->prepare("
    SELECT
        mc.id,
        mc.patient_id,
        mc.certificate_date,
        mc.diagnosis,

        p.patient_id AS patient_display_id,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.birthdate,
        p.sex,
        p.address

    FROM medical_certificates mc

    INNER JOIN patients p
        ON mc.patient_id = p.id

    WHERE mc.id = ?

    LIMIT 1
");


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$stmt->bind_param(
    "i",
    $certificateId
);


if (!$stmt->execute()) {

    $stmt->close();

    die(
        "Unable to retrieve medical certificate."
    );

}


$result = $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();

    die(
        "Medical certificate not found."
    );

}


$data = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   PATIENT DATABASE ID
========================================================= */

$patientId = isset($data["patient_id"])
    ? (int)$data["patient_id"]
    : 0;


if ($patientId <= 0) {

    die("Invalid patient ID.");

}


/* =========================================================
   PATIENT NAME
========================================================= */

$fullName = trim(

    $data["first_name"] . " " .
    $data["middle_name"] . " " .
    $data["last_name"]

);


$fullName = preg_replace(
    '/\s+/',
    ' ',
    $fullName
);


$fullName = strtoupper(
    $fullName
);


/* =========================================================
   AGE
========================================================= */

$age = "";


if (!empty($data["birthdate"])) {

    try {

        $birthDate = new DateTime(
            $data["birthdate"]
        );

        $today = new DateTime();

        $age = $today->diff(
            $birthDate
        )->y;

    } catch (Exception $e) {

        $age = "";

    }

}


/* =========================================================
   SEX
========================================================= */

$sex = "";


if (!empty($data["sex"])) {

    $sex = strtoupper(
        $data["sex"]
    );

}


/* =========================================================
   ADDRESS
========================================================= */

$address = "";


if (!empty($data["address"])) {

    $address = trim(
        $data["address"]
    );

}


/* =========================================================
   CERTIFICATE DATE
========================================================= */

$certificateDate = "";


if (!empty($data["certificate_date"])) {

    $certificateDate = date(
        "F d, Y",
        strtotime(
            $data["certificate_date"]
        )
    );

}


/* =========================================================
   DIAGNOSIS
========================================================= */

$diagnosis = "";


if (isset($data["diagnosis"])) {

    $diagnosis = trim(
        $data["diagnosis"]
    );

}


/* =========================================================
   ESCAPE FUNCTION
========================================================= */

function e($value)
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

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
        Medical Certificate -
        <?php echo e($fullName); ?>
    </title>


    <link
        rel="icon"
        type="image/png"
        href="../asset/images/1.png"
    >


    <style>

        /* =====================================================
           RESET
        ====================================================== */

        * {
            box-sizing: border-box;
        }


        html,
        body {

            margin: 0;

            padding: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #000;

            background: #e5e5e5;

        }


        /* =====================================================
           PRINT CONTROLS
        ====================================================== */

        .print-controls {

            width: 210mm;

            margin: 12px auto;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .back-btn,
        .print-btn {

            border: none;

            border-radius: 4px;

            padding: 9px 16px;

            font-size: 13px;

            text-decoration: none;

            cursor: pointer;

        }


        .back-btn {

            background: #6c757d;

            color: #fff;

        }


        .print-btn {

            background: #1f4e78;

            color: #fff;

        }


        /* =====================================================
           A4 PAPER
        ====================================================== */

        .paper {

            width: 210mm;

            height: 297mm;

            margin: 0 auto;

            background: #fff;

            padding:
                8mm
                7.5mm
                8mm;

            position: relative;

            overflow: hidden;

            box-shadow:
                0 2px 12px rgba(
                    0,
                    0,
                    0,
                    0.15
                );

        }


        /* =====================================================
           HEADER
        ====================================================== */

        .header {

            width: 100%;

            height: 39mm;

            position: relative;

        }


        /* =====================================================
           LEFT LOGO
        ====================================================== */

        .left-logo {

            position: absolute;

            left: 2mm;

            top: 0;

            width: 29mm;

            height: 29mm;

            display: flex;

            align-items: center;

            justify-content: center;

        }


        .left-logo img {

            max-width: 29mm;

            max-height: 29mm;

            object-fit: contain;

        }


        /* =====================================================
           RIGHT LOGO
        ====================================================== */

        .right-logo {

            position: absolute;

            right: 2mm;

            top: 0;

            width: 29mm;

            height: 29mm;

            display: flex;

            align-items: center;

            justify-content: center;

        }


        .right-logo img {

            max-width: 29mm;

            max-height: 29mm;

            object-fit: contain;

        }


        /* =====================================================
           CENTER HEADER
        ====================================================== */

        .header-center {

            width: 145mm;

            margin: 0 auto;

            text-align: center;

        }


        .doctor-name {

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 22px;

            font-weight: bold;

            line-height: 1.05;

            margin-bottom: 2px;

        }


        .doctor-line {

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 10px;

            font-weight: bold;

            line-height: 1.25;

        }


        .clinic-line {

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 9.5px;

            font-weight: bold;

            line-height: 1.2;

            margin-top: 4px;

        }


        /* =====================================================
           HEADER LINES
        ====================================================== */

        .header-line {

            width: 100%;

            border-top: 2px solid #000;

            margin-top: 0;

        }


        .header-line-thick {

            width: 100%;

            border-top: 6px solid #000;

            margin-top: 1px;

        }


        /* =====================================================
           DATE
        ====================================================== */

        .date-area {

            margin-top: 6mm;

            font-size: 13px;

            line-height: 1;

        }


        .date-underline {

            display: inline-block;

            width: 45mm;

            height: 18px;

            border-bottom: 1px solid #000;

            vertical-align: bottom;

            padding-left: 2px;

        }


        /* =====================================================
           TITLE
        ====================================================== */

        .title {

            text-align: center;

            font-size: 23px;

            font-weight: bold;

            margin-top: 13mm;

            margin-bottom: 15mm;

        }


        /* =====================================================
           BODY
        ====================================================== */

        .body {

            font-size: 14px;

            line-height: 1.42;

        }


        .to-whom {

            margin-bottom: 5mm;

        }


        /* =====================================================
           PATIENT INFORMATION
        ====================================================== */

        .info-line {

            margin-bottom: 1px;

        }


        .underline {

            display: inline-block;

            border-bottom: 1px solid #000;

            height: 20px;

            vertical-align: bottom;

            padding:
                0
                3px;

            white-space: nowrap;

            overflow: hidden;

        }


        /* NAME */

        .name-underline {

            width: 138mm;

        }


        /* AGE */

        .age-underline {

            width: 15mm;

            text-align: center;

        }


        /* SEX */

        .sex-underline {

            width: 15mm;

            text-align: center;

        }


        /* ADDRESS */

        .address-underline {

            width: 132mm;

        }


        /* DATE SEEN */

        .seen-date-underline {

            width: 68mm;

        }


        /* REST DAYS */

        .days-underline {

            width: 28mm;

        }


        /* =====================================================
           DIAGNOSIS
        ====================================================== */

        .diagnosis-section {

            margin-top: 6mm;

        }


        .diagnosis-title {

            font-weight: normal;

            margin-bottom: 2mm;

        }


        .diagnosis-content {

            min-height: 10mm;

            border-bottom: 1px solid #000;

            padding:
                2mm
                1mm
                1mm
                0;

            line-height: 1.5;

            white-space: normal;

        }


        .diagnosis-empty-line {

            width: 100%;

            height: 8mm;

            border-bottom: 1px solid #000;

        }


        /* =====================================================
           PURPOSE
        ====================================================== */

        .purpose-section {

            margin-top: 7mm;

        }


        .purpose-title {

            font-weight: bold;

            margin-bottom: 2mm;

        }


        .purpose-row {

            height: 6.3mm;

            line-height: 6.3mm;

        }


        .checkbox {

            display: inline-block;

            width: 12px;

            height: 12px;

            border: 1px solid #000;

            margin-right: 5px;

            vertical-align: -1px;

        }


        .other-line {

            display: inline-block;

            width: 110mm;

            height: 20px;

            border-bottom: 1px solid #000;

            vertical-align: bottom;

        }


        /* =====================================================
           REQUEST
        ====================================================== */

        .request-section {

            margin-top: 8mm;

            white-space: nowrap;

        }


        .request-underline {

            display: inline-block;

            width: 105mm;

            height: 20px;

            border-bottom: 1px solid #000;

            vertical-align: bottom;

            padding-left: 3px;

            overflow: hidden;

        }


        /* =====================================================
           THANK YOU
        ====================================================== */

        .thank-you {

            margin-top: 8mm;

        }


        /* =====================================================
           DOCTOR INFORMATION
        ====================================================== */

        .doctor-signature {

            position: absolute;

            right: 12mm;

            bottom: 14mm;

            width: 58mm;

            font-size: 13px;

            line-height: 1.3;

        }


        .doctor-signature-name {

            font-size: 15px;

            font-weight: bold;

            text-decoration: underline;

            margin-bottom: 1mm;

        }


        /* =====================================================
           SCREEN RESPONSIVE
        ====================================================== */

        @media screen and (max-width: 850px) {

            body {

                padding: 10px;

            }


            .print-controls {

                width: 100%;

            }


            .paper {

                width: 100%;

                height: auto;

                min-height: 297mm;

                padding: 25px;

            }


            .header {

                height: auto;

                min-height: 180px;

            }


            .header-center {

                width: 100%;

                padding:
                    0
                    40px;

            }


            .left-logo,
            .right-logo {

                width: 42px;

                height: 42px;

            }


            .left-logo img,
            .right-logo img {

                max-width: 40px;

                max-height: 40px;

            }


            .name-underline,
            .address-underline,
            .seen-date-underline,
            .request-underline {

                width: 60%;

            }


            .doctor-signature {

                position: relative;

                right: auto;

                bottom: auto;

                margin-top: 80px;

                margin-left: auto;

            }

        }


        /* =====================================================
           PRINT
        ====================================================== */

        @media print {

            @page {

                size: A4 portrait;

                margin: 0;

            }


            html,
            body {

                width: 210mm;

                height: 297mm;

                margin: 0;

                padding: 0;

                background: #fff;

            }


            .print-controls {

                display: none !important;

            }


            .paper {

                width: 210mm;

                height: 297mm;

                margin: 0;

                padding:
                    8mm
                    7.5mm
                    8mm;

                box-shadow: none;

                overflow: hidden;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     PRINT CONTROLS
========================================================= -->

<div class="print-controls">


    <a
        href="index.php?id=<?php echo $patientId; ?>"
        class="back-btn"
    >

        ← Back

    </a>


    <button
        type="button"
        class="print-btn"
        onclick="window.print();"
    >

        Print Medical Certificate

    </button>


</div>



<!-- =========================================================
     A4 PAPER
========================================================= -->

<div class="paper">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="header">


        <!-- LEFT LOGO -->

        <div class="left-logo">

            <img
                src="../asset/images/2.png"
                alt="Clinic Logo"
            >

        </div>


        <!-- CENTER HEADER -->

        <div class="header-center">


            <div class="doctor-name">

                DARWIN G. CRUZ, MD

            </div>


            <div class="doctor-line">

                ALUMNI, INSTITUTE FOR STUDIES ON DIABETES FOUNDATION

            </div>


            <div class="doctor-line">

                MEMBER, PHILIPPINE ASSOCIATION OF DIABETES EDUCATORS

            </div>


            <div class="clinic-line">

                Rosario Medical Center of Guagua Inc. Annex 1 Bldg. Room 124 Sn.

            </div>


            <div class="clinic-line">

                Roque, Guagua, Pampanga | 09171453720

            </div>


            <div class="clinic-line">

                Wed and Saturday: 10:00 AM - 2:00 PM

            </div>


        </div>


        <!-- RIGHT LOGO -->

        <div class="right-logo">

            <img
                src="../asset/images/1.png"
                alt="Institute Logo"
            >

        </div>


    </div>



    <!-- =====================================================
         HEADER DOUBLE LINE
    ====================================================== -->

    <div class="header-line"></div>

    <div class="header-line-thick"></div>



    <!-- =====================================================
         DATE
    ====================================================== -->

    <div class="date-area">

        Date:

        <span class="date-underline">

            <?php echo e($certificateDate); ?>

        </span>

    </div>



    <!-- =====================================================
         TITLE
    ====================================================== -->

    <div class="title">

        MEDICAL CERTIFICATE

    </div>



    <!-- =====================================================
         BODY
    ====================================================== -->

    <div class="body">


        <!-- =================================================
             TO WHOM
        ================================================== -->

        <div class="to-whom">

            To Whom it may concern,

        </div>



        <!-- =================================================
             PATIENT NAME
        ================================================== -->

        <div class="info-line">

            This letter is to inform you that

            <span class="underline name-underline">

                <?php echo e($fullName); ?>

            </span>

        </div>



        <!-- =================================================
             AGE / SEX / ADDRESS
        ================================================== -->

        <div class="info-line">

            Age:

            <span class="underline age-underline">

                <?php echo e($age); ?>

            </span>


            Sex:

            <span class="underline sex-underline">

                <?php echo e($sex); ?>

            </span>


            From:

            <span class="underline address-underline">

                <?php echo e($address); ?>

            </span>

        </div>



        <!-- =================================================
             DATE SEEN
        ================================================== -->

        <div class="info-line">

            was seen in my clinic on the following dates,

            <span class="underline seen-date-underline">

                <?php echo e($certificateDate); ?>

            </span>

            which may require rest for a

        </div>


        <div class="info-line">

            period of

            <span class="underline days-underline"></span>

            days.

        </div>



        <!-- =================================================
             DIAGNOSIS
        ================================================== -->

        <div class="diagnosis-section">


            <div class="diagnosis-title">

                Due to the following conditions:

            </div>


            <?php if ($diagnosis !== "") { ?>


                <div class="diagnosis-content">

                    <?php

                    echo nl2br(
                        e($diagnosis)
                    );

                    ?>

                </div>


                <div class="diagnosis-empty-line"></div>


            <?php } else { ?>


                <div class="diagnosis-empty-line"></div>

                <div class="diagnosis-empty-line"></div>

                <div class="diagnosis-empty-line"></div>


            <?php } ?>


        </div>



        <!-- =================================================
             PURPOSE
        ================================================== -->

        <div class="purpose-section">


            <div class="purpose-title">

                Purpose of Medical Certificate:

            </div>


            <div class="purpose-row">

                <span class="checkbox"></span>

                Sick Leave

            </div>


            <div class="purpose-row">

                <span class="checkbox"></span>

                Fit to Work

            </div>


            <div class="purpose-row">

                <span class="checkbox"></span>

                Medical Clearance

            </div>


            <div class="purpose-row">

                <span class="checkbox"></span>

                School Requirement

            </div>


            <div class="purpose-row">

                <span class="checkbox"></span>

                Pre-Employment

            </div>


            <div class="purpose-row">

                <span class="checkbox"></span>

                Others:

                <span class="other-line"></span>

            </div>


        </div>



        <!-- =================================================
             REQUEST
        ================================================== -->

        <div class="request-section">

            This letter was issue upon the request of

            <span class="request-underline">

                <?php echo e($fullName); ?>

            </span>

        </div>



        <!-- =================================================
             THANK YOU
        ================================================== -->

        <div class="thank-you">

            Thank you very much!

        </div>


    </div>



    <!-- =====================================================
         DOCTOR INFORMATION
    ====================================================== -->

    <div class="doctor-signature">


        <div class="doctor-signature-name">

            Darwin G. Cruz M.D.

        </div>


        <div>

            Lic. No: 119102

        </div>


        <div>

            PTR:

        </div>


    </div>


</div>


</body>

</html>