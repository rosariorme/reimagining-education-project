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
		 $courseid = $request->courseid;
		 $userid = $request->userid;
		 $batch_id = $request->batch_id;
            // Create connection
            $conn = new mysqli($servernameDB, $usernameDB, $passwordDB, $dbnameDB);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
            $sql = "select ddate as date
,batch_course.course_id
,batch_user.user_id
, CAST( STR_TO_DATE( batch_date.start_datetime,  '%W%d%M%Y - %H:%i' ) AS DATE) AS course_start_date
, CAST( STR_TO_DATE( batch_date.end_datetime,  '%W%d%M%Y - %H:%i' ) AS DATE) AS course_end_date
, batch_venue.address
, batch_venue.latitude
, batch_venue.longitude
, CASE WHEN att.datetime IS NULL THEN 0 ELSE 1 END AS saved
, CAST( att.datetime AS DATE ) AS date_saved
, att.action
, att.inarea
, att.location 
from (select d.date as ddate from 
(select adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) date from
 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) d
where d.date between (select DATE_SUB(cast(STR_TO_DATE(batch_date.start_datetime,'%W%d%M%Y - %H:%i') as date),INTERVAL 1 DAY) from batch_date where batch_date.batch_id = '$batch_id' limit 1) and (select cast(STR_TO_DATE(batch_date.end_datetime,'%W%d%M%Y - %H:%i') as date) from batch_date where batch_date.batch_id = '$batch_id' limit 1)
group by d.date) d
inner join batch_date on batch_date.batch_id = '$batch_id' 
inner join batch_user on batch_date.batch_id = batch_user.batch_id 
inner join batch_course on batch_course.batch_id = batch_user.batch_id 
inner join batch_venue on batch_venue.batch_id = batch_user.batch_id
left join (SELECT user_id, course_id, datetime, action, location, inarea, unitid, batchid FROM attendance) att on att.user_id = batch_user.user_id and att.batchid = batch_user.batch_id and cast(att.datetime as date) = d.ddate
where batch_course.course_id = '$courseid' and batch_user.batch_id = '$batch_id' and batch_user.user_id = '$userid' order by ddate";
            $result = $conn->query($sql);
            $r2= array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $r=array("course_id"=>utf8_decode($row["course_id"])
						,"date"=>utf8_decode($row["date"])
						,"saved"=>utf8_decode($row["saved"])
						,"inarea"=>utf8_decode($row["inarea"])
						,"action"=>utf8_decode($row["action"])
						,"latitude"=>utf8_decode($row["latitude"])
						,"longitude"=>utf8_decode($row["longitude"])
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