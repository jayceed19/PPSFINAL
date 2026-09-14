<?php

require_once "../config/auth.php";
require_once "../config/database.php";


/* =========================================================
   ADMINISTRATOR ONLY
========================================================= */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../dashboard.php");
    exit;
}


/* =========================================================
   GET USER ID
========================================================= */

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid user ID.");
}


/* =========================================================
   GET USER DATA
========================================================= */

$sql = "
    SELECT id, username, full_name, role, is_active
    FROM users
    WHERE id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error.");
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("User not found.");
}

$user = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   HEADER & NAVIGATION
========================================================= */

require_once "../includes/header.php";
require_once "../includes/navigation.php";

?>

<style>

/* =========================================================
   EDIT USER PAGE
========================================================= */

.user-edit-wrapper {
    width: calc(100% - 40px);
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px 0 40px;
    box-sizing: border-box;
}


/* =========================================================
   PAGE TOP
========================================================= */

.user-edit-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
}


/* =========================================================
   PAGE TITLE
   SAME STYLE AS OTHER PAGES
========================================================= */

.user-edit-top .page-title {
    margin: 0;
}

.user-edit-top .page-title h2 {
    margin: 0;
}


/* =========================================================
   BACK BUTTON
========================================================= */

.user-back-btn {
    flex-shrink: 0;
    min-height: 38px;
    padding: 8px 15px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}


/* =========================================================
   MAIN CARD
========================================================= */

.user-edit-card {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    overflow: hidden;
    box-shadow:
        0 3px 12px rgba(0, 0, 0, 0.05);
}


/* =========================================================
   CARD HEADER
========================================================= */

.user-edit-header {
    padding: 18px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;

    display: flex;
    align-items: center;
    gap: 13px;
}

.user-icon {
    width: 42px;
    height: 42px;
    border-radius: 8px;

    background: #eaf2fb;
    color: #1f4e78;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 18px;
    flex-shrink: 0;
}

.user-edit-header-title {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
}

.user-edit-header-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin-top: 3px;
}


/* =========================================================
   CARD BODY
========================================================= */

.user-edit-body {
    padding: 24px;
}


/* =========================================================
   FORM SECTION
========================================================= */

.form-section {
    margin-bottom: 25px;
}

.form-section:last-child {
    margin-bottom: 0;
}


/* =========================================================
   FORM SECTION HEADER
========================================================= */

.form-section-title {
    display: flex;
    align-items: center;
    gap: 8px;

    font-size: 13px;
    font-weight: 700;
    color: #374151;

    padding-bottom: 10px;
    margin-bottom: 17px;

    border-bottom: 1px solid #edf0f3;
}


/* =========================================================
   FORM SECTION DOT
========================================================= */

.form-section-title:before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #1f4e78;
    flex-shrink: 0;
}


/* =========================================================
   FORM LABEL
========================================================= */

.user-edit-body .form-label {
    display: block;

    font-size: 12px !important;
    font-weight: 600 !important;
    color: #374151 !important;

    margin-bottom: 6px !important;
}


/* =========================================================
   INPUTS
========================================================= */

.user-edit-body .form-control,
.user-edit-body .form-select {

    width: 100%;

    min-height: 42px;

    padding:
        8px
        12px;

    border: 1px solid #d1d5db !important;

    border-radius: 6px !important;

    font-size: 13px !important;

    color: #374151;

    background-color: #ffffff;

    box-shadow: none !important;

    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease,
        background-color 0.15s ease;

    box-sizing: border-box;
}


.user-edit-body .form-control:hover,
.user-edit-body .form-select:hover {

    border-color: #b8c0c8 !important;

}


.user-edit-body .form-control:focus,
.user-edit-body .form-select:focus {

    border-color: #6f9fd0 !important;

    box-shadow:
        0 0 0 3px
        rgba(31, 78, 120, 0.08) !important;

    outline: none;

}


.user-edit-body .form-control::placeholder {

    color: #9ca3af;

    font-size: 12px;

}


/* =========================================================
   PASSWORD FIELD
========================================================= */

.password-field {
    position: relative;
    width: 100%;
}


.password-input {
    padding-right: 46px !important;
}


/* =========================================================
   PASSWORD EYE
========================================================= */

.password-eye {

    position: absolute;

    right: 0;
    top: 0;

    width: 42px;
    height: 42px;

    border: 0;

    background: transparent;

    color: #6b7280;

    display: flex;

    align-items: center;

    justify-content: center;

    cursor: pointer;

    font-size: 16px;

    padding: 0;

    z-index: 5;

    border-radius: 0 6px 6px 0;

    transition:
        color 0.15s ease,
        background-color 0.15s ease;
}


.password-eye:hover {

    color: #1f4e78;

    background: #f7f9fb;

}


.password-eye:focus {

    outline: none;

}


.password-eye.active {

    color: #1f4e78;

}


/* =========================================================
   PASSWORD NOTE
========================================================= */

.password-note {

    font-size: 11px;

    color: #6b7280;

    margin-top: 6px;

    line-height: 1.45;

}


/* =========================================================
   FORM ACTION AREA
========================================================= */

.user-form-actions {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    gap: 9px;

    padding-top: 20px;

    margin-top: 25px;

    border-top: 1px solid #e5e7eb;
}


.user-form-actions .btn {

    min-width: 110px;

    min-height: 39px;

    padding: 8px 15px;

    font-size: 13px;

    font-weight: 600;

    border-radius: 6px;

}


/* =========================================================
   SAVE BUTTON
========================================================= */

.user-form-actions .btn-primary {

    background: #1f4e78;

    border-color: #1f4e78;

}


.user-form-actions .btn-primary:hover {

    background: #173b5d;

    border-color: #173b5d;

}


/* =========================================================
   CANCEL BUTTON
========================================================= */

.user-form-actions .btn-light {

    color: #4b5563;

    background: #ffffff;

    border-color: #d1d5db;

}


.user-form-actions .btn-light:hover {

    background: #f8fafc;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .user-edit-wrapper {

        width: calc(100% - 24px);

        padding:
            15px
            0
            30px;

    }


    .user-edit-top {

        align-items: flex-start;

        gap: 12px;

    }


    .user-edit-body {

        padding: 20px;

    }


    .user-edit-header {

        padding:
            16px
            18px;

    }

}


@media (max-width: 576px) {

    .user-edit-top {

        flex-direction: column;

        align-items: stretch;

    }


    .user-back-btn {

        align-self: flex-start;

    }


    .user-form-actions {

        flex-direction: column-reverse;

        align-items: stretch;

    }


    .user-form-actions .btn {

        width: 100%;

    }

}

</style>


<!-- =========================================================
     PAGE
========================================================= -->

<div class="user-edit-wrapper">


    <!-- =====================================================
         PAGE TITLE + BACK BUTTON
    ====================================================== -->

    <div class="user-edit-top">


        <div class="page-title">

            <h2>
                Edit User
            </h2>

        </div>


        <a
            href="index.php"
            class="btn btn-outline-secondary user-back-btn"
        >
            ← Back
        </a>


    </div>



    <!-- =====================================================
         MAIN CARD
    ====================================================== -->

    <div class="user-edit-card">


        <!-- =================================================
             CARD HEADER
        ================================================== -->

        <div class="user-edit-header">

            <div class="user-icon">
                👤
            </div>

            <div>

                <div class="user-edit-header-title">
                    User Account
                </div>

                <div class="user-edit-header-subtitle">
                    Update the information associated with this account
                </div>

            </div>

        </div>



        <!-- =================================================
             CARD BODY
        ================================================== -->

        <div class="user-edit-body">

            <form
                method="POST"
                action="update.php"
                id="editUserForm"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $user['id']; ?>"
                >


                <!-- =================================================
                     ACCOUNT INFORMATION
                ================================================== -->

                <div class="form-section">

                    <div class="form-section-title">
                        Account Information
                    </div>


                    <div class="row g-3">


                        <!-- FULL NAME -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                placeholder="Enter full name"
                                required
                            >

                        </div>


                        <!-- USERNAME -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="<?php echo htmlspecialchars($user['username']); ?>"
                                placeholder="Enter username"
                                required
                            >

                        </div>


                        <!-- ROLE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                User Role
                            </label>

                            <select
                                name="role"
                                class="form-select"
                                required
                            >

                                <option
                                    value="Staff"
                                    <?php
                                    echo ($user['role'] === 'Staff')
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Staff
                                </option>

                                <option
                                    value="Administrator"
                                    <?php
                                    echo ($user['role'] === 'Administrator')
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Administrator
                                </option>

                            </select>

                        </div>


                        <!-- ACCOUNT STATUS -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Account Status
                            </label>

                            <select
                                name="is_active"
                                class="form-select"
                                required
                            >

                                <option
                                    value="1"
                                    <?php
                                    echo ($user['is_active'] == 1)
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="0"
                                    <?php
                                    echo ($user['is_active'] == 0)
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     CHANGE PASSWORD
                ================================================== -->

                <div class="form-section">

                    <div class="form-section-title">
                        Change Password
                    </div>


                    <div class="row g-3">


                        <!-- NEW PASSWORD -->

                        <div class="col-md-6">

                            <label class="form-label">
                                New Password
                            </label>


                            <div class="password-field">

                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control password-input"
                                    minlength="6"
                                    placeholder="Enter new password"
                                >


                                <button
                                    type="button"
                                    class="password-eye"
                                    id="passwordToggle"
                                    aria-label="Show password"
                                    title="Show password"
                                >
                                    👁
                                </button>

                            </div>


                            <div class="password-note">
                                Leave blank to keep the current password.
                                Minimum 6 characters.
                            </div>

                        </div>



                        <!-- CONFIRM PASSWORD -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Confirm New Password
                            </label>


                            <div class="password-field">

                                <input
                                    type="password"
                                    name="confirm_password"
                                    id="confirm_password"
                                    class="form-control password-input"
                                    minlength="6"
                                    placeholder="Re-enter new password"
                                >


                                <button
                                    type="button"
                                    class="password-eye"
                                    id="confirmPasswordToggle"
                                    aria-label="Show password"
                                    title="Show password"
                                >
                                    👁
                                </button>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     ACTION BUTTONS
                ================================================== -->

                <div class="user-form-actions">

                    <a
                        href="index.php"
                        class="btn btn-light border"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<script>

/* =========================================================
   PASSWORD TOGGLE FUNCTION
========================================================= */

function setupPasswordToggle(buttonId, inputId) {

    var button =
        document.getElementById(buttonId);

    var input =
        document.getElementById(inputId);


    if (!button || !input) {
        return;
    }


    button.addEventListener("click", function () {

        if (input.type === "password") {

            input.type = "text";

            button.classList.add("active");

            button.innerHTML = "🙈";

            button.setAttribute(
                "aria-label",
                "Hide password"
            );

            button.setAttribute(
                "title",
                "Hide password"
            );

        } else {

            input.type = "password";

            button.classList.remove("active");

            button.innerHTML = "👁";

            button.setAttribute(
                "aria-label",
                "Show password"
            );

            button.setAttribute(
                "title",
                "Show password"
            );

        }

    });

}


/* =========================================================
   NEW PASSWORD TOGGLE
========================================================= */

setupPasswordToggle(
    "passwordToggle",
    "password"
);


/* =========================================================
   CONFIRM PASSWORD TOGGLE
========================================================= */

setupPasswordToggle(
    "confirmPasswordToggle",
    "confirm_password"
);


/* =========================================================
   FORM VALIDATION
========================================================= */

var editUserForm =
    document.getElementById("editUserForm");


if (editUserForm) {

    editUserForm.addEventListener(
        "submit",
        function (event) {

            var password =
                document.getElementById("password").value;

            var confirmPassword =
                document.getElementById("confirm_password").value;


            /* =============================================
               PASSWORD LENGTH
            ============================================= */

            if (
                password !== "" &&
                password.length < 6
            ) {

                alert(
                    "Password must be at least 6 characters."
                );

                event.preventDefault();

                return false;
            }


            /* =============================================
               PASSWORD CONFIRMATION
            ============================================= */

            if (
                password !== "" &&
                password !== confirmPassword
            ) {

                alert(
                    "New Password and Confirm New Password do not match."
                );

                event.preventDefault();

                document
                    .getElementById("confirm_password")
                    .focus();

                return false;
            }

        }
    );

}

</script>


<?php

require_once "../includes/footer.php";

?>