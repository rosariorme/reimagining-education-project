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
    
    if (isset($postdata) && !empty($postdata)) {
         $request = json_decode($postdata);
		 //var_dump($request);
         $selection = @$request->selection;
         $user_id = @$request->userid;
         $course_id = @$request->courseid;
         $batch_id = @$request->batch_id;
		 
         if ($selection != "") {
            // Create connection
            $conn = new mysqli($servernameDB, $usernameDB, $passwordDB, $dbnameDB);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
			if($selection == "confirm"){
				$sql = "INSERT INTO batch_user_course_confirmation(user_id, course_id, batch_id,user_action) VALUES ('$user_id', '$course_id', '$batch_id',1);";
			}else{
				$sql = "INSERT INTO batch_user_course_confirmation(user_id, course_id, batch_id,user_action) VALUES ('$user_id', '$course_id', '$batch_id',-1);";
			}
            $result = $conn->query($sql);
            $r2= array();
			//echo $sql;
            
            echo json_encode($r2, JSON_UNESCAPED_UNICODE);

            $conn->close();
         }else {
         echo "Empty username parameter!";
         }
     }else {
        echo "Not called properly with username parameter!";
     }
?>