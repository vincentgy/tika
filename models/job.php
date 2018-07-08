<?php
class JOB
{

    static function getcategories() {
        $sql = "SELECT * FROM categories";
        $r = false;
        $rows = [];
        require __DIR__ ."/../config.php";
        if($result = mysqli_query($link, $sql)) {
            while ($row=mysqli_fetch_row($result, MYSQLI_ASSOC)) {
                $rows[] = $row;
            }
            // Free result set
            mysqli_free_result($result);
        }
        mysqli_close($link);
        return $rows;
    }
?>