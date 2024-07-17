<?php

declare(strict_types=1);

require 'database_requests.php';

validateCookie(); // Check if the user is logged in

// Get user id from the cookie and sanitize it
$id = (int)htmlspecialchars($_COOKIE['id']);

// Get the classList from the database
$classList = getClassList($id);

// Format the classList as a string
$classListFormatted = '<select id="course" name="course" required>';
foreach ($classList as $class) {
    $classListFormatted .= '<option value="' . $class['course'] .
        '">' . $class['course'] . '</option>';
}
$classListFormatted .= '</select>';

// Add Header
include 'header.php';
echo getHeader('Feedback');

// Add Feedback Form
echo '<!-- Feedback Form -->
<div class="flex center">
<form id="feedback_form" action="feedback_form.php" method="post">
    <h1>Feedback</h1>
    <label for="course">Course:</label>
    ' . $classListFormatted . '
    <div><textarea id="feedback" name="feedback" placeholder="Enter your feedback here" required></textarea></div>
    <div><button id="button_feedback" class="center">Submit</button></div>
</form>
</div>
<!-- End of Feedback Form -->';

// Add Footer
include 'footer.php';

?>