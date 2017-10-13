<?php
// Model an abstract class that exports basic database functionalities like connecting, reading
//updating e.t.c the database
class Model{
    protected $conn = null;
    protected $result = null;
    public $error = null;
    public $errorNo = null;
    public $absPath = "http://localhost/budeshi-2.0/webroot/";

    function __construct(){
        $this->conn = mysqli_connect(SQL_HOST,SQL_USER,SQL_PASS,SQL_DB);
        if(!$this->conn){
            $this->error = mysqli_error();
            die($this->error);
        }
    }

    function query($querystring){
        $this->result = mysqli_query($this->conn, $querystring);
        if($this->result){
          return $this->result;  
        }
        else{
            $this->error = mysqli_error($this->conn);
            $this->errorNo = mysqli_errno($this->conn);
            die($this->error);
            return false;
        }

    }

    public function update($id, $fieldSet = [], $table = "institution", $where = "release_id"){
        $fieldString = "";
        if(!empty($fieldSet)){
            foreach($fieldSet as $name=> $value){
                if(is_string($value))
                $fieldString .= $name."= '".$value."', ";
                else
                $fieldString .= $name."=".$value.", ";

            }
            $fieldString = rtrim($fieldString,' ,');
        }
        else{
            echo "Error Empty FieldSet passed to update function..";
            print_r($fieldSet);
            die();
        }
        $query = "UPDATE ".$table." SET ".$fieldString." WHERE ".$where." = '".$id."'";
        $result = $this->query($query);
        if(!$result){
            echo $this->error;
            die();
        }
        return $result;
    }

    public function read($id, $table = "institution"){
        $query = "SELECT * FROM ".$table." WHERE id = ".$id;
        $result = $this->query($query);
        if(!$result){
            echo $this->error;
            die();
        }
        return $result;
    }

    public function delete($id, $table = "institution"){
        $query = "DELETE FROM ".$table." WHERE id = ".$id." LIMIT 1;";
        $result = $this->query($query);
        if(!$result){
            echo $this->error;
            die();
        }
        return $result;
    }
    //create function requires fieldset parmeter to be assoc array of tablecolumn=>value set
    public function create($fieldset, $table = "institution"){
        $fieldString = [];
        $valueString = [];
        if(!empty($fielset)){
            foreach($fieldset as $name=> $value){
                $fieldString[] = $name;
                if(is_string($value)){
                $valueString[] = "'".$value."'";
                }
                else{
                    $valueString[] = $value;
                }
            }
        }
        $fields = "(".implode(",",$fieldString).")";
        $values = "(".implode(",", $valueString).")";

        $query = "INSERT INTO ".$table.$fields." VALUES ".$values;
        $result = $this->query($query);
        if(!$result){
            echo $this->error;
            die();
        }
        return $result;

    }
    public function queryToJson($query, $prefix= ""){
        $output = array();
        $result = $this->query($query);
        if(!$result){
            die($this->error);
        }
        if(mysqli_num_rows($result) <= 0){
            return "empty";
        }
        else{
            while($row = mysqli_fetch_assoc($result)){
                foreach($row as $name=>$value){
                    $output[$prefix.$name] = $value;
                }
            }
        
            $output["ajaxstatus"] = "success";
            $output["message"] = "Fetched successfully";
            $output = json_encode($output);
            return $output; 
        }
    }
    public function ajaxSuccess($data_obj, $type = "success"){
        
        
    }
    protected function trimText($text, $max = 100, $pgrh = 1)
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
    protected function generate_ocid($mda_id){
        $query = "SELECT short_name FROM mdas WHERE id = ".$mda_id;
        $result = $this->query($query);
        if(!$result){
            die($this->error);
        }
        $name = mysqli_fetch_array($result)[0];
        $code = md5(time());
        $code = substr($code,0,3).substr($code,-3)."ng";
        $ocid = "ocds-".OC_PREFIX."-".$code."-".$name;
        return $ocid;
    }
    public function renderRow($type, $value){
        $type = strtolower($type);
        $row = "";
      
            $row = "<".$type.">".$value."</".$type.">";
        
        return $row;
    }
    
}