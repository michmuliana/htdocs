<?php

declare(strict_types=1);

require 'database_requests.php';
validateCookie();

require 'header.php';
echo getHeader('Drop Out');

echo "<div style='display: flex; justify-content: center; align-items: center;'>";

echo "<form action='dropOut.php' method='post' id='drop_out_form'>";
echo "<h1>Drop Out</h1>";
echo "<div>";
echo "Student ID: <input type='text' name='studentID' required><br>";
echo "</div>";
echo "<div>";
echo "Password: <input type='password' name='password' required><br>";
echo "</div>";
echo "<input type='submit' value='Drop Out'>";
echo "</form>";

echo "</div>";

// Check if the form was submitted and sanitize the input
if (isset($_POST['studentID'])) {
    $studentID = htmlspecialchars($_POST['studentID']);
    $student = getStudentInfo($studentID);
    $password = htmlspecialchars($_POST['password']);

    if ($student === null) {
        echo "Student not found.";
    } else {
        // Check if the password is correct
        if (md5($password) == getPasswordHash($studentID)) {
            $success = dropOut($studentID, md5($password));
            if ($success) {
                // Remove Cookies
                setcookie('user', '', time() - 3600);
                setcookie('id', '', time() - 3600);
                setcookie('firstName', '', time() - 3600);
                setcookie('lastName', '', time() - 3600);

                // Redirect to login page
                echo '<script>alert("You have successfully dropped out."); window.location="login.php"</script>';
            }
        } else {
            echo "Incorrect password.";
        }
    }
}

require 'footer.php';

closeConnection();

?>