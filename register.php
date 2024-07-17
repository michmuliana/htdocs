<?php

declare(strict_types=1);

require 'database_requests.php';
validateCookie(); // Check if the user is logged in

// Add Header
include 'header.php';
echo getHeader('Register');

// Register Form
echo '<!-- Register Form -->
<div class="flex center">
<form id="register_form" action="register.php" method="post">
    <h1>Register / Drop Form</h1>
    <div><input type="text" id="course" name="course" placeholder="Course" required></div>
    <select id="action" name="action" required>
        <option value="register">Register</option>
        <option value="drop">Drop</option>
    </select>
    <div><button id="button_register" class="center">Submit</button></div>
</form>
</div>
<!-- End of Register Form -->';

// Check if the form was submitted
if (isset($_POST['course'])) {
    // Sanitize the input
    $course = htmlspecialchars($_POST['course']);
    $action = htmlspecialchars($_POST['action']);
    $id = (int)htmlspecialchars($_COOKIE['id']);

    // Register or Drop the student from the course
    if ($action === "register") {
        $confirm = registerCourse($id, $course);
    } else {
        $confirm = dropCourse($id, $course);
    }

    if ($confirm) {
        echo "<script>alert('You have successfully $action from $course.'); window.location='index.php'</script>";
    } else {
        echo "<script>alert('You could not be $action from $course.'); window.location='index.php'</script>";
    }
}

// Add Footer
include 'footer.php';

?>