<?php 
class Controller{
    public $absPath = "http://localhost/budeshi-2.0/webroot/";
    public $fileroot = "C:/xampp/htdocs/budeshi-2.0/app/";
    public $notSet = [];

    public function checkLogin(){
        session_start();
        $status = false;
        if(isset($_SESSION["username"]) and isset($_SESSION["id"]) and isset($_SESSION["access_level"])){
            $status = $_SESSION["access_level"];
        }
        return $status;
    }
    protected function trimData($data){
        $dataToReturn = trim($data);
        $dataToReturn = stripslashes($dataToReturn);
        $dataToReturn = htmlspecialchars($dataToReturn);
        return $dataToReturn;
    }
    public function redirect($url){
        if(!headers_sent()){
            header("Location: ".$this->absPath.$url);
            }
        else{
            die('Link Error: headers already sent');
            }
    }
    public function checkIfSet($array){
        $status = true;
        foreach($array as $value){
            if(!isset($value)){
                $status = false;
                $this->notSet[] = $value;
            }
        }
        return $status;
    }
    public function login($username, $password){
        require_once("../app/core/Model.php");
        $query = "SELECT * FROM users WHERE username = '".$username."' AND password = '".$password."'";
        $model = new Model();
        $result = $model->query($query);
        if(!result){
            die($this->error);
        }
        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_array($result);
            session_start();
            $_SESSION["username"] = $row["username"];
            $_SESSION["id"] = $row["id"];
            $_SESSION["access_level"] = $row["access_id"];
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    public function checkRequestMethod($type = "POST"){
        $status = FALSE;
        switch($type){
            case "POST":
            if($_SERVER["REQUEST_METHOD"] == $type){
                $status = TRUE;
            }
            else{
                $status = FALSE;
            }
            break;
            case "GET":
            if($_SERVER["REQUEST_METHOD"] == $type){
                $status = TRUE;
            }
            else{
                $status = FALSE;
            }
            break;
        }
        return $status;
        
    }
}