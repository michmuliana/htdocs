<?php

declare(strict_types=1);

require 'database_requests.php';
validateCookie();

include 'header.php';
echo getHeader('Reset Password');

echo '<!-- Reset Password Form -->
<div class="flex center">
    <form id="reset_pass_form" action="reset_pass.php" method="post">
        <h1>Reset your password</h1>
        <div><input type="password" id="old_password" name="old_password" placeholder="Old Password" required></div>
        <div><input type="password" id="new_password" name="new_password" placeholder="New Password" required></div>
        <div><button id="button_reset_pass" class="center">Reset Password</button></div>
    </form>
</div>
<!-- End of Reset Password Form -->';

// Check if the form was submitted
if (isset($_POST['old_password']) && isset($_POST['new_password'])) {
    // Sanitize the input
    $oldPassword = htmlspecialchars($_POST['old_password']);
    $newPassword = htmlspecialchars($_POST['new_password']);
    $id = (int)htmlspecialchars($_COOKIE['id']);

    // Ensure that there was no invalid input in the newPassword
    if ($_POST['new_password'] !== $newPassword) {
        echo '<div class="flex center"><h2>Invalid characters in the new password.</h2></div>';
        return;
    }

    // Check if the old password is correct
    $stmt = $conn->prepare("SELECT passHash FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $oldPassHash = md5($oldPassword);
    
    // Check if the old password is correct
    if ($oldPassHash !== $row['passHash']) {
        echo '<div class="flex center"><h2>Old password is incorrect.</h2></div>';
    } else {
        $newPassHash = md5($newPassword);
        $stmt = $conn->prepare("UPDATE students SET passHash = ? WHERE id = ?");
        $stmt->bind_param("si", $newPassHash, $id);
        if ($stmt->execute()) {
            echo '<div class="flex center"><h2>Password reset successful.</h2></div>';
        } else {
            echo '<div class="flex center"><h2>Password reset failed.</h2></div>';
        }
    }
    $stmt->close();
}

closeConnection();

include 'footer.php';

?>