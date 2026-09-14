<?php

require_once "../config/database.php";
require_once "../config/auth.php";

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

$pageTitle = "Medicine Management";
$pageSubtitle = "Manage medicine master list";
$basePath = "../";
$activePage = "medicines";

/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";

/* =========================================================
   GET MEDICINES
========================================================= */

$medicines = array();

if ($search !== "") {

    $stmt = $conn->prepare("
        SELECT
            id,
            medicine_name,
            strength,
            form,
            is_active,
            created_at
        FROM medicines
        WHERE
            medicine_name LIKE ?
            OR strength LIKE ?
            OR form LIKE ?
        ORDER BY
            medicine_name ASC,
            strength ASC,
            form ASC,
            id ASC
    ");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $searchLike = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $searchLike,
        $searchLike,
        $searchLike
    );

} else {

    $stmt = $conn->prepare("
        SELECT
            id,
            medicine_name,
            strength,
            form,
            is_active,
            created_at
        FROM medicines
        ORDER BY
            medicine_name ASC,
            strength ASC,
            form ASC,
            id ASC
    ");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
}

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $medicines[] = $row;

}

$stmt->close();

/* =========================================================
   HEADER
========================================================= */

include "../includes/header.php";

/* =========================================================
   NAVIGATION
========================================================= */

include "../includes/navigation.php";

?>

<style>

/* =========================================================
   MAIN CONTAINER
========================================================= */

.medicine-container {

    max-width: 1200px;

    margin: 20px auto;

    padding: 0 20px 40px;

    box-sizing: border-box;

}


/* =========================================================
   PAGE TOP
========================================================= */

.medicine-page-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 20px;

}


/* =========================================================
   PAGE TITLE
   SAME STYLE AS OTHER PAGES
========================================================= */

.medicine-page-top .page-title {

    margin: 0;

}


.medicine-page-top .page-title h2 {

    margin: 0;

}


/* =========================================================
   ADD MEDICINE BUTTON
========================================================= */

.medicine-add-btn {

    flex-shrink: 0;

    white-space: nowrap;

}


/* =========================================================
   PAGE DESCRIPTION
========================================================= */

.medicine-page-description {

    margin-top: 6px;

    margin-bottom: 20px;

    color: #6b7280;

    font-size: 13px;

}


/* =========================================================
   BUTTONS
========================================================= */

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


.btn-warning {

    background: #f0ad4e;

    color: white;

}


.btn-warning:hover {

    background: #ec971f;

}


.btn-success {

    background: #198754;

    color: white;

}


.btn-success:hover {

    background: #157347;

}


.btn-danger {

    background: #dc3545;

    color: white;

}


.btn-danger:hover {

    background: #bb2d3b;

}


.btn-secondary {

    background: #6c757d;

    color: white;

}


.btn-secondary:hover {

    background: #5a6268;

}


/* =========================================================
   TOOLBAR
========================================================= */

.toolbar {

    background: white;

    border: 1px solid #d9e2ec;

    border-radius: 10px;

    padding: 18px;

    margin-bottom: 20px;

    box-shadow:
        0 2px 8px
        rgba(0,0,0,0.05);

}


.search-form {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

    align-items: center;

}


.search-input {

    flex: 1;

    min-width: 240px;

    padding: 10px 12px;

    border: 1px solid #cbd5df;

    border-radius: 6px;

    font-size: 14px;

    outline: none;

    box-sizing: border-box;

}


.search-input:focus {

    border-color: #1f4e78;

    box-shadow:
        0 0 0 2px
        rgba(31,78,120,0.10);

}


/* =========================================================
   TABLE CARD
========================================================= */

.table-card {

    background: white;

    border: 1px solid #d9e2ec;

    border-radius: 10px;

    overflow: hidden;

    box-shadow:
        0 2px 8px
        rgba(0,0,0,0.05);

}


.table-wrapper {

    width: 100%;

    overflow-x: auto;

}


.medicine-table {

    width: 100%;

    border-collapse: collapse;

    min-width: 800px;

}


.medicine-table th {

    background: #1f4e78;

    color: white;

    text-align: left;

    padding: 13px 14px;

    font-size: 13px;

    white-space: nowrap;

}


.medicine-table td {

    padding: 13px 14px;

    border-bottom: 1px solid #e7edf3;

    color: #333;

    font-size: 14px;

    vertical-align: middle;

}


.medicine-table tr:last-child td {

    border-bottom: none;

}


.medicine-table tbody tr:hover {

    background: #f8fbff;

}


/* =========================================================
   MEDICINE NAME
========================================================= */

.medicine-name {

    font-weight: 700;

    color: #1f4e78;

}


/* =========================================================
   ACTIONS
========================================================= */

.actions {

    display: flex;

    gap: 7px;

    flex-wrap: wrap;

}


.action-btn {

    padding: 7px 10px;

    font-size: 12px;

}


/* =========================================================
   STATUS
========================================================= */

.status-badge {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

}


.status-active {

    background: #d1e7dd;

    color: #0f5132;

}


.status-inactive {

    background: #f8d7da;

    color: #842029;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    padding: 45px 20px;

    text-align: center;

    color: #777;

}


.empty-state h3 {

    margin: 0 0 8px;

    color: #1f4e78;

    font-size: 18px;

}


.empty-state p {

    margin: 0;

    font-size: 14px;

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
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .medicine-container {

        padding: 0 12px 30px;

    }


    .medicine-page-top {

        flex-direction: column;

        align-items: stretch;

        gap: 12px;

    }


    .medicine-add-btn {

        align-self: flex-start;

    }


    .search-form {

        flex-direction: column;

        align-items: stretch;

    }


    .search-input {

        min-width: 0;

        width: 100%;

    }


    .search-form .btn {

        width: 100%;

        text-align: center;

    }

}

</style>


<div class="medicine-container">


    <!-- =====================================================
         PAGE TITLE
    ====================================================== -->

    <div class="medicine-page-top">


        <div class="page-title">

            <h2>
                Medicine Management
            </h2>

        </div>


        <a
            href="add.php"
            class="btn btn-primary medicine-add-btn"
        >
            + Add Medicine
        </a>


    </div>

    <!-- =====================================================
         INFORMATION
    ====================================================== -->

    <div class="info-box">

        <strong>Administrator Only:</strong>

        Add, edit, activate, or deactivate medicines from this page.
        Deactivated medicines will no longer appear in new prescription
        medicine searches, but existing prescription records will remain.

    </div>


    <!-- =====================================================
         SEARCH TOOLBAR
    ====================================================== -->

    <div class="toolbar">


        <form
            method="GET"
            action="index.php"
            class="search-form"
        >


            <input
                type="text"
                name="search"
                class="search-input"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search medicine name, strength, or form..."
                autocomplete="off"
            >


            <button
                type="submit"
                class="btn btn-secondary"
            >
                Search
            </button>


            <?php if ($search !== "") { ?>


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Clear
                </a>


            <?php } ?>


        </form>

    </div>


    <!-- =====================================================
         MEDICINE TABLE
    ====================================================== -->

    <div class="table-card">


        <?php if (count($medicines) > 0) { ?>


            <div class="table-wrapper">


                <table class="medicine-table">


                    <thead>


                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Medicine Name
                            </th>

                            <th>
                                Strength
                            </th>

                            <th>
                                Form
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Created
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>


                    </thead>


                    <tbody>


                    <?php

                    /*
                     * DISPLAY ID
                     *
                     * This number is intentionally sequential
                     * based on the current displayed list.
                     *
                     * The actual database ID remains untouched
                     * and is still used for Edit/Activate/Deactivate.
                     */

                    $displayNumber = 1;

                    ?>


                    <?php foreach ($medicines as $medicine) { ?>


                        <tr>


                            <!-- =================================================
                                 DISPLAY ID
                            ================================================== -->

                            <td>

                                <?php

                                echo $displayNumber;

                                ?>

                            </td>


                            <!-- =================================================
                                 MEDICINE NAME
                            ================================================== -->

                            <td>

                                <div class="medicine-name">

                                    <?php

                                    echo htmlspecialchars(
                                        $medicine["medicine_name"]
                                    );

                                    ?>

                                </div>

                            </td>


                            <!-- =================================================
                                 STRENGTH
                            ================================================== -->

                            <td>

                                <?php


                                if (
                                    !empty(
                                        $medicine["strength"]
                                    )
                                ) {

                                    echo htmlspecialchars(
                                        $medicine["strength"]
                                    );

                                } else {

                                    echo "-";

                                }


                                ?>

                            </td>


                            <!-- =================================================
                                 FORM
                            ================================================== -->

                            <td>

                                <?php


                                if (
                                    !empty(
                                        $medicine["form"]
                                    )
                                ) {

                                    echo htmlspecialchars(
                                        $medicine["form"]
                                    );

                                } else {

                                    echo "-";

                                }


                                ?>

                            </td>


                            <!-- =================================================
                                 STATUS
                            ================================================== -->

                            <td>

                                <?php


                                if (
                                    (int) $medicine["is_active"] === 1
                                ) {


                                ?>

                                    <span
                                        class="status-badge status-active"
                                    >
                                        Active
                                    </span>


                                <?php


                                } else {


                                ?>

                                    <span
                                        class="status-badge status-inactive"
                                    >
                                        Inactive
                                    </span>


                                <?php


                                }


                                ?>

                            </td>


                            <!-- =================================================
                                 CREATED
                            ================================================== -->

                            <td>

                                <?php


                                if (
                                    !empty(
                                        $medicine["created_at"]
                                    )
                                ) {


                                    echo htmlspecialchars(

                                        date(

                                            "M j, Y",

                                            strtotime(
                                                $medicine["created_at"]
                                            )

                                        )

                                    );


                                } else {


                                    echo "-";


                                }


                                ?>

                            </td>


                            <!-- =================================================
                                 ACTIONS
                            ================================================== -->

                            <td>


                                <div class="actions">


                                    <!-- EDIT -->

                                    <a
                                        href="edit.php?id=<?php echo (int) $medicine["id"]; ?>"
                                        class="btn btn-warning action-btn"
                                    >
                                        Edit
                                    </a>


                                    <?php


                                    if (
                                        (int) $medicine["is_active"] === 1
                                    ) {


                                    ?>

                                        <!-- DEACTIVATE -->

                                        <a
                                            href="toggle_status.php?id=<?php echo (int) $medicine["id"]; ?>&status=0"
                                            class="btn btn-danger action-btn"
                                            onclick="return confirm('Deactivate this medicine? It will no longer appear in new prescription searches.');"
                                        >
                                            Deactivate
                                        </a>


                                    <?php


                                    } else {


                                    ?>

                                        <!-- ACTIVATE -->

                                        <a
                                            href="toggle_status.php?id=<?php echo (int) $medicine["id"]; ?>&status=1"
                                            class="btn btn-success action-btn"
                                            onclick="return confirm('Activate this medicine? It will appear in new prescription searches.');"
                                        >
                                            Activate
                                        </a>


                                    <?php


                                    }


                                    ?>

                                </div>


                            </td>


                        </tr>


                        <?php

                        $displayNumber++;

                        ?>


                    <?php } ?>


                    </tbody>


                </table>


            </div>


        <?php } else { ?>


            <div class="empty-state">


                <h3>
                    No medicines found
                </h3>


                <p>


                    <?php


                    if ($search !== "") {

                        echo "No medicine matched your search.";

                    } else {

                        echo "There are no medicines in the master list yet.";

                    }


                    ?>


                </p>


            </div>


        <?php } ?>


    </div>


</div>


<?php

include "../includes/footer.php";

?>