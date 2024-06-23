<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';

class Login extends DBConnection
{

	private $USER_ROLE = "USER";
	private $ADMIN_ROLE = "ADMIN";
	private $BANNED_TEMPORALY = "BANNED_TMP";

	private $settings;
	public function __construct()
	{
		global $_settings;
		$this->settings = $_settings;

		parent::__construct();
		ini_set('display_error', 1);
	}
	public function __destruct()
	{
		parent::__destruct();
	}
	public function index()
	{
		echo "<h1>Accès refusé</h1> <a href='" . BASE_URL . "'>Veuillez retourner à la page précédente.</a>";
	}

	function loginTest()
	{
		$input = json_decode(file_get_contents('php://input'), true);

	}

	public function login()
	{

		$input = json_decode(file_get_contents('php://input'), true);
		$identity = '';
		$stmt = $this->conn->prepare("SELECT * from user where username = ? or email = ? and password = ? and banned_forever = 0 and code_role = ? ");
		$password = md5($input['password']);
		if(isset($input['username'])){
			$identity = $input['username']; 
		}
		else {
			$identity = $input['email'];
		}
		$stmt->bind_param('ssss', $identity, $identity, $password, $this->ADMIN_ROLE);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows != 0) {
			$data = $result->fetch_array();
			foreach ($data as $k => $v) {
				if (!is_numeric($k) && $k != 'password') {
					$this->settings->set_userdata($k, $v);
				}
			}
			//Mise à jour de l'utilisateur
			$query = $this->conn->prepare("UPDATE user SET connected = 1 WHERE username = ? or email = ? ");
			$query->bind_param('ss', $username, $username);
			$query->execute();
			//Saugevarde des valeurs en session
			$this->settings->set_userdata('id_user', $data['id_user']);
			$this->settings->set_userdata('email', $data['email']);
			$this->settings->set_userdata('role', "ADMIN");
			return json_encode(array('status' => 'success', 'message' => 'Login successfull'));
		} else {
			return json_encode(array('status' => 'failed', 'message' => 'Nom d\'utilisateur ou mot de passe incorrect. Veuillez rééssayer.'));
		}

	}
	public function logout()
	{
		if ($this->settings->sess_des()) {
			redirect('admin/login.php');
		}
	}

	public function user_register()
	{

	}

	public function user_login()
	{
		$input = json_decode(file_get_contents('php://input'), true);

		if (!isset($input['username']) || !isset($input['password'])) {
			echo json_encode(['status' => 'failed', 'message' => 'Les champs username et password sont obligatoires.']);
			return;
		}

		$username = $input['username'];
		$password = md5($input['password']);
		$username = formatUsername($username);

		$stmt = $this->conn->prepare("SELECT * FROM user WHERE (username = ? OR email = ?) AND password = ? AND banned_forever = 0 AND code_role = ?");
		$stmt->bind_param('ssss', $username, $username, $password, $this->USER_ROLE);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows != 0) {
			$data = $result->fetch_array();
			foreach ($data as $k => $v) {
				if (!is_numeric($k) && $k != 'password') {
					$this->settings->set_userdata($k, $v);
				}
			}

			$query = $this->conn->prepare("UPDATE user SET connected = 1 WHERE username = ? OR email = ?");
			$query->bind_param('ss', $username, $username);
			$query->execute();

			if ($data['banned_temporarly'] != 0) {
				$process = $this->conn->query("SELECT * FROM interdiction WHERE id_user = '{$data['id_user']}' AND actif = 0 AND code_interdiction_type = '{$this->BANNED_TEMPORALY}'");
				if ($process->num_rows > 0) {
					$data1 = $process->fetch_array();
					foreach ($data1 as $k => $v) {
						if ($k == 'delay') {
							$this->settings->set_userdata($k, $v);
						}
						if ($k == 'interdiction_date') {
							$this->settings->set_userdata($k, $v);
						}
					}
				}
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
				'role' => $this->USER_ROLE,
				'self_intro' => $this->settings->userdata('self_intro'),
				'address' => $this->settings->userdata('address'),
				'phone' => $this->settings->userdata('phone'),
			];

			echo json_encode($resp);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'Identifiants incorrects. Veuillez réessayer.']);
		}
	}
	public function user_logout($id_user)
	{
		//Mise à jour de l'utilisateur
		$query = $this->conn->prepare("UPDATE user SET `connected`= 0 WHERE `id_user`= $id_user ");
		$query->execute();
		if ($this->settings->sess_des()) {
			echo json_encode(['status' => 'success', 'message' => 'Déconnexion réussie.']);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'Échec de la déconnexion.']);
		}
	}

	public function user_update()
	{
		if (isset($_POST["username"]) && isset($_POST["confirm_password"])) {
			$username = $_POST["username"];
			$password = md5($_POST["confirm_password"]);
			echo $password;

			$query1 = $this->conn->prepare("SELECT * FROM user WHERE username = '$username'");
			$query1->execute();
			$result = $query1->get_result();
			if ($result->num_rows == 0) {
				echo '<div class="alert alert-danger" role="alert">' . "Nom d'utilisateur incorrect" . '</div>';
			}
			$query = $this->conn->prepare("UPDATE user SET password = '$password' WHERE username = '$username'");
			$query->execute();
			$data = $query->get_result();
			if ($data) {
				$resp['status'] = 'success';
			} else {
				$resp['status'] = 'failed';
			}

			return json_encode($resp);
		}
	}

}
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$auth = new Login();
switch ($action) {
	case 'login':
		echo $auth->login();
		break;
	case 'logout':
		echo $auth->logout();
		break;
	case 'user_login':
		echo $auth->user_login();
		break;
	case 'user_logout':
		if (isset($_GET['id_user'])) {
			echo $auth->user_logout($_GET['id_user']);
		} else {
			echo json_encode(['status' => 'failed', 'message' => 'ID utilisateur manquant.']);
		}
		break;
	case 'user_update':
		echo $auth->user_update();
		break;
	default:
		echo $auth->index();
		break;
}

