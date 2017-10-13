<?php 
class Home extends Controller
{

    public function index()
    {
        require_once ("../app/views/frontend/landing.html");

    }
    public function data()
    {
        require_once "../app/models/HomeDb.php";
        $db = new Explorer();
        $db->projects(1);
        if(!$db){
            echo $db->projectHtml;
            die();
        }
        $project_table = $db->table_rows;
        $project_card = $db->project_cards;
        $mdas = $db->mdas_html;
        $average = number_format($db->avg);
        $highest = number_format($db->max);
        $lowest = number_format($db->min);
        $number = number_format($db->total);

        require_once ("../app/views/frontend/index.html");
    }
    public function getProjectArray()
    {
        require_once "../app/models/HomeDb.php";
        $db = new Explorer();
        $array = $db->ajaxProject(1);
        $json = json_encode($array);
        echo $json;
    }
    public function search()
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $search_query = json_decode($_POST["data"]);
        require_once "../app/models/HomeDb.php";
        $db = new Explorer();
        $data_obj = [];
        $data_obj["array"] = $db->ajaxsearch($search_query);
        $data_obj["min"] = number_format($db->min);
        $data_obj["max"] = number_format($db->max);
        $data_obj["avg"] = number_format($db->avg);
        $json = json_encode($data_obj);
        echo $json;

    }
    public function project($id)
    {
        require_once ("../app/models/recordDb.php");
        require_once ("../app/models/View.php");
        $db = new Record();
        $vw = new Template();
        $output = "";
        $nav_tab = "";
        $package = $db->getProjectObj($id);
        if (!$package) {
            echo "no object found for this project";
            die();
        }
        $release = $package->releases[0];
        if (isset($release->planning)) {
            $planning = $release->planning;
            $nav_tab .= '<li><a href="#">Planning</a></li>';
            $source = isset($planning->budget->source) ? $planning->budget->source : "";
            $source = empty($source) ? $planning->budget->description : $source;
            $output .= $vw->loadPlanningView($planning->budget->project, $planning->budget->amount->amount, $source);
        }

        if (isset($release->tender)) {
            $tender = $release->tender;
            $nav_tab .= '<li><a href="#">Initiation (Tender)</a></li>';
            $ammendments = empty($tender->amendments) ? "<em>N/A</em>" : $db->getAmendments($tender);
            $documents = empty($tender->documents) ? "<em>N/A</em>" : $db->getDocuments($tender);
            $items = $db->getItems($tender);
            $tenders = $db->getTenderer($tender);

            $output .= $vw->loadTenderView($db->mda_name, $tender->status, $ammendments, $tenders, $documents, $items);
        }
        if (isset($release->awards) && isset($release->awards[0])) {
            $award = $release->awards[0];
            $nav_tab .= '<li><a href="#">Award</a></li>';
            $ammendments = $db->getAmendments($award);
            $documents = $db->getDocuments($award);
            $items = $db->getItems($award);
            $suppliers = $db->getTenderer($award, "suppliers");
            $award_date = empty($award->date) ? "N/A" : date_format(DateTime::createFromFormat("d/m/Y",$award->date), "d, M Y");
            $output .= $vw->loadAwardView($db->mda_name, $award_date, $ammendments, $items, $suppliers, $documents);

        }

        if (isset($release->contracts) && isset($release->contracts[0])) {
            $contract = $release->contracts[0];
            $nav_tab .= '<li><a href="#">Contract</a></li>';
            $ammendments = $db->getAmendments($contract);
            $documents = $db->getDocuments($contract);
            $items = $db->getItems($contract);
            $start_date = empty($contract->period->startDate) ? "N/A" : $contract->period->startDate;
            $end_date = empty($contract->period->endDate) ? "N/A" : $contract->period->endDate;
            $output .= $vw->loadConntractView($contract->title, $contract->description, $contract->status, $start_date, $end_date, $contract->value->amount, $items, $documents);
        }


        require ("../app/views/frontend/project.html");

    }
}


?>