<?php

declare(strict_types=1);

require 'database_requests.php';

// Get parameters from the form and sanitize them
$username = (int)htmlspecialchars($_POST['username']);
$password = htmlspecialchars($_POST['password']);
$passHash = md5($password);

$studentInfo = getStudentInfo($username);
$storedPassHash = (string)getPasswordHash($username);
$id = $username;
$firstName = $studentInfo['firstName'];
$lastName = $studentInfo['lastName'];

// Check if the password is correct
if ($passHash == $storedPassHash) {
    // Set a cookie with the user's id and password hash
    $cookieValue = md5($id . $passHash);
    setcookie('user', $cookieValue, time() + (86400 * 30), '/');
    setcookie('id', (string)$id, time() + (86400 * 30), '/');

    // Set a cookie with the user's first and last name
    setcookie('firstName', $firstName, time() + (86400 * 30), '/');
    setcookie('lastName', $lastName, time() + (86400 * 30), '/');

    // Redirect to the home page
    header('Location: index.php');
} else {
    // Redirect to the login page
    header('Location: login.php');
}

closeConnection();

?>