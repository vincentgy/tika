<?php

class CHAT
{
    static function addchatuser($link, $chat_id, $user_id) {
        $r = false;
        $sql = "INSERT INTO chat_users (chat_id, user_id) VALUES (?,?)";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $chat_id, $user_id);
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

    static function create($link, $users) {
        $r = false;
        $sql = "INSERT INTO chats (created_at) VALUES (UNIX_TIMESTAMP())";

        if($stmt = mysqli_prepare($link, $sql)) {
            if(mysqli_stmt_execute($stmt)){
                $new_id = mysqli_insert_id($link);
                if ($new_id !== false) {
                    foreach($users as $user) {
                        CHAT::addchatuser($link, $new_id, $user);
                    }
                    $r = $new_id;
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

    static function addchatmessage($link, $chat_id, $user_id, $message) {
        $r = false;
        $sql = "INSERT INTO chat_messages (chat_id, user_id, message, timestamp) VALUES (?,?,?, UNIX_TIMESTAMP())";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iis", $chat_id, $user_id, $message);
            if(mysqli_stmt_execute($stmt)){
                 $new_id = mysqli_insert_id($link);
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

    static function updatelastseen($link, $chat_id, $user_id, $message_id)
    {
        $r = false;
        $sql = "UPDATE chat_users SET last_seen=? WHERE chat_id=? AND user_id=?";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $message_id, $chat_id, $user_id);
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

    static function getchatlist($link, $user_id) {
        $sql = "SELECT DISTINCT(chat_id) FROM chat_users WHERE user_id = ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $chat_id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                while ($row=mysqli_fetch_assoc($result)) {
                    $rows[] = $row['chat_id'];
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        return $rows;
    }

    static function getparticipants($link, $chat_id) {
        $sql = "SELECT user_id FROM chat_users WHERE chat_id = ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $chat_id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                while ($row=mysqli_fetch_assoc($result)) {
                    $rows[] = $row['user_id'];
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        return $rows;
    }

    static function getnewmessages($link, $chat_id, $user_id, $last_seen) {
        $sql = "SELECT * FROM chat_messages WHERE chat_id = ? AND user_id = ? id > ? ORDER BY id DESC";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "iii", $chat_id, $user_id, $last_seen);
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
}
?>
