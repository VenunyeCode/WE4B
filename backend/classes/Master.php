<?php
require_once ('../config.php');
require_once ('./../utils/AppUtils.php');
include ('./ImgFileUploader.php');
class Master extends DBConnection
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
	function capture_err()
	{
		if (!$this->conn->error)
			return false;
		else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function delete_img()
	{
		extract($_POST);
		if (is_file($path)) {
			if (unlink($path)) {
				$resp['status'] = 'success';
			} else {
				$resp['status'] = 'failed';
				$resp['error'] = 'failed to delete ' . $path;
			}
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = 'Unkown ' . $path . ' path';
		}
		return json_encode($resp);
	}

	function save_post2()
	{
		extract($_POST);
		//if (empty($_POST['id'])) {
		//	$_POST['owner'] = $this->settings->userdata('id_user');
		//}

		//Requête de création d'un nouveau post/message
		$query = "INSERT INTO `post` (content_post, id_user) VALUES            
		('" . AppUtils::securizeStringForSQL($content) . "', '" . $this->settings->userdata('id_user') . "')";
		//Créer des notifications pour tous les followers de l'auteur du post
		$notifStatement = $this->conn->prepare("INSERT INTO notification (id_user, content_notification, id_post)
		SELECT f.id_user_follower, CONCAT('@',u.username,' a crée un nouveau post'), ?
		FROM followship f
		INNER JOIN post p ON f.id_user_following = p.id_user
		INNER JOIN user u ON p.id_user = u.id_user
		WHERE p.id_post = ?");

		$result = $this->conn->query($query);

		if ($result) {
			$aid = $this->conn->insert_id;
			//Exécution de la requête des notifications
			$notifStatement->bind_param('ss', $aid, $aid);
			$notifStatement->execute();

			$resp['aid'] = $aid;
			$resp["status"] = "success";
			$resp["message"] = "Nouveau post effectué avec succès.";
			//Insérer l'image, si c'est un post avec media
			if (isset($_FILES['img'])) {
				$resp["error"] = "Fichier chargé";

				$img = new ImgFileUploader($this->conn);

				$img->SaveFileAsNew($this->conn->insert_id);
			} else {
				$resp["error"] = "Pas de fichier chargé";
			}
		} else {
			$resp["status"] = "failed";
			$resp["message"] = "Erreur survenue lors du processus d'ajout de nouveau post.";
		}
		return json_encode($resp);
	}
	function delete_post()
	{
		extract($_POST);
		$path = $this->conn->query("SELECT upload_path from `post` where id_post = '{$id}'")->fetch_array()[0];
		$del = $this->conn->query("DELETE FROM `post` where id_post = '{$id}'");
		if ($del) {
			$resp['status'] = 'success';
			if (is_dir(BASE_APP . $path)) {
				$fopen = scandir(BASE_APP . $path);
				foreach ($fopen as $file) {
					if (!in_array($file, ['.', '..']) && is_file(BASE_APP . $path . $file)) {
						unlink(BASE_APP . $path . $file);
					}
				}
				rmdir(BASE_APP . $path);
			}
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function update_post_status()
	{
		extract($_POST);
		$update = $this->conn->query("UPDATE `post` set `removed` = '{$status}' where id_post = '{$id}' ");
		if ($update) {
			$resp['status'] = 'success';
			$resp['msg'] = 'post\'s Status has been updated successfully.';
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = $this->conn->error;
		}
		
		return json_encode($resp);
	}
	function update_like()
	{
		extract($_POST);
		if ($status == 1) {
			$sql = "INSERT INTO `post_like` set id_post = '{$post_id}', id_user = '{$this->settings->userdata('id_user')}'";
		} else {
			$sql = "DELETE FROM `post_like` where id_post = '{$post_id}' and id_user = '{$this->settings->userdata('id_user')}'";
		}
		$process = $this->conn->query($sql);
		if ($process) {
			$resp['status'] = 'success';
			$newLikes = $this->conn->query("SELECT id_user FROM `post_like` where id_post = '{$post_id}' ")->num_rows;
			$this->conn->query("UPDATE post SET `likes` = $newLikes where id_post = '{$post_id}' ");
			$resp['likes'] = $newLikes;
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_comment()
	{
		extract($_POST);
		$sql = "INSERT INTO `post` set id_post_comment = '{$post_id}', id_user = '{$this->settings->userdata('id_user')}', `content_post` = '{$comment}'";
		$process = $this->conn->query($sql);
		if ($process) {
			$resp['status'] = 'success';
			$commentQuery = "SELECT COUNT(id_post) as comments FROM post where removed = 0 and id_post_comment = '{$post_id}'";
			$commentResult = $this->conn->query($commentQuery);
			if ($commentResult->num_rows != 0) {
				$comments = $commentResult->fetch_assoc();
				$resp['comments'] = $comments['comments'];
			} else {
				$resp['comments'] = 0;
			}
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function delete_comment()
	{
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `post` where id_post = '{$id}'");
		if ($del) {
			$resp['status'] = 'success';
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'save_post':
		echo $Master->save_post2();
		break;
	case 'delete_post':
		echo $Master->delete_post();
		break;
	case 'update_post_status':
		echo $Master->update_post_status();
		break;
	case 'update_like':
		echo $Master->update_like();
		break;
	case 'save_comment':
		echo $Master->save_comment();
		break;
	case 'delete_comment':
		echo $Master->delete_comment();
		break;
	default:
		// echo $sysset->index();
		break;
}