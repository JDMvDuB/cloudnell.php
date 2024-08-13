<?php
  error_reporting(E_ALL);
	session_start();
	include "../../../db/db.php";
	include "../../../functions/functions.php";

  if(isset($_POST['manager_info'])){
    $id_cafe = FILTER($_POST['id_cafe']);

    $query_s = $link->query("SELECT `manager`.telegram_id AS 'manager_telegram_id', `personal`.telegram_id AS 'telegram_id', `notice_orders`.id AS 'comment_id' FROM `notice-orders` JOIN `manager` ON `manager`.id_cafe = `notice_orders`.id_cafe JOIN `personal` ON `personal`.id_cafe = `notice_orders`.id_cafe WHERE `id_cafe` = '$id_cafe'");
    $data = $query_s->fetch_assoc();

    if($data){
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ничего не найдено']);
    }
  }
?>