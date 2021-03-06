<?php

class USER
{

    static function checkuser($link, $email, $password) {
        $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $email, $password);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    $r = $row['id'];
                    mysqli_stmt_close($stmt);
                }
            }
            else {
                echo("Error description: " . mysqli_error($link));
                mysqli_stmt_close($stmt);
            }
        }

        return $r;
    }

    static function getuseridbyemail($link, $email) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $email);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                // Get user
                if (mysqli_num_rows($result) == 1) {
                    if ($row = mysqli_fetch_assoc($result)) {
                        $r = $row['id'];
                        mysqli_stmt_close($stmt);
                    }
                }
            }
            else {
                echo("Error description: " . mysqli_error($link));
                mysqli_stmt_close($stmt);
            }
        }
        return $r;
    }

    static function getuserbyid($link, $userid) {
        $sql = "SELECT name, email, description, skills, avatar, title, company FROM users WHERE id = ?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $userid);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                // Get user
                if (mysqli_num_rows($result) == 1) {
                    if ($row = mysqli_fetch_assoc($result)) {
                        $r = $row;
                        mysqli_stmt_close($stmt);
                    }
                }
            }
            else {
                echo("Error description: " . mysqli_error($link));
                mysqli_stmt_close($stmt);
            }
        }

        return $r;
    }

    static function checkemail($link, $email) {
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
                echo("Error description: " . mysqli_error($link));
                mysqli_stmt_close($stmt);
            }
        }

        return $r;
    }

    static function checkuserid($link, $user_id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if(mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_close($stmt);
                    $r = true;
                }
            }
            else {
                echo("Error description: " . mysqli_error($link));
                mysqli_stmt_close($stmt);
            }
        }

        return $r;
    }

    static function adduser($link, $email, $password, $name)
    {
        $r = false;
        $sql = "INSERT INTO users (email, password, name, created_at, lastlogin) VALUES (?,?,?,UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";

        if (USER::checkemail($link, $email) === true) {
            return $r;
        }

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $email, $password, $name);
            if(mysqli_stmt_execute($stmt)){
                $r = true;
            }
            else {
                $r = false;
            }
            mysqli_stmt_close($stmt);
        }
        else {
            echo("Error description: " . mysqli_error($link));
            $r = false;
        }

        return $r;
    }


}

?>
