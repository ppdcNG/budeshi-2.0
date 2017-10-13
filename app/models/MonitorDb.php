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
                    $outHTML .= $this->tableListTemplate($row["id"], $row["name"],$row["short_name"]);
                }
            }
        }
        return $outHTML;
    }
    private function tableListTemplate($id, $name, $short_name)
    {
        $tableRow = "<tr>
                    <td><a href='#gin' onclick = 'editmodal(\"".$id."\")' title='Edit MDA' uk-tooltip='pos: bottom'><span class='uk-margin-small-right' uk-icon='icon: file-edit'></span></a></td>
                    <td><a href= '" . $this->absPath . "Project/" . $id . "'>" . $name ." (".$short_name.")". "</a></td>
                    <td><a href='#' title='Delete MDA' uk-tooltip='pos: bottom'><span class='uk-margin-small-right' uk-icon='icon: trash' onclick = 'delete_mda(\"".$id."\")'></span></a></td>
                </tr>";
        return $tableRow;
    }
    
    
    public function getMDAProjects($id)
    {
        $output = "";
        $query = "SELECT * FROM projects WHERE mda_id = ".$id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        else{
            while($row = mysqli_fetch_array($result)){
                $output .= $this->projectListTemplate($row["id"],$row["title"],$row["description"],$row["state"],$row["year"]);
            }
        }
        return $output;

    }
    
    public function getMDA($mda_id){
        $json_obj = null;
        $query = "SELECT * FROM mdas WHERE id = ".$mda_id;
        $result = $this->queryToJson($query, "e_");
        return $result;
        
    }
    public function addMDA($obj){
        $email = isset($obj->email)? $obj->email : "NULL";

        $query = "INSERT INTO mdas (name, sector, address, email, phone, short_name) VALUES ('".$obj->commonName."', '".$obj->sector."','".$obj->address."', '".$email."','".
        $obj->phone."', '".$obj->short_name."')";
        $result = $this->query($query);
        if(!$result){
            die($this->error);
        }
    }
    public function updateMDA($id, $obj){
        $data = Array();
        $data["name"] = $obj->commonName;
        $data["address"] = $obj->address or "";
        $data["short_name"] = $obj->shortname;
        $data["email"] = $obj->email or "NULL";
        $data["website"] = $obj->website or "";
        $data["phone"] = $obj->phone or "NULL";
        $this->update($id,$data,"mdas","id");


    }

    


}
?>