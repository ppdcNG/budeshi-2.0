<?php 
class Record extends Model
{
    public $planning;
    public $tender;
    public $award;
    public $contract;
    public $implementation;

    public $projectTitle;
    public $mda_name;
///////////////////////Array properties as html////////////
    public $tenderDocuments;
    public $awardDocuments;
    public $contractDocuments;
    public $implementationDocuments;

    public $tenderItems;
    public $awardItems;
    public $contractItems;

    public $awardAmendments;
    public $tenderAmendments;
    public $contractAmendments;

    public $implementationMiles;

    public $tenderers;
    public $suppliers;
    public $transactions;
    public $monitorimages;
////////////////////////////////////////////////////////

    public function __construct()
    {
        Parent::__construct();
    }


    public function getProjectObj($id)
    {
        $query = "SELECT oc_id FROM  projects WHERE id = " . $id;
        $result = $this->query($query);
        if (mysqli_num_rows($result) < 0) {
            return NULL;
        }
        $this->getProjectProp($id);
        $release_name = mysqli_fetch_array($result)[0];
        $path = FILE_ROOT . "compiled/" . $release_name . ".json";
        $obj = json_decode(file_get_contents($path));
        return $obj;

    }
    public function getProjectProp($id){
        $query = "SELECT p.title, m.name FROM projects p JOIN mdas m ON p.mda_id = m.id WHERE p.id = ".$id;
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        $this->projectTitle = $row["title"];
        $this->mda_name = $row["name"];
    }
    public function getPlanningObj($obj)
    {
        $html = null;
        if (isset($obj) && !empty($obj)) {
         
        }
    }

    public function getTenderObj($id, $type)
    {
        $obj = $this->getProjectObj($id, $type);
        if ($obj) {
            $this->tenderAmendments = $this->getAmendments($obj, "tender");
            $this->tenderDocuments = $this->getDocuments($obj, "tender");
            $this->tenderItems = $this->getItems($obj, "tender");
            $this->tenderers = $this->getTenderer($obj, "tender");


        }
        return $obj;
    }
    public function getAwardObj($id, $type)
    {
        $obj = $this->getProjectObj($id, $type);
        if ($obj) {
            $this->awardAmendments = $this->getAmendments($obj, "award");
            $this->awardDocuments = $this->getDocuments($obj, "award");
            $this->awardItems = $this->getItems($obj, "award");
            $this->suppliers = $this->getTenderer($obj, "award", "suppliers");

        }
        return $obj;

    }
    public function getContractObj($id, $type)
    {
        $obj = $this->getProjectObj($id, "contract");
        if ($obj) {
            $this->contractAmendments = $this->getAmendments($obj, "contract");
            $this->contractDocuments = $this->getDocuments($obj, "contract");
            $this->contractItems = $this->getItems($obj, "contract");
        }
        return $obj;
    }
    public function getImplementationObj($id, $type)
    {
        $obj = $this->getProjectObj($id, $type);
        if ($obj) {
            $this->implementationDocuments = $this->getDocuments($obj, "contract");
            $this->transactions = $this->getTransaction($obj, "contract");
            $this->monitorimages = $this->getMonitorImages($obj, "contract");
        }
        return $obj;
    }
    public function getAmendments($obj)
    {
        $list = "";
        $amendments = (isset($obj->amendments) && is_array($obj->amendments)) ? $obj->amendments : "";
        if (!empty($amendments)) {
            foreach ($amendments as $amend) {
                $list .= "<tr>" . $this->renderRow("td", $amend->description) . $this->renderRow("td", $amend->rationale) . "</tr>";
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;

    }
    public function getDocuments($obj)
    {
        $list = "";
        $documents = (isset($obj->documents) && is_array($obj->documents)) ? $obj->documents : "";
        if (!empty($documents)) {
            foreach ($documents as $doc) {
                $list .= '<div>
							<div class="uk-card uk-card-default uk-card-body" style="background-color: #43c7f2">
								<div class="uk-inline-clip uk-transition-toggle">				
									' . $doc->title . '			
									<div class="uk-position-center uk-overlay uk-overlay-default uk-transition-fade uk-padding">				
										<a href = "' . $doc->url . '"><span uk-icon="icon: download; ratio: 2"></span></a>				
									</div>				
								</div>				
							</div>				
						</div>';
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;
    }
    public function getItems($obj)
    {
        $list = "";
        $items = (isset($obj->items) && is_array($obj->items)) ? $obj->items : "";
        if (!empty($items)) {

            foreach ($items as $item) {
                if ($item) {
                    $list .= "<tr>" . $this->renderRow("td", $item->description) . $this->renderRow("td", $item->quantity) . $this->renderRow("td", $item->unit->name) . "</tr>";
                }
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;

    }
    public function getTenderer($obj, $title = "tenderers")
    {
        $list = "";
        $tenderers = (isset($obj->$title) && is_array($obj->$title)) ? $obj->$title : "";
        if (!empty($tenderers)) {
            foreach ($tenderers as $tender) {
                $list .= "<tr>" . $this->renderRow("td", $tender->identifier->legalName) . "</tr>";
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;

    }
    public function getTransaction($obj, $type)
    {
        $list = "";
        $transactions = (isset($obj->transactions) && is_array($obj->transactions)) ? $obj->transactions : "";
        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                $list .= "<tr>" . $this->renderRow("td", $transaction->payer->identifier->legalName) . $this->renderRow("td", $transaction->value->amount) . $this->renderRow("td", $transaction->payee->identifier->legalName) .
                    $this->renderRow("td", date("m/y/d", strtotime($transaction->date))) . "</tr>";
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;

    }
    public function getMonitorImages($obj, $type)
    {
        $list = "";
        $documents = (isset($obj->contract->documents) && is_array($obj->contract->documents)) ? $obj->contract->documents : "";
        if (!empty($documents)) {
            foreach ($documents as $doc) {
                if ($doc->documentType = "monitorImage") {
                    $list = '<div>
								<div class="uk-card uk-card-default uk-card-body">
									<img src="' . $doc->uri . '">
								</div>
							</div>';
                }

            }
        }

        return $list;
    }
    public function getMDAName($id)
    {
        $query = "SELECT p.title, i.name FROM planning p JOIN mdas i ON p.mda_id = i.id WHERE p.id = " . $id;
        $result = $this->query($query);
        $result = mysqli_fetch_assoc($result);
        $name = $result["name"];
        $this->projectTitle = $result["title"];
        return $name;
    }
}


?>