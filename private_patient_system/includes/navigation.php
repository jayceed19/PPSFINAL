<?php

if (!isset($activePage)) {
    $activePage = "";
}


/*
|--------------------------------------------------------------------------
| CURRENT LOGGED-IN USER
|--------------------------------------------------------------------------
*/

$loggedInName = isset($_SESSION['full_name'])
    ? $_SESSION['full_name']
    : '';

$loggedInRole = isset($_SESSION['role'])
    ? $_SESSION['role']
    : 'Staff';


/*
|--------------------------------------------------------------------------
| FOLLOW-UP DUE SOON NOTIFICATION
|--------------------------------------------------------------------------
|
| Count pending/active follow-ups from today up to 7 days.
|
*/

$followUpDueSoonCount = 0;

if (isset($conn)) {

    $followUpNotificationSql = "
        SELECT COUNT(*) AS total
        FROM consultations c
        INNER JOIN patients p
            ON c.patient_id = p.id
        WHERE
            UPPER(TRIM(p.status)) != 'DECEASED'
            AND c.follow_up_date IS NOT NULL
            AND (
                UPPER(TRIM(c.follow_up_status)) = 'PENDING'
                OR UPPER(TRIM(c.follow_up_status)) = 'ACTIVE'
            )
            AND c.follow_up_date >= CURDATE()
            AND c.follow_up_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ";

    $followUpNotificationResult = $conn->query(
        $followUpNotificationSql
    );

    if ($followUpNotificationResult) {

        $followUpNotificationRow =
            $followUpNotificationResult->fetch_assoc();

        if ($followUpNotificationRow) {

            $followUpDueSoonCount = intval(
                $followUpNotificationRow["total"]
            );

        }

    }

}

?>


<nav class="navigation">

    <div class="navigation-inner">


        <!-- =====================================================
             MAIN NAVIGATION
        ====================================================== -->

        <div class="navigation-menu">


            <!-- DASHBOARD -->

            <a
                href="/private_patient_system/dashboard.php"
                class="nav-link <?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>"
            >
                Dashboard
            </a>


            <!-- PATIENTS -->

            <a
                href="/private_patient_system/patients/index.php"
                class="nav-link <?php echo ($activePage == 'patients') ? 'active' : ''; ?>"
            >
                Patients
            </a>


            <!-- CONSULTATIONS -->

            <a
                href="/private_patient_system/patients/consultations.php"
                class="nav-link <?php echo ($activePage == 'consultations') ? 'active' : ''; ?>"
            >
                Consultation
            </a>


            <!-- =================================================
                 LABORATORY
            ================================================== -->

            <a
                href="/private_patient_system/laboratory/search.php"
                class="nav-link <?php echo ($activePage == 'laboratory') ? 'active' : ''; ?>"
            >
                Laboratory
            </a>


            <!-- =================================================
                 PRESCRIPTION
            ================================================== -->

            <a
                href="/private_patient_system/prescriptions/search.php"
                class="nav-link <?php echo ($activePage == 'prescriptions') ? 'active' : ''; ?>"
            >
                Medicine
            </a>


            <!-- =================================================
                 FOLLOW-UP
            ================================================== -->

            <a
                href="/private_patient_system/patients/follow_up.php?type=due"
                class="nav-link nav-follow-up <?php echo ($activePage == 'follow_up') ? 'active' : ''; ?>"
            >

                <span>
                    Follow-up
                </span>


                <?php if ($followUpDueSoonCount > 0) { ?>

                    <span class="follow-up-notification">

                        <?php
                        echo $followUpDueSoonCount;
                        ?>

                    </span>

                <?php } ?>

            </a>


            <!-- =================================================
                 REPORTS
            ================================================== -->

            <div class="nav-dropdown">

                <a
                    href="#"
                    class="nav-link nav-reports-link <?php echo ($activePage == 'reports') ? 'active' : ''; ?>"
                    onclick="return false;"
                >

                    Reports

                    <span class="dropdown-arrow">
                        ▼
                    </span>

                </a>


                <!-- REPORTS DROPDOWN -->

                <div class="nav-dropdown-menu">


                    <!-- PATIENT REPORTS -->

                    <a
                        href="/private_patient_system/reports/patients.php"
                    >
                        Patient Reports
                    </a>


                    <!-- CONSULTATION REPORTS -->

                    <a
                        href="/private_patient_system/reports/consultations.php"
                    >
                        Consultation Reports
                    </a>


                    <!-- DIAGNOSIS REPORT -->

                    <a
                        href="/private_patient_system/reports/diagnoses.php"
                    >
                        Diagnosis Report
                    </a>


                </div>

            </div>


            <!-- =================================================
                 ADMINISTRATOR SETTINGS
            ================================================== -->

            <?php if ($loggedInRole === 'Administrator'): ?>


                <div class="nav-dropdown">


                    <!-- SETTINGS -->

                    <a
                        href="#"
                        class="nav-link nav-settings-link <?php echo (
                            $activePage == 'users' ||
                            $activePage == 'activity_logs' ||
                            $activePage == 'backup' ||
                            $activePage == 'medicines'
                        ) ? 'active' : ''; ?>"
                        onclick="return false;"
                    >

                        Settings

                        <span class="dropdown-arrow">
                            ▼
                        </span>

                    </a>


                    <!-- SETTINGS DROPDOWN -->

                    <div class="nav-dropdown-menu settings-dropdown">


                        <!-- USER MANAGEMENT -->

                        <a
                            href="/private_patient_system/users/index.php"
                        >
                            User Management
                        </a>


                        <!-- ACTIVITY LOG -->

                        <a
                            href="/private_patient_system/activity_logs/index.php"
                        >
                            Activity Log
                        </a>


                        <!-- BACKUP & RESTORE -->

                        <a
                            href="/private_patient_system/backup/index.php"
                        >
                            Backup & Restore
                        </a>


                        <!-- MEDICINE MANAGEMENT -->

                        <a
                            href="/private_patient_system/medicines/index.php"
                        >
                            Medicine Management
                        </a>


                    </div>

                </div>


            <?php endif; ?>


        </div>


        <!-- =====================================================
             USER / LOGOUT
        ====================================================== -->

        <div class="navigation-user">


            <!-- LOGGED-IN USER INFORMATION -->

            <div class="navigation-user-info">

                <span class="navigation-user-name">

                    <?php

                    echo htmlspecialchars(
                        $loggedInName
                    );

                    ?>

                </span>


                <span class="navigation-user-role">

                    <?php

                    echo htmlspecialchars(
                        $loggedInRole
                    );

                    ?>

                </span>

            </div>


            <!-- LOGOUT -->

            <a
                href="/private_patient_system/logout.php"
                class="nav-logout"
                onclick="return confirm('Are you sure you want to logout?');"
            >
                Logout
            </a>


        </div>


    </div>

</nav>


<style>

/* =========================================================
   NAVIGATION INNER
========================================================= */

.navigation-inner {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

}


/* =========================================================
   MAIN NAVIGATION MENU
========================================================= */

.navigation-menu {

    display: flex;

    align-items: center;

}


/* =========================================================
   DROPDOWN
========================================================= */

.nav-dropdown {

    position: relative;

}


/* =========================================================
   DROPDOWN LINK
========================================================= */

.nav-reports-link,
.nav-settings-link {

    display: flex;

    align-items: center;

}


/* =========================================================
   DROPDOWN ARROW
========================================================= */

.dropdown-arrow {

    font-size: 9px;

    margin-left: 5px;

    opacity: 0.7;

}


/* =========================================================
   DROPDOWN MENU
========================================================= */

.nav-dropdown-menu {

    display: none;

    position: absolute;

    top: 100%;

    left: 0;

    min-width: 210px;

    background: #ffffff;

    border: 1px solid #e1e5e9;

    border-radius: 6px;

    box-shadow:
        0 5px 18px
        rgba(0, 0, 0, 0.10);

    padding: 5px 0;

    z-index: 1000;

}


/* =========================================================
   SHOW DROPDOWN
========================================================= */

.nav-dropdown:hover .nav-dropdown-menu {

    display: block;

}


/* =========================================================
   DROPDOWN ITEMS
========================================================= */

.nav-dropdown-menu a {

    display: block;

    padding: 9px 15px;

    color: #333333;

    text-decoration: none;

    font-family: inherit;

    font-size: inherit;

    font-weight: inherit;

    line-height: inherit;

    white-space: nowrap;

}


/* =========================================================
   DROPDOWN ITEM HOVER
========================================================= */

.nav-dropdown-menu a:hover {

    background: #f4f7fa;

    color: #1f4e78;

}


/* =========================================================
   FOLLOW-UP NOTIFICATION
========================================================= */

.nav-follow-up {

    display: flex;

    align-items: center;

    gap: 6px;

}


/* =========================================================
   FOLLOW-UP NOTIFICATION BADGE
========================================================= */

.follow-up-notification {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 18px;

    height: 18px;

    padding: 0 5px;

    background: #dc3545;

    color: #ffffff;

    border-radius: 50px;

    font-size: 10px;

    font-weight: 700;

    line-height: 1;

    box-sizing: border-box;

}


/* =========================================================
   USER AREA
========================================================= */

.navigation-user {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-left: auto;

}


/* =========================================================
   USER INFORMATION
========================================================= */

.navigation-user-info {

    display: flex;

    flex-direction: column;

    align-items: flex-end;

    line-height: 1.2;

}


/* =========================================================
   USER NAME
========================================================= */

.navigation-user-name {

    font-size: 13px;

    font-weight: 700;

    color: #ffffff;

    line-height: 1.2;

}


/* =========================================================
   USER ROLE
========================================================= */

.navigation-user-role {

    font-size: 10px;

    color: #ffffff;

    margin-top: 2px;

    line-height: 1.2;

    opacity: 0.9;

}


/* =========================================================
   LOGOUT BUTTON
========================================================= */

.nav-logout {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    height: 34px;

    padding: 0 13px;

    background: #ffffff;

    color: #b42318;

    border: 1px solid #d9b5b2;

    border-radius: 5px;

    text-decoration: none;

    font-size: 12px;

    font-weight: 600;

    transition: 0.2s ease;

}


.nav-logout:hover {

    background: #fdf0ef;

    border-color: #b42318;

    color: #9b1c13;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .navigation-inner {

        flex-direction: column;

        align-items: stretch;

    }


    .navigation-menu {

        width: 100%;

        flex-wrap: wrap;

    }


    .navigation-user {

        width: 100%;

        justify-content: flex-end;

        padding-top: 8px;

        border-top: 1px solid #e5e7eb;

    }


    .nav-dropdown {

        width: 100%;

    }


    .nav-reports-link,
    .nav-settings-link {

        width: 100%;

    }


    .nav-dropdown-menu {

        position: static;

        width: 100%;

        box-shadow: none;

        border-radius: 0;

        border-left: none;

        border-right: none;

    }


    .nav-dropdown:hover .nav-dropdown-menu {

        display: block;

    }

}

</style>