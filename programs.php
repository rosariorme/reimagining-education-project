<?php
require "config.php";
$url_download = "http://www.ziksa.biz/admin/";
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
        @$role_id = $request->role_id;
        @$userid = $request->userid;
        @$is_completed = $request->is_completed;
        @$starttime = $request->starttime;
        @$endtime = $request->endtime;
		if ($role_id != "") {
            // Create connection
            $conn = new mysqli($servernameDB, $usernameDB, $passwordDB, $dbnameDB);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
			$sql = "select * from ( 
						select bat.user_id
						, bat.batch_id
						, batches.client_img as image
						, bat.course_id
						, cast(STR_TO_DATE(batch_date.start_datetime,'%W%d%M%Y - %H:%i') as datetime) as course_start_date
						, cast(STR_TO_DATE(batch_date.end_datetime,'%W%d%M%Y - %H:%i') as datetime) as course_end_date
						, course.course_name
						, course.course_description
						, course.preview_image
						, course.pdf_url
						, course.cvs_url
						, course.id as courseid
						, course.course_cost
						, course.course_pass_mark
						, course.exams_enabled
						, course.pretest_enabled
						, batch_venue.address as venue
						,case when bat.user_type = 1 then bat.user_id else -1 end as trainerid
						from (select max(batch_course.batch_id) as batch_id,batch_user.user_id,batch_course.course_id,batch_user.user_type  
						from batch_user 
						inner join batch_course on batch_course.batch_id = batch_user.batch_id
						group by batch_user.user_id ,batch_course.course_id,batch_user.user_type) bat 
						inner join batches on batches.id =  bat.batch_id 
						inner join batch_date on batch_date.batch_id = bat.batch_id
						left join batch_venue on batch_venue.batch_id = bat.batch_id 
						inner join course on course.id = bat.course_id
						inner join user on user.id = bat.user_id 
						left join (select batch_id,course_id from batch_user_course_confirmation where user_id = '$userid' and user_action = -1 group by batch_id,course_id) filter on filter.batch_id = bat.batch_id and filter.course_id = bat.course_id 
						where bat.user_id = '$userid' and filter.course_id is null) q";
			
			if($is_completed==false){
				$sql .=" WHERE q.course_end_date >= DATE_FORMAT(NOW(),'%Y-%m-%d')";
            }else{
				$sql .=" WHERE q.course_end_date < DATE_FORMAT(NOW(),'%Y-%m-%d')";
			}
			
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $r2= array();
				
                while($row = $result->fetch_assoc()) {
					$img = utf8_decode($row["image"]);
					if($img != "" &&$img != NULL && $img != "NULL"){
						$img = str_replace("../../","",$img);
						$img = $url_download . $img;
					}
                    $r=array("course_description"=>utf8_decode($row["course_description"])
                    ,"courseid"=>utf8_decode($row["courseid"])
                    ,"course_name"=>utf8_decode($row["course_name"])
                    ,"course_start_date"=>utf8_decode($row["course_start_date"])
                    ,"course_end_date"=>utf8_decode($row["course_end_date"])
                    ,"venue"=>utf8_decode($row["venue"])
                    ,"course_cost"=>utf8_decode($row["course_cost"])
                    ,"course_pass_mark"=> utf8_decode($row["course_pass_mark"])
                    ,"exams_enabled"=>utf8_decode($row["exams_enabled"])
                    ,"pretest_enabled"=>utf8_decode($row["pretest_enabled"])
                    ,"trainerid"=>utf8_decode($row["trainerid"])
                    ,"image"=>$img
                    ,"batch_id"=>utf8_decode($row["batch_id"])
                    );
					array_push($r2,$r);
                }
                echo json_encode($r2, JSON_UNESCAPED_UNICODE);
            } else {
                echo "{}";
            }
            $conn->close();
         }else {
         echo "";
         }
     }else {
        echo "Not called properly with username parameter!";
     }
?>