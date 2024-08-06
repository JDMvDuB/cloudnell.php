<?php
	error_reporting(E_ALL);
	session_start();
	include "../../../db/db.php";
	include "../../../functions/functions.php";

	if(isset($_POST['view_next_notice'])){
		$from_notice = +$_POST['view_next_notice'];

		$query_s = $link->query("SELECT `notice_orders`.id, `orders`.id_personal,`personal`.login, `notice_orders`.notice,`notice_orders`.date_created, `desc`.title as title_desc, `notice_orders`.type as type_notice FROM `notice_orders` INNER JOIN `desc` ON `notice_orders`.id_desc = `desc`.id INNER JOIN `orders` ON `notice_orders`.id_orders = `orders`.id LEFT JOIN `personal` ON `orders`.id_personal = `personal`.id WHERE `notice_orders`.id_desc IN (SELECT `id_desc` FROM `service_desc` WHERE `orders`.id_cafe = '$_SESSION[id_cafe]') ORDER BY `notice_orders`.id DESC LIMIT $from_notice, 20");

		$notice = [];
		while($row = $query_s->fetch_assoc()){
			$notice[$row['id']]['id'] = $row['id'];
			$notice[$row['id']]['id_personal'] = $row['id_personal'];
			$notice[$row['id']]['login'] = $row['login'];
			
			$notice[$row['id']]['date_created'] = $row['date_created'];
			$notice[$row['id']]['title_desc'] = $row['title_desc'];
			$notice[$row['id']]['notice'] = $row['notice'];
			$notice[$row['id']]['type_notice'] = $row['type_notice'];
			
			if($row['type_notice']==0){
				$notice[$row['id']]['notice'] .= $row['title_desc'];
			}else if($row['type_notice']==1){
				$notice[$row['id']]['notice'] = '<span style="color:darkorange">'.$row['login'].'</span> '.$notice[$row['id']]['notice'];
			}
		}

		if(count($notice) > 0){
			echo json_encode(['status'=>'success','data'=>$notice]);
		}else{
			echo json_encode(['status'=>'empty','data'=>[]]);
		}
	}

	if(isset($_POST['view_next_lknotice'])){
		$from_notice = +$_POST['view_next_lknotice'];
		$query_s = $link->query("SELECT `notice`.id, `notice`.notice, `notice`.type, `notice`.date, `personal`.login FROM `notice` INNER JOIN `personal` ON `notice`.id_user = `personal`.id ORDER BY `notice`.id DESC LIMIT $from_notice,20");

		$notice = [];
		$i = 0;
		while($row = $query_s->fetch_assoc()){
			$notice[$i]['notice'] = '<span style="color:darkorange">'.$row['login'].'</span> '.$row['notice'];
			$notice[$i]['date_created'] = $row['date'];
			$notice[$i]['type_notice'] = $row['type'];
      $notice[$i]['id'] = $row['id'];
			$i++;
		}
		if(count($notice) > 0){
			echo json_encode(['status'=>'success','data'=>$notice]);
		}else{
			echo json_encode(['status'=>'empty','data'=>[]]);
		}
	}

	if(isset($_POST['get_notice_sound'])){
		$id_user = +$_SESSION['id_user'];
		$query_s = $link->query("SELECT COUNT(`id`) as sound FROM `notice_sound_off` WHERE `id_user` = '$id_user' AND `type_personal` = 'manager'");
		$row = $query_s->fetch_assoc();
		
		echo json_encode(['status'=>'success','sound'=>$row['sound']]);

	}
	if(isset($_POST['notice_sound_off'])){
		$id_user = +$_SESSION['id_user'];

		$query_idss = $link->query("SELECT MAX(`id`) as id_m FROM `notice_sound_off`");
		$row = $query_idss->fetch_assoc();
		$row['id_m'] = (empty($row['id_m'])) ? 1 : $row['id_m']+1;

		$query_i = $link->query("INSERT INTO `notice_sound_off` VALUES ('$row[id_m]','$id_user','manager')");

		if(isset($link->insert_id)){
			echo json_encode(['status'=>'success','data'=>[]]);
		}
	}

	if(isset($_POST['notice_sound_on'])){
		$id_user = +$_SESSION['id_user'];

		$query_d = $link->query("DELETE FROM `notice_sound_off` WHERE `id_user` = '$id_user' AND `type_personal` = 'manager'");
	}

	if(isset($_POST['is_type_notice'])){
		$is_type_notice = $_POST['is_type_notice'];
		if($is_type_notice=='orders_notice'){
			$query_s = $link->query("SELECT `notice_orders`.id, `orders`.id_personal,`personal`.login, `notice_orders`.notice,`notice_orders`.date_created, `desc`.title as title_desc, `notice_orders`.type as type_notice FROM `notice_orders` INNER JOIN `desc` ON `notice_orders`.id_desc = `desc`.id INNER JOIN `orders` ON `notice_orders`.id_orders = `orders`.id LEFT JOIN `personal` ON `orders`.id_personal = `personal`.id WHERE `notice_orders`.id_desc IN (SELECT `id_desc` FROM `service_desc` WHERE `orders`.id_cafe = '$_SESSION[id_cafe]') ORDER BY `notice_orders`.id DESC LIMIT 20");

			$notice = [];
			while($row = $query_s->fetch_assoc()){
				$notice[$row['id']]['id'] = $row['id'];
				$notice[$row['id']]['id_personal'] = $row['id_personal'];
				$notice[$row['id']]['login'] = $row['login'];
				
				$notice[$row['id']]['date_created'] = $row['date_created'];
				$notice[$row['id']]['title_desc'] = $row['title_desc'];
				$notice[$row['id']]['notice'] = $row['notice'];
				$notice[$row['id']]['type_notice'] = $row['type_notice'];
				
				if($row['type_notice']==0){
					$notice[$row['id']]['notice'] .= $row['title_desc'];
				}else if($row['type_notice']==1){
					$notice[$row['id']]['notice'] = '<span style="color:darkorange">'.$row['login'].'</span> '.$notice[$row['id']]['notice'];
				}
			}
			$notice = array_values($notice);
			$notice['type_notice_list'] = 'orders';

			echo json_encode($notice);
		}else{
			$query_s = $link->query("SELECT `notice`.id, `notice`.notice, `notice`.type, `notice`.date, `personal`.login FROM `notice` INNER JOIN `personal` ON `notice`.id_user = `personal`.id ORDER BY `notice`.id DESC LIMIT 20");
			$notice = [];
			$i = 0;
			while($row = $query_s->fetch_assoc()){
				$notice[$i]['notice'] = '<span style="color:darkorange">'.$row['login'].'</span> '.$row['notice'];
				$notice[$i]['date_created'] = $row['date'];
				$notice[$i]['type'] = $row['type'];
				$i++;
			}
			$notice['type_notice_list'] = 'lk';
			echo json_encode($notice);
		}
	}

	if(isset($_POST['call_personal'])){
		$id_user_personal = +$_POST['call_personal'];

		$query_s = $link->query("SELECT `id` FROM `internal_call` WHERE `id_personal` = '$id_user_personal'");
		if($query_s->num_rows == 0){
			$query_idss = $link->query("SELECT MAX(`id`) as id_m FROM `internal_call`");
			$row = $query_idss->fetch_assoc();
			$row['id_m'] = (empty($row['id_m'])) ? 1 : $row['id_m']+1;

			$user = INFO_USER_PERSONAL($link,$id_user_personal);

			if(count($user) > 0){
				$query_i = $link->query("INSERT INTO `internal_call` VALUES('$row[id_m]','$id_user_personal')");
				if($link->insert_id){
					$notice = 'вас вызвал управлющий';

					$query_idss = $link->query("SELECT MAX(`id`) as id_m FROM `notice`");
					$row = $query_idss->fetch_assoc();
					$row['id_m'] = (empty($row['id_m'])) ? 1 : $row['id_m']+1;

					$query_i = $link->query("INSERT INTO `notice` VALUES('$row[id_m]','$_SESSION[id_cafe]','$id_user_personal','$notice','3',NOW())");

					$notice = $user['login'].' '.$notice;

					echo json_encode(['status'=>'success','data'=>['id_cafe'=>$_SESSION["id_cafe"],'id_user_personal'=>$id_user_personal,'message'=>$notice]]);
				}
			}else{
				echo json_encode(['status'=>'error','data'=>'Пользователя не существует']);
			}
		}else{
			echo json_encode(['status'=>'error','data'=>'Вы уже вызвали пользователя']);
		}
		
	}
?>