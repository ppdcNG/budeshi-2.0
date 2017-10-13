public function ajaxProject($page){
        $arrayToReturn = [];
        $query = "SELECT SQL_CALC_FOUND_ROWS p.oc_id,p.title,p.year, p.id, p.state, p.status, p.monitored, p.description, m.short_name, b.budget_amount, c.amount, ct.contractor_id, i.name FROM projects p LEFT JOIN mdas m
         ON p.mda_id = m.id LEFT JOIN planning b on p.id = b.project_id RIGHT JOIN contract c ON c.project_id = p.id RIGHT JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id ORDER BY p.id";


        $result = $this->query($query);
        if (mysqli_num_rows($result) <= 0) {
            $this->projecthtml = "No projects found in the data base";
            return;
        }
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];
        $this->total = $number;

        while($row = mysqli_fetch_assoc($result)){
            $arrayToReturn [] = $row;

        }
        return $arrayToReturn;

    }