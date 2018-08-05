<?php

class APPLICATION
{

    static function checkapplication($link, $userid, $positionid) {
        $sql = "SELECT * FROM applications WHERE user_id = ? AND position_id = ?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ii", $userid, $positionid);
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

    static function addapplication($link, $userid, $positionid)
    {
        $r = false;
        $sql = "INSERT INTO applications (user_id, position_id, timestamp) VALUES (?,?,UNIX_TIMESTAMP())";

        if (APPLICATION::checkapplication($userid, $positionid) === true) {
            return $r;
        }

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $userid, $positionid);
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
