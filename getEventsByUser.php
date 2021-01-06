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
         $userid = @$request->userid;
         if ($userid != "") {
            // Create connection
            $conn = new mysqli($servernameDB, $usernameDB, $passwordDB, $dbnameDB);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
            $sql = "select q.course_description as details,q.course_start_date as time_start,q.course_end_date as time_end,q.venue as address_details,q.course_name as title from ( select batch_user.user_id, batch_user.batch_id, batch_user.user_type, batch_course.course_id, cast(STR_TO_DATE(batch_date.start_datetime,'%W%d%M%Y - %H:%i') as datetime) as course_start_date, cast(STR_TO_DATE(batch_date.end_datetime,'%W%d%M%Y - %H:%i') as datetime) as course_end_date, course.course_name, course.course_description, course.preview_image, course.pdf_url, course.cvs_url, course.id as courseid, course.course_cost, course.course_pass_mark, course.exams_enabled, course.pretest_enabled, batch_venue.address as venue from batch_user inner join batch_course on batch_course.batch_id = batch_user.batch_id inner join batch_date on batch_date.batch_id = batch_user.batch_id left join batch_venue on batch_venue.batch_id = batch_user.batch_id inner join course on course.id = batch_course.course_id inner join user on user.id = batch_user.user_id where user_id = '$userid') q";
            $result = $conn->query($sql);
            $r2= array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $r=array("title"=>utf8_decode($row["title"])
                    ,"time_start"=>utf8_decode($row["time_start"])
                    ,"time_end"=>utf8_decode($row["time_end"])
                    ,"address_details"=>utf8_decode($row["address_details"])
                    ,"details"=>utf8_decode($row["details"])
					);
                    array_push($r2,$r);
                }
            }
			echo json_encode($r2, JSON_UNESCAPED_UNICODE);

            $conn->close();
         }else {
         echo "Empty username parameter!";
         }
     }else {
        echo "Not called properly with username parameter!";
     }
?>