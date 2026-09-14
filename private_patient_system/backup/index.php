<?php

require_once "../config/auth.php";
require_once "../config/database.php";
require_once "../config/activity_log.php";

/* =========================================================
   ADMIN ONLY
========================================================= */

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== "Administrator"
) {
    http_response_code(403);
    die("Access denied.");
}

/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Database Backup";
$pageSubtitle = "Backup and restore system database";

$basePath = "../";
$activePage = "backup";

/* =========================================================
   DATABASE INFORMATION
========================================================= */

$databaseName = "private_patient_db";

/* =========================================================
   BACKUP DATABASE
========================================================= */

if (
    isset($_GET["action"]) &&
    $_GET["action"] === "backup"
) {

    $tablesResult = $conn->query("SHOW TABLES");

    if (!$tablesResult) {
        die(
            "Unable to read database tables: " .
            $conn->error
        );
    }

    $sqlDump = "";

    $sqlDump .= "-- =====================================================\n";
    $sqlDump .= "-- PRIVATE PATIENT SYSTEM DATABASE BACKUP\n";
    $sqlDump .= "-- Database: " . $databaseName . "\n";
    $sqlDump .= "-- Date: " . date("Y-m-d H:i:s") . "\n";
    $sqlDump .= "-- =====================================================\n\n";

    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    while (
        $tableRow =
        $tablesResult->fetch_array()
    ) {

        $tableName = $tableRow[0];

        $sqlDump .=
            "-- -----------------------------------------------------\n";

        $sqlDump .=
            "-- Table: " .
            $tableName .
            "\n";

        $sqlDump .=
            "-- -----------------------------------------------------\n\n";

        $sqlDump .=
            "DROP TABLE IF EXISTS `" .
            $tableName .
            "`;\n\n";

        $createResult = $conn->query(
            "SHOW CREATE TABLE `" .
            $tableName .
            "`"
        );

        if (!$createResult) {
            continue;
        }

        $createRow =
            $createResult->fetch_assoc();

        $createSql =
            $createRow["Create Table"];

        $sqlDump .=
            $createSql .
            ";\n\n";

        $dataResult = $conn->query(
            "SELECT * FROM `" .
            $tableName .
            "`"
        );

        if (
            $dataResult &&
            $dataResult->num_rows > 0
        ) {

            $columns = [];

            while (
                $field =
                $dataResult->fetch_field()
            ) {

                $columns[] =
                    "`" .
                    $field->name .
                    "`";
            }

            while (
                $dataRow =
                $dataResult->fetch_assoc()
            ) {

                $values = [];

                foreach (
                    $dataRow as $value
                ) {

                    if ($value === null) {

                        $values[] = "NULL";

                    } else {

                        $values[] =
                            "'" .
                            $conn->real_escape_string(
                                $value
                            ) .
                            "'";
                    }
                }

                $sqlDump .=
                    "INSERT INTO `" .
                    $tableName .
                    "` (" .
                    implode(
                        ", ",
                        $columns
                    ) .
                    ") VALUES (" .
                    implode(
                        ", ",
                        $values
                    ) .
                    ");\n";
            }

            $sqlDump .= "\n";
        }
    }

    $sqlDump .=
        "SET FOREIGN_KEY_CHECKS=1;\n";

    logActivity(
        $conn,
        "DATABASE_BACKUP",
        "Created database backup: " .
        $databaseName
    );

    $filename =
        "private_patient_db_backup_" .
        date("Y-m-d_H-i-s") .
        ".sql";

    header(
        "Content-Type: application/sql"
    );

    header(
        "Content-Disposition: attachment; filename=\"" .
        $filename .
        "\""
    );

    header(
        "Content-Length: " .
        strlen($sqlDump)
    );

    header(
        "Cache-Control: no-store, no-cache, must-revalidate"
    );

    header(
        "Pragma: no-cache"
    );

    echo $sqlDump;

    exit;
}

/* =========================================================
   PAGE
========================================================= */

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navigation.php";

?>

<style>

/* =========================================================
   BACKUP PAGE
========================================================= */

.backup-page {
    max-width: 1100px;
    margin: 0 auto;
    padding-bottom: 40px;
}

/* PAGE HEADER */

.backup-header {
    margin-bottom: 25px;
}

.backup-header h2 {
    margin: 0;
    font-size: 27px;
    font-weight: 700;
    color: #1f2937;
}

.backup-header p {
    margin: 7px 0 0;
    color: #6b7280;
    font-size: 14px;
}

/* GRID */

.backup-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

/* CARD */

.backup-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.05);
}

.backup-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
}

.backup-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
    flex-shrink: 0;
}

.backup-icon-blue {
    background: #e8f1ff;
    color: #2563eb;
}

.backup-icon-orange {
    background: #fff4df;
    color: #d97706;
}

.backup-card-title {
    margin: 0;
    font-size: 19px;
    font-weight: 700;
    color: #1f2937;
}

.backup-card-subtitle {
    margin-top: 3px;
    font-size: 13px;
    color: #6b7280;
}

/* DESCRIPTION */

.backup-description {
    color: #555;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 20px;
}

/* DATABASE INFO */

.database-info {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 15px 17px;
    margin-bottom: 22px;
}

.database-info-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 7px 0;
    font-size: 14px;
}

.database-info-row + .database-info-row {
    border-top: 1px solid #e5e7eb;
}

.database-label {
    color: #6b7280;
}

.database-value {
    color: #1f2937;
    font-weight: 600;
    text-align: right;
}

/* BUTTONS */

.backup-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 18px;
    border-radius: 9px;
    border: none;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
    box-sizing: border-box;
}

.backup-btn-primary {
    background: #2563eb;
    color: #ffffff;
}

.backup-btn-primary:hover {
    background: #1d4ed8;
}

.backup-btn-danger {
    background: #dc3545;
    color: #ffffff;
}

.backup-btn-danger:hover {
    background: #bb2d3b;
}

/* FILE INPUT */

.backup-file-wrapper {
    margin-bottom: 18px;
}

.backup-file-label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.backup-file-input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 9px;
    background: #ffffff;
    font-size: 13px;
}

/* WARNING */

.backup-warning {
    background: #fff8e1;
    border: 1px solid #f6d77a;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 20px;
    color: #795900;
    font-size: 13px;
    line-height: 1.6;
}

.backup-warning-title {
    font-weight: 700;
    margin-bottom: 3px;
}

/* INFO STRIP */

.backup-info-strip {
    margin-top: 22px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 17px 20px;
    display: flex;
    align-items: center;
    gap: 13px;
}

.backup-info-icon {
    font-size: 20px;
}

.backup-info-text {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}

.backup-info-text strong {
    color: #374151;
}

/* RESPONSIVE */

@media (max-width: 800px) {

    .backup-grid {
        grid-template-columns: 1fr;
    }

    .backup-page {
        padding: 0 10px 30px;
    }

}

</style>


<main class="main-container">

    <div class="backup-page">

        <!-- =================================================
             PAGE HEADER
        ================================================== -->

        <div class="backup-header">

            <h2>
                Database Backup
            </h2>

            <p>
                Protect and manage your clinic database
                through backup and restore tools.
            </p>

        </div>


        <!-- =================================================
             CARDS
        ================================================== -->

        <div class="backup-grid">


            <!-- =============================================
                 BACKUP CARD
            ============================================== -->

            <div class="backup-card">

                <div class="backup-card-header">

                    <div class="backup-icon backup-icon-blue">
                        💾
                    </div>

                    <div>

                        <h3 class="backup-card-title">
                            Backup Database
                        </h3>

                        <div class="backup-card-subtitle">
                            Create a copy of your current database
                        </div>

                    </div>

                </div>


                <div class="backup-description">

                    Create a complete SQL backup containing
                    your database tables and their current
                    records.

                </div>


                <div class="database-info">

                    <div class="database-info-row">

                        <span class="database-label">
                            Database
                        </span>

                        <span class="database-value">
                            <?= htmlspecialchars($databaseName) ?>
                        </span>

                    </div>

                    <div class="database-info-row">

                        <span class="database-label">
                            Server
                        </span>

                        <span class="database-value">
                            localhost
                        </span>

                    </div>

                    <div class="database-info-row">

                        <span class="database-label">
                            Backup format
                        </span>

                        <span class="database-value">
                            SQL
                        </span>

                    </div>

                </div>


                <a
                    href="index.php?action=backup"
                    class="backup-btn backup-btn-primary"
                    onclick="
                        return confirm(
                            'Create a database backup now?'
                        );
                    "
                >

                    <span>⬇</span>

                    Download Database Backup

                </a>

            </div>


            <!-- =============================================
                 RESTORE CARD
            ============================================== -->

            <div class="backup-card">

                <div class="backup-card-header">

                    <div class="backup-icon backup-icon-orange">
                        🔄
                    </div>

                    <div>

                        <h3 class="backup-card-title">
                            Restore Database
                        </h3>

                        <div class="backup-card-subtitle">
                            Recover data from an SQL backup
                        </div>

                    </div>

                </div>


                <div class="backup-description">

                    Restore your database using a previously
                    created SQL backup file.

                </div>


                <div class="backup-warning">

                    <div class="backup-warning-title">
                        ⚠ Important Warning
                    </div>

                    Restoring a backup may replace the
                    current database records. Always create
                    a fresh backup before restoring.

                </div>


                <form
                    method="POST"
                    action="restore.php"
                    enctype="multipart/form-data"
                    onsubmit="
                        return confirm(
                            'WARNING: Restoring this database may replace the current records. Continue?'
                        );
                    "
                >

                    <div class="backup-file-wrapper">

                        <label
                            for="backup_file"
                            class="backup-file-label"
                        >
                            Select SQL Backup File
                        </label>

                        <input
                            type="file"
                            id="backup_file"
                            name="backup_file"
                            class="backup-file-input"
                            accept=".sql"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="backup-btn backup-btn-danger"
                    >

                        <span>↻</span>

                        Restore Database

                    </button>

                </form>

            </div>

        </div>


        <!-- =================================================
             INFORMATION
        ================================================== -->

        <div class="backup-info-strip">

            <div class="backup-info-icon">
                ℹ️
            </div>

            <div class="backup-info-text">

                <strong>Backup reminder:</strong>

                Regularly create a database backup,
                especially before making major changes
                to the system or database.

            </div>

        </div>

    </div>

</main>


<?php

include __DIR__ . "/../includes/footer.php";

$conn->close();

?>