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
			$course_id = @$request->course_id;
			$batch_id = @$request->batch_id;
            $conn = new mysqli($servernameDB, $usernameDB, $passwordDB, $dbnameDB);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
            $sql = "select user.id as userid,user.name as usercompletename,user.phone, cast(STR_TO_DATE(batch_date.start_datetime,'%W%d%M%Y - %H:%i') as datetime) as startcourse,cast(STR_TO_DATE(batch_date.end_datetime,'%W%d%M%Y - %H:%i') as datetime) as endcourse from batch_user inner join batch_course on batch_course.batch_id = batch_user.batch_id inner join user on user.id = batch_user.user_id inner join batch_date on batch_date.batch_id = batch_user.batch_id where batch_user.batch_id = '$batch_id' and batch_course.course_id = '$course_id' and user.role_id in(2, 5)";
            $result = $conn->query($sql);
            $r2= array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $r=array("userid"=>utf8_decode($row["userid"])
                    ,"usercompletename"=>utf8_decode($row["usercompletename"])
                    ,"phone"=>utf8_decode($row["phone"])
                    ,"startcourse"=>utf8_decode($row["startcourse"])
                    ,"endcourse"=>utf8_decode($row["endcourse"])
					);
                    array_push($r2,$r);
                }
            }
			$sql = "select user.id as userid 
			,user.name as usercompletename 
			,cast(STR_TO_DATE(batch_date.start_datetime,'%W%d%M%Y - %H:%i') as datetime) as startcourse 
			,cast(STR_TO_DATE(batch_date.end_datetime,'%W%d%M%Y - %H:%i') as datetime) as endcourse 
			,cast(attendance.datetime as date) as datetime
			,attendance.action 
			from batch_user 
			inner join batch_course on batch_course.batch_id = batch_user.batch_id 
			inner join user on user.id = batch_user.user_id 
			inner join batch_date on batch_date.batch_id = batch_user.batch_id 
			inner join attendance on attendance.user_id = batch_user.user_id and batch_course.course_id = attendance.course_id 
			where batch_user.batch_id = '$batch_id' and batch_course.course_id = '$course_id' and user.role_id = 5";
            $result = $conn->query($sql);
            $r4= array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $r=array("userid"=>utf8_decode($row["userid"])
                    ,"usercompletename"=>utf8_decode($row["usercompletename"])
                    ,"startcourse"=>utf8_decode($row["startcourse"])
                    ,"endcourse"=>utf8_decode($row["endcourse"])
					,"datetime"=>utf8_decode($row["datetime"])
					,"action"=>utf8_decode($row["action"])
					);
                    array_push($r4,$r);
                }
            }
           $r3 = array("participants" =>$r2,
		   "attendance"=>$r4);
            echo json_encode($r3, JSON_UNESCAPED_UNICODE);

            $conn->close();

     }else {
        echo "Not called properly with username parameter!";
     }
?>