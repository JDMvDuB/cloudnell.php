<?php
  error_reporting(E_ALL);
	session_start();
	include "../../../db/db.php";
	include "../../../functions/functions.php";

  if(isset($_POST['manager_info'])){
    $id_cafe = FILTER($_POST['id_cafe']);

    $query_s = $link->query("SELECT * FROM `manager` WHERE `id_cafe` = '$id_cafe'");
    $manager = $query_s->fetch_assoc();

    if($manager){
        echo json_encode(['status' => 'success', 'data' => $manager]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Управляющий не найден']);
    }
  }
?>