<?php 
class Release extends Controller
{


    public function index($id, $type)
    {

    }
    public function add($type, $id, $mda_id)
    {
        if (!$this->checkLogin()) {
            echo "Unauthorized you cant view this page";
            die();
        }
        switch ($type) {
            case "planning" :
                require_once ("../app/models/ReleaseDb.php");
                $planning = new ReleaseModel();
                $oc_id = $planning->getOCID($id);
                $release_id = $oc_id . "-planning-" . $planning->getNextId("planning", $id);
                $today = date("Y-m-d", time());
                require_once ("../app/views/backend/new-planning-release.html");
                break;
            case "tender" :
                require_once ("../app/models/ReleaseDb.php");
                $tender = new ReleaseModel();
                $ocid = $tender->getOCID($id);
                $project_id = $id;
                $release_id = $ocid . "-tender-" . $tender->getNextId("tender", $id);
                $today = date("Y-m-d", time());
                require_once ("../app/views/backend/new-tender-release.html");
                break;
            case "award" :
                require_once ("../app/models/ReleaseDb.php");
                $award = new ReleaseModel();
                $ocid = $award->getOCID($id);
                $project_id = $id;
                $release_id = $ocid . "-award-" . $award->getNextId("award", $id);
                $amendment_releases = $award->fetchReleases("award", $id);
                $today = date("Y-m-d", time());
                require_once ("../app/views/backend/new-award-release.html");
                break;
            case "contract" :
                require_once ("../app/models/ReleaseDb.php");
                $contract = new ReleaseModel();
                $ocid = $contract->getOCID($id);
                $project_id = $id;
                $release_id = $ocid . "-contract-" . $contract->getNextId("contract", $id);
                $award_releases = $contract->fetchReleases("award", $id);
                $amendment_releases = $contract->fetchReleases("contract", $id);
                $today = date("Y-m-d");
                require_once ("../app/views/backend/new-contract-release.html");
                break;
            case "implementation" :
                require_once ("../app/models/ReleaseDb.php");
                $implementation = new ReleaseModel();
                $ocid = $implementation->getOCID($id);
                $project_id = $id;
                $release_id = $ocid . "-implementation-" . $implementation->getNextId("implementation", $id);
                $contract_releases = $implementation->fetchReleases("contract", $id);
                $amendment_releases = $implementation->fetchReleases("contract", $id);
                $today = date("Y-m-d");
                require_once ("../app/views/backend/new-implementation-release.html");
                break;
        }
    }
    public function getorg()
    {
        $search_text = isset($_GET["searchText"]) ? $_GET["searchText"] : "";
        require_once ("../app/models/ReleaseDb.php");
        $planning = new ReleaseModel();
        $institutions = $planning->getOrganisations($search_text);
        $institutions = json_encode($institutions);
        echo $institutions;


    }
    public function transactadd($type, $id, $mda_id)
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
        switch ($type) {
            case "planning" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $planning_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                $parties = $release_db->getParties($planning_obj->parties);
                $planning_obj->parties = $parties;
                $buyer = $release_db->getOCDOrganisation($mda_id);
                $planning_obj->buyer = $buyer;
                $release_db->addPlanningRelease($id, $mda_id, $planning_obj);
                $json_data = json_encode($planning_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $planning_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->complileReleases($id,$planning_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "tender" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $tender_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                if (isset($tender_obj->parties) && is_array($tender_obj->parties) && count($tender_obj->parties) > 0 && !empty($tender_obj->parties)) {
                    $parties = $release_db->getParties($tender_obj->parties);
                    $tender_obj->parties = $parties;
                }
                if (isset($tender_obj->tender->tenderers) && !empty($tender_obj->tender->tenderers)) {
                    $tenderers = $release_db->getTenderers($tender_obj->tender->tenderers);
                    $tender_obj->tender->tenderers = $tenderers;
                }
                $release_db->addTenderRelease($id, $mda_id, $tender_obj);
                $json_data = json_encode($tender_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $tender_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->complileReleases($id,$planning_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "award" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $award_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                $award_obj->buyer = $release_db->getOCDOrganisation($id);
                if (isset($award_obj->parties) && is_array($award_obj->parties) && count($award_obj->parties) > 0) {
                    $parties = $release_db->getParties($award_obj->parties);
                    $award_obj->parties = $parties;
                }
                $supplier = $award_obj->award->suppliers[0];
                $award_obj->award->suppliers = $release_db->getSuppliers($award_obj->award->suppliers);
                $release_db->addAwardRelease($id, $mda_id, $supplier, $award_obj);
                $json_data = json_encode($award_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $award_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->complileReleases($id,$planning_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "contract" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $contract_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                $contract_obj->buyer = $release_db->getOCDOrganisation($id);
                if (isset($contract_obj->parties) && is_array($contract_obj->parties) && count($contract_obj->parties) > 0) {
                    $parties = $release_db->getParties($contract_obj->parties);
                    $contract_obj->parties = $parties;
                }

                $release_db->addContractRelease($id, $mda_id, $contract_obj);
                $json_data = json_encode($contract_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $contract_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->complileReleases($id,$planning_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "implementation" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $implementation_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                $transactions = [];
                foreach ($implementation_obj->transactions as $transact) {
                    $release_db->addImplementationRelease($implementation_obj, $id, $mda_id, $transact->value->amount, $transact->payer, $transact->payee);
                    $transact->payee = $release_db->getOCDOrganisation($transact->payee);
                    $transact->payer = $release_db->getOCDOrganisation($transact->payer);
                    $transactions[] = $transact;
                }
                $contract_file = fopen($this->fileroot . "releases/" . $implementation_obj->contractID . ".json", "r");
                $contract_json = fread($contract_file, 150000);
                $contract_obj = json_decode($contract_json);
                $contract_obj->id = $implementation_obj->id;
                $contract_obj->date = $implementation_obj->date;
                if (isset($implementation_obj->documents)) {
                    $contract_obj->contract->documents = $implementation_obj->documents;
                }
                $contract_obj->contract->transactions = $transactions;

                $json_data = json_encode($contract_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $contract_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->complileReleases($id,$planning_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;



        }
    }
    public function transactedit($type, $id, $mda_id)
    {
        $access = $this->checkLogin();
        if (!$access or $access > 1) {
            $object["ajaxstatus"] = "failed";
            $object["message"] = "You don't have clearance to perform this opperation";
            $data = json_encode($object);
            echo $data;
            die();
        }
        switch ($type) {
            case "planning" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $planning_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                $parties = $release_db->getParties($planning_obj->parties);
                $planning_obj->parties = $parties;
                $buyer = $release_db->getOCDOrganisation($mda_id);
                $planning_obj->buyer = $buyer;
                $release_db->editPlanningRelease($id, $mda_id, $planning_obj);
                $json_data = json_encode($planning_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $planning_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $data_obj["message"] = "SuccessFully Edit the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "tender" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $tender_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                if (isset($tender_obj->parties) && is_array($tender_obj->parties) && count($tender_obj->parties) > 0) {
                    $parties = $release_db->getParties($tender_obj->parties);
                    $tender_obj->parties = $parties;
                }
                $tenderers = $release_db->getTenderers($tender_obj->tender->tenderers);
                $tender_obj->tender->tenderers = $tenderers;
                $release_db->editTenderRelease($id, $mda_id, $tender_obj);
                $json_data = json_encode($tender_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $tender_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $data_obj["message"] = "SuccessFully Edit the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "award" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $award_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                $award_obj->buyer = $release_db->getOCDOrganisation($id);
                if (isset($award_obj->parties) && is_array($award_obj->parties) && count($award_obj->parties) > 0) {
                    $parties = $release_db->getParties($award_obj->parties);
                    $award_obj->parties = $parties;
                }
                $supplier = $award_obj->award->suppliers[0];
                $award_obj->award->suppliers = $release_db->getSuppliers($award_obj->award->suppliers);
                $release_db->editAwardRelease($id, $mda_id, $supplier, $award_obj);
                $json_data = json_encode($award_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $award_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $data_obj["message"] = "SuccessFully Edited the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "contract" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $contract_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                $contract_obj->buyer = $release_db->getOCDOrganisation($id);
                if (isset($contract_obj->parties) && is_array($contract_obj->parties) && count($contract_obj->parties) > 0) {
                    $parties = $release_db->getParties($contract_obj->parties);
                    $contract_obj->parties = $parties;
                }

                $release_db->editContractRelease($id, $mda_id, $contract_obj);
                $json_data = json_encode($contract_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $contract_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $data_obj["message"] = "SuccessFully Edited the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "implementation" :
                require_once ("../app/models/ReleaseDb.php");
                $json_data = $_POST["data"];
                $implementation_obj = json_decode($json_data);
                $release_db = new ReleaseModel();
                $transactions = [];
                foreach ($implementation_obj->transactions as $transact) {
                    $release_db->editImplementationRelease($implementation_obj, $id, $mda_id, $transact->value->amount, $transact->payer, $transact->payee);
                    $transact->payee = $release_db->getOCDOrganisation($transact->payee);
                    $transact->payer = $release_db->getOCDOrganisation($transact->payer);
                    $transactions[] = $transact;
                }
                $contract_file = fopen($this->fileroot . "releases/" . $implementation_obj->contractID . ".json", "r");
                $contract_json = fread($contract_file, 150000);
                $contract_obj = json_decode($contract_json);
                $contract_obj->id = $implementation_obj->id;
                $contract_obj->date = $implementation_obj->date;
                if (isset($implementation_obj->documents)) {
                    $contract_obj->contract->documents = $implementation_obj->documents;
                }
                $contract_obj->contract->transactions = $transactions;

                $json_data = json_encode($contract_obj, JSON_PRETTY_PRINT);
                $release_path = $this->fileroot . "releases/" . $contract_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $data_obj["message"] = "SuccessFully Edited the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;



        }
    }
    public function ajaxdocument()
    {

        if (is_uploaded_file($_FILES["files"]["tmp_name"][0])) {

            if (move_uploaded_file($_FILES["files"]["tmp_name"][0], $this->fileroot . "documents/" . $_FILES["files"]["name"][0])) {
                $id = substr(md5(time()), 0, 5);
                $name = $id;
                $ext = explode(".", $_FILES["files"]["name"][0]);
                $name = $name . "." . $ext[count($ext) - 1];
                $newname = $this->fileroot . "documents/" . $name;
                $type = $_FILES["files"]["type"][0];
                rename($this->fileroot . "documents/" . $_FILES["files"]["name"][0], $newname);
                echo $newname . "**" . $id . "**" . $type;
            }
        }
        else {
            echo "blah";
        }
    }
    public function delajaxdocument()
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
        $path = $_POST["data"];
        if (file_exists($path)) {
            unlink($path);
            $data_obj["message"] = "File Successfully deleted";
            $data_obj["ajaxstatus"] = "success";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        else {
            $data_obj["message"] = "File does not exists";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
    }
    public function edit($id, $type)
    {
        if (!$this->checkLogin()) {
            die();
        }
        $type = strtolower($type);
        switch ($type) {
            case "planning" :
                require_once ("../app/models/ReleaseDb.php");
                $release_db = new ReleaseModel();
                $release_array = $release_db->getRelease($id, $type);
                $project_id = $release_array["project_id"];
                $mda_id = $release_array["mda_id"];
                $obj = $release_db->getReleaseJSON($release_array["release_id"]);
                $today = date("Y-m-d");
                $parties_html = "";
                $milestones_html = "";
                //fill parties
                if (isset($obj->parties) && is_array($obj->parties)) {
                    $parties = $obj->parties;
                    $count = 0;
                    foreach ($parties as $party_obj) {
                        $party = $release_db->getPartiesID($party_obj);
                        $parties_html .= $release_db->partyRender($count, $party["name"]);
                        $count++;
                    }
                }
                //fill milestones
                if (isset($obj->planning->milestones) && is_array($obj->planning->milestones)) {
                    $milestones = $obj->planning->milestones;
                    for ($i = 1; $i <= count($milestones); $i++) {
                        $milestones_html .= $release_db->milestoneRender($id);
                    }
                }
                require_once ("../app/views/backend/edit-planning-release.html");
                break;
            case "tender" :
                require_once ("../app/models/ReleaseDb.php");
                $release_db = new ReleaseModel();
                $release_array = $release_db->getRelease($id, $type);
                $project_id = $release_array["project_id"];
                $mda_id = $release_array["mda_id"];
                $obj = $release_db->getReleaseJSON($release_array["release_id"]);
                $today = date("Y-m-d");
                $parties_html = $release_db->partyHTML($obj);
                $milestones_html = $release_db->milestonesHTML($obj->tender);
                $documents_html = $release_db->documentHTML($obj->tender);
                $item_html = $release_db->itemsHTML($obj->tender);
                $startDate = date("Y-m-d", strtotime($obj->tender->tenderPeriod->startDate));
                $endDate = date("Y-m-d", strtotime($obj->tender->tenderPeriod->endDate));
                $no_of_tenderers = count($obj->tender->tenderers);
                require_once ("../app/views/backend/edit-tender-release.html");
                break;
        }
    }
    public function delete($id, $type)
    {
        $data_obj = [];
        $access = $this->checkLogin();
        if (!$access) {
            $data_obj["message"] = "You are not authorized to perform this action";
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
        require_once ("../app/models/ReleaseDb.php");
        $release_db = new ReleaseModel();
        $release_db->delete($id, $type);
        $data_obj["message"] = "Release Deleted";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;
        die();

    }
    public function getArrays($id, $type)
    {
        if (!$this->checkLogin()) {
            die("Unauthorized");
        }
        if (!isset($id) and !isset($type)) {
            die("Wrong Params");
        }
        switch ($type) {
            case "planning" :
                require_once ("../app/models/ReleaseDb.php");
                $release_db = new ReleaseModel();
                $return_obj = new stdClass;
                $release_array = $release_db->getRelease($id, $type);
                $obj = $release_db->getReleaseJSON($release_array["release_id"]);
                $return_obj->parties = $release_db->getJavaParties($obj->parties);
                $return_obj->milestones = $obj->planning->milestones;
                $return_obj->ajaxstatus = "success";
                $return_obj->message = "Thank you for waiting";
                echo json_encode($return_obj);
                break;
            case "tender" :
                require_once ("../app/models/ReleaseDb.php");
                $release_db = new ReleaseModel();
                $return_obj = new stdClass;
                $release_array = $release_db->getRelease($id, $type);
                $obj = $release_db->getReleaseJSON($release_array["release_id"]);
                if (isset($obj->tender->milestones)) $return_obj->milestones = $obj->tender->milestones;
                if (isset($obj->tender->documents)) $return_obj->documents = $obj->tender->documents;
                if (isset($obj->tender->items)) $return_obj->items = $obj->tender->items;
                $return_obj->ajaxstatus = "success";
                $return_obj->message = "Thank you for waiting";
                echo json_encode($return_obj);



        }


    }
    public function compileRelease()
    {
        require_once ("../app/models/ReleaseDb.php");
        $release_db = new ReleaseModel();

        $release = $release_db->complileReleases(1264,"ocds-azam7x-a405e2ng-UBEC");
        echo $release;

    }

}


?>