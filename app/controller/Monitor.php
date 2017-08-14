<?php
class Monitor extends Controller{

    public function index(){
        require_once("../app/models/MonitorDb.php");
        $monitor = new MonitorModel();
        $mdas = $monitor->getMDAList();
        require_once("../app/views/backend/home.html");
    }
    public function projects($mda_id){
        
    }
}
 ?>