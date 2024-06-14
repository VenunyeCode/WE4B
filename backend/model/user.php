<?php
require_once('../config.php');
require_once('../classes/DBConnection.php');
class User {
    private $username;
    private $firstname;
    private $lastname;
    private $password;
    private $email;
    private $phone;

    function getUsername()
    {
        return $this->username;
    }
    function getFirstName()
    {
        return $this->firstname;
    }
    function getLastName()
    {
        return $this->lastname;
    }
    function getPassword(){
        return $this->password;
    }

    function getEmail(){
        return $this->email;
    }

    function getPhone(){
        return $this->phone;
    }

    function __construct($username, $password, $email, $firstname, $lastname, $phone){
        global $sQLConn;
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->phone = $phone;
    }

    function setPassword($pass){
        $this->password = $pass;
    }

    function getUserId() {
        $user_id = 0;
        $query = "SELECT id_user FROM user WHERE username = '" . $this->getUsername() . "'";    
        $conn = new DBConnection();
        $result = $conn->conn->query($query);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id_user'];
        }
        return $user_id;
    }
    
}