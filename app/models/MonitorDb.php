<?php
class MonitorModel extends Model
{
    public $MDAS = null;

    public function __construct()
    {
        Parent::__construct();
    }
    public function getMDAList()
    {
        $query = "SELECT * FROM mdas";
        $result = $this->query($query);
        $outHTML = "";
        if (!$result) {
            die($this->error);
        }
        else {
            if (mysqli_num_rows($result) <= 0) {
                $outHTML = "<em>No MDAS found</em>";
            }
            else {
                while ($row = mysqli_fetch_array($result)) {
                    $outHTML .= $this->tableListTemplate($row["id"], $row["name"]);
                }
            }
        }
        return $outHTML;
    }
    private function tableListTemplate($id, $name)
    {
        $tableRow = "<tr>
                    <td><a href='#add-institution' title='Edit MDA' uk-tooltip='pos: bottom' uk-toggle><span class='uk-margin-small-right' uk-icon='icon: file-edit'></span></a></td>
                    <td><a href= '" . $this->absPath . "Monitor/Projects/" . $id . "'>" . $name . "</a></td>
                    <td><a href='#delete-institution' title='Delete MDA' uk-tooltip='pos: bottom' uk-toggle><span class='uk-margin-small-right' uk-icon='icon: trash'></span></a></td>
                </tr>";
        return $tableRow;
    }
    private function projectListTemplate($id, $title, $description, $location, $year)
    {
        $tableRow = '<tr>
                        <td><a href="#edit-project" title="Edit Project" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: file-edit"></span></a></td>
                        <td>'.$title.'</td>
                        <td>'.$this->trimText($description).'</td>
                        <td>'.$location.'</td>
                        <td>'.$year.'</td>
                        <td><a href="#view-releases" title="View Releases" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: folder"></span></a><a href="#add-release"
                           title="Add Release" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: plus"></span></a></td>
                        <td><a href="#delete-project" title="Delete Project" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a></td>
                    </tr>';
        return $tableRow;
    }
    public function getMDAProjects($id)
    {
        $output = "";
        $query = "SELECT * FROM projects";
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        else{
            while($row = mysqli_fetch_array($result)){
                $output .= $this->projectListTemplate($row["id"],$row["title"],$row["description"]);
            }
        }

    }
    private function trimText($text, $max = 100, $pgrh = 1)
    {

        $textToReturn = '';
        $len = strlen($text);
        if (strlen($text) > $max) {
            for ($i = 0; $i < $pgrh; $i++)
                {
                if ($pos = strpos($text, '\n'))
                    {
                    $textToReturn .= substr($text, 0, $pos);
                    $text = substr($text, $pos + 1, $len);
                }
                else {
                    $pos = strrpos($text, ' ');
                    $textToReturn .= substr($text, 0, $max) . "...";
                }
            }
        }
        else {
            $textToReturn = $text;
        }
        return $textToReturn;
    }

}
?>