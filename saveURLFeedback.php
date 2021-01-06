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
        $modified_date = date("Y-m-d h:i:s");
        $course_id = $request->course_id;
        $batch_id = $request->batch_id;
        $course_id = $request->course_id;
		$feedback_url_trainer = $request->feedback_url_trainer;
		$feedback_url_students = $request->feedback_url_students;
         if ($batch_id != "") {
            // Create connection
            $conn = new mysqli($servernameDB, $usernameDB, $passwordDB, $dbnameDB);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
            //update data
            $sql = "UPDATE batch_course SET batch_course.feedback_url_students = '$feedback_url_students', batch_course.feedback_url_trainer = '$feedback_url_trainer' where batch_course.batch_id = '$batch_id' and batch_course.course_id = '$course_id'";
            $result = $conn->query($sql);
            echo json_encode("done", JSON_UNESCAPED_UNICODE);

            $conn->close();
         }else {
         echo "Empty username parameter!";
         }
     }else {
        echo "Not called properly with username parameter!";
     }
?>