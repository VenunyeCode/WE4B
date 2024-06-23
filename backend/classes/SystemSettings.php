<?php
if (!class_exists('DBConnection')) {
	require_once ('../config.php');
	require_once ('DBConnection.php');
}
class SystemSettings extends DBConnection
{
	public function __construct()
	{
		parent::__construct();
	}
	function __destruct()
	{
	}
	function check_connection()
	{
		return ($this->conn);
	}
	function load_system_info()
	{
		$sql = "SELECT * FROM system_info";
		$qry = $this->conn->query($sql);
		while ($row = $qry->fetch_assoc()) {
			$_SESSION['system_info'][$row['meta_field']] = $row['meta_value'];
		}
	}
	function update_system_info()
	{
		$sql = "SELECT * FROM system_info";
		$qry = $this->conn->query($sql);
		while ($row = $qry->fetch_assoc()) {
			if (isset($_SESSION['system_info'][$row['meta_field']]))
				unset($_SESSION['system_info'][$row['meta_field']]);
			$_SESSION['system_info'][$row['meta_field']] = $row['meta_value'];
		}
		return true;
	}
	function update_settings_info()
	{
		$data = "";
		foreach ($_POST as $key => $value) {
			if (!in_array($key, array("content")))
				if (isset($_SESSION['system_info'][$key])) {
					$value = str_replace("'", "&apos;", $value);
					$qry = $this->conn->query("UPDATE system_info set meta_value = '{$value}' where meta_field = '{$key}' ");
				} else {
					$qry = $this->conn->query("INSERT into system_info set meta_value = '{$value}', meta_field = '{$key}' ");
				}
		}

		if (isset($_POST['content'])) {
			foreach ($_POST['content'] as $k => $v) {
				file_put_contents("../$k.html", $v);
			}
		}
		if (!empty($_FILES['img']['tmp_name'])) {
			$ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
			$fname = "uploads/logo.png";
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
			$uploadfile = imagescale($uploadfile, 200, 200);
			$temp = imagecreatetruecolor(200, 200);
			$alpha = imagecolorallocatealpha($temp, 0, 0, 0, 127);
			imagecolortransparent($temp, $alpha);
			imagefill($temp, 0, 0, $alpha);
			imagecopy($temp, $uploadfile, 0, 0, 0, 0, 200, 200);
			imagesavealpha($temp, true);

			if (is_file(BASE_APP . $fname))
				unlink(BASE_APP . $fname);

			$upload = imagepng($temp, BASE_APP . $fname, 9);
			if ($upload) {
				if (isset($_SESSION['system_info']['logo'])) {
					$qry = $this->conn->query("UPDATE system_info set meta_value = CONCAT('{$fname}', '?v=',unix_timestamp(CURRENT_TIMESTAMP)) where meta_field = 'logo' ");
					if (is_file(BASE_APP . $_SESSION['system_info']['logo']))
						unlink(BASE_APP . $_SESSION['system_info']['logo']);
				} else {
					$qry = $this->conn->query("INSERT into system_info set meta_value = '{$fname}',meta_field = 'logo' ");
				}
			}
			imagedestroy($temp);
		}
		if (!empty($_FILES['cover']['tmp_name'])) {
			$ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
			$fname = "uploads/cover.png";
			$accept = array('image/jpeg', 'image/png');
			if (!in_array($_FILES['cover']['type'], $accept)) {
				$err = "Image file type is invalid";
			}
			if ($_FILES['cover']['type'] == 'image/jpeg')
				$uploadfile = imagecreatefromjpeg($_FILES['cover']['tmp_name']);
			elseif ($_FILES['cover']['type'] == 'image/png')
				$uploadfile = imagecreatefrompng($_FILES['cover']['tmp_name']);
			if (!$uploadfile) {
				$err = "Image is invalid";
			}
			list($width, $height) = getimagesize($_FILES['cover']['tmp_name']);
			$temp = imagescale($uploadfile, $width, $height);
			if (is_file(BASE_APP . $fname))
				unlink(BASE_APP . $fname);
			$upload = imagepng($temp, BASE_APP . $fname, 8);
			if ($upload) {
				if (isset($_SESSION['system_info']['cover'])) {
					$qry = $this->conn->query("UPDATE system_info set meta_value = CONCAT('{$fname}', '?v=',unix_timestamp(CURRENT_TIMESTAMP)) where meta_field = 'cover' ");
					if (is_file(BASE_APP . $_SESSION['system_info']['cover']))
						unlink(BASE_APP . $_SESSION['system_info']['cover']);
				} else {
					$qry = $this->conn->query("INSERT into system_info set meta_value = '{$fname}',meta_field = 'cover' ");
				}
			}
			imagedestroy($temp);
		}
		if (isset($_FILES['banners']) && count($_FILES['banners']['tmp_name']) > 0) {
			$err = '';
			$banner_path = "uploads/banner/";
			if (!is_dir(BASE_APP . $banner_path))
				mkdir(BASE_APP . $banner_path);
			foreach ($_FILES['banners']['tmp_name'] as $k => $v) {
				if (!empty($_FILES['banners']['tmp_name'][$k])) {
					$accept = array('image/jpeg', 'image/png');
					if (!in_array($_FILES['banners']['type'][$k], $accept)) {
						$err = "Image file type is invalid";
						break;
					}
					if ($_FILES['banners']['type'][$k] == 'image/jpeg')
						$uploadfile = imagecreatefromjpeg($_FILES['banners']['tmp_name'][$k]);
					elseif ($_FILES['banners']['type'][$k] == 'image/png')
						$uploadfile = imagecreatefrompng($_FILES['banners']['tmp_name'][$k]);
					if (!$uploadfile) {
						$err = "Image is invalid";
						break;
					}
					list($width, $height) = getimagesize($_FILES['banners']['tmp_name'][$k]);
					if ($width > 1200 || $height > 480) {
						if ($width > $height) {
							$perc = ($width - 1200) / $width;
							$width = 1200;
							$height = $height - ($height * $perc);
						} else {
							$perc = ($height - 480) / $height;
							$height = 480;
							$width = $width - ($width * $perc);
						}
					}
					$temp = imagescale($uploadfile, $width, $height);
					$spath = BASE_APP . $banner_path . '/' . $_FILES['banners']['name'][$k];
					$i = 1;
					while (true) {
						if (is_file($spath)) {
							$spath = BASE_APP . $banner_path . '/' . ($i++) . '_' . $_FILES['banners']['name'][$k];
						} else {
							break;
						}
					}
					if ($_FILES['banners']['type'][$k] == 'image/jpeg')
						imagejpeg($temp, $spath, 80);
					elseif ($_FILES['banners']['type'][$k] == 'image/png')
						imagepng($temp, $spath, 8);

					imagedestroy($temp);
				}
			}
			if (!empty($err)) {
				$resp['status'] = 'failed';
				$resp['msg'] = $err;
			}
		}

		$update = $this->update_system_info();
		if ($update) {
			// var_dump($_SESSION);
			$resp['status'] = 'success';
		} else {
			$resp['status'] = 'failed';
		}
		return json_encode($resp);
	}
	function set_userdata($field = '', $value = '')
	{
		if (!empty($field)) {
			$_SESSION['userdata'][$field] = $value;
		}
	}

	function set_respdata($field = '', $value = '')
	{
		if (!empty($field)) {
			$_SESSION['userdata'][$field] = $value;
		}
	}
	function userdata($field = '')
	{
		if (!empty($field)) {
			if (isset($_SESSION['userdata'][$field]))
				return $_SESSION['userdata'][$field];
			else
				return null;
		} else {
			return false;
		}
	}

	function set_flashdata($flash='',$value=''){
		if(!empty($flash) && !empty($value)){
			$_SESSION['flashdata'][$flash]= $value;
		return true;
		}
	}
	function chk_flashdata($flash = ''){
		if(isset($_SESSION['flashdata'][$flash])){
			return true;
		}else{
			return false;
		}
	}
	function flashdata($flash = ''){
		if(!empty($flash)){
			$_tmp = $_SESSION['flashdata'][$flash];
			unset($_SESSION['flashdata']);
			return $_tmp;
		}else{
			return false;
		}
	}

	function sess_des()
	{
		if (isset($_SESSION['userdata'])) {
			unset($_SESSION['userdata']);
			return true;
		}
		return true;
	}
	function info($field = '')
	{
		if (!empty($field)) {
			if (isset($_SESSION['system_info'][$field]))
				return $_SESSION['system_info'][$field];
			else
				return false;
		} else {
			return false;
		}
	}
	function set_info($field = '', $value = '')
	{
		if (!empty($field) && !empty($value)) {
			$_SESSION['system_info'][$field] = $value;
		}
	}
}
$_settings = new SystemSettings();
$_settings->load_system_info();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'update_settings':
		echo $sysset->update_settings_info();
		break;
	default:
		// echo $sysset->index();
		break;
}
?>