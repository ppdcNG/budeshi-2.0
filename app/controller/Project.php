<?php 
class Project extends Controller
{

    public function index($mda_id, $page = 1)
    {
        if (!$this->checkLogin()) {
            echo "forbidden access";
            die();
        }
        require_once ("../app/models/ProjectDb.php");
        $projectDb = new ProjectModel();
        $mda_name = $projectDb->mda_name;
        $projects = $projectDb->getMDAprojects($mda_id,$page);
        $pagination = $projectDb->renderPageLinks($page,$projectDb->no,$mda_id);
        $mda_name = $projectDb->mda_name;
        require_once ("../app/views/backend/institution.html");
    }
    public function getReleases()
    {
        require_once ("../app/models/ProjectDb.php");
        $projectDb = new ProjectModel();
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
        $releases_html = "";
        $project_id = isset($_POST["id"]) ? $_POST["id"] : 1;
        $planning_releases = $projectDb->fillRelease($project_id, "Planning", "planning");
        $tender_releases = $projectDb->fillRelease($project_id, "Tender", "tender");
        $award_releases = $projectDb->fillRelease($project_id, "Award", "award");
        $contract_releases = $projectDb->fillRelease($project_id, "Contract", "contract");
        $implmntn_releases = $projectDb->fillRelease($project_id, "Implementation", "implementation");
        $yes_releases = $planning_releases or $tender_releases or $award_releases or $contract_releases or $implmntn_releases;
        if ($yes_releases) {
            $releases_html .= $planning_releases == false ? "" : $planning_releases;
            $releases_html .= $tender_releases == false ? "" : $tender_releases;
            $releases_html .= $implmntn_releases == false ? "" : $tender_releases;
            $releases_html .= $contract_releases == false ? "" : $contract_releases;
            $releases_html .= $award_releases == false ? "" : $award_releases;
        }
        else {
            $releases_html = "<li><em>No Releases found for this Record</em></li>";
        }
        echo $releases_html;
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
            require_once ("../app/models/ProjectDb.php");
            $projectDb = new ProjectModel();
            $json = $projectDb->getProject($_POST["id"], "e_");
            echo $json;
        }

    }
    public function ajax($type)
    {
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if (!isset($_POST["data"])) {
            $data_obj["message"] = "Parameter Not set";
            $data_obj["ajaxstatus"] = "Failed";
            echo json_encode($json);
            die();
        }
        require_once ("../app/models/ProjectDb.php");
        switch ($type) {
            case "add" :
                $data_obj = json_decode($_POST["data"]);
                $project_db = new ProjectModel();
                $data_obj->updated_by = $access;
                $output = $project_db->addProject($data_obj);
                $output["ajaxstatus"] = "success";
                $json_output = json_encode($output);
                echo $json_output;
                break;
            case "edit" :
                $data_obj = json_decode($_POST["data"]);
                $project_db = new ProjectModel();
                $output = $project_db->editProject($data_obj);
                $output["ajaxstatus"] = "success";
                $json_output = json_encode($output);
                echo $json_output;
                break;
        }
    }
    public function delete()
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
        if ($access > 2) {
            $data_obj["message"] = "You are not authorized to perform this action";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if (!isset($_POST["id"])) {
            $data_obj["message"] = "Parameter Not set";
            $data_obj["ajaxstatus"] = "Failed";
            echo json_encode($data_obj);
            die();
        }
        require_once ("../app/models/ProjectDb.php");
        $data_obj = json_decode($_POST["id"]);
        $project_db = new ProjectModel();
        $project_db->delete($_POST["id"]);
        $output = null;
        $output["message"] = "Delete Successfull";
        $output["ajaxstatus"] = "success";
        $json = json_encode($output);
        echo $json;
        die();
    }
    public function deleteRelease($id, $type)
    {
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if ($access > 2) {
            $data_obj["message"] = "You are not authorized to perform this action";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        require_once ("../app/models/ProjectDb.php");
        $project_db = new ProjectModel();
        $type = strtolower($type);
        $project_db->deleteRelease($id, $type);
        $output["message"] = "Delete Successfull";
        $output["ajaxstatus"] = "success";
        $json = json_encode($output);
        echo $json;
        die();


    }
    public function updatePub(){
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        $data = $_POST["data"];
        $params = json_decode($data);
        require_once ("../app/models/ProjectDb.php");
        $project_db = new ProjectModel();
        $project_db->setUpdate($params->id, $params->res);
        $output["message"] = "project ".$params->res."ed !";
        $output["ajaxstatus"] = "success";
        $json = json_encode($output);
        echo $json;
        die();
    }
    
}


?>