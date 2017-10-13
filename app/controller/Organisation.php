<?php
class Organisation extends Controller{
    

    public function index($page = 1){
        if(!$this->checkLogin()){
            $this->redirect("Monitor/");
        }
        if(!isset($page)){
            $this->redirect("Monitor/");
        }
        require_once ("../app/models/OrgDb.php");
        $orgdb = new OrganisationModel();
        $organizations = $orgdb->fetchOrgs($page);
        require_once ("../app/views/backend/organizations.html");

    }
    public function ajaxget(){
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
            require_once ("../app/models/OrgDb.php");
            $org = new OrganisationModel();
            $json = $org->getOrg($_POST["id"], "");
            echo $json;
        }

    }
    public function edit($id) {
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $obj = json_decode($_POST["data"]);
        require_once ("../app/models/OrgDb.php");
        $org = new OrganisationModel();
        $org->updateOrg($id, $obj);
        $data_obj["message"] = "Org Edited";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;
        die();
    }
    public function addOrg(){
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
        require_once ("../app/models/OrgDb.php");
        $org = new OrganisationModel();
        $org->addOrg($data);
        $data_obj["message"] = "Organisation added";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;
        die();
    }
    public function delete($id){
        $data_obj = [];
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if($access > 1){
            $data_obj["message"] = "You don't have clearance for this operation";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if(!isset($id) or !is_int((int)$id)){
            $data_obj["message"] = "Invalid Params";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }

        require_once ("../app/models/OrgDb.php");
        $org = new OrganisationModel();
        $org->deleteOrg($id);
        $data_obj["message"] = "Organisation Deleted";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;
        die();


    }
}
 ?>