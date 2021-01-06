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
         $role_id = @$request->role_id;
         $userid = @$request->userid;
         $courseid = @$request->courseid;
         $batch_id = @$request->batch_id;
		 
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
						, bat.balance
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
                        , batch_venue.latitude
                        , batch_venue.longitude
						,course.rating 
						,bat.feedback_url_students
						,bat.feedback_url_trainer
						,COALESCE((select count(id) from batch_user_course_confirmation where user_id = '$userid' and course_id = '$courseid' and batch_id = '$batch_id' and user_action = 1),0) as confirmed_course
						,case when bat.user_type = 1 then bat.user_id 
					else -1 end as trainerid
					from (
						select max(batch_course.batch_id) as batch_id,batch_user.user_id,batch_course.course_id,batch_user.user_type,batch_user.balance,batch_course.feedback_url_students,batch_course.feedback_url_trainer 
						from batch_user 
						inner join batch_course on batch_course.batch_id = batch_user.batch_id
						group by batch_user.user_id ,batch_course.course_id,batch_user.user_type,batch_user.balance,batch_course.feedback_url_students,batch_course.feedback_url_trainer
					) bat
					inner join batch_date on batch_date.batch_id = bat.batch_id
					left join batch_venue on batch_venue.batch_id = bat.batch_id 
					inner join course on course.id = bat.course_id
					inner join user on user.id = bat.user_id 
					where bat.user_id = '$userid' and course.id = '$courseid' and bat.batch_id = '$batch_id' ) q";
            $result = $conn->query($sql);
            $r2= array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $r=array(
					"course_description"=>utf8_decode($row["course_description"])
                    ,"courseid"=>utf8_decode($row["courseid"])
                    ,"course_name"=>utf8_decode($row["course_name"])
                    ,"balance"=>utf8_decode($row["balance"])
                    ,"course_start_date"=>utf8_decode($row["course_start_date"])
                    ,"course_end_date"=>utf8_decode($row["course_end_date"])
                    ,"venue"=>utf8_decode($row["venue"])
                    ,"course_cost"=>utf8_decode($row["course_cost"])
                    ,"course_pass_mark"=> utf8_decode($row["course_pass_mark"])
                    ,"exams_enabled"=>utf8_decode($row["exams_enabled"])
                    ,"pretest_enabled"=>utf8_decode($row["pretest_enabled"])
                    ,"trainerid"=>utf8_decode($row["trainerid"])
                    ,"rating"=>utf8_decode($row["rating"])
                    ,"preview_image"=>utf8_decode($row["preview_image"])
                    ,"pdf_url"=>utf8_decode($row["pdf_url"])
                    ,"cvs_url"=>utf8_decode($row["cvs_url"])
                    ,"latitude"=>utf8_decode($row["latitude"])
                    ,"longitude"=>utf8_decode($row["longitude"])
                    ,"confirmed_course"=>utf8_decode($row["confirmed_course"])					
                    ,"feedback_url_trainer"=>utf8_decode($row["feedback_url_trainer"])					
                    ,"feedback_url_students"=>utf8_decode($row["feedback_url_students"])					
                    );
                    array_push($r2,$r);
                }
            }
            
            $sql2 = "select *,TOTALINVITES - (TOTALACCEPTS + TOTALDECLINED) as TOTALPENDING 
from (select distinct 
COALESCE((SELECT count(DISTINCT user_id) as participants FROM attendance WHERE course_id  = bat.course_id and batchid = '$batch_id' and action = 'confirm'),0) as TOTALACCEPTS
,COALESCE((SELECT count(DISTINCT user_id) as participants FROM attendance WHERE course_id  = bat.course_id and batchid = '$batch_id' and action = 'decline'),0) as TOTALDECLINED
,COALESCE((select count(DISTINCT batch_user.user_id) from batch_user inner join batch_course on batch_course.batch_id = batch_user.batch_id where batch_course.course_id = bat.course_id),0) as TOTALINVITES 
from (select max(batch_course.batch_id) as batch_id,batch_user.user_id,batch_course.course_id,batch_user.user_type from batch_user inner join batch_course on batch_course.batch_id = batch_user.batch_id group by batch_user.user_id ,batch_course.course_id,batch_user.user_type) bat 
where bat.course_id = '$courseid' and bat.batch_id = '$batch_id' ) q";
            $result2 = $conn->query($sql2);
            $rarray2 = array();
            if ($result2->num_rows > 0) {
				
                while($row = $result2->fetch_assoc()) {
					$rarray2=array("CONFIRMED"=>$row["TOTALACCEPTS"]
                    ,"PENDING"=>$row["TOTALPENDING"]
                    ,"DECLINED"=>$row["TOTALDECLINED"]
                    );
                }
            }
            
            $sqlr = "SELECT cast(sum(answerpointage)/count(id) as decimal(18,2)) as rating FROM feedback_answers WHERE courseid = '$courseid'";
            $resultr = $conn->query($sqlr);
            $rating = 0;
            if ($resultr->num_rows > 0) {
                while($row = $resultr->fetch_assoc()) {
					if($row["rating"] == null){
						$rating = 0;
					}else{
                    $rating = $row["rating"];
					}
                }
            }
			
            $sql3 = "SELECT id, exam_title, creation_datetime, course_id, start_date, end_date, enabled, question, answer1, answer2, answer3, answer4, answer5, correct_answer FROM exam WHERE course_id = '$courseid'";
            $result3 = $conn->query($sql3);
            $r3= array();
            if ($result3->num_rows > 0) {
                while($row = $result3->fetch_assoc()) {
                    $r=array(
					"examid"=>utf8_decode($row["id"]),
					"exam_title"=>utf8_decode($row["exam_title"]),
					"creation_datetime"=>utf8_decode($row["creation_datetime"]),
					"course_id"=>utf8_decode($row["course_id"]),
					"start_date"=>utf8_decode($row["start_date"]),
					"end_date"=>utf8_decode($row["end_date"]),
					"enabled"=>utf8_decode($row["enabled"]),
					"question"=>utf8_decode($row["question"]),
					"answer1"=>utf8_decode($row["answer1"]),
					"answer2"=>utf8_decode($row["answer2"]),
					"answer3"=>utf8_decode($row["answer3"]),
					"answer4"=>utf8_decode($row["answer4"]),
					"answer5"=>utf8_decode($row["answer5"]),
					"correct_answer"=>utf8_decode($row["correct_answer"])
					);
                    array_push($r3,$r);
                }
            }
			$sql4 = "
			select name,city,phone,state,country,corporate,Brief,experience
			from batch_course
			inner join batch_user on batch_user.batch_id = batch_course.batch_id and batch_user.user_type = 1
			inner join user on user.id = batch_user.user_id 
			INNER JOIN trainer_details ON trainer_details.trainerid = user.id 
			where batch_course.course_id = '$courseid' and batch_course.batch_id = '$batch_id'";
            $result4 = $conn->query($sql4);
            $r4= array();
            if ($result4->num_rows > 0) {
                while($row = $result4->fetch_assoc()) {
                    $r=array(
					"name"=>utf8_decode($row["name"]),
					"contact"=>utf8_decode($row["phone"]),
					"city"=>utf8_decode($row["city"]),
					"state"=>utf8_decode($row["state"]),
					"country"=>utf8_decode($row["country"]),
					"company"=>utf8_decode($row["corporate"]),
					"brief"=>utf8_decode($row["Brief"]),
					"experience"=>utf8_decode($row["experience"])
					);
                    array_push($r4,$r);
                }
            }

            $resultcomplete = array();
            $resultcomplete["info"] = $r2;
            $resultcomplete["participants"] = $rarray2;
            $resultcomplete["rating"] = $rating;
            $resultcomplete["preexam"] = $r3;
            $resultcomplete["trainer"] = $r4;
            echo json_encode($resultcomplete, JSON_UNESCAPED_UNICODE);

            $conn->close();
         }else {
         echo "Empty username parameter!";
         }
     }else {
        echo "Not called properly with username parameter!";
     }
?>