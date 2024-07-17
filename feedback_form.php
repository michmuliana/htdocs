<?php

declare(strict_types=1);

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get parameters from the form and sanitize them
    $id = htmlspecialchars($_COOKIE['id']);
    $course = htmlspecialchars($_POST['course']);
    $feedback = htmlspecialchars($_POST['feedback']);

    // Send the feedback to the database
    include 'database_requests.php';

    $sent = sendFeedback($id, $course, $feedback);

    closeConnection();
}

if ($sent) {
    // Alert the user that the feedback was sent
    echo '<script>alert("Feedback sent successfully!"); window.location="index.php"</script>';
} else {
    // Alert the user that the feedback was not sent
    echo '<script>alert("Feedback not sent. Please try again."); window.location="index.php"</script>';
}

?>