<?php
class Explorer extends Model
{
    public $project_cards = "";
    public $table_rows = "";
    public $perpage = 8;
    public $projects_array = [];
    public $total;
    private $pages;
    public $avg;
    public $max;
    public $min;
    public $mdas_html;
    public $projectHtml;

    public function __construct()
    {
        Parent::__construct();
    }
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->total / $this->perpage;
        }
    }

    public function projects($page, $paginated = TRUE)
    {
        $start = ($page - 1) * $this->perpage;
        $offset = $this->perpage;
        $end = $start + $this->perpage;
        $query = "SELECT SQL_CALC_FOUND_ROWS p.oc_id,p.mda_id,p.title,p.year, p.id, p.state, p.status, p.monitored, p.description, p.year, m.short_name, m.name AS mda, b.budget_amount, c.amount, ct.contractor_id, i.name, cd.latitude, cd.longitude FROM projects p LEFT JOIN mdas m
         ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id LEFT JOIN cordinates cd ON p.state = cd.state";


        $results = $this->query($query);
        if (mysqli_num_rows($results) <= 0) {
            $this->projectHtml = "No projects found in the data base";
            return null;
        }
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $this->total = $number;

        $contract_amount = [];
        $count = 1;

        while ($row = mysqli_fetch_assoc($results)) {
            if ($count < 9) {
                $year = (empty($row["year"]) or $row["year"] == "NULL") ? "N/A" : $row["year"];
                $this->project_cards .= $this->renderProjectCard($row["id"], $row["short_name"], $row["title"], $row["description"], $row["state"], $year);
            }
            $this->table_rows .= $this->renderTable($row["id"], $row["oc_id"], $row["title"], $row["state"], $row["name"], $row["amount"], $row["budget_amount"], $row["year"], $row["short_name"], $row["status"], $row["monitored"]);
            $count++;
            if ( (!empty($row["amount"])) and $row["amount"] != 0) {
                $contract_amount[] = $row["amount"];
            }
        }
        $query = "SELECT id, short_name, name FROM mdas";
        $result = $this->query($query);
        while ($row = mysqli_fetch_array($result)) {
            $this->mdas_html .= $this->renderMda($row["id"], $row["name"]);
        }
        $this->max = max($contract_amount);
        $this->min = min($contract_amount);
        $this->avg = array_sum($contract_amount) / count($contract_amount);
        return true;




    }
    public function ajaxProject($page)
    {
        $arrayToReturn = [];
        $query = "SELECT SQL_CALC_FOUND_ROWS p.oc_id,p.title,p.year, p.id, p.state, p.status, p.monitored, p.description, m.short_name, b.budget_amount, c.amount, ct.contractor_id, i.name, cd.latitude, cd.longitude FROM projects p LEFT JOIN mdas m
         ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id LEFT JOIN cordinates cd ON p.state = cd.state";


        $result = $this->query($query);
        if (mysqli_num_rows($result) <= 0) {
            $this->projecthtml = "No projects found in the data base";
            return;
        }
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $this->total = $number;

        while ($row = mysqli_fetch_assoc($result)) {
            $arrayToReturn[] = $row;

        }
        return $arrayToReturn;

    }
    public function ajaxsearch($search_query)
    {
        $arrayToReturn = [];
        $queries = $this->getSQL($search_query);
        $attach = implode("AND ", $queries);
        $query = "SELECT SQL_CALC_FOUND_ROWS p.oc_id,p.title,p.year, p.id, p.state, p.status, p.monitored, p.description, m.short_name, b.budget_amount, c.amount, ct.contractor_id, i.name, cd.latitude, cd.longitude FROM projects p LEFT JOIN mdas m
         ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id LEFT JOIN contract c ON c.project_id = p.id LEFT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id LEFT JOIN cordinates cd ON p.state = cd.state" .
            " WHERE " . $attach;


        $result = $this->query($query);
        if (mysqli_num_rows($result) <= 0) {
            $this->projecthtml = "No projects found in the data base";
            return $arrayToReturn;
        }
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $this->total = $number;
        $contract_amount = [];
        while ($row = mysqli_fetch_assoc($result)) {
            if ( (!empty($row["amount"])) and $row["amount"] != 0) {
                $contract_amount[] = $row["amount"];
            }
            //$row["amount"] = number_format($row["amount"]);
            //$row["budget_amount"] = number_format($row["budget_amount"]);
            $arrayToReturn[] = $row;

        }
        $this->max = max($contract_amount);
        $this->min = min($contract_amount);
        $this->avg = array_sum($contract_amount) / count($contract_amount);

        return $arrayToReturn;

    }
    private function getSQL($obj)
    {
        $queries = [];
        $state = empty($obj->state) ? false : $this->queryBuilder($obj->state, "state");
        $mda = empty($obj->mda) ? false : $this->queryBuilder($obj->mda, "mda");
        $year = empty($obj->year) ? false : $this->queryBuilder($obj->year, "year");
        $contractor = empty($obj->contractor) ? false : $this->queryBuilder($obj->contractor, "contractor");
        $text = empty($obj->text) ? false : $this->queryBuilder($obj->text, "text");
        $status = empty($obj->status) ? false : $this->queryBuilder($obj->status, "status");
        $monitored = empty($obj->monitored) ? false : $this->queryBuilder($obj->monitored, "monitored");
        if ($state) {
            $queries[] = $state;
        }
        if ($year) {
            $queries[] = $year;
        }
        if ($mda) {
            $queries[] = $mda;
        }
        if ($contractor) {
            $queries[] = $contractor;
        }
        if ($text) {
            $queries[] = $text;
        }
        if ($status) {
            $queries[] = $status;
        }
        if ($monitored) {
            $queries[] = $monitored;
        }
        return $queries;
    }
    private function queryBuilder($value, $type)
    {
        $query = "";
        switch ($type) {
            case "year" :
                if (is_array($value) and count($value) > 1) {
                    $data = [];
                    foreach ($value as $val) {
                        $data[] = "'" . $val . "'";
                    }
                    $join = implode(",", $data);
                    $join = "(" . $join . ")";
                    $query = " p.year IN " . $join;
                }
                else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = " p.year = '" . $val . "' ";
                }
                break;

            case "state" :
                if (is_array($value) and count($value) > 1) {
                    $data = [];
                    foreach ($value as $val) {
                        $data[] = "'" . $val . "'";
                    }
                    $join = implode(",", $data);
                    $join = "(" . $join . ")";
                    $query = " p.state IN " . $join . " ";
                }
                else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = "p.state = '" . $val . "' ";
                }
                break;
            case "contractor" :
                if (is_array($value) and count($value) > 1) {
                    $join = implode(",", $value);
                    $join = "(" . $join . ")";
                    $query = " ct.contractor_id IN " . $join . " ";
                }
                else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = " ct.contractor_id = " . $val . " ";
                }
                break;
            case "mda" :
                if (is_array($value) and count($value) > 1) {
                    $join = implode(",", $value);
                    $join = "(" . $join . ")";
                    $query = " p.mda_id IN " . $join . " ";
                }
                else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = " p.mda_id = " . $val . " ";
                }
                break;
            case "text" :
                $query = " p.title LIKE '%" . $value . "%' ";
                break;
            case "status" :
                if (is_array($value) and count($value) > 1) {
                    $data = [];
                    foreach ($value as $val) {
                        $data[] = "'" . $val . "'";
                    }
                    $join = implode(",", $data);
                    $join = "(" . $join . ")";
                    $query = " p.state IN " . $join . " ";
                }
                else {
                    $val = is_array($value) ? $value[0] : $value;
                    $query = " p.status = '" . $val . "' ";
                }
                break;
            case "monitored" :
                $val = is_array($value) ? $value[0] : $value;
                $query = " p.monitored = '" . $val . "' ";
                break;






        }
        return $query;
    }
    public function getProjectObj($id, $type)
    {
        $query = "SELECT release_id FROM " . $type . " WHERE project_id = " . $id;
        $result = $this->query($query);
        if (mysqli_num_rows($result) < 0) {
            return NULL;
        }
        $row = mysqli_fetch_array($result)[0];
        if (empty($row)) {
            return false;
        }
        $file = FILE_ROOT . "releases/" . $row . ".json";
        $file = fopen($file);
        $fileJson = fread($file, 100000);
        $obj = json_decode($fileJson);
        return $obj;

    }

    public function renderProjectCard($id, $mda, $title, $des, $state, $year)
    {

        $html = '<div class = "project-card">
                <div class="uk-card uk-card-default uk-card-hover uk-card-body" onclick="viewProject(\'' . $id . '\')">
                    <div class="uk-card-badge uk-label">' . $year . '</div>
                    <h3 class="uk-card-title uk-heading-bullet">' . $state . '</h3>
                    <p>' . $mda . '</p>
                    <p  class="uk-text-truncate" title="'.$title.'" uk-tooltip>' . $title . '</p>
                </div>
            </div>';
        return $html;
    }
    public function renderTable($id, $ocid, $title, $state, $contractor, $c_amount, $b_amount, $year, $mda, $status, $monitored)
    {
        $monitored = ($monitored == 'no') ? '<span class="uk-margin-small-right" uk-icon="icon: close"></span>' : '<span class="uk-margin-small-right" uk-icon="icon: check"></span>';
        $html = '<tr>
					<td><a href = "' . ABS_PATH . '/Home/project/' . $id . '" >' . $title . '</a> </td>
					<td>' . $state . '</td>
					<td>' . $contractor . '</td>
					<td>' . number_format($c_amount) . '</td>
                    <td>' . number_format($b_amount) . '</td>
					<td>' . $year . '</td>
                    <td>' . $mda . '</td>
                    <td>' . $status . '</td>
                    <td>' . $monitored . '</td>
				</tr>';
        return $html;
    }
    public function renderMda($value, $option)
    {
        $html = "<option value = '" . $value . "'>" . $option . "</option>";
        return $html;
    }
}


?>