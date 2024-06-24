<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once ('../config.php');
require_once ('../model/user.php');
require_once ('../utils/AppUtils.php');
class Users extends DBConnection
{
	private $settings;
	public function __construct()
	{
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct()
	{
		parent::__destruct();
	}
	public function save_users()
	{
		if (empty($_POST['password']))
			unset($_POST['password']);
		else
			$_POST['password'] = md5($_POST['password']);
		extract($_POST);
		$data = '';
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id'))) {
				if (!empty($data))
					$data .= " , ";
				$data .= " {$k} = '{$v}' ";
			}
		}
		if (empty($id)) {
			$qry = $this->conn->query("INSERT INTO users set {$data}");
			if ($qry) {
				$id = $this->conn->insert_id;

				foreach ($_POST as $k => $v) {
					if ($k != 'id') {
						if (!empty($data))
							$data .= " , ";
						if ($this->settings->userdata('id') == $id)
							$this->settings->set_userdata($k, $v);
					}
				}
				if (!empty($_FILES['img']['tmp_name'])) {
					if (!is_dir(BASE_APP . "uploads/avatars"))
						mkdir(BASE_APP . "uploads/avatars");
					$ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
					$fname = "uploads/avatars/$id.png";
					$accept = array('image/jpeg', 'image/png');
					if (!in_array($_FILES['img']['type'], $accept)) {
						$err = "Image file type is invalid";
					}
					if ($_FILES['img']['type'] == 'image/jpeg')
						$uploadfile = imagecreatefromjpeg($_FILES['img']['tmp_name']);
					elseif ($_FILES['img']['type'] == 'image/png')
						$uploadfile = imagecreatefrompng($_FILES['img']['tmp_name']);
					if (!$uploadfile) {
						$err = "Image is invalid";
					}
					$temp = imagescale($uploadfile, 200, 200);
					if (is_file(BASE_APP . $fname))
						unlink(BASE_APP . $fname);
					$upload = imagepng($temp, BASE_APP . $fname);
					if ($upload) {
						$this->conn->query("UPDATE `users` set `avatar` = CONCAT('{$fname}', '?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$id}'");
						if ($this->settings->userdata('id') == $id)
							$this->settings->set_userdata('avatar', $fname . "?v=" . time());
					}

					imagedestroy($temp);
				}
				return 1;
			} else {
				return 2;
			}
		} else {
			$qry = $this->conn->query("UPDATE users set $data where id = {$id}");
			if ($qry) {
				foreach ($_POST as $k => $v) {
					if ($k != 'id') {
						if (!empty($data))
							$data .= " , ";
						if ($this->settings->userdata('id') == $id)
							$this->settings->set_userdata($k, $v);
					}
				}
				if (!empty($_FILES['img']['tmp_name'])) {
					if (!is_dir(BASE_APP . "uploads/avatars"))
						mkdir(BASE_APP . "uploads/avatars");
					$ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
					$fname = "uploads/avatars/$id.png";
					$accept = array('image/jpeg', 'image/png');
					if (!in_array($_FILES['img']['type'], $accept)) {
						$err = "Image file type is invalid";
					}
					if ($_FILES['img']['type'] == 'image/jpeg')
						$uploadfile = imagecreatefromjpeg($_FILES['img']['tmp_name']);
					elseif ($_FILES['img']['type'] == 'image/png')
						$uploadfile = imagecreatefrompng($_FILES['img']['tmp_name']);
					if (!$uploadfile) {
						$err = "Image is invalid";
					}
					$temp = imagescale($uploadfile, 200, 200);
					if (is_file(BASE_APP . $fname))
						unlink(BASE_APP . $fname);
					$upload = imagepng($temp, BASE_APP . $fname);
					if ($upload) {
						$this->conn->query("UPDATE `users` set `avatar` = CONCAT('{$fname}', '?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$id}'");
						if ($this->settings->userdata('id') == $id)
							$this->settings->set_userdata('avatar', $fname . "?v=" . time());
					}

					imagedestroy($temp);
				}

				return 1;
			} else {
				return "UPDATE users set $data where id = {$id}";
			}
		}
	}
	public function delete_users()
	{
		extract($_POST);
		$qry = $this->conn->query("UPDATE user set banned_temporarly = 1 where id_user = '{$id_user}'");
		if ($qry) {
			$this->settings->set_flashdata('success', 'User Details successfully deleted.');
			$interdiction_query = $this->conn->prepare("INSERT into interdiction(message, actif, code_interdiction_type, id_user, DELAY)
			values ('{$text}', 0, 'BANNED_TMP', '{$id_user}', '{$delay}')");
			$interdiction_query->execute();
			$id_notification = $interdiction_query->insert_id;
			$query = $this->conn->prepare("insert into notification(content_notification, id_user, id_interdiction) values 
			('Vous avez reçu un avertissement pour comportement inapproprié', '{$id_user}', '{$id_notification}')");
			$query->execute();
			if (is_file(BASE_APP . "uploads/avatars/$id_user.png"))
				unlink(BASE_APP . "uploads/avatars/$id_user.png");
			return json_encode(array('status' => 'success', 'message' => 'Utilisateur supprimé avec succès'));
		} else {
			return json_encode(array('status' => 'success', 'message' => 'Une erreur est survenue.'));
		}
	}

	/* public function delete_user_forever()
															 {
																 extract($_POST);
																 $qry = $this->conn->query("UPDATE user set banned_forever = 1 where id_user = $id");
																 if ($qry) {
																	 if (is_file(BASE_APP . "uploads/avatars/$id.png"))
																		 unlink(BASE_APP . "uploads/avatars/$id.png");
																	 return json_encode(array('status' => 'success', 'message' => 'Utilisateur supprimé avec succès'));
																 } else {
																	 return json_encode(array('status' => 'success', 'message' => 'Une erreur est survenue.'));
																 }
															 } */
	public function save_member()
	{

		if (!empty($_POST['password'])) {
			$_POST['password'] = md5($_POST['password']);
		}
		extract($_POST);
		$data = '';
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id_user'))) {
				if (!empty($data))
					$data .= " , ";
				$v1 = AppUtils::securizeStringForSQL($v);
				$data .= " {$k} = '{$v1}' ";
			}
		}
		if (empty($id_user)) {
			$qry = $this->conn->query("INSERT INTO user set {$data}");
			if ($qry) {
				$id = $this->conn->insert_id;
				//$this->settings->set_flashdata('success', 'Information utilisateur ajouté avec succès.');
				foreach ($_POST as $k => $v) {
					if ($k != 'id_user') {
						if (!empty($data))
							$data .= " , ";
						if ($this->settings->userdata('id_user') == $id)
							$this->settings->set_userdata($k, $v);
					}
				}
				if (!empty($_FILES['img']['tmp_name'])) {
					if (!is_dir(BASE_APP . "uploads/member"))
						mkdir(BASE_APP . "uploads/member");
					$ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
					$fname = "uploads/member/$id.png";
					$accept = array('image/jpeg', 'image/png');
					if (!in_array($_FILES['img']['type'], $accept)) {
						$err = "Image file type is invalid";
					}
					if ($_FILES['img']['type'] == 'image/jpeg')
						$uploadfile = imagecreatefromjpeg($_FILES['img']['tmp_name']);
					elseif ($_FILES['img']['type'] == 'image/png')
						$uploadfile = imagecreatefrompng($_FILES['img']['tmp_name']);
					if (!$uploadfile) {
						$err = "Image is invalid";
					}
					$temp = imagescale($uploadfile, 200, 200);
					if (is_file(BASE_APP . $fname))
						unlink(BASE_APP . $fname);
					$upload = imagepng($temp, BASE_APP . $fname);
					if ($upload) {
						$this->conn->query("UPDATE `user` set `avatar` = CONCAT('{$fname}', '?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id_user = '{$id}'");
						if ($this->settings->userdata('id_user') == $id)
							$this->settings->set_userdata('avatar', $fname . "?v=" . time());
					}

					imagedestroy($temp);
				}
				$resp['status'] = "success";
				$resp['message'] = "Connexion réussie";

				$resp['data'] = [
					'id_user' => $this->settings->userdata('id_user'),
					'username' => $this->settings->userdata('username'),
					'email' => $this->settings->userdata('email'),
					'firstname' => $this->settings->userdata('firstname'),
					'lastname' => $this->settings->userdata('lastname'),
					'avatar' => $this->settings->userdata('avatar'),
					'banned_temporarly' => $this->settings->userdata('banned_temporarly'),
					'interdiction_date' => $this->settings->userdata('interdiction_date'),
					'role' => $this->settings->userdata('code_role'),
					'self_intro' => $this->settings->userdata('self_intro'),
					'address' => $this->settings->userdata('address'),
					'phone' => $this->settings->userdata('phone'),
				];
				return json_encode($resp);
			} else {
				return json_encode(array('status' => 'failed', 'message' => 'Eched de mis à jour'));
			}

		} else {
			$qry = $this->conn->query("UPDATE user set $data where id_user = {$id_user}");
			if ($qry) {
				//$this->settings->set_flashdata('success', 'Information utilisateur mise à jour avec succès.');
				foreach ($_POST as $k => $v) {
					if ($k != 'id_user') {
						if (!empty($data))
							$data .= " , ";
						if ($this->settings->userdata('id_user') == $id_user)
							$this->settings->set_userdata($k, $v);
					}
				}
				if (!empty($_FILES['img']['tmp_name'])) {
					if (!is_dir(BASE_APP . "uploads/member"))
						mkdir(BASE_APP . "uploads/member");
					$ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
					$fname = "uploads/member/$id_user.png";
					$accept = array('image/jpeg', 'image/png');
					if (!in_array($_FILES['img']['type'], $accept)) {
						$err = "Image file type is invalid";
					}
					if ($_FILES['img']['type'] == 'image/jpeg')
						$uploadfile = imagecreatefromjpeg($_FILES['img']['tmp_name']);
					elseif ($_FILES['img']['type'] == 'image/png')
						$uploadfile = imagecreatefrompng($_FILES['img']['tmp_name']);
					if (!$uploadfile) {
						$err = "Image is invalid";
					}
					$temp = imagescale($uploadfile, 200, 200);
					if (is_file(BASE_APP . $fname))
						unlink(BASE_APP . $fname);
					$upload = imagepng($temp, BASE_APP . $fname);
					if ($upload) {
						$this->conn->query("UPDATE `user` set `avatar` = '{$fname}' where id_user = '{$id_user}'");
						if ($this->settings->userdata('id_user') == $id_user)
							$this->settings->set_userdata('avatar', $fname);
					}

					imagedestroy($temp);
				}
				$user = [];
				$qry1 = $this->conn->query("SELECT * FROM user where id_user = {$id_user}");
				if ($qry1->num_rows > 0) {
					while ($row = $qry1->fetch_assoc()) {
						$user[] = $row;
					}
				}

				$resp['status'] = "success";
				$resp['message'] = "Connexion réussie";

				$resp['data'] = $user[0];
				return json_encode($resp);
			} else {
				return json_encode(array('status' => 'failed', 'message' => 'Echec de mis à jour'));
			}

		}
	}

	public function registration()
	{
		$input = json_decode(file_get_contents('php://input'), true);

		$input['password'] = md5($input['password']);
		//extract($_POST);
		$data = "";
		$check = $this->conn->query("SELECT * FROM `user` where email = '{$input["email"]}'")->num_rows;
		if ($check > 0) {
			$resp['status'] = 'failed';
			$resp['msg'] = 'Email already exists.';
			return json_encode($resp);
		} else {
			$check = $this->conn->query("SELECT * FROM `user` where username = '{$input["username"]}'")->num_rows;
			if ($check > 0) {
				$resp['status'] = 'failed';
				$resp['msg'] = 'Username already exists.';
				return json_encode($resp);
			} else {
				$check = $this->conn->query("SELECT * FROM `user` where phone = '{$input["phone"]}'")->num_rows;
				if ($check > 0) {
					$resp['status'] = 'failed';
					$resp['msg'] = 'Phone number already exists.';
					return json_encode($resp);
				}
			}
		}
		$user = new User($input["username"], $input["password"], $input["email"], $input["firstname"], $input["lastname"], $input["phone"]);
		AppUtils::securizeStringForSQL($user->getUsername());
		AppUtils::securizeStringForSQL($user->getPassword());
		AppUtils::securizeStringForSQL($user->getEmail());
		AppUtils::securizeStringForSQL($user->getPhone());
		AppUtils::securizeStringForSQL($user->getLastname());
		AppUtils::securizeStringForSQL($user->getFirstname());
		$user->setPassword(md5($user->getPassword()));
		$code_role = 'USER';
		$sql = "INSERT INTO user(username, password, email, phone, firstname, lastname, code_role, date_added) VALUES
		('" . $user->getUsername() . "', '" . $user->getPassword() . "', '" . $user->getEmail() . "', '" . $user->getPhone() . "'
		, '" . $user->getFirstname() . "', '" . $user->getLastname() . "', '" . $code_role . "', NOW());";
		$save = $this->conn->query($sql);
		if ($save) {
			$resp['status'] = 'success';
			$resp['message'] = 'Your Account has been created successfully';
			$uid = $this->conn->insert_id;
			$user = [];
			$qry = $this->conn->query("SELECT * FROM user where id_user = {$uid}");
			if ($qry->num_rows > 0) {
				while ($row = $qry->fetch_assoc()) {
					$user[] = $row;
				}
			}

			$resp['data'] = $user[0];

			/*if (!empty($_FILES['img']['tmp_name'])) {
																																														   if (!is_dir(BASE_APP . "uploads/member"))
																																															   mkdir(BASE_APP . "uploads/member");
																																														   $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
																																														   $fname = "uploads/member/$uid.png";
																																														   $accept = array('image/jpeg', 'image/png');
																																														   if (!in_array($_FILES['img']['type'], $accept)) {
																																															   $resp['msg'] = "Image file type is invalid";
																																														   }
																																														   if ($_FILES['img']['type'] == 'image/jpeg')
																																															   $uploadfile = imagecreatefromjpeg($_FILES['img']['tmp_name']);
																																														   elseif ($_FILES['img']['type'] == 'image/png')
																																															   $uploadfile = imagecreatefrompng($_FILES['img']['tmp_name']);
																																														   if (!$uploadfile) {
																																															   $resp['msg'] = "Image is invalid";
																																														   }
																																														   $temp = imagescale($uploadfile, 200, 200);
																																														   if (is_file(BASE_APP . $fname))
																																															   unlink(BASE_APP . $fname);
																																														   $upload = imagepng($temp, BASE_APP . $fname);
																																														   if ($upload) {
																																															   $this->conn->query("UPDATE `user` set `avatar` = CONCAT('{$fname}', '?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$uid}'");
																																														   }
																																														   imagedestroy($temp);
																																													   }*/
		} else {
			$resp['status'] = 'failed';
			$resp['message'] = 'Erreur d\'inscription.';
		}

		return json_encode($resp);
	}

	function getImage($filename)
	{

	}

	function get_all()
	{
		$users = $this->conn->query("SELECT *, CONCAT(lastname, ' ', firstname) AS `name` FROM `user` ORDER BY `name` ASC ");
		$user_data = array();
		while ($row = $users->fetch_assoc()) {
			$user_data[] = $row;
		}
		return json_encode($user_data);
	}

	public function nbr_users()
	{
		$query = $this->conn->prepare("SELECT distinct count(*) as nbr_users from user");
		$query->execute();
		$data = $query->get_result()->fetch_assoc();
		return json_encode($data);
	}

	public function nbr_connected_users()
	{
		$query = $this->conn->prepare("SELECT distinct count(*) as connected_users from user where connected = true");
		$query->execute();
		$users = $query->get_result()->fetch_assoc();
		return json_encode($users);
	}
	public function return_query($query)
	{
		$result = array();
		if ($query) {
			$result['status'] = 'success';
			$result['message'] = 'Operation effectuee avec succes';
		} else {
			$result['status'] = 'failed';
			$result['message'] = 'Une erreur s\'est produite';
		}

		return json_encode($result);
	}

	public function warn_user() //here we warn user and delete warning //When the admin clickes first on warn user, if user is not warned, it will warn him but if user is already warned, it will delete warning.
	{
		$input = json_decode(file_get_contents('php://input'), true);
		$warning_text = "Vous avez recu un avertissement dû à vos derniers posts qui ne respectent pas notre politique";
		$username = $input["username"];
		$query = $this->conn->prepare("SELECT id_user from user where username = '{$username}'");
		$query->execute();
		$result = $query->get_result();

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$user_id = (int) $row['id_user'];
		} else {
			$user_id = null;
			return json_encode(array('status' => 'failed', 'message' => 'Une erreur s\'est produite.'));
		}
		$interdiction_query = $this->conn->prepare("INSERT into interdiction(message, actif, interdiction_date, code_interdiction_type, id_user)
			values ('{$warning_text}', 0,  NOW(), 'AVERTIR', '{$user_id}');");
		$interdiction_query->execute();
		if ($interdiction_query) {
			$select_last_interdiction = $this->conn->insert_id;
			$notification_query = $this->conn->prepare("INSERT INTO notification(content_notification, id_user, id_interdiction) values
				('Vous avez reçu un avertissement pour comportement approprié', '{$user_id}', '{$select_last_interdiction}');");
			$insert_result = $notification_query->execute();
			if ($insert_result) {
				return json_encode(array('status' => 'success', 'message' => 'Opération effectuéee avec succès'));
			} else {
				return json_encode(array('status' => 'failed', 'message' => 'Une erreur s\'est produite.'));
			}

		}
		return json_encode(array('status' => 'failed', 'message' => 'Une erreur s\'est produite.'));
	}

	public function make_post_sensitive()
	{
		extract($_POST);
		$result = array();
		$insert_interdiction_query = $this->conn->prepare("INSERT INTO interdiction(message, actif, interdiction_date, code_interdiction_type, id_user) 
    		VALUES ('$interdiction_text', 0, NOW(), 'SENSIBLE', $id_user)");
		$insert_interdiction_query->execute();
		if ($insert_interdiction_query) {
			$id_interdiction = $this->conn->insert_id;
			$insert_notification_query = $this->conn->prepare("INSERT into notification(content_notification, notification_date, is_notification_read, id_post, id_user, id_interdiction) values
			('Un de vos posts a été marqué comme sensible par nos modérateurs', NOW(), 0, $id_post, $id_user, $id_interdiction)");
			$insert_notification_query->execute();
			if ($insert_notification_query) {
				$result['status'] = 'success';
				$result['message'] = 'Opération effectuée avec succès';
			} else {
				$result['status'] = 'failed';
				$result['message'] = 'Une erreur s\'est produite';
			}
		}

		return json_encode($result);
	}

	public function reintegrate()
	{
		extract($_POST);
		$result = array();
		$update_query = $this->conn->prepare("UPDATE user set banned_temporarly = 0 where id_user = '{$id}'");
		$update_query->execute();
		$interdiction_query = $this->conn->prepare("UPDATE interdiction set actif = 1 where id_user = '{$id}' and code_interdiction_type = 'BANNED_TMP' and actif = 0");
		$interdiction_query->execute();
		if ($update_query) {
			$result['status'] = 'success';
			$result['message'] = 'Opération effectuée avec succès';
		} else {
			$result['status'] = 'failed';
			$result['message'] = 'Une erreur s\'est produite';
		}
		return json_encode($result);
	}

	function follow()
	{
		$input = json_decode(file_get_contents('php://input'), true);

		if ($input['status'] == 1) {
			$sql = "INSERT INTO `followship` set `id_user_following` = '{$input['following']}', `id_user_follower` = '{$input['follower']}' ";
		} else {
			$sql = "DELETE FROM `followship` where `id_user_following` = '{$input['following']}' and `id_user_follower` = '{$input['follower']}' ";
		}
		$process = $this->conn->query($sql);
		if ($process) {
			$resp['status'] = 'success';
			$followers = $this->conn->query("SELECT id_user_follower FROM followship WHERE id_user_following = {$input['following']} ")->num_rows;
			$resp['followers'] = $followers;
			$resp['message'] = 'Nouveau follower';
		} else {
			$resp['status'] = 'error';
			$resp['message'] = 'Erreur lors du process de follow/unfollow';
		}
		return json_encode($resp);
	}

	function delete_notif()
	{
		extract($_POST);
		$sql = "UPDATE `notification` SET removed = 1 WHERE `id_notification` = '{$id_notification}' ";

		$process = $this->conn->query($sql);
		if ($process) {
			$resp['status'] = 'success';
			//$followers = $this->conn->query("SELECT id_user_follower FROM followship WHERE id_user_following = {$id_user_following} ")->num_rows;
			$resp['unread'] = 0;
			$resp['msg'] = 'Notification supprimé avec succès';
		} else {
			$resp['status'] = 'error';
			$resp['msg'] = 'Echec lors de la suppression de notification';
		}
		return json_encode($resp);
	}

	function update_unread_notif($id_user)
	{
		try {
			$unreadNotif = $this->conn->query("SELECT id_notification FROM `notification` WHERE is_notification_read = 0 AND removed = 0 AND id_user = {$id_user} ")->num_rows;
			$resp['status'] = 'success';
			$resp['message'] = 'success';
			$resp['unread_notif'] = $unreadNotif;
		} catch (\Throwable $th) {
			$resp['status'] = 'failed';
			$resp['message'] = 'failed';
		}

		return json_encode($resp);
	}

	function statistics($idUser)
	{
		$data = array();
		$sql = "SELECT YEAR(post_date) AS years, MONTH(post_date) AS months, COUNT(*) AS post_count
        FROM post WHERE id_post_comment IS NULL AND id_user = {$idUser}
        GROUP BY YEAR(post_date), MONTH(post_date)
        ORDER BY years, months";
		$process = $this->conn->query($sql);
		if ($process) {
			// Récupération des résultats de la requête
			while ($row = $process->fetch_assoc()) {
				$data[] = $row;
			}
		}
		$resp['status'] = 'success';
		$resp['message'] = 'Successful';
		$resp['data'] = $data;
		return json_encode($resp);
	}

	function search_user()
	{
		extract($_GET);
		$sql = "SELECT * FROM user WHERE banned_forever = 0 AND username LIKE '%$searchTerm%' OR LOWER(firstname) LIKE '%$searchTerm%' OR LOWER(lastname) LIKE '%$searchTerm%'";
		$sql2 = "SELECT * FROM post WHERE id_post_comment IS NULL AND removed = 0 AND LOWER(content_post) LIKE '%$searchTerm%'";
		$result = $this->conn->query($sql);
		$result2 = $this->conn->query($sql2);

		$users = [];
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$users[] = $row;
			}
		}
		if ($result2->num_rows > 0) {
			while ($row = $result2->fetch_assoc()) {
				$users[] = $row;
			}
		}
		return json_encode($users);
	}

	public function delete_user_forever()
	{
		extract($_POST);
		$qry = $this->conn->query("UPDATE user set banned_forever = 1 where id_user = $id");
		if ($qry) {
			$this->settings->set_flashdata('success', 'User Details successfully deleted.');
			if (is_file(BASE_APP . "uploads/avatars/$id.png"))
				unlink(BASE_APP . "uploads/avatars/$id.png");
			return json_encode(array('status' => 'success', 'message' => 'Utilisateur supprimé avec succès'));
		} else {
			return json_encode(array('status' => 'success', 'message' => 'Une erreur est survenue.'));
		}
	}

	public function delete_user_temporarly()
	{
		extract($_POST);
		$qry = $this->conn->query("UPDATE user set banned_temporarly = 1 where id_user = '{$id_user}'");
		if ($qry) {
			$this->settings->set_flashdata('success', 'User Details successfully deleted.');
			$interdiction_query = $this->conn->prepare("INSERT into interdiction(message, actif, code_interdiction_type, id_user, delay)
			values ('{$text}', 0, 'BANNED_TMP', '{$id_user}', '{$delay}')");
			$interdiction_query->execute();
			$id_notification = $interdiction_query->insert_id;
			$query = $this->conn->prepare("insert into notification(content_notification, id_user, id_interdiction) values 
			('Vous avez reçu un avertissement pour comportement inapproprié', '{$id_user}', '{$id_notification}')");
			$query->execute();
			if (is_file(BASE_APP . "uploads/avatars/$id_user.png"))
				unlink(BASE_APP . "uploads/avatars/$id_user.png");
			return json_encode(array('status' => 'success', 'message' => 'Utilisateur supprimé avec succès'));
		} else {
			return json_encode(array('status' => 'success', 'message' => 'Une erreur est survenue.'));
		}
	}

	public function load_image($filename)
	{
		// Validate filename to prevent directory traversal attacks
		$filepath = BASE_APP . $filename;
		// Check if the file exists
		if (file_exists($filepath)) {
			// Get the file extension and determine content type
			$extension = pathinfo($filepath, PATHINFO_EXTENSION);
			$contentTypes = [
				'jpg' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png' => 'image/png',
				'gif' => 'image/gif',
				'bmp' => 'image/bmp',
			];

			$contentType = isset($contentTypes[$extension]) ? $contentTypes[$extension] : 'application/octet-stream';

			// Set the content type header
			header('Content-Type: ' . $contentType);

			// Output the image file
			readfile($filepath);

			// Exit to prevent further output
			exit;
		} else {
			// Image file not found
			header("HTTP/1.0 404 Not Found");
			echo "Image not found " . $filepath;
		}
	}
}

$users = new users();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
switch ($action) {
	case 'registration':
		echo $users->registration();
		break;
	case 'delete_user_temporarly':
		echo $users->delete_user_temporarly();
		break;
	case 'save_member':
		echo $users->save_member();
		break;
	case 'delete_member':
		echo $users->delete_users();
		break;
	case 'get_all':
		echo $users->get_all();
		break;
	case 'nbr_users':
		echo $users->nbr_users();
	case 'nbr_connected_users':
		echo $users->nbr_connected_users();
		break;
	case 'warn_user':
		echo $users->warn_user();
		break;
	case 'make_post_sensitive':
		echo $users->make_post_sensitive();
		break;
	case 'delete_user_forever':
		echo $users->delete_user_forever();
		break;
	case 'reintegrate':
		echo $users->reintegrate();
		break;
	case 'follow':
		echo $users->follow();
		break;
	case 'del_notif':
		echo $users->delete_notif();
		break;
	case 'num_notif':
		if (isset($_GET['id_user'])) {
			echo $users->update_unread_notif($_GET['id_user']);
		}
		break;
	case 'stats':
		if (isset($_GET['id_user'])) {
			echo $users->statistics($_GET['id_user']);
		}
		break;
	case 'search':
		echo $users->search_user();
		break;
	case 'load_image':
		if (isset($_GET['filename'])) {
			echo $users->load_image($_GET['filename']);
		} else {
			echo $users->load_image('no-image-available.png');
		}
		break;
	default:
		// echo $sysset->index();
		break;
}
