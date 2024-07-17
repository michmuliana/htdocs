<?php

declare(strict_types=1);

function getHeader($title = "Student Website") {
    /*
    * Function to generate the header for the website
    * @param string $title - The title of the page
    * @return string - The header for the website
    */
    $id = '';
    $firstName = '';
    $lastName = '';

    $login_logout = '<li><a href="login.php">Login</a></li>';
    $logged_in_options = '';
    $user_details = '';

    // Check if the user is logged in
    if (isset($_COOKIE['user'])) {
        // Add Options for logged in users
        $login_logout = '<li><a href="logout.php">Logout</a></li>';
        $logged_in_options = '<li><a href="grades.php">Grades</a></li>
        <li><a href="classList.php">Class List</a></li>
        <li><a href="feedback.php">Feedback</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="reset_pass.php">Reset Password</a></li>
        <li><a href="dropOut.php">Drop Out</a></li>';

        $id = $_COOKIE['id'];
        $firstName = $_COOKIE['firstName'];
        $lastName = $_COOKIE['lastName'];
        $user_details = '<li><a> ' . $firstName . ' ' . $lastName . ' | ' . $id . '</a></li>';
    }
    
    $header = '<!DOCTYPE html>
    <html>
    
    <head>
        <title>' . $title . '</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    
    <body>
        <!--Navigation bar-->
        <div id="nav-placeholder">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="directory.php">Directory</a></li>
                ' . $logged_in_options . '
                ' . $user_details . '
                ' . $login_logout . '
            </ul>
        </div>
        <!--end of Navigation bar-->';

    return $header;
}

?>