<?php

declare(strict_types=1);

// Check if the user is logged in
if (isset($_COOKIE['user'])) {
    // Remove the cookies
    setcookie('user', '', time() - 3600);
    setcookie('id', '', time() - 3600);
    setcookie('firstName', '', time() - 3600);
    setcookie('lastName', '', time() - 3600);
}

// Redirect to the login page
echo '<script> alert("You have successfully logged out."); window.location="login.php"</script>';

?>