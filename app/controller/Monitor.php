<?php
class Monitor extends Controller
{

    public function index($login_error = "")
    {
        if ($this->checkLogin()) {
            require_once ("../app/models/MonitorDb.php");
            $monitor = new MonitorModel();
            $mdas = $monitor->getMDAList();
            require_once ("../app/views/backend/home.html");
        }
        else {
            require_once ("../app/views/backend/login.html");
        }
    }
    public function transact($type = "login")
    {
        if ($type == "login") {
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                die("Unauthorized Access");
            }
            $username = filter_var($_POST["username"]);
            $password = filter_var($_POST["password"]);
            if ($this->login($username, $password)) {
                $this->redirect("Monitor/");
            }
            else {
                $this->redirect("Monitor/index/e");
            }
        }
        if ($type == "logout") {
            session_start();
            session_unset();
            session_destroy();
            $this->redirect("Monitor/");
        }
    }
    public function delete($id)
    {
        $access = $this->checkLogin();
        if (!$access) {
            $data_obj["message"] = "You are not authorized to perform this action";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if ($access > 1) {
            $data_obj["message"] = "You are not authorized to perform this action";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        require_once ("../app/models/MonitorDb.php");
        $monitor = new MonitorModel();

        $monitor->delete($id, "mdas");
        $data_obj = [];
        $data_obj["message"] = "MDA Deleted";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;

    }
    public function addMDA()
    {
        $data_obj = [];
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        $data = json_decode($_POST["data"]);
        require_once ("../app/models/MonitorDb.php");
        $monitor = new MonitorModel();
        $monitor->addMDA($data);
        $data_obj["message"] = "MDA added";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;
        die();

    }
    public function ajaxget()
    {
        $data_obj = [];
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        if (isset($_POST["id"])) {
            require_once ("../app/models/MonitorDb.php");
            $mda = new MonitorModel();
            $json = $mda->getMDA($_POST["id"], "e_");
            echo $json;
        }

    }
    public function edit($id)
    {
        $access = $this->checkLogin();
        if (!$access) {
            $data_obj["message"] = "You are not authorized to perform this action";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if ($access > 1) {
            $data_obj["message"] = "You are not authorized to perform this action";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        $obj = json_decode($_POST["data"]);
        require_once ("../app/models/MonitorDb.php");
        $monitor = new MonitorModel();
        $monitor->updateMDA($id, $obj);
        $data_obj["message"] = "MDA Edited";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;
        die();



    }



}
?>