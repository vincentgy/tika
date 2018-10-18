<?php
include_once "user.php";
include_once "profile.php";

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

    static function countapplicationsbyjob($link, $positionid) {
        $sql = "SELECT COUNT(*) AS count applications WHERE position_id = ?";
        $count = 0;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $positionid);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if (mysqli_num_rows($result) == 1) {
                    if ($row = mysqli_fetch_assoc($result)) {
                        $count = $row['count'];
                    }
                }
                // Free result set
                mysqli_free_result($result);
            }
            mysqli_stmt_close($stmt);
        }
        return $count;
    }

    static function getapplicationsbyjob($link, $positionid) {
        $sql = "SELECT user_id, timestamp FROM applications WHERE position_id = ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $positionid);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $r = USER::getuserbyid($link, $row['user_id']);
                    $r['qualifications'] = PROFILE::getqualificationsbyuser($link, $row['user_id']);
                    $r['experiences'] = PROFILE::getexperiencesbyuser($link, $row['user_id']);
                    $r['timestamp'] = $row['timestamp'];
                    $rows[] = $r;
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        error_log(print_r($rows, true));
        return $rows;
    }
}
?>
