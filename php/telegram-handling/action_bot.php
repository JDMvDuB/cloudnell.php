<?php
	error_reporting(E_ALL);
	session_start();
	include "../../../db/db.php";
	include "../../../functions/functions.php";
	
  if(isset($_POST['read-message'])) {
    $comment_id = FILTER($_POST['id']);
    $telegram_id = FILTER($_POST['user_id']);

    $host = 'ws://178.178.96.254:5000';
    $data = json_encode(['comment_id' => $comment_id, 'telegram_id' => $telegram_id]);
    $context = stream_context_create(['http' => ['header' => 'Connection: close\r\n']]);
    $fp = stream_socket_client($host, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

    if (!$fp) {
        echo "Ошибка: $errstr ($errno)<br />\n";
    } else {
        fwrite($fp, $data);
        fclose($fp);
    }
  }


?>