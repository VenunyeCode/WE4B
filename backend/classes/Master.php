<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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
		$input = json_decode(file_get_contents('php://input'), true);

		$content = $_POST['content'] ?? null;
		$id_user = $_POST['id_user'] ?? null;

		if (!$content || !$id_user) {
			$resp["status"] = "failed";
			$resp["message"] = "Missing content or user ID.";
			return json_encode($resp);
		}


		//Requête de création d'un nouveau post/message
		$query = "INSERT INTO `post` (content_post, id_user) VALUES            
		('" . AppUtils::securizeStringForSQL($_POST['content']) . "', '" . $_POST['id_user'] . "')";
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

			//$resp['aid'] = $aid;
			$resp["status"] = "success";
			$resp["message"] = "Nouveau post effectué avec succès.";
			//Insérer l'image, si c'est un post avec media
			if (isset($_FILES['img'])) {
				$resp["error"] = "Fichier chargé";

				$img = new ImgFileUploader($this->conn);

				$img->SaveFileAsNew($aid);
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
		$input = json_decode(file_get_contents('php://input'), true);
		if ($input['status'] == 1) {
			$sql = "INSERT INTO `post_like` set id_post = '{$input['id_post']}', id_user = '{$input['id_user']}'";
		} else {
			$sql = "DELETE FROM `post_like` where id_post = '{$input['id_post']}' and id_user = '{$input['id_user']}'";
		}
		$process = $this->conn->query($sql);
		if ($process) {
			$resp['status'] = 'success';
			$resp['message'] = 'Post liked successfully';
			$newLikes = $this->conn->query("SELECT id_user FROM `post_like` where id_post = '{$input['id_post']}' ")->num_rows;
			$this->conn->query("UPDATE post SET `likes` = $newLikes where id_post = '{$input['id_post']}' ");
			$resp['likes'] = $newLikes;
		} else {
			$resp['status'] = 'failed';
			$resp['message'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_comment()
	{
		$input = json_decode(file_get_contents('php://input'), true);

		$sql = "INSERT INTO `post` set id_post_comment = '{$input['id_post']}', id_user = '{$input['id_user']}', `content_post` = '{$input['comment']}'";
		$process = $this->conn->query($sql);
		if ($process) {
			$resp['status'] = 'success';
			$resp['message'] = 'Comment saved successfully';
			$commentQuery = "SELECT COUNT(id_post) as comments FROM post where removed = 0 and id_post_comment = '{$input['id_post']}'";
			$commentResult = $this->conn->query($commentQuery);
			if ($commentResult->num_rows != 0) {
				$comments = $commentResult->fetch_assoc();
				$resp['comments'] = $comments['comments'];
			} else {
				$resp['comments'] = 0;
			}
		} else {
			$resp['status'] = 'failed';
			$resp['message'] = $this->conn->error;
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

	function get_lastest_post()
	{
		$sql = "SELECT p.*, concat(u.firstname, ' ',u.lastname) as `author_name` , u.avatar as `author_avatar`, u.username as `author_username` FROM `post` p INNER JOIN `user` u ON p.id_user = u.id_user WHERE p.id_post_comment IS NULL AND p.removed = 0 ORDER BY p.`post_date` DESC";
		$process = $this->conn->query($sql);
		$data = [];
		if ($process) {
			$resp['status'] = 'success';
			$resp['message'] = 'Posts successfully retrieved';
			if ($process->num_rows > 0) {
				while ($row = $process->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$resp['data'] = $data;
		} else {
			$resp['status'] = 'failed';
			$resp['message'] = $this->conn->error;
		}
		return json_encode($resp);
	}

	function get_user_posts($idUser)
	{
		$sql = "SELECT p.*, concat(u.firstname, ' ',u.lastname) as `author_name` , u.avatar as `author_avatar`, u.username as `author_username` FROM `post` p INNER JOIN `user` u ON p.id_user = u.id_user WHERE p.id_post_comment IS NULL AND p.removed = 0 AND p.id_user = '{$idUser}' ORDER BY p.`post_date` DESC";
		$process = $this->conn->query($sql);
		$data = [];
		if ($process) {
			$resp['status'] = 'success';
			$resp['message'] = 'Posts successfully retrieved';
			if ($process->num_rows > 0) {
				while ($row = $process->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$resp['data'] = $data;
		} else {
			$resp['status'] = 'failed';
			$resp['message'] = $this->conn->error;
		}
		return json_encode($resp);
	}

	function get_post_detail($id_post, $id_viewer)
	{
		$data = [];
		if ($id_viewer != 0) {
			$query = "SELECT p.*, concat(u.firstname, ' ',u.lastname) as `author_name` , u.avatar as `author_avatar`, u.username as `author_username` FROM `post` p INNER JOIN `user` u ON p.id_user = u.id_user WHERE p.id_post_comment IS NULL AND p.removed = 0 AND p.id_post = '{$id_post}' ORDER BY p.`post_date` DESC";
			$queryAddView = "INSERT INTO `post_view` SET `id_post` = {$id_post}, id_user = {$id_viewer}";
			$result = $this->conn->query($query);
			$result1 = NULL;

			if ($result->num_rows != 0) {
				$verifyUser = $this->conn->query("SELECT id_user FROM `post_view` WHERE id_post = '{$id_post}' ")->num_rows;
				if ($verifyUser == 0) {
					$this->conn->query($queryAddView);
					$newViews = $this->conn->query("SELECT id_user FROM `post_view` WHERE id_post = '{$id_post}' ")->num_rows;
					$queryUpdateView = "UPDATE `post` SET `views`= $newViews WHERE id_post = $id_post";
					$this->conn->query($queryUpdateView);
					$result1 = $this->conn->query($query);
				}

				while ($row = $verifyUser == 0 ? $result1->fetch_assoc() : $result->fetch_assoc()) {
					$data[] = $row;
				}
				$resp['status'] = 'success';
				$resp['message'] = 'Post detail successfully retrieved';
				$resp['data'] = $data;
			} else {
				$resp['status'] = 'failed';
				$resp['message'] = $this->conn->error;
			}
		} else {
			$query = "SELECT p.*, concat(u.firstname, ' ',u.lastname) as `author_name` , u.avatar as `author_avatar`, u.username as `author_username` FROM `post` p INNER JOIN `user` u ON p.id_user = u.id_user WHERE p.id_post_comment IS NULL AND p.removed = 0 AND p.id_post = '{$id_post}' ORDER BY p.`post_date` DESC";
			$result = $this->conn->query($query);
			if ($result->num_rows != 0) {
				while ($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$resp['status'] = 'success';
			$resp['message'] = 'Post detail successfully retrieved';
			$resp['data'] = $data;
		}


		return json_encode($resp);
	}

	function get_comments_by_post($id_post)
	{
		$sql = "SELECT p.*, concat(u.firstname, ' ',u.lastname) as `author_name` , u.avatar as `author_avatar`, u.username as `author_username` FROM `post` p inner join `user` u on p.id_user = u.id_user WHERE p.removed = 0 and p.`id_post_comment` = {$id_post} ORDER BY p.`post_date` DESC";
		$process = $this->conn->query($sql);
		$data = [];
		if ($process) {
			$resp['status'] = 'success';
			$resp['message'] = 'Comment of post successfully retrieved';
			if ($process->num_rows > 0) {
				while ($row = $process->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$resp['data'] = $data;
		} else {
			$resp['status'] = 'failed';
			$resp['message'] = $this->conn->error;
		}
		return json_encode($resp);
	}

	function check_liked_post()
	{
		$input = json_decode(file_get_contents('php://input'), true);
		$likeQuery = $this->conn->query("SELECT id_post_like FROM `post_like` where id_post = '{$input['id_post']}' and id_user = '{$input['id_user']}'")->num_rows > 0;
		$resp['status'] = 'success';
		if (isset($likeQuery) && !!$likeQuery) {
			$resp['message'] = 'User has liked this post';
			$resp['liked'] = true;
		} else {
			$resp['message'] = 'User didn\'t like this post';
			$resp['liked'] = false;
		}

		return json_encode($resp);
	}

	function get_insight($id_user)
	{
		try {
			$dataLike = [];
			$dataView = [];
			$unreadNotif = $this->conn->query("SELECT id_notification FROM `notification` WHERE is_notification_read = 0 AND removed = 0 AND id_user = {$id_user} ")->num_rows;
			$allLikes = $this->conn->query("SELECT pl.id_post_like FROM `post_like` pl JOIN post p ON pl.id_post = p.id_post JOIN user u ON p.id_user = u.id_user WHERE p.id_user = {$id_user}")->num_rows;
			$allViews = $this->conn->query("SELECT pv.id_post_view FROM `post_view` pv JOIN post p ON pv.id_post = p.id_post JOIN user u ON p.id_user = u.id_user WHERE p.id_user = {$id_user}")->num_rows;
			$queryWeekView = "SELECT pv.id_post_view, us.avatar as `author_avatar`, us.id_user, concat(us.firstname,' ',us.lastname) as `author_name` FROM `post_view` pv JOIN post p ON pv.id_post = p.id_post JOIN user u ON p.id_user = u.id_user JOIN user us ON pv.id_user = us.id_user WHERE YEARWEEK(pv.view_date, 1) = YEARWEEK(CURDATE(), 1) AND p.id_user = {$id_user}";
			$queryWeekLike = "SELECT pl.id_post_like, us.avatar as `author_avatar`, us.id_user, concat(us.firstname,' ',us.lastname) as `author_name` FROM `post_like` pl JOIN post p ON pl.id_post = p.id_post JOIN user u ON p.id_user = u.id_user JOIN user us ON pl.id_user = us.id_user WHERE YEARWEEK(pl.like_date, 1) = YEARWEEK(CURDATE(), 1) AND p.id_user = {$id_user}";

			$weekViewResult = $this->conn->query($queryWeekView);
			$weekLikeResult = $this->conn->query($queryWeekLike);

			//if($weekViewResult->num_rows>0){
			$weekViews = $weekViewResult->num_rows > 0 ? $weekViewResult->num_rows : 0;
			//}
			//if($weekLikeResult->num_rows>0){
			$weekLikes = $weekLikeResult->num_rows > 0 ? $weekLikeResult->num_rows : 0;

			if ($weekLikes > 0) {
				while ($weekLike = $weekLikeResult->fetch_assoc()) {
					$dataLike[] = $weekLike;
				}
			}
			if ($weekViews > 0) {
				while ($weekView = $weekViewResult->fetch_assoc()) {
					$dataView[] = $weekView;
				}
			}

			$resp['status'] = 'success';
			$resp['message'] = 'Successful';
			$resp['unread_notif'] = $unreadNotif;
			$resp['likes'] = $allLikes;
			$resp['views'] = $allViews;
			$resp['week_views'] = $weekViews;
			$resp['week_likes'] = $weekLikes;
			$resp['data_like'] = $dataLike;
			$resp['data_view'] = $dataView;
		} catch (\Throwable $th) {
			$resp['status'] = 'failed';
			$resp['message'] = 'Failed';
		}

		return json_encode($resp);
	}

	function get_user_info($idUser, $connectedUser)
	{
		try {
			$ownerQuery = "SELECT * FROM `user` WHERE id_user = " . $idUser;
			$ownerResult = $this->conn->query($ownerQuery);
			$data = [];
			if ($ownerResult->num_rows > 0) {
				while ($row = $ownerResult->fetch_assoc()) {
					$data[] = $row;
				}
			}
			//Récupération des informations de follower
			$followers = $this->conn->query("SELECT id_user_follower FROM followship WHERE id_user_following = {$idUser} ")->num_rows;
			//Savoir si l'utilisateur connecté suis ou pas la présente page de l'utilisateur
			$connectedFollower = $this->conn->query("SELECT id_user_follower FROM followship WHERE id_user_following = {$idUser} and id_user_follower = {$connectedUser} ")->num_rows;

			$resp['status'] = 'success';
			$resp['message'] = 'Successful';
			$resp['followers'] = $followers;
			$resp['is_following'] = $connectedFollower == 0 ? false : true;
			$resp['data'] = $data[0];
		} catch (\Throwable $th) {
			$resp['status'] = 'failed';
			$resp['message'] = 'Failed';
		}

		return json_encode($resp);
	}

	function get_user_notification($idUser)
	{
		try {
			//Suppression des notifications vielle de plus de deux semaines
			$this->conn->query("DELETE FROM notification WHERE notification_date < DATE_SUB(NOW(), INTERVAL 2 WEEK)");

			$resultQueryNotif = $this->conn->query("SELECT n.*, COALESCE(u.id_user,'') as 'post_author', COALESCE(u.avatar,'') as 'author_avatar'
                                    FROM notification n
                                    LEFT JOIN post p ON n.id_post = p.id_post
                                    LEFT JOIN user u ON p.id_user = u.id_user
                                    WHERE n.id_user = {$idUser} AND n.removed = 0 ORDER BY n.notification_date DESC");
			$data = [];
			if ($resultQueryNotif->num_rows > 0) {
				while ($row = $resultQueryNotif->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$resp['status'] = 'success';
			$resp['message'] = 'Successful';
			$resp['data'] = $data;
		} catch (\Throwable $th) {
			$resp['status'] = 'failed';
			$resp['message'] = 'Failed';
		}

		return json_encode($resp);
	}

	function get_followers_likers($idUser)
	{
		try {
			//Récupération des informations de follower
			$nbFollowings = 0;
			$nbFollowers = 0;
			$nbLikers = 0;

			$followers = $this->conn->query("SELECT id_user_follower FROM followship WHERE id_user_following = {$idUser} ")->num_rows;
			$queryLikers = "SELECT up.*, COALESCE(COUNT(up.id_user),0) as 'likers' FROM `post_like` pl JOIN post p ON pl.id_post = p.id_post JOIN user u ON p.id_user = u.id_user JOIN user up ON pl.id_user = up.id_user WHERE p.id_user = {$idUser} GROUP BY up.id_user ";
			$queryFollowers = "SELECT u.*, COALESCE(COUNT(u.id_user),0) as 'followers' FROM followship f JOIN user u ON f.id_user_follower = u.id_user WHERE f.id_user_following = {$idUser} GROUP BY u.id_user ";
			$queryFollowings = "SELECT u.* FROM followship f JOIN user u ON f.id_user_following = u.id_user WHERE f.id_user_follower = {$idUser}";
			//$myFollowings = $conn->query("SELECT u.*, COALESCE(COUNT(u.id_user),0) as 'followings' FROM followship f JOIN user u ON f.id_user_following = u.id_user WHERE f.id_user_follower = {$_settings->userdata('id_user')}")->num_rows;
			$resultQueryLikers = $this->conn->query($queryLikers);
			$resultQueryFollowers = $this->conn->query($queryFollowers);
			$resultQueryFollowings = $this->conn->query($queryFollowings);
			$dataLikers = [];
			$dataFollowers = [];
			$dataFollowings = [];
			if ($resultQueryLikers->num_rows > 0) {
				$nbLikers = $resultQueryLikers->num_rows;
				while ($row = $resultQueryLikers->fetch_assoc()) {
					$dataLikers[] = $row;
				}
			}
			if ($resultQueryFollowers->num_rows > 0) {
				$nbFollowers = $resultQueryFollowers->num_rows;
				while ($row = $resultQueryFollowers->fetch_assoc()) {
					$dataFollowers[] = $row;
				}
			}
			if ($resultQueryFollowings->num_rows > 0) {
				$nbFollowings = $resultQueryFollowings->num_rows;
				while ($row = $resultQueryFollowings->fetch_assoc()) {
					$dataFollowings[] = $row;
				}
			}

			$resp['status'] = 'success';
			$resp['message'] = 'Successful';
			$resp['followers'] = $nbFollowers;
			$resp['followings'] = $nbFollowings;
			$resp['likes'] = $nbLikers;
			$resp['data_like'] = $dataLikers;
			$resp['data_follower'] = $dataFollowers;
			$resp['data_following'] = $dataFollowings;
		} catch (\Throwable $th) {
			$resp['status'] = 'failed';
			$resp['message'] = 'Failed';
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
	case 'lastest_post':
		echo $Master->get_lastest_post();
		break;
	case 'post_comments':
		if (isset($_GET['id_post'])) {
			echo $Master->get_comments_by_post($_GET['id_post']);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'ID post manquant.']);
		}
		break;
	case 'user_posts':
		if (isset($_GET['id_user'])) {
			echo $Master->get_user_posts($_GET['id_user']);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'ID user manquant.']);
		}
		break;
	case 'check_like':
		echo $Master->check_liked_post();
		break;
	case 'insight':
		if (isset($_GET['id_user'])) {
			echo $Master->get_insight($_GET['id_user']);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'ID user manquant.']);
		}
		break;
	case 'post_detail':
		if (isset($_GET['id_post']) && isset($_GET['id_viewer'])) {
			echo $Master->get_post_detail($_GET['id_post'], $_GET['id_viewer']);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'ID post manquant.']);
		}
		break;
	case 'user_info':
		if (isset($_GET['id_user']) && isset($_GET['id_connected'])) {
			echo $Master->get_user_info($_GET['id_user'], $_GET['id_connected']);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'ID USER manquant.']);
		}
		break;
	case 'user_notif':
		if (isset($_GET['id_user'])) {
			echo $Master->get_user_notification($_GET['id_user']);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'ID USER manquant.']);
		}
		break;
	case 'user_follows':
		if (isset($_GET['id_user'])) {
			echo $Master->get_followers_likers($_GET['id_user']);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'ID USER manquant.']);
		}
		break;
	default:
		// echo $sysset->index();
		break;
}