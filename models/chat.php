<?php
include_once("user.php");

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
                $r = $new_id;
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

    static function getchatlistinfo($link, $user_id) {
        $r = array();
        $chatList = CHAT::getchatlist($link, $user_id);
        $length = count($chatList);
        $chatListInfo = array();
        for ($x = 0; $x < $length; $x++) {
            $chatInfo = array();
            $userList = CHAT::getparticipants($link, $chatList[$x]);
            $chatInfo['id'] = $chatList[$x];
            $userListInfo = array();
            $uLength = count($userList);
            for ($y = 0; $y < $uLength; $y++) {
                $uInfo = USER::getuserbyid($link, $userList[$y]);
                $uInfo['id'] = $userList[$y];
                $userListInfo[] = $uInfo;
            }
            $chatInfo['participantList'] = $userListInfo;
            $chatListInfo[]= $chatInfo;
        }
        $r['myuserid'] = $user_id;
        $r['chatList'] = $chatListInfo;
        return $r;
    }

    static function getchatlist($link, $user_id) {
        $sql = "SELECT DISTINCT(chat_id) FROM chat_users WHERE user_id = ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $user_id);
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

    static function getlastseen($link, $chat_id, $user_id) {
        $sql = "SELECT last_seen FROM chat_users WHERE chat_id = ? AND user_id = ?";
        $r = 0;

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ii", $chat_id, $user_id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if($row=mysqli_fetch_assoc($result)) {
                    $r = $row['last_seen'];
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        return $r;
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

    static function getnewmessages($link, $chat_id, $user_id) {
        $last_seen = CHAT::getlastseen($link, $chat_id, $user_id);
        $sql = "SELECT * FROM chat_messages WHERE chat_id = ? AND id > ? ORDER BY id ASC";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ii", $chat_id, $last_seen);
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

    static function gethistmessages($link, $chat_id, $user_id, $start, $count) {
        $last_seen = CHAT::getlastseen($link, $chat_id, $user_id);
        $sql = ($start === 0) ? "SELECT * FROM chat_messages WHERE chat_id = ? ORDER BY id DESC LIMIT ?" :
                "SELECT * FROM chat_messages WHERE chat_id = ? AND id < ? ORDER BY id DESC LIMIT ?";
        $rows = [];

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            if ($start === 0) {
                mysqli_stmt_bind_param($stmt, "ii", $chat_id, $count);
            }
            else {
                mysqli_stmt_bind_param($stmt, "iii", $chat_id, $start, $count);
            }
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
