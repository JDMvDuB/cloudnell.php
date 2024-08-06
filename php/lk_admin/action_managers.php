<?php
	error_reporting(E_ALL);
	session_start();
	include "../../../db/db.php";
	include "../../../functions/functions.php";

	if(isset($_POST['get_managers'])){
		$query_s = $link->query("SELECT `manager`.*, `services`.title as title_services, `cafe`.title as title_cafe FROM `manager` INNER JOIN `services` ON `manager`.id_services = `services`.id LEFT JOIN `cafe` ON `manager`.id_cafe  = cafe.`id`");
		$managers = [];
		
		$i = 0;
		if($query_s->num_rows > 0){
			while($row = $query_s->fetch_assoc()){
				$managers[$i]['id'] = $row['id'];
				$managers[$i]['title_services'] = $row['title_services'];
				$managers[$i]['title_cafe'] = $row['title_cafe'];
				$managers[$i]['email'] = $row['email'];
				$managers[$i]['login'] = $row['login'];
				$managers[$i]['id_services'] = $row['id_services'];
				$managers[$i]['id_cafe'] = $row['id_cafe'];
				$managers[$i]['photo_profile'] = $row['photo_profile'];

				$query_sq = $link->query("SELECT `id` FROM `personal` WHERE `id_cafe` = '$row[id_cafe]'");
				$managers[$i]['count_personal'] = $query_sq->num_rows;
				$i++;
			}

			echo json_encode(['status'=>'success','data'=>$managers]);
		}else{
			echo json_encode(['status'=>'empty','data'=>[]]);
		}
	
	}

	if(isset($_POST['edit_manager_id'])){
    $id = +$_POST['edit_manager_id'];

    $query_s = $link->query("SELECT `id`,`email`,`login`,`id_employees`,`id_services`,`id_cafe`,`photo_profile`,`lock_user`,`start_tariff`,`end_tariff`,`balance`, `telegram_id` FROM `manager` WHERE `id` = '$id'"); // Добавлено поле telegram_id
    $row = $query_s->fetch_assoc();

    if($query_s->num_rows > 0){
        echo json_encode(['status'=>'success','data'=>$row]);
    }else{
        echo json_encode(['status'=>'empty','data'=>[]]);
    }
  }

	if(isset($_POST['id_edit_personal'])){
		$id  = +FILTER($_POST['id_edit_personal']);
		$edit_email_pers = FILTER($_POST['edit_email_pers']);
		$edit_name_pers = FILTER($_POST['edit_name_pers']);
		$edit_services = +FILTER($_POST['edit_services']);
		$edit_cafe = +FILTER($_POST['edit_cafe']);
		
		$password = FILTER($_POST['edit_password']);
		$repeat_password = FILTER($_POST['edit_repeat_password']);

		$balance = FILTER($_POST['edit_balance']);
		$edit_start_date = FILTER($_POST['edit_start_date']);
		$edit_end_date = FILTER($_POST['edit_end_date']);

		$data = [];

		$error = false;

		if(preg_match("~^\s*$~",$edit_email_pers)){
			$error = 'Пустое поле Email';
		}else if(!filter_var($edit_email_pers,FILTER_VALIDATE_EMAIL)){
			$error = 'Не верный форма Email';
		}else if(preg_match("~^\s*$~",$edit_name_pers)){
			$error = 'Пустое поле Имя';
		}else if(!empty($password) || !empty($repeat_password)){
			if($password!=$repeat_password){
				$error = 'Пароли не совпадают';
			}
		}

		if($error){
			echo json_encode(['status'=>'error','data'=>$error]);
		}else{

		if(!empty($password)){
			$password = password_hash($password,PASSWORD_DEFAULT);
			$query_s = $link->query("UPDATE `manager` SET `email` = '$edit_email_pers', `login` = '$edit_name_pers', `password`='$password', `id_services` = '$edit_services', `id_cafe` = '$edit_cafe',`balance` = '$balance', `start_tariff`='$edit_start_date', `end_tariff` = '$edit_end_date' WHERE `id` = '$id'");
		}else{
			$query_s = $link->query("UPDATE `manager` SET `email` = '$edit_email_pers', `login` = '$edit_name_pers', `id_services` = '$edit_services', `id_cafe` = '$edit_cafe', `balance` = '$balance', `start_tariff`='$edit_start_date', `end_tariff` = '$edit_end_date' WHERE `id` = '$id'");
		}
		
		print_r($_FILES['photo_edit_waiter']);

		if(isset($_FILES['photo_edit_waiter'])){
			$uploads_dir = "../../../lk/lk_manager/image/photo_profile/$id";
			$direct_photo = array_diff(scandir($uploads_dir),['.','..']);
			

			if(count($direct_photo) > 0){
				@unlink($uploads_dir.'/'.end($direct_photo));
			}

			$tmp_name = trim($_FILES['photo_edit_waiter']['tmp_name']);
			$name = trim(basename($_FILES["photo_edit_waiter"]["name"]));

			move_uploaded_file($tmp_name,$uploads_dir.'/'.$name);

			$query_s = $link->query("UPDATE `manager` SET `photo_profile` = '$name' WHERE `id` = '$id'");
		}

		echo json_encode(['status'=>'success','data'=>[]]);
		
		}

	}

	if(isset($_POST['add_name_managers'])){
		
		$email = FILTER($_POST['add_email_managers']);
		$name_pers = FILTER($_POST['add_name_managers']);
		$password = FILTER($_POST['add_password_managers']);
		$select_services = FILTER($_POST['add_select_services']);
		$select_cafe = FILTER($_POST['add_select_cafe']);
		$add_start_date = FILTER($_POST['add_start_date']);
		$add_end_date = FILTER($_POST['add_end_date']);
		
		$id_waiter_to_desk = FILTER($_POST['add_waiter_to_desk_arr']);
		
		$error = '';

		if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
			$error = 'Не верный формат Email';
		}else if(preg_match("~^\\s*$~",$password)){
			$error = 'Пустое поле Пароль';
		}else if(mb_strlen($name_pers) > 50){
			$error = 'В поле Имя может быть максимум 50 символов';
		}else if(preg_match("~^\\s*$~",$name_pers)){
			$error = 'Пустое поле Имя';
		}

		$query_s = $link->query("SELECT `id` FROM `manager` WHERE `email` = '$email'");
		if($query_s->num_rows > 0){
			$error = 'Такой эмейл уже существует';
		}


		if($error){
			echo json_encode(['status'=>'error','data'=>$error]);
		}else{
		$password = password_hash($password, PASSWORD_DEFAULT);

		$query_idss = $link->query("SELECT MAX(`id`) as id_m FROM `manager`");
		$row = $query_idss->fetch_assoc();
		$row['id_m'] = (empty($row['id_m'])) ? 1 : $row['id_m']+1;

		$hash_user = HASH__SIMBOL();

		$query_i = $link->query("INSERT INTO `manager` VALUES('$row[id_m]','$email','','$name_pers','$password','2','$select_services','$select_cafe',NOW(),'','$hash_user','0','0','music.mp3','0','$add_start_date','$add_end_date')");
		
		$insert_id = $link->insert_id;

		if(isset($_FILES['add_photo_managers'])){
			$uploads_dir = "../../../lk/lk_manager/image/photo_profile/$insert_id";
			
			mkdir($uploads_dir,0777);

			$tmp_name = trim($_FILES['add_photo_managers']['tmp_name']);
			$name = trim(basename($_FILES["add_photo_managers"]["name"]));
			
			echo $name;
			
			move_uploaded_file($tmp_name,$uploads_dir.'/'.$name);

			$query_s = $link->query("UPDATE `manager` SET `photo_profile` = '$name' WHERE `id` = '$insert_id'");

		}else{
			
			$uploads_dir = "../../../lk/lk_manager/image/photo_profile/$insert_id";
			
			mkdir($uploads_dir,0777);

			copy("../../../lk/lk_manager/image/photo_profile/no_avatar.png", "../../../lk/lk_manager/image/photo_profile/$insert_id/no_avatar.png");

			$query_s = $link->query("UPDATE `manager` SET `photo_profile` = 'no_avatar.png' WHERE `id` = '$insert_id'");
		}

		if(isset($insert_id)){
			echo json_encode(['status'=>'success','data'=>$insert_id]);
		}

	}
}

	if(isset($_POST['delete_managers_id'])){
		$id = +$_POST['delete_managers_id'];
		$query_d = $link->query("DELETE FROM `manager` WHERE `id` = '$id'");
	}

	if(isset($_POST['baned_manager'])){
		list($user_id,$status) = json_decode($_POST['baned_manager'],true);
		$query_s = $link->query("UPDATE `manager` SET `lock_user` = '$status' WHERE `id` = '$user_id'");
		$status = ($status==0) ? 'Пользователь разблокирован' : 'Пользователь заблокирован';
		echo json_encode(['status'=>'success','data'=>$status]);
	}
	
	if(isset($_POST['get_num_days'])){
		$id = +$_POST['get_num_days'];
		$query_s = $link->query("SELECT `num_days` FROM `tariff` WHERE `id` = '$id'");
		$row = $query_s->fetch_assoc();

		$num_days = (int)$row['num_days'];
		if($num_days == 0){
			$today = date("Y-m-d H:i", strtotime("+100 year"));
			
		}else{
			$today = date("Y-m-d H:i", strtotime("+$num_days days"));
			
		}

		echo json_encode(['status'=>'success','start_date'=>date("Y-m-d H:i"),'end_date'=>$today]);

	}

  if(isset($_POST['telegram_id'])) {
    $id = FILTER($_POST['id']);
    $telegram_id = FILTER($_POST['telegram_id']);

    // Проверка, есть ли менеджер с таким id
    $query = $link->query("SELECT * FROM `manager` WHERE `id` = '$id'");
    if($query->num_rows > 0) {
        // Обновление telegram_id
        $query_update = $link->query("UPDATE `manager` SET `telegram_id` = '$telegram_id' WHERE `id` = '$id'");
        if($query_update) {
            echo json_encode(['status' => 'success', 'data' => 'Telegram ID обновлен']);
        } else {
            echo json_encode(['status' => 'error', 'data' => 'Ошибка при обновлении Telegram ID']);
        }
    } else {
        echo json_encode(['status' => 'error', 'data' => 'Менеджер не найден']);
    }
  }
	
?>