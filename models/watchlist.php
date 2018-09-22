<?php
include_once "job.php";

class WATCHLIST
{

    static function checkexisted($link, $userid, $positionid) {
        $sql = "SELECT * FROM watchlist WHERE user_id = ? AND position_id = ?";
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

    static function addwatch($link, $userid, $positionid)
    {
        $r = false;
        $sql = "INSERT INTO watchlist (user_id, position_id, timestamp) VALUES (?,?,UNIX_TIMESTAMP())";

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

    static function deletewatch($link, $id)
    {
        $r = false;
        $sql = "DELETE FROM watchlist WHERE id=?";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
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

    static function deletewatchbyposition($link, $userid, $positionid)
    {
        $r = false;
        $sql = "DELETE FROM watchlist WHERE user_id=? AND position_id=?";

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

    static function getwatchlistbyuser($link, $userid) {
        $sql = "SELECT position_id, timestamp FROM watchlist WHERE user_id = ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $userid);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $r = JOB::searchjobbyid($link, $row['position_id']);
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
