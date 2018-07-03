<?php
require_once __DIR__ ."../config.php";

class USER
{

    static function checkuser($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $email, $password);
            if(mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_close($stmt);
                    $r = true;
                }
            }
            else {
                echo("Error description: " . mysqli_error($con));
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_close($link);
        return $r;
    }

    static function checkemail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $email);
            if(mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_close($stmt);
                    $r = true;
                }
            }
            else {
                echo("Error description: " . mysqli_error($con));
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_close($link);
        return $r;
    }

    static function adduser($email, $password)
    {
        if (USER::checkemail($email) === true) {
            return false;
        }
        $r = false;

        $sql = "INSERT INTO users (email, password, lastlogin) VALUES (?,?, UNIX_TIMESTAMP())";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $email, $password);
            if(mysqli_stmt_execute($stmt)){
                $r = true;
            }
            else {
                $r = false;
            }
            mysqli_stmt_close($stmt);
        }
        else {
            echo("Error description: " . mysqli_error($con));
            $r = false;
        }
        mysqli_close($link);
        return $r;
    }
}
?>
