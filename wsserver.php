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

class OPCODE {
	const CLIENTID = 1;
	const CHATLIST = 2;
	const NEWROOM = 3;
	const JOIN   =4;
	const HIST   =5;
	const NEWMSG = 6;
	const OLDMSG = 7;
}


//handle system messages.
function handle_msg($msg, $socket) {
	global $conn, $users, $rooms;
	switch ($msg->opcode) {
		case OPCODE::CLIENTID:
			$userId = SESSION::getuseridbytoken($conn, $msg->token);
			if (!array_key_exists($userId, $users)) {
				$users[$userId] = array();
			}
			echo 'USER:'.$userId." FETCH CHAT LIST\n";
			$users[$userId][] = $socket;
			$chats = CHAT::getchatlist($conn, $userId);
			$response = mask(json_encode(array('opcode' => OPCODE::CHATLIST, 'chatList' => $chats)));
			send_message($response, $socket);
		break;
		case OPCODE::NEWROOM:
			$chatId = CHAT::create($conn, $msg->users);
			$response = mask(json_encode(array('opcode' => OPCODE::NEWROOM, 'chatId' => $chatId)));
			foreach ($msg->users as $userId) {
				send_message_to_user($response, $userId);
			}
		break;
		case OPCODE::JOIN:
			$chat_users = CHAT::getparticipants($conn, $msg->chatId);
			if (!array_key_exists($msg->chatId, $rooms)) {
				$rooms[$msg->chatId] = array();
			}
			echo 'USER:'.$msg->userId.' JOIN ROOM '. $msg->chatId. "\n";
			$rooms[$msg->chatId][] = $socket;
			foreach ($chat_users as $userId) {
				$response = mask(json_encode(array('opcode' => OPCODE::JOIN, 'chatId' => $msg->chatId, 'userId' => $userId)));
				send_message($response, $socket);
			}
			$response = mask(json_encode(array('opcode' => OPCODE::JOIN, 'chatId' => $msg->chatId, 'userId' => 0)));
			send_message($response, $socket);
			$messages = CHAT::getnewmessages($conn, $msg->chatId, $msg->userId);
			foreach ($messages as $msg) {
				$response = mask(json_encode(array('opcode' => OPCODE::NEWMSG, 'chatId' => $msg['chat_id'], 'userId' => $msg['user_id'], 'message' => $msg['message'], 'timestamp' => $msg['timestamp'])));
			}
		break;
		case OPCODE::NEWMSG:
			CHAT::addchatmessage($conn, $msg->chatId, $msg->userId, $msg->message);
			$response = mask(json_encode(array('opcode' => OPCODE::NEWMSG, 'chatId' => $msg->chatId, 'userId' => $msg->userId, 'message' => $msg->message, 'timestamp' => time())));
			send_message_to_room($response, $msg->chatId);
			echo 'USER:'.$msg->userId.' SEND MESSAGE ' . $msg->message.  ' TO ROOM '. $msg->chatId. "\n";
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
		while (socket_recv($changed_socket, $buf, 1024, 0) >= 1) {
			$received_text = unmask($buf); //unmask data
			$msg = json_decode($received_text); //json decode

			handle_msg($msg, $changed_socket);
			break 2; //exist this loop

		}

		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if ($buf === false) { // check disconnected client
			// remove client for $clients array
			$found_socket = array_search($changed_socket, $clients);
			socket_getpeername($changed_socket, $ip);
			unset($clients[$found_socket]);
			//notify all users about disconnected connection
			$response = mask(json_encode(array('type' => 'system', 'message' => 5 . ' disconnected')));
			send_message_to_socks($response, $clients);
			echo $found_socket . 'disconnected'. "\n";
		}
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
