<?php
include_once("user.php");

class SESSION
{
    static function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min;
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1;
        $bits = (int) $log + 1;
        $filter = (int) (1 << $bits) - 1;
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    static function getToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[SESSION::crypto_rand_secure(0, $max-1)];
        }

        return $token;
    }

    static function checktoken($link, $token) {
        $sql = "SELECT * FROM sessions WHERE token = ?";
        $r = false;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $token);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    $t = time();
                    if ($t < $row['expiry_time']) {
                        // not expired
                        $r = true;
                    }
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

    static function addtoken($link, $email, $ipaddress)
    {
        $r = false;
        $token = SESSION::getToken(32);
        $user_id = USER::getuseridbyemail($link, $email);

        $sql = "INSERT INTO sessions(token, user_id, created_at, expiry_time, ipaddress) VALUES (?,?,UNIX_TIMESTAMP(), UNIX_TIMESTAMP() + 2592000,?)";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sis", $token, $user_id, $ipaddress);
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
        return ($r === false) ? $r : $token;
    }

    static function updatetoken($link, $token)
    {
        $r = false;
        $sql = "UPDATE sessions SET expiry_time = UNIX_TIMESTAMP() + 2592000 WHERE token = ?";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $token);
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
