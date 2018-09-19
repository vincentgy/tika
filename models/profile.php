<?php

class PROFILE
{

    static function update($link, $userid, $description, $phone, $skills, $qualifications, $experiences) {
        $sql = "UPDATE users SET description=?, phone=?, skills=? WHERE id = ?";
        $r = false;
        $skills = explode(",", $skills);
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssi", $description, $phone, $skills, $userid);
            if(mysqli_stmt_execute($stmt)){
                $r = true;
            }
            else {
                $r = false;
            }
            mysqli_stmt_close($stmt);
            foreach($qualifications as $item) {
                PROFILE::addqualification($link, $userid, $item['degree'], $item['school'], $item['major'], $item['start'], $item['end']);
            }
            foreach($experiences as $item) {
                PROFILE::addexperience($link, $userid, $item['place'], $item['task'], $item['start'], $item['end']);
            }       
        }
        else {
            echo("Error description: " . mysqli_error($link));
            $r = false;
        }

        return $r;
    }

    static function get($link, $userid) {
        $sql = "SELECT email, name, description, phone, skills, avatar FROM users WHERE id=?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $userid);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    $r = $row;
                    $r['qualifications'] = PROFILE::getqualificationsbyuser($link, $userid);
                    $r['experiences'] = PROFILE::getexperiencesbyuser($link, $userid);
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

    static function getqualificationsbyuser($link, $userid) {
        $sql = "SELECT * FROM qualifications WHERE user_id = ? ORDER BY start DESC";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $userid);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $rows[] = $row;
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        error_log(print_r($rows, true));
        return $rows;
    }

    static function addqualification($link, $userid, $degree, $school, $major, $start, $end)
    {
        $r = false;
        $sql = "INSERT INTO qualifications (user_id, degree, school, major, start, end) VALUES (?,?,?,?,?,?)";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "isssss", $userid, $degree, $school, $major, $start, $end);
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


    static function updatequalification($link, $id, $degree, $school, $major, $start, $end)
    {
        $r = false;
        $sql = "UPDATE qualifications SET degree=?, school=?, major=?, start=?, end=? WHERE id = ?";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssi", $degree, $school, $major, $start, $end, $id);
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

    static function getexperiencesbyuser($link, $userid) {
        $sql = "SELECT * FROM work_experience WHERE user_id = ? ORDER BY start DESC";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $userid);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $rows[] = $row;
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        error_log(print_r($rows, true));
        return $rows;
    }

    static function addexperience($link, $userid, $place, $task, $start, $end)
    {
        $r = false;
        $sql = "INSERT INTO work_experience (user_id, place, task, start, end) VALUES (?,?,?,?,?)";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "issss", $userid, $place, $task, $start, $end);
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

    static function updateexperience($link, $id, $place, $task, $start, $end)
    {
        $r = false;
        $sql = "UPDATE work_experience SET place=?, task=?, start=?, end=? WHERE id=?";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssi", $place, $task, $start, $end, $id);
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
