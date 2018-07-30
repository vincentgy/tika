<?php
class JOB
{

    static function getcategories() {
        $sql = "SELECT * FROM categories";
        $r = false;
        $rows = [];
        require_once __DIR__ ."/../config.php";
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

    static function gettypes() {
        $sql = "SELECT * FROM types";
        $r = false;
        $rows = [];
        require_once __DIR__ ."/../config.php";
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

    static function getpaytypes() {
        $sql = "SELECT * FROM pay_types";
        $r = false;
        $rows = [];
        require_once __DIR__ ."/../config.php";
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
        require_once __DIR__ ."/../config.php";
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
        require_once __DIR__ ."/../config.php";
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

    static function addjobcategory($job_id, $category_id)
    {
        $r = false;
        $sql = "INSERT INTO position_category (position_id, category_id) VALUES (?,?)";

        require_once __DIR__ ."/../config.php";
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $job_id, $category_id);
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
        mysqli_close($link);
        return $r;
    }

    static function addjob($title, $company, $user_id, $type, $pay_type, $minimum_pay, $maximum_pay, $number, $region_id, $district_id, $location, $categories)
    {
        $r = false;
        $maxmum_pay = is_null($minimum_pay) ? $minimum_pay : $minimum_pay;
        $number = is_null($number) ? 1 : $number;

        $sql = "INSERT INTO positions (title, company, user_id, type, pay_type, minimum_pay, maximum_pay, numbers, region_id, district_id, location, timestamp) VALUES (?,?,?,?,?,?,?,?,?,?,?,UNIX_TIMESTAMP())";

        if (USER::checkuserid($user_id) === false) {
            return $r;
        }
        require_once __DIR__ ."/../config.php";
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssiiiiiiiis", $title, $company, $user_id, $type, $pay_type, $minimum_pay, $maximum_pay, $number, $region_id, $district_id, $location);
            if(mysqli_stmt_execute($stmt)){
                $r = true;
                $jid = mysqli_insert_id($link);
                for ($i = 0; $i < count($categories); $i++) {
                    JOB::addjobcategory($jid, $categories[$i]);
                }
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
        mysqli_close($link);
        return $r;
    }
}
?>