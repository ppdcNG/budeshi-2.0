<?php
class ProjectModel extends Model{
    public $mda_name = null;
    public $perpage = 20;
    public $no = null;
    public function __construct(){
        Parent::__construct();
    }

    public function getMDAProjects($id,$page)
    {
        $output = "";
        $query = "SELECT p.*, m.name FROM projects p JOIN mdas m ON p.mda_id = m.id WHERE mda_id = ".$id." ORDER BY id DESC";
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        else{
            $pub = "";
        $ans= "";
            while($row = mysqli_fetch_array($result)){
                $pub = $row["published"];
                $output .= $this->projectListTemplate($row["id"],$row["title"],$row["monitored"],$row["state"],$row["year"], $row["oc_id"],$row["status"],$pub);
                $this->mda_name = $row["name"];
            }
        }
        return $output;

    }
    private function projectListTemplate($id, $title, $monitored, $location, $year,$oc_id,$status,$pub)
    {
        $tableRow = '<tr>
                        <td><a title="Edit Project" uk-tooltip="pos: bottom" onclick = \'editmodal("'.$id.'")\'><span class="uk-margin-small-right" uk-icon="icon: file-edit"></span></a></td>
                        <td>'.$title.'</td>
                        <td>'.$oc_id.'</td>
                        <td>'.$location.'</td>
                        <td>'.$year.'</td>
                        <td><a href="#" title="View Releases" uk-tooltip="pos: bottom" onclick = \'getReleases("'.$id.'")\'><span class="uk-margin-small-right" uk-icon="icon: folder"></span></a><a href="#add-release" onclick = \'add_project("'.$id.'")\'
                           title="Add Release" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: plus"></span></a></td>
                        <td><a href="#delete-project" onclick = \'deleteProject("'.$id.'")\' title="Delete Project" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a></td>
                        <td>'.$status.'</td>
                        <td>'.ucfirst($pub).'</td>
                        <td>'.ucfirst($monitored).'</td>
                    </tr>';
        return $tableRow;
    }
    private function releaseList($id, $release_id, $oc_id, $type = "Planning"){
        $list_item = "<li><a href = '".$this->absPath."release/edit/".$id."/".$type."'>".$release_id."</a><a href=\"#\" onclick = \"deleteRelease('".$id."','".$type."')\" title=\"Delete Project\" uk-tooltip=\"pos: bottom\"><span class=\"uk-margin-small-right\" uk-icon=\"icon: trash\"></span></a></li>";
        return $list_item;
    }
    private function getRealeases($project_id, $table){
        $p_query = "SELECT r.id, r.release_id, p.oc_id FROM ".$table." r JOIN projects p ON r.project_id = p.id WHERE r.project_id = ".$project_id;
        $result = $this->query($p_query);
        if(!$result){
            die($this->error);
        }
        $data_array = array();
        if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_array($result)){
                $data_array[] = $row;
            }
            return $data_array;
        }
        else{
            return false;
        }
    }
    public function fillRelease($mda_id, $release_type, $table){
        $output = "";
        $releases = $this->getRealeases($mda_id, $release_type); // returns array of rows 
        if($releases){
        foreach($releases as $row){
            $output .= $this->releaseList($row["id"], $row["release_id"], $row["oc_id"], $release_type);
        }
        return $output;
        }
        else return false;
    }
    public function getProject($id, $prefix = ""){
        $query = "SELECT * FROM projects WHERE id = ".$id;
        $return_obj = $this->queryToJson($query, $prefix);
        return $return_obj;

    }
    public function addProject($data_obj){
        $oc_id = $this->generate_ocid($data_obj->mda_id);
        $record = $oc_id."-record";
        $output = null;
        $query = "INSERT INTO projects (oc_id, mda_id, state, year, updated_by, title, description, record_name, published, monitored) VALUES ('".$oc_id."',".$data_obj->mda_id.", 
        '".$data_obj->location."','".$data_obj->year."',".$data_obj->updated_by.",'".mysqli_real_escape_string($this->conn,$data_obj->title)."', '".mysqli_real_escape_string($this->conn,$data_obj->description)."','".$record."','".$data_obj->monitored."','".$data_obj->published."')";
        echo $query;
        $result = $this->query($query);
        if(!$result){
            die($this->error);
        }
        $output["message"] = "Project successfully Added";
        $output["project_id"] = mysqli_insert_id($this->conn);
        $output["ajaxstatus"] = "success";
        return $output;
    }
    public function editProject($data_obj){
        $output = null;
        $last_update = date("Y-m-d");
        $query = "UPDATE projects SET title = '".$data_obj->e_title."', description = '".$data_obj->e_description."',
        year = '".$data_obj->e_year."', state = '".$data_obj->e_location."', updated_by = ".$_SESSION["id"].",
        date_updated = '".$last_update."', monitored = '".$data_obj->e_monitored."', published = '".$data_obj->e_published."' WHERE id = ".$data_obj->e_project_id;
        $result = $this->query($query);
        if(!$result){
            die($this->error);
        }
        $output["message"] = "Project Edited Succesfully";
        $output["project_id"] = $data_obj->e_project_id;
        $output["ajaxstatus"] = "success";
        return $output;
    }
    public function delete($id) {
        $query = "DELETE FROM projects WHERE id = ".$id;
        $result = $this->query($query);
        if(!$result){
            die($this->error);
        }
        
    }
    public function deleteRelease($id,$type){
        $query = "SELECT release_id FROM ".$type." WHERE id = ".$id;
        $result = $this->query($query);

        if(!$result){
            die($this->error);
        }
        $rel_id = mysqli_fetch_array($result)[0];
        $path = FILE_ROOT."releases/".$rel_id.".json";
        unlink($path);
        Parent::delete($id,$type);
    }
    public function setUpdate($id, $ans){
        if($ans == "un-publish") $pub = "no";
        if($ans == "publish") $pub = "yes";
        $data = [];
        $data["published"] = $pub;
        $this->update($id,$data,"projects", "id");
        
    }
    public function renderPageLinks($page, $no,$mda_id){
        $start = ($page - 1) * $this->perpage;
        $startPage = $page - 4;
        $endPage = $page + 4;

        if ($startPage <= 0) {
            $endPage -= ($startPage - 1);
            $startPage = 1;
        }

        if ($endPage > $no)
            $endPage = $no;
        $middle = "";
        if ($startPage > 1) echo " First ... ";
        for ($i = $startPage; $i <= $endPage; $i++) $middle .= '<li><a href="'.ABS_PATH.'project/'.$mda_id.'/'.($i).'">'.$i.'</a></li>';
        if ($endPage < $no) echo " ... Last ";
        $prev = '<li><a href="'.ABS_PATH.'project/'.$mda_id.'/'.($page-1).'"><span uk-pagination-previous></span></a></li>';
        $next = '<li><a href="'.ABS_PATH.'project/'.$mda_id.'/'.($page+1).'"><span uk-pagination-next></span></a></li>';
        $pages = floor($no/$this->perpage);
        
        if($page < 2) $prev = '<li class = "uk-disabled"><span><span uk-pagination-previous></span></span></li>';
        if($page >= $pages) $next = '<li class = "uk-disabled"><span><span uk-pagination-next></span></span></li>';
        return $prev.$middle.$next;


    }
  
}
?>