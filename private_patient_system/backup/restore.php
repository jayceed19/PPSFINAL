<?php

require_once "../config/auth.php";
require_once "../config/database.php";
require_once "../config/activity_log.php";


/*
|--------------------------------------------------------------------------
| ADMINISTRATOR ONLY
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== "Administrator"
) {
    http_response_code(403);
    die("Access denied.");
}


/*
|--------------------------------------------------------------------------
| PAGE SETTINGS
|--------------------------------------------------------------------------
*/

$pageTitle = "Restore Database";
$pageSubtitle = "Restore system database from a previous backup";
$basePath = "../";
$activePage = "backup";


/*
|--------------------------------------------------------------------------
| RESTORE DATABASE
|--------------------------------------------------------------------------
*/

$message = "";
$messageType = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | CHECK UPLOADED FILE
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_FILES["backup_file"]) ||
        $_FILES["backup_file"]["error"] !== UPLOAD_ERR_OK
    ) {

        $message = "Please select a valid SQL backup file.";
        $messageType = "error";

    } else {

        $file = $_FILES["backup_file"];

        $fileName = $file["name"];
        $tmpName = $file["tmp_name"];
        $fileSize = $file["size"];


        /*
        |--------------------------------------------------------------------------
        | CHECK FILE EXTENSION
        |--------------------------------------------------------------------------
        */

        $extension = strtolower(
            pathinfo($fileName, PATHINFO_EXTENSION)
        );


        if ($extension !== "sql") {

            $message = "Only .sql backup files are allowed.";
            $messageType = "error";

        } elseif ($fileSize <= 0) {

            $message = "The uploaded backup file is empty.";
            $messageType = "error";

        } elseif ($fileSize > 50 * 1024 * 1024) {

            $message = "Backup file is too large. Maximum allowed size is 50 MB.";
            $messageType = "error";

        } else {

            /*
            |--------------------------------------------------------------------------
            | READ SQL FILE
            |--------------------------------------------------------------------------
            */

            $sql = file_get_contents($tmpName);


            if (
                $sql === false ||
                trim($sql) === ""
            ) {

                $message = "Unable to read the backup file.";
                $messageType = "error";

            } else {

                /*
                |--------------------------------------------------------------------------
                | REMOVE UTF-8 BOM IF PRESENT
                |--------------------------------------------------------------------------
                */

                if (
                    substr($sql, 0, 3) === "\xEF\xBB\xBF"
                ) {
                    $sql = substr($sql, 3);
                }


                /*
                |--------------------------------------------------------------------------
                | DISABLE FOREIGN KEY CHECKS
                |--------------------------------------------------------------------------
                */

                if (
                    !$conn->query(
                        "SET FOREIGN_KEY_CHECKS = 0"
                    )
                ) {

                    $message = "Unable to prepare database for restore.";
                    $messageType = "error";

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | EXECUTE SQL BACKUP
                    |--------------------------------------------------------------------------
                    */

                    $success = $conn->multi_query($sql);

                    $restoreError = "";


                    /*
                    |--------------------------------------------------------------------------
                    | PROCESS ALL MULTI QUERY RESULTS
                    |--------------------------------------------------------------------------
                    */

                    if ($success) {

                        do {

                            if (
                                $result = $conn->store_result()
                            ) {

                                $result->free();

                            }

                        } while (
                            $conn->more_results() &&
                            $conn->next_result()
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | CHECK FOR MYSQL ERROR AFTER PROCESSING RESULTS
                        |--------------------------------------------------------------------------
                        */

                        if ($conn->errno) {

                            $success = false;

                            $restoreError = $conn->error;

                        }

                    } else {

                        $restoreError = $conn->error;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | RE-ENABLE FOREIGN KEY CHECKS
                    |--------------------------------------------------------------------------
                    */

                    $conn->query(
                        "SET FOREIGN_KEY_CHECKS = 1"
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | CHECK RESTORE RESULT
                    |--------------------------------------------------------------------------
                    */

                    if (!$success) {

                        if ($restoreError === "") {
                            $restoreError = "Unknown database error.";
                        }

                        $message =
                            "Database restore failed: " .
                            $restoreError;

                        $messageType = "error";

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | ACTIVITY LOG
                        |--------------------------------------------------------------------------
                        */

                        $safeFileName = basename($fileName);

                        logActivity(
                            $conn,
                            "DATABASE_RESTORE",
                            "Restored database from backup file: " .
                            $safeFileName
                        );


                        $message =
                            "Database restored successfully.";

                        $messageType = "success";

                    }

                }

            }

        }

    }

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
        Restore Database
    </title>


    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        /*
        =========================================================
        RESTORE PAGE
        =========================================================
        */

        body {

            background: #f4f6f9;

        }


        .restore-container {

            max-width: 850px;

            margin: 40px auto;

            padding: 20px;

        }


        /*
        =========================================================
        RESTORE CARD
        =========================================================
        */

        .restore-card {

            background: #ffffff;

            border-radius: 12px;

            padding: 30px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

        }


        /*
        =========================================================
        TITLE
        =========================================================
        */

        .restore-title {

            margin-top: 0;

            margin-bottom: 8px;

            font-size: 26px;

            color: #222222;

        }


        /*
        =========================================================
        DESCRIPTION
        =========================================================
        */

        .restore-description {

            color: #666666;

            margin-bottom: 25px;

            line-height: 1.5;

        }


        /*
        =========================================================
        WARNING
        =========================================================
        */

        .warning-box {

            background: #fff3cd;

            border: 1px solid #ffe69c;

            color: #664d03;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 25px;

            line-height: 1.5;

        }


        .warning-box strong {

            display: block;

            margin-bottom: 5px;

        }


        /*
        =========================================================
        MESSAGE
        =========================================================
        */

        .message {

            padding: 14px;

            border-radius: 8px;

            margin-bottom: 20px;

            line-height: 1.5;

        }


        .message.success {

            background: #d1e7dd;

            color: #0f5132;

            border: 1px solid #badbcc;

        }


        .message.error {

            background: #f8d7da;

            color: #842029;

            border: 1px solid #f5c2c7;

        }


        /*
        =========================================================
        FILE LABEL
        =========================================================
        */

        .file-label {

            display: block;

            margin-bottom: 8px;

            color: #333333;

        }


        /*
        =========================================================
        FILE INPUT
        =========================================================
        */

        .file-input {

            width: 100%;

            padding: 12px;

            border: 1px solid #cccccc;

            border-radius: 8px;

            background: #ffffff;

            margin-bottom: 20px;

            box-sizing: border-box;

            cursor: pointer;

        }


        /*
        =========================================================
        RESTORE BUTTON
        =========================================================
        */

        .restore-button {

            background: #dc3545;

            color: #ffffff;

            border: none;

            padding: 12px 22px;

            border-radius: 8px;

            cursor: pointer;

            font-size: 15px;

            font-weight: 600;

            transition: 0.2s ease;

        }


        .restore-button:hover {

            background: #bb2d3b;

        }


        .restore-button:active {

            transform: translateY(1px);

        }


        /*
        =========================================================
        BACK BUTTON
        =========================================================
        */

        .back-button {

            display: inline-block;

            margin-top: 18px;

            text-decoration: none;

            color: #333333;

            font-size: 14px;

        }


        .back-button:hover {

            color: #1f4e78;

            text-decoration: underline;

        }


        /*
        =========================================================
        MOBILE
        =========================================================
        */

        @media (max-width: 600px) {

            .restore-container {

                margin: 20px auto;

                padding: 15px;

            }


            .restore-card {

                padding: 20px;

            }


            .restore-title {

                font-size: 22px;

            }


            .restore-button {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<?php

require_once "../includes/header.php";

?>


<div class="restore-container">


    <div class="restore-card">


        <!-- =====================================================
             TITLE
        ====================================================== -->

        <h1 class="restore-title">

            Restore Database

        </h1>


        <p class="restore-description">

            Restore your clinic database using a previously
            created SQL backup file.

        </p>


        <!-- =====================================================
             MESSAGE
        ====================================================== -->

        <?php if ($message !== ""): ?>

            <div
                class="message <?php echo htmlspecialchars($messageType); ?>"
            >

                <?php

                echo htmlspecialchars($message);

                ?>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             WARNING
        ====================================================== -->

        <div class="warning-box">

            <strong>
                ⚠ Important Warning
            </strong>

            Restoring a database may replace existing records
            with the records contained in the backup file.

            Make sure you have a recent backup before continuing.

        </div>


        <!-- =====================================================
             RESTORE FORM
        ====================================================== -->

        <form
            method="POST"
            enctype="multipart/form-data"
            onsubmit="return confirmRestore();"
        >


            <label
                for="backup_file"
                class="file-label"
            >

                <strong>
                    Select SQL Backup File
                </strong>

            </label>


            <input
                type="file"
                name="backup_file"
                id="backup_file"
                class="file-input"
                accept=".sql"
                required
            >


            <button
                type="submit"
                class="restore-button"
            >

                Restore Database

            </button>


        </form>


        <!-- =====================================================
             BACK TO BACKUP PAGE
        ====================================================== -->

        <a
            href="index.php"
            class="back-button"
        >

            ← Back to Backup & Restore

        </a>


    </div>


</div>


<script>

/*
|--------------------------------------------------------------------------
| CONFIRM RESTORE
|--------------------------------------------------------------------------
*/

function confirmRestore() {

    const fileInput =
        document.getElementById("backup_file");


    /*
    |--------------------------------------------------------------------------
    | CHECK FILE
    |--------------------------------------------------------------------------
    */

    if (!fileInput.files.length) {

        alert(
            "Please select an SQL backup file."
        );

        return false;

    }


    /*
    |--------------------------------------------------------------------------
    | GET FILE NAME
    |--------------------------------------------------------------------------
    */

    const fileName =
        fileInput.files[0].name;


    /*
    |--------------------------------------------------------------------------
    | CHECK EXTENSION
    |--------------------------------------------------------------------------
    */

    const extension =
        fileName
            .split(".")
            .pop()
            .toLowerCase();


    if (extension !== "sql") {

        alert(
            "Only .sql backup files are allowed."
        );

        return false;

    }


    /*
    |--------------------------------------------------------------------------
    | FINAL CONFIRMATION
    |--------------------------------------------------------------------------
    */

    return confirm(

        "WARNING!\n\n" +

        "Restoring this backup may replace " +
        "your current database records.\n\n" +

        "Selected file:\n" +

        fileName +

        "\n\n" +

        "Are you sure you want to continue?"

    );

}

</script>


<?php

require_once "../includes/footer.php";

?>


</body>

</html>