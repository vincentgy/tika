<?php
include_once "./models/chat.php";
include_once "./models/session.php";

$host = '18.222.175.208'; //host
$port = '9527'; //port
$null = null; //null var
$conn = mysqli_connect('localhost', 'root', 'r00t', 'tikadb');
// Check connection
if($conn === false){
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

//Create TCP/IP sream socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//reuseable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
//bind socket to specified host
socket_bind($socket, 0, $port);
//listen to port
socket_listen($socket);
//create & add listning socket to the list
$clients = array($socket);
$users  = array();
$rooms  = array();
$histIndexes = array();

class OPCODE {
	const CLIENTID = 1;
	const CHATLIST = 2;
	const NEWROOM = 3;
	const JOIN   =4;
	const HIST   =5;
	const NEWMSG = 6;
	const OLDMSG = 7;
	const LASTSEEN = 8;
}

function pack64le ($x) {
    $r = '';

    for ($i = 8; $i--;) {
        $r .= chr($x & 255);
        $x >>= 8;
    }

    return $r;
};

function unpack64le($x) {
    $r = 0;

    for ($i = 8; $i--;) {
        $r = (($r << 8) >> 0) + ord($x[$i]);
    }

    return $r;
};

function pack32le ($x) {
    $r = '';

    for ($i = 4; $i--;) {
        $r .= chr($x & 255);
        $x >>= 8;
    }

    return $r;
};

function unpack32le($x) {
    $r = 0;

    for ($i = 4; $i--;) {
        $r = (($r << 8) >> 0) + ord($x[$i]);
    }

    return $r;
};

function pack16le($x) {
    $r = '';

    for ($i = 2; $i--;) {
        $r .= chr($x & 255);
        $x >>= 8;
    }

    return $r;
};

function hex_dump($data, $newline="\n")
{
  static $from = '';
  static $to = '';

  static $width = 16; # number of bytes per line

  static $pad = '.'; # padding for non-visible characters

  if ($from==='')
  {
    for ($i=0; $i<=0xFF; $i++)
    {
      $from .= chr($i);
      $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
    }
  }

  $hex = str_split(bin2hex($data), $width*2);
  $chars = str_split(strtr($data, $from, $to), $width);

  $offset = 0;
  foreach ($hex as $i => $line)
  {
    echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
    $offset += $width;
  }
}

function unpack16le($x) {
    $r = 0;

    for ($i = 2; $i--;) {
        $r = (($r << 8) >> 0) + ord($x[$i]);
    }

    return $r;
};

function disconnect($socket) {
	global $clients;
	unset($clients[$socket]);
	//send_message_to_socks($response, $clients);
	echo $socket . 'disconnected'. "\n";
}

function cmd($msg) {
    $buf = chr($msg['opcode']);
    switch ($msg['opcode']) {
    case OPCODE::CHATLIST:
        $buf .= pack16le(count($msg['chatList']));
        for($i = 0; $i < count($msg['chatList']); $i++) {
            $buf .= pack32le($msg['chatList'][$i]);
        }
    break;
    case OPCODE::NEWROOM:
        $buf .= pack32le($msg['chatId']);
    break;
    case OPCODE::JOIN:
        $buf .= pack32le($msg['chatId']);
        $buf .= pack32le($msg['userId']);
    break;
    case OPCODE::LASTSEEN:
        $buf .= pack32le($msg['chatId']);
        $buf .= pack32le($msg['userId']);
        $buf .= pack64le($msg['messageId']);
    break;
    case OPCODE::NEWMSG:
    case OPCODE::OLDMSG:
        $buf .= pack32le($msg['chatId']);
        $buf .= pack32le($msg['userId']);
        $buf .= pack16le(strlen($msg['message']));
        $buf .= $msg['message'];
    break;
    }
    hex_dump($buf);
    return $buf;
}

function parse_cmd($cmdq) {
	$r = array();
	$opcode = ord($cmdq[0]);
	$r['opcode'] = $opcode;
	switch ($opcode) {
		case OPCODE::CLIENTID:
			$len = unpack16le(substr($cmdq, 1, 2));
			$token = substr($cmdq, 3, $len);
			$r['token'] = $token;
		break;
		case OPCODE::NEWROOM:
			$count = unpack16le(substr($cmdq, 1, 2));
			$users = array();
			for($i = 0; $i < $count; $i++) {
				$uId = unpack32le(substr($cmdq, 3 + ($i * 4), 4));
				$users[] = $uId;
			}
			$r['users'] = $users;
		break;
		case OPCODE::JOIN:
			$chatId = unpack32le(substr($cmdq, 1, 4));
			$userId = unpack32le(substr($cmdq, 5, 4));
			$r['chatId'] = $chatId;
			$r['userId'] = $userId;
		break;
		case OPCODE::HIST:
			$chatId = unpack32le(substr($cmdq, 1, 4));
			$userId = unpack32le(substr($cmdq, 5, 4));
			$count = unpack16le(substr($cmdq, 9, 2));
			$r['chatId'] = $chatId;
			$r['userId'] = $userId;
			$r['count'] = $count;
		break;
		case OPCODE::NEWMSG:
		case OPCODE::OLDMSG:
			$chatId = unpack32le(substr($cmdq, 1, 4));
			$userId = unpack32le(substr($cmdq, 5, 4));
			$len = unpack16le(substr($cmdq, 9, 2));
			$r['chatId'] = $chatId;
			$r['userId'] = $userId;
			$r['message'] = substr($cmdq, 11, $len);
		break;
		case OPCODE::LASTSEEN:
			$chatId = unpack32le(substr($cmdq, 1, 4));
			$userId = unpack32le(substr($cmdq, 5, 4));
			$messageId = unpack64le(substr($cmdq, 9, 8));
			$r['chatId'] = $chatId;
			$r['userId'] = $userId;
			$r['messageId'] = $messageId;
		break;
	}
	return $r;
}

//handle system messages.
function handle_msg($msg, $socket) {
	global $conn, $users, $rooms, $histIndexes;

	if (!isset($msg['opcode'])) {
		return;
	}
	switch ($msg['opcode']) {
		case OPCODE::CLIENTID:
			$userId = SESSION::getuseridbytoken($conn, $msg['token']);
			if (!array_key_exists($userId, $users)) {
				$users[$userId] = array();
			}
			echo 'USER:'.$userId." FETCH CHAT LIST\n";
			$users[$userId][] = $socket;
			$chats = CHAT::getchatlist($conn, $userId);
			foreach ($chats as $cId) {
				$chat_users = CHAT::getparticipants($conn, $cId);
				if (!array_key_exists($msg['chatId'], $rooms)) {
					$rooms[$msg['chatId']] = array();
				}
				$histKey = $msg['chatId'].'#'.$msg['userId'];

				if (!array_key_exists($histKey, $histIndexes)) {
					$histIndexes[$histKey] = array();
				}
				$sid = (int) $socket;
				$histIndexes[$histKey][$sid] = 0;
				echo 'USER:'.$msg['userId'].' JOIN ROOM '. $msg['chatId']. "\n";
				$rooms[$msg['chatId']][] = $socket;
				foreach ($chat_users as $userId) {
					$cmd_str = cmd(array('opcode' => OPCODE::JOIN, 'chatId' => $msg['chatId'], 'userId' => $userId));
					$response = mask($cmd_str);
					send_message($response, $socket);
				}
				$cmd_str = cmd(array('opcode' => OPCODE::JOIN, 'chatId' => $msg['chatId'], 'userId' => 0));
				$response = mask($cmd_str);
				send_message($response, $socket);
				$messages = CHAT::getnewmessages($conn, $msg['chatId'], $msg['userId']);
				foreach ($messages as $nmsg) {
					$cmd_str = cmd(array('opcode' => OPCODE::NEWMSG, 'chatId' => $nmsg['chat_id'], 'userId' => $nmsg['user_id'], 'messageId' => $nmsg['id'], 'message' => $nmsg['message'], 'timestamp' => $nmsg['timestamp']));
					$response = mask($cmd_str);
					send_message($response, $socket);
				}
			}
		break;
		case OPCODE::NEWROOM:
			$chatId = CHAT::create($conn, $msg['users']);
			$cmd_str = cmd(array('opcode' => OPCODE::NEWROOM, 'chatId' => $chatId));
			$response = mask($cmd_str);
			foreach ($msg['users'] as $userId) {
				send_message_to_user($response, $userId);
			}
		break;
		case OPCODE::JOIN:
			$chat_users = CHAT::getparticipants($conn, $msg['chatId']);
			if (!array_key_exists($msg['chatId'], $rooms)) {
				$rooms[$msg['chatId']] = array();
			}
			$histKey = $msg['chatId'].'#'.$msg['userId'];

			if (!array_key_exists($histKey, $histIndexes)) {
				$histIndexes[$histKey] = array();
			}
			$sid = (int) $socket;
			$histIndexes[$histKey][$sid] = 0;
			echo 'USER:'.$msg['userId'].' JOIN ROOM '. $msg['chatId']. "\n";
			$rooms[$msg['chatId']][] = $socket;
			foreach ($chat_users as $userId) {
				$cmd_str = cmd(array('opcode' => OPCODE::JOIN, 'chatId' => $msg['chatId'], 'userId' => $userId));
				$response = mask($cmd_str);
				send_message($response, $socket);
			}
			$cmd_str = cmd(array('opcode' => OPCODE::JOIN, 'chatId' => $msg['chatId'], 'userId' => 0));
			$response = mask($cmd_str);
			send_message($response, $socket);
			$messages = CHAT::getnewmessages($conn, $msg['chatId'], $msg['userId']);
			foreach ($messages as $nmsg) {
				$cmd_str = cmd(array('opcode' => OPCODE::NEWMSG, 'chatId' => $nmsg['chat_id'], 'userId' => $nmsg['user_id'], 'messageId' => $nmsg['id'], 'message' => $nmsg['message'], 'timestamp' => $nmsg['timestamp']));
				$response = mask($cmd_str);
				send_message($response, $socket);
			}
		break;
		case OPCODE::NEWMSG:
			$mId = CHAT::addchatmessage($conn, $msg['chatId'], $msg['userId'], $msg['message']);
			CHAT::updatelastseen($conn, $msg['chatId'], $msg['userId'], $mId);
			$cmd_str = cmd(array('opcode' => OPCODE::LASTSEEN, 'chatId' => $msg['chatId'], 'userId' => $msg['userId'], 'messageId' => $mId));
			$response = mask($cmd_str);
			send_message_to_user($response, $msg['userId']);
			//update broadcast NEWMSG to room.
			$cmd_str = cmd(array('opcode' => OPCODE::NEWMSG, 'chatId' => $msg['chatId'], 'userId' => $msg['userId'], 'messageId' => $mId, 'message' => $msg['message'], 'timestamp' => time()));
			$response = mask($cmd_str);
			send_message_to_room($response, $msg['chatId']);
			echo 'USER:'.$msg['userId'].' SEND MESSAGE ' . $msg['message'].  ' TO ROOM '. $msg['chatId']. "\n";
		break;
		case OPCODE::LASTSEEN:
			CHAT::updatelastseen($conn, $msg['chatId'], $msg['userId'], $msg['messageId']);
			$cmd_str = cmd(array('opcode' => OPCODE::LASTSEEN, 'chatId' => $msg['chatId'], 'userId' => $msg['userId'], 'messageId' => $msg['messageId']));
			$response = mask($cmd_str);
			send_message_to_user($response, $msg['userId']);
			echo 'USER:'.$msg['userId'].' SET LAST SEEN ' . $msg['messageId'].  ' TO ROOM '. $msg['chatId']. "\n";
		break;
		case OPCODE::HIST:
			$histKey = $msg['chatId'].'#'.$msg['userId'];
			$sid = (int) $socket;
			$messages = CHAT::gethistmessages($conn, $msg['chatId'], $msg['userId'], $histIndexes[$histKey][$sid], $msg['count']);
			$lastMsgId = 0;
			foreach ($messages as $nmsg) {
				$cmd_str = cmd(array('opcode' => OPCODE::OLDMSG, 'chatId' => $nmsg['chat_id'], 'userId' => $nmsg['user_id'], 'messageId' => $nmsg['id'], 'message' => $nmsg['message'], 'timestamp' => $nmsg['timestamp']));
				$response = mask($cmd_str);
				send_message($response, $socket);
				$lastMsgId = $nmsg['id'];
			}
			$histIndexes[$histKey][$sid] = $lastMsgId;
		break;
	}
}

//start endless loop, so that our script doesn't stop
while (true) {
	//manage multipal connections
	$changed = $clients;
	//returns the socket resources in $changed array
	socket_select($changed, $null, $null, 0, 10);
	//check for new socket
	if (in_array($socket, $changed)) {
		$socket_new = socket_accept($socket); //accpet new socket
		$clients[] = $socket_new; //add socket to client array

		$header = socket_read($socket_new, 1024); //read data sent by the socket
		perform_handshaking($header, $socket_new, $host, $port); //perform websocket handshake

		socket_getpeername($socket_new, $ip); //get ip address of connected socket

		//make room for new socket
		$found_socket = array_search($socket, $changed);
		unset($changed[$found_socket]);
	}
	//loop through all connected sockets
	foreach ($changed as $changed_socket) {
		//check for any incomming data
		while (socket_recv($changed_socket, $buf, 2048, 0) >= 1) {
			if (strlen($buf) < 9) {
				disconnect($changed_socket);
			}
			else {
				$received_text = unmask($buf); //unmask data

				$msg = parse_cmd($received_text); //json decode
				var_dump($msg);
				handle_msg($msg, $changed_socket);
			}
			break 2; //exist this loop

		}

		/*$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if ($buf === false) { // check disconnected client
			// remove client for $clients array
			$found_socket = array_search($changed_socket, $clients);
			socket_getpeername($changed_socket, $ip);
			unset($clients[$found_socket]);
			//notify all users about disconnected connection
			$response = mask(json_encode(array('type' => 'system', 'message' => 5 . ' disconnected')));
			//send_message_to_socks($response, $clients);
			echo $found_socket . 'disconnected'. "\n";
		}*/
	}
}
// close the listening socket
socket_close($sock);

function send_message_to_user($msg, $userId)
{
	global $users;
	return send_message_to_socks($msg, $users[$userId]);
}

function send_message_to_room($msg, $roomId)
{
	global $rooms;
	return send_message_to_socks($msg, $rooms[$roomId]);
}

function send_message_to_socks($msg, $socks)
{
	foreach ($socks as $changed_socket) {
		@socket_write($changed_socket, $msg, strlen($msg));
	}
	return true;
}
function send_message($msg, $socket)
{
	@socket_write($socket, $msg, strlen($msg));
	return true;
}
//Unmask incoming framed message
function unmask($text)
{
	$length = ord($text[1]) & 127;
	if ($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	} elseif ($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	} else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text.= $data[$i] ^ $masks[$i % 4];
	}
	return $text;
}
//Encode message for transfer to client.
function mask($text)
{
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);

	if ($length <= 125) $header = pack('CC', $b1, $length);
	elseif ($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
	elseif ($length >= 65536) $header = pack('CCNN', $b1, 127, $length);
	return $header . $text;
}
//handshake new client.
function perform_handshaking($receved_header, $client_conn, $host, $port)
{
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
	foreach ($lines as $line) {
		$line = chop($line);
		if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
			$headers[$matches[1]] = $matches[2];
		}
	}

	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	//hand shaking header
	$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" . "Upgrade: websocket\r\n" . "Connection: Upgrade\r\n" . "WebSocket-Origin: $host\r\n" . "WebSocket-Location: ws://$host:$port/demo/shout.php\r\n" . "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($client_conn, $upgrade, strlen($upgrade));
}
