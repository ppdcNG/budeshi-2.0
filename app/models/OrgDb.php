<?php 
class OrganisationModel extends Model{
    public $perpage;
    public function __construct(){
        Parent::__construct();
        $this->perpage = 11;
    }

    public function fetchOrgs($page){

        $html = "";
        $start = ($page-1) * $this->perpage;
        $end = $start + $this->perpage;
        $query = "SELECT * FROM institutions LIMIT ".$start.",".$end;
        $result = $this->query($query);
        if(mysqli_num_rows($result) > 0){
           while($row = mysqli_fetch_assoc($result)) {
               $html .= $this->renderOrg($row["id"], $row["name"], $row["rc_no"]);
           }

        }
        else{
            $html = "<tr>No Oragnisations yet</tr>";
        }
        return $html;
    }
    public function renderOrg($id,$name, $rc_no){
        $html = '<tr>
                    <td><a href="#edit-institution" onclick = "editmodal(\''.$id.'\')" title="Edit Organization" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: file-edit"></span></a></td>
                    <td uk-toggle="target: #view-institution">'.$name.'</td>
                    <td>'.$rc_no.'</td>
                    <td><a href="#id" onclick = "delete_org(\''.$id.'\')" title="Delete Organization" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a></td>
                </tr>';
        return $html;
    }
    public function getOrg($id){
        $query = "SELECT * FROM institutions WHERE id = ".$id;
        $json = $this->queryToJson($query);
        return $json;
    }
    public function updateOrg($id, $obj){
        $data = Array();
        $data["name"] = $obj->name;
        $data["rc_no"] = $obj->rc_no;
        $data["address"] = $obj->streetName;
        $data["url"] = $obj->uri;
        $data["postal_code"] = isset($obj->postalCode) ? $obj->postalCode : "NULL";
        $data["state"] = $obj->region;
        $data["lga"] = isset($obj->locality) ? $obj->locality: "NULL";
        $data["contact_name"] = isset($obj->contactName) ? $obj->contactName : "NULL";
        $data["phone"] = $obj->phone;
        $data["email"] = isset($obj->email) ? $obj->email: "NULL";
        $this->update($id,$data,"institutions", "id");
    }
    public function addOrg($obj){
        $email = isset($obj->email) ? $obj->email: "NULL";
        $contact = isset($obj->contactName) ? $obj->contactName : "NULL";
        $postal_code = isset($obj->postalCode) ? $obj->postalCode : "NULL";
        $lga = isset($obj->locality) ? $obj->locality: "NULL";

        $query = "INSERT INTO institutions (name, rc_no, phone, address, contact_name, url, postal_code, state, lga, email) VALUES ('".$obj->name."',".$obj->rc_no.", '".
        $obj->phone."', '".$obj->streetName."', '".$contact."','".$obj->uri."',".$postal_code.", '".$obj->locality."','".$lga."', '".$email."')";
        $result = $this->query($query);
        if(!$result){
            die($this->error);
        }

        
    }
    public function deleteOrg($id){
        $this->delete($id, "institutions");
    }
}
?>