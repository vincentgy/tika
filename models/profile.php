<?php

class PROFILE
{

    static function update($link, $userid, $description, $phone, $skills, $qualifications, $experiences) {
        $sql = "UPDATE users SET description=?, phone=?, skills=? WHERE id = ?";
        $r = false;

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
                PROFILE::addapplication($link, $userid, $item['degree'], $item['school'], $item['major'], $item['start'], $item['end']);
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

    static function addapplication($link, $userid, $degree, $school, $major, $start, $end)
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
}
?>
