<?php
class JOB
{

    static function getcategories() {
        $sql = "SELECT * FROM categories";
        $r = false;
        $rows = [];
        require __DIR__ ."/../config.php";
        if($result = mysqli_query($link, $sql)) {
            while ($row=mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            // Free result set
            mysqli_free_result($result);
        }
        mysqli_close($link);
        return $rows;
    }

    static function getregionsbycountrycode($code = 'NZ') {
        $sql = "SELECT * FROM regions WHERE country_code = ?";
        $rows = [];
        require __DIR__ ."/../config.php";
        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $code);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $rows[] = $row;
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        mysqli_close($link);
        return $rows;
    }

    static function getdistrictsbyregion($region_id) {
        $sql = "SELECT * FROM districts WHERE region_id = ?";
        $rows = [];
        require __DIR__ ."/../config.php";
        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $region_id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $rows[] = $row;
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        mysqli_close($link);
        return $rows;
    }
}
?>