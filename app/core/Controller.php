<?php 
class Controller{
    public $absPath = "http://localhost/budeshi-2.0/webroot/";
    public $fileroot = "testmvc/webroot/";
    public $notSet = [];

    public function checkLogin(){
        session_start();
        $status = false;
        if(isset($_SESSION["username"]) and isset($_SESSION["id"])){
            $status = true;
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
}