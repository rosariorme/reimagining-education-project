<?php
require "config.php";

 if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
 
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
 
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
 
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
 
        exit(0);
    }
    
    $postdata = file_get_contents("php://input");
    
    if (isset($postdata)) {
         $request = json_decode($postdata);
            // Create connection
            $conn = new mysqli($servernameDB, $usernameDB, $passwordDB, $dbnameDB);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
            $sql = "SELECT id, role_description, status FROM role WHERE status = 1";
            $result = $conn->query($sql);
            $r2= array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $r=array("roleid"=>utf8_decode($row["id"])
                    ,"role_description"=>utf8_decode($row["role_description"])
                    ,"status"=>utf8_decode($row["status"])
                    );
                    array_push($r2,$r);
                }
            }
           
            echo json_encode($r2, JSON_UNESCAPED_UNICODE);

            $conn->close();

     }else {
        echo "Not called properly with username parameter!";
     }
?>