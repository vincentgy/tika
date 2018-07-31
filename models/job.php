<?php
include_once("location.php");

class JOB
{
    static function getcategories($link) {
        $sql = "SELECT * FROM categories";
        $r = false;
        $rows = [];

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

    static function gettypes($link) {
        $sql = "SELECT * FROM types";
        $r = false;
        $rows = [];

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

    static function getpaytypes($link) {
        $sql = "SELECT * FROM pay_types";
        $r = false;
        $rows = [];

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

    static function getregionsbycountrycode($link, $code = 'NZ') {
        $sql = "SELECT * FROM regions WHERE country_code = ?";
        $rows = [];

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

    static function getdistrictsbyregion($link, $region_id) {
        $sql = "SELECT * FROM districts WHERE region_id = ?";
        $rows = [];

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

    static function addjobcategory($link, $job_id, $category_id)
    {
        $r = false;
        $sql = "INSERT INTO position_category (position_id, category_id) VALUES (?,?)";

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

    static function addjob($link, $title, $company, $user_id, $type, $pay_type, $minimum_pay, $maximum_pay, $number, $region_id, $district_id, $location, $categories)
    {
        $r = false;
        $maxmum_pay = is_null($minimum_pay) ? $minimum_pay : $minimum_pay;
        $number = is_null($number) ? 1 : $number;
        $address = Address::getaddress($location, $district_id, $region_id);
        error_log(print_r($address, true));
        $geo = Geometry::covertToLocation($address);
        error_log(print_r($geo, true));

        $sql = "INSERT INTO positions (title, company, user_id, type, pay_type, minimum_pay, maximum_pay, numbers, region_id, district_id, location, latitude, longitude, timestamp) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,UNIX_TIMESTAMP())";

        if (USER::checkuserid($link, $user_id) === false) {
            return $r;
        }

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssiiiiiiiis", $title, $company, $user_id, $type, $pay_type, $minimum_pay, $maximum_pay, $number, $region_id, $district_id, $geo->latitude, $geo->longitude, $location);
            if(mysqli_stmt_execute($stmt)){
                $r = true;
                $jid = mysqli_insert_id($link);
                for ($i = 0; $i < count($categories); $i++) {
                    JOB::addjobcategory($link, $jid, $categories[$i]);
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