<?php

declare(strict_types=1);

// Add Header
include 'header.php';
echo getHeader('Home');

// Check if the user is logged in
if (isset($_COOKIE['user'])) {
    $firstname = htmlspecialchars($_COOKIE['firstName']);
    echo '<h1>Welcome ' . $firstname . '</h1>';
} else {
    echo '<h1>Welcome to the Student Website</h1>';
}

// Add Footer
include 'footer.php';

?>