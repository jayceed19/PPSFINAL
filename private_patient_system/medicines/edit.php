<?php

require_once "../config/database.php";
require_once "../config/auth.php";

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "Administrator"
) {
    http_response_code(403);
    die("Access denied.");
}

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    die("Invalid medicine ID.");
}

$stmt = $conn->prepare("
    SELECT id, medicine_name, strength, form, is_active
    FROM medicines
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$medicine = $result->fetch_assoc();
$stmt->close();

if (!$medicine) {
    die("Medicine not found.");
}

$pageTitle = "Edit Medicine";
$pageSubtitle = "Update medicine master list";
$basePath = "../";
$activePage = "medicines";

include "../includes/header.php";
include "../includes/navigation.php";

?>

<style>
.medicine-form-container {
    max-width: 850px;
    margin: 30px auto;
    padding: 0 20px 40px;
}

.page-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.page-top h2 {
    margin: 0;
    color: #1f4e78;
    font-size: 26px;
}

.page-top p {
    margin: 5px 0 0;
    color: #666;
    font-size: 14px;
}

.form-card {
    background: white;
    border: 1px solid #d9e2ec;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

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

.form-grid {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1fr;
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 13px;
    font-weight: 700;
    color: #444;
    margin-bottom: 6px;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;
    padding: 11px 12px;
    border: 1px solid #cbd5df;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    outline: none;
}

.form-group input:focus {
    border-color: #1f4e78;
    box-shadow: 0 0 0 2px rgba(31,78,120,0.10);
}

.form-help {
    margin-top: 5px;
    font-size: 12px;
    color: #777;
}

.required {
    color: #dc3545;
}

.status-info {
    margin-top: 20px;
    padding: 12px 14px;
    border-radius: 6px;
    background: #f7f9fb;
    border: 1px solid #e1e7ed;
    font-size: 13px;
    color: #555;
    line-height: 1.6;
}

.status-active {
    color: #0f5132;
    font-weight: 700;
}

.status-inactive {
    color: #842029;
    font-weight: 700;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e1e7ed;
    flex-wrap: wrap;
}

.form-actions-left,
.form-actions-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

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

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

@media (max-width: 700px) {
    .medicine-form-container {
        padding: 0 12px 30px;
    }

    .form-card {
        padding: 18px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .page-top {
        align-items: flex-start;
    }

    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .form-actions-left,
    .form-actions-right {
        width: 100%;
    }

    .form-actions .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<div class="medicine-form-container">

    <div class="page-top">
        <div>
            <h2>Edit Medicine</h2>
            <p>Update the medicine information in the master list.</p>
        </div>
    </div>

    <div class="info-box">
        <strong>Medicine Master List:</strong>
        Changes made here will be used by the Prescription medicine search
        for future prescriptions.
    </div>

    <div class="form-card">

        <form method="POST" action="update.php" autocomplete="off">

            <input
                type="hidden"
                name="id"
                value="<?php echo (int) $medicine["id"]; ?>"
            >

            <div class="form-grid">

                <div class="form-group">
                    <label for="medicine_name">
                        Medicine Name <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="medicine_name"
                        name="medicine_name"
                        maxlength="150"
                        value="<?php echo htmlspecialchars($medicine["medicine_name"]); ?>"
                        required
                    >

                    <div class="form-help">
                        Enter the generic or standard medicine name.
                    </div>
                </div>

                <div class="form-group">
                    <label for="strength">Strength</label>

                    <input
                        type="text"
                        id="strength"
                        name="strength"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($medicine["strength"]); ?>"
                        placeholder="e.g. 500 MG"
                    >

                    <div class="form-help">
                        Example: 500 MG, 250 MG/5 ML.
                    </div>
                </div>

                <div class="form-group">
                    <label for="form">Form</label>

                    <input
                        type="text"
                        id="form"
                        name="form"
                        maxlength="50"
                        value="<?php echo htmlspecialchars($medicine["form"]); ?>"
                        placeholder="e.g. TABLET"
                    >

                    <div class="form-help">
                        Example: TABLET, CAPSULE, SYRUP.
                    </div>
                </div>

            </div>

            <div class="status-info">
                <strong>Current Status:</strong>

                <?php if ((int) $medicine["is_active"] === 1) { ?>

                    <span class="status-active">Active</span>

                <?php } else { ?>

                    <span class="status-inactive">Inactive</span>

                <?php } ?>

                <br>

                Activation status is managed from the Medicine List.
            </div>

            <div class="form-actions">

                <div class="form-actions-left">
                    <a href="index.php" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

                <div class="form-actions-right">
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Update Medicine
                    </button>
                </div>

            </div>

        </form>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const fields = document.querySelectorAll(
        "#medicine_name, #strength, #form"
    );

    fields.forEach(function (field) {

        field.addEventListener("input", function () {
            field.value = field.value.toUpperCase();
        });

    });

    document.querySelector("form").addEventListener(
        "submit",
        function (event) {

            const medicineName =
                document.getElementById("medicine_name");

            const strength =
                document.getElementById("strength");

            const form =
                document.getElementById("form");

            if (!medicineName.value.trim()) {
                event.preventDefault();

                alert("Please enter the medicine name.");

                medicineName.focus();

                return;
            }

            medicineName.value =
                medicineName.value
                    .trim()
                    .replace(/\s+/g, " ")
                    .toUpperCase();

            strength.value =
                strength.value
                    .trim()
                    .replace(/\s+/g, " ")
                    .toUpperCase();

            form.value =
                form.value
                    .trim()
                    .replace(/\s+/g, " ")
                    .toUpperCase();
        }
    );

});
</script>

<?php

include "../includes/footer.php";

?>
