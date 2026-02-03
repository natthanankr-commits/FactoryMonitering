<?php
session_start();
require_once "../config.php";

/* ===== AUTH GUARD ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: /factory_monitoring/login.php");
    exit();
}

/* ===== LOAD OPERATOR DATA ===== */
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT username, email, phone, profile_image
    FROM users
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

/* ===== DATA GUARD ===== */
if ($result->num_rows === 0) {
    // session มี แต่ user ไม่มี → state พัง
    session_destroy();
    header("Location: /factory_monitoring/login.php");
    exit();
}

$op = $result->fetch_assoc();

/* ===== PROFILE IMAGE ===== */
$uploadPath = "/factory_monitoring/Manager/uploads/";
$profileImage = (!empty($op['profile_image']))
    ? $uploadPath . htmlspecialchars($op['profile_image'])
    : $uploadPath . "default_profile.png";
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Operator Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="/factory_monitoring/Operator/assets/css/profile.css">

    <!-- FontAwesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="profile-container">

    <!-- ===== PROFILE IMAGE ===== -->
    <img src="<?= $profileImage ?>"
         class="profile-img"
         onerror="this.src='/factory_monitoring/Manager/uploads/default_profile.png'">

    <h2><?= htmlspecialchars($op['username']) ?></h2>
    <p class="role">Operator</p>

    <!-- ===== INFO ===== -->
    <div class="info-box">
        <p><strong>Email:</strong> <?= htmlspecialchars($op['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($op['phone']) ?></p>
    </div>

    <!-- ===== ACTION ===== -->
    <button class="btn-edit" onclick="openPasswordModal()">
        <i class="fa-solid fa-key"></i> เปลี่ยนรหัสผ่าน
    </button>
</div>

<!-- ===== CHANGE PASSWORD MODAL ===== -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closePasswordModal()">&times;</span>

        <h3>เปลี่ยนรหัสผ่าน</h3>

        <form action="change_password.php" method="POST">
            <label>รหัสผ่านใหม่</label>
            <input type="password" name="password" required>

            <label>ยืนยันรหัสผ่าน</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" class="btn-save">
                <i class="fa-solid fa-save"></i> บันทึก
            </button>
        </form>
    </div>
</div>

<script>
function openPasswordModal() {
    document.getElementById("passwordModal").style.display = "flex";
}
function closePasswordModal() {
    document.getElementById("passwordModal").style.display = "none";
}
window.onclick = function(e) {
    const modal = document.getElementById("passwordModal");
    if (e.target === modal) modal.style.display = "none";
}
</script>

</body>
</html>
