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
            return false;
        }

    }

    public function update($id, $fieldSet = [], $table = "institution"){
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
        $query = "UPDATE ".$table." SET ".$fieldString." WHERE id = ".$id;
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
}