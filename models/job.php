<?php
include_once("geometry.php");
include_once("user.php");
include_once("application.php");

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

    static function deletejobcategory($link, $job_id) {
        $r = false;
        $sql = "DELETE FROM position_category WHERE position_id = ?";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $job_id);
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

    static function updatejob($link, $update) {
        $r = false;
        if (isset($update['position_id'])) {
            $sql = JOB::generateupdatesql($update);
             error_log(print_r($sql, true));
            if($stmt = mysqli_prepare($link, $sql)) {
                if(mysqli_stmt_execute($stmt)) {

                }
                // Free result set
                if (isset($update['category_ids'])) {
                    JOB::deletejobcategory($link, $update['position_id']);
                    for ($i = 0; $i < count($update['position_id']); $i++) {
                        JOB::addjobcategory($link, $update['position_id'], $update['position_id'][$i]);
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
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
            mysqli_stmt_close($stmt);
        }

        return $rows;
    }

    static function generateupdatesql($update) {
        $sql = 'UPDATE positions ';
        $set = '';
        $where = ' WHERE id='.$update['position_id'];
        foreach ($update as $key => $val) {
            switch ($key) {
                case 'title':
                    $set .= ' SET title ='.$val.',';
                break;
                case 'company':
                    $set .= ' SET company ='.$val.',';
                break;
                case 'description':
                    $set .= ' SET description ='.$val.',';
                break;
                case 'type':
                    $set .= ' SET type = '.$val.',';
                break;
                case 'pay_type':
                    $set .= ' SET pay_type = '.$val.',';
                break;
                case 'minimum_pay':
                    $set .= ' SET minimum_pay = '.$val.',';
                break;
                case 'maximum_pay':
                    $set .= ' SET maximum_pay = '.$val.',';
                break;
                case 'region_id':
                    $set .= ' SET region_id = '.$val.',';
                break;
                case 'district_id':
                    $set .= ' SET district_id ='.$val.',';
                break;
                case 'location':
                    $set .= ' SET location ='.$val.',';
                break;
            }
        }
        if (strlen($set) > 0)
        {
            $set = substr($set, 0, -1);
            $sql .= $set;
            $sql .= $where;
        } else {
            $sql = '';
        }
        return $sql;
    }

    static function generatesql($query) {
        $sql = 'SELECT * FROM positions JOIN position_category on positions.id = position_category.position_id';
        $where = ' WHERE 1';

        foreach ($query as $key => $val) {
            switch ($key) {
                case 'title':
                    $where .= ' AND (title LIKE  "%'.$val.'%"';
                break;
                case 'company':
                    $where .= ' OR company LIKE  "%'.$val.'%"';
                break;
                case 'description':
                    $where .= ' OR description LIKE  "%'.$val.'%")';
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
                case 'district_ids':
                    $where .= ' AND district_id IN ('.implode(',', $val).')';
                break;
                case 'location':
                    $where .= ' AND location LIKE  "%'.$val.'%"';
                break;
                case 'category_ids':
                    $where .= ' AND category_id IN ('.implode(',', $val).')';
                break;
            }
        }
        $sql .= $where;
        if (isset($query['pageSize']) && isset($query['currentPage'])) {
            $total = $query['currentPage'] * $query['pageSize'];
            $sql .= ' LIMI '. $total . ','. $query['pageSize'];
        }
        return $sql;
    }

    static function searchjobs($link, $query, $location) {
        $rows = [];
        $sql = JOB::generatesql($query);
        error_log(print_r($sql, true));
        if($stmt = mysqli_prepare($link, $sql)) {
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $row['region'] = Address::getregionbyId($link, $row['region_id']);
                    $row['district'] = Address::getdistrictbyId($link, $row['district_id']);
                    $row['poster'] = USER::getuserbyid($link, $row['user_id']);

                    if (isset($location['latitude']) && isset($location['longitude'])) {
                        $dist = Geometry::distance($row['latitude'], $row['longitude'], $location['latitude'], $location['longitude']);
                        error_log(print_r($dist, true));
                        $row['distance'] = $dist;
                        $rows[] = $row;
                    }
                    else {
                        $rows[] = $row;
                    }
                }
                // Free result set
                mysqli_free_result($result);
                mysqli_stmt_close($stmt);
            }

        }
        error_log(print_r($rows, true));
        return $rows;
    }

    // search jobs posted by a given user.
    static function checkowner($link, $position_id, $user_id) {
        $sql = "SELECT * FROM positions WHERE id = ? AND user_id = ?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ii", $position_id, $user_id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1) {
                    $r = true;
                }
                // Free result set
                mysqli_free_result($result);
            }
            mysqli_stmt_close($stmt);
        }
        error_log(print_r($rows, true));
        return $r;
    }

    // search jobs posted by a given user.
    static function searchjobsbyuser($link, $userid) {
        $sql = "SELECT * FROM positions WHERE user_id = ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $userid);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $row['categories'] = JOB::getcategoriesbyposition($link, $row['id']);
                    $row['application_number'] = APPLICATION::countapplicationsbyjob($link, $row['id']);
                    $rows[] = $row;
                }
                // Free result set
                mysqli_free_result($result);
            }
            mysqli_stmt_close($stmt);
        }
        error_log(print_r($rows, true));
        return $rows;
    }

    // search jobs posted by a given user.
    static function searchjobbyid($link, $id) {
        $sql = "SELECT * FROM positions WHERE id = ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $row['poster'] = USER::getuserbyid($link, $row['user_id']);
                    $row['categories'] = JOB::getcategoriesbyposition($link, $row['id']);
                    $rows[] = $row;
                }
                // Free result set
                mysqli_free_result($result);
            }
            mysqli_stmt_close($stmt);
        }
        error_log(print_r($rows, true));
        return $rows;
    }
}
?>