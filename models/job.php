<?php
include_once("geometry.php");

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

        return $rows;
    }

    static function addjobcategory($link, $job_id, $category_id) {
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

        return $r;
    }

    static function addjob($link, $title, $company, $description, $user_id, $type, $pay_type, $minimum_pay, $maximum_pay, $number, $region_id, $district_id, $location, $categories) {
        $r = false;
        $maxmum_pay = is_null($minimum_pay) ? $minimum_pay : $minimum_pay;
        $number = is_null($number) ? 1 : $number;
        $address = Address::getaddress($link, $location, $district_id, $region_id);
        error_log(print_r($address, true));
        $geo = Geometry::covertToLocation($address);
        error_log(print_r($geo, true));

        $sql = "INSERT INTO positions (title, company, description, user_id, type, pay_type, minimum_pay, maximum_pay, numbers, region_id, district_id, location, latitude, longitude, timestamp) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,UNIX_TIMESTAMP())";

        if (USER::checkuserid($link, $user_id) === false) {
            return $r;
        }

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssiiiiiiiisdd", $title, $company, $description, $user_id, $type, $pay_type, $minimum_pay, $maximum_pay, $number, $region_id, $district_id, $location, $geo->latitude, $geo->longitude);
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

        return $r;
    }

    static function getcategoriesbyposition($link, $position) {
        $sql = "SELECT * FROM position_category WHERE position_id = ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $position);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $rows[] = $row;
                }
                // Free result set
                mysqli_free_result($result);
            }
        }

        return $rows;
    }

    static function generatesql($query) {
        $sql = 'SELECT * FROM postions JOIN position_category on postions.id = position_category.position_id';
        $where = ' WHERE 1';

        foreach ($query as $key => $val) {
            switch ($key) {
                case 'title':
                    $where .= ' AND title LIKE  "%'.$val.'%"';
                break;
                case 'company':
                    $where .= ' AND company LIKE  "%'.$val.'%"';
                break;
                case 'description':
                    $where .= ' AND description LIKE  "%'.$val.'%"';
                break;
                case 'type':
                    $where .= ' AND type = '.$val;
                break;
                case 'pay_type':
                    $where .= ' AND pay_type = '.$val;
                break;
                case 'minimum_pay':
                    $where .= ' AND minimum_pay >= '.$val;
                break;
                case 'maximum_pay':
                    $where .= ' AND maximum_pay <= '.$val;
                break;
                case 'region_id':
                    $where .= ' AND region_id = '.$val;
                break;
                case 'district_id':
                    $where .= ' AND district_id = '.$val;
                break;
                case 'location':
                    $where .= ' AND location LIKE  "%'.$val.'%"';
                break;
                case 'category_ids':
                    $where .= ' AND category_id IN "('.implode(',', $val).')"';
                break;
            }
        }
    }

    static function searchjobs($link, $query, $location) {
        $rows = [];
        $sql = JOB::generatesql($query);
        error_log(print_r($sql, true));
        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $region_id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    if (isset($query['distance']) && isset($location['latitude']) && isset($location['longitude'])) {
                        $dist = Geometry::distance($row['latitude'], $row['longitude'], $location['latitude'], $location['longitude']);
                        if ($dist <= $query['distance']) {
                            $rows[] = $row;
                        }
                    }
                    else {
                        $rows[] = $row;
                    }
                }
                // Free result set
                mysqli_free_result($result);
            }
        }

        return $rows;
    }

    static function searchjobsbyuser($link, $userid) {
        $sql = "SELECT * FROM regions WHERE id = ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $row['categories'] = JOB::getcategoriesbyposition($link, $row['id']);
                    $rows[] = $row;
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