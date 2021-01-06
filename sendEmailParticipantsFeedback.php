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
			$sql = "select user.id as userid ,user.username ,course.course_name ,batch_course.feedback_url_students ,batch_course.feedback_url_trainer from batch_user inner join batch_course on batch_course.batch_id = batch_user.batch_id inner join user on user.id = batch_user.user_id inner join batch_date on batch_date.batch_id = batch_user.batch_id inner join course on course.id = batch_course.course_id where batch_user.batch_id = '$batch_id' and batch_course.course_id = '$course_id' and user.role_id = 5";
            $result = $conn->query($sql);
            $r= array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
	               //$to = "emilse.e@vsstechnology.com";
	               $to = $row["username"];
                    $subject = "Ziksa Feedback";
                    
                    $message = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html style="margin: 0;padding: 0;" xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="viewport" content="width=device-width"><meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Ziksa Feedback Course</title><style type="text/css" id="media-query">
body {margin: 0;padding: 0; } table, tr, td {vertical-align: top;border-collapse: collapse; } .ie-browser table, .mso-container table {table-layout: fixed; } * {line-height: inherit; }
a[x-apple-data-detectors=true] {color: inherit !important;text-decoration: none !important; }
[owa] .img-container div, [owa] .img-container button {display: block !important; }
[owa] .fullwidth button {width: 100% !important; }.ie-browser .col, [owa] .block-grid .col {display: table-cell;float: none !important;vertical-align: top; }.ie-browser .num12, .ie-browser .block-grid, [owa] .num12, [owa] .block-grid {width: 500px !important; }.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%; }
.ie-browser .mixed-two-up .num4, [owa] .mixed-two-up .num4 {width: 164px !important; }.ie-browser .mixed-two-up .num8, [owa] .mixed-two-up .num8 {width: 328px !important; }.ie-browser .block-grid.two-up .col, [owa] .block-grid.two-up .col {width: 250px !important; }.ie-browser .block-grid.three-up .col, [owa] .block-grid.three-up .col {width: 166px !important; }.ie-browser .block-grid.four-up .col, [owa] .block-grid.four-up .col {width: 125px !important; }.ie-browser .block-grid.five-up .col, [owa] .block-grid.five-up .col {width: 100px !important; }.ie-browser .block-grid.six-up .col, [owa] .block-grid.six-up .col {width: 83px !important; }.ie-browser .block-grid.seven-up .col, [owa] .block-grid.seven-up .col {width: 71px !important; }.ie-browser .block-grid.eight-up .col, [owa] .block-grid.eight-up .col {width: 62px !important; }.ie-browser .block-grid.nine-up .col, [owa] .block-grid.nine-up .col {width: 55px !important; }.ie-browser .block-grid.ten-up .col, [owa] .block-grid.ten-up .col {width: 50px !important; }.ie-browser .block-grid.eleven-up .col, [owa] .block-grid.eleven-up .col {width: 45px !important; }.ie-browser .block-grid.twelve-up .col, [owa] .block-grid.twelve-up .col {width: 41px !important; }
@media only screen and (min-width: 520px) {.block-grid {width: 500px !important; }.block-grid .col {display: table-cell;Float: none !important;vertical-align: top; }.block-grid .col.num12 {width: 500px !important; }.block-grid.mixed-two-up .col.num4 {width: 164px !important; }
.block-grid.mixed-two-up .col.num8 {width: 328px !important; }
.block-grid.two-up .col {width: 250px !important; }
.block-grid.three-up .col {width: 166px !important; }
.block-grid.four-up .col {width: 125px !important; }
.block-grid.five-up .col {width: 100px !important; }
.block-grid.six-up .col {width: 83px !important; }
.block-grid.seven-up .col {width: 71px !important; }
.block-grid.eight-up .col {width: 62px !important; }
.block-grid.nine-up .col {width: 55px !important; }
.block-grid.ten-up .col {width: 50px !important; }
.block-grid.eleven-up .col {width: 45px !important; }
.block-grid.twelve-up .col {width: 41px !important; } }
@media (max-width: 520px) {.block-grid, .col {min-width: 320px !important;max-width: 100% !important; }
.block-grid {width: calc(100% - 40px) !important; }
.col {width: 100% !important; }
.col > div {margin: 0 auto; }
img.fullwidth {max-width: 100% !important; } }
</style></head>
<body class="clean-body" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #FFFFFF">
<div class="nl-container" style="min-width: 320px;Margin: 0 auto;background-color: #FFFFFF">
<div style="background-color:#ff6b00;"><div style="Margin: 0 auto;min-width: 320px;max-width: 500px;width: 500px;width: calc(19000% - 98300px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid two-up">
<div style="border-collapse: collapse;display: table;width: 100%;">
<div class="col num12" style="Float: left;max-width: 320px;min-width: 250px;width: 250px;width: calc(35250px - 7000%);background-color: transparent;">
<div style="background-color: transparent; width: 100% !important;">
<div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent; padding-top:20px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<div align="center" class="img-container center" style="padding-right: 0px;  padding-left: 0px;">
<img class="center" align="center" border="0" src="http://ziksa.biz/ziksa_api/icon-144-xxhdpi.png" alt="Image" title="Image" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: 0;height: auto;float: none;width: 100%;max-width: 150px" width="72">
</div></div></div></div></div></div></div><div style="background-color:#fdd74a;"><div style="Margin: 0 auto;min-width: 320px;max-width: 500px;width: 500px;width: calc(19000% - 98300px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid ">
<div style="border-collapse: collapse;display: table;width: 100%;">
<div class="col num12" style="min-width: 320px;max-width: 500px;width: 500px;width: calc(18000% - 89500px);background-color: transparent;">
<div style="background-color: transparent; width: 100% !important;">
<div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent; padding-top:30px; padding-bottom:30px; padding-right: 0px; padding-left: 0px;">
<div style="padding-right: 10px; padding-left: 10px; padding-top: 25px; padding-bottom: 10px;">
<div style="font-size:12px;line-height:14px;color:#ffffff;text-align: center;"><p style="margin: 0;font-size: 12px;line-height: 14px;text-align: center;"><span style="font-size: 18px; line-height: 21px;"><strong>Please fill this feedback form about the course: '.$row["course_name"].'</strong></span></p></div></div>
<div align="center" class="button-container center" style="Margin-right: 10px;Margin-left: 10px;">
<div style="line-height:15px;font-size:1px">&nbsp;</div><a href="'.$row["feedback_url_students"].'" target="_blank" style="color: #ffffff; text-decoration: none;">
<div style="color: #ffffff; background-color: #C7702E; border-radius: 25px; -webkit-border-radius: 25px; -moz-border-radius: 25px; max-width: 126px; width: 25%; border-top: 0px solid transparent; border-right: 0px solid transparent; border-bottom: 0px solid transparent; border-left: 0px solid transparent; padding-top: 5px; padding-right: 20px; padding-bottom: 5px; padding-left: 20px; text-align: center;">
<span style="font-size:16px;line-height:32px;"><span style="font-size: 14px; line-height: 28px;" data-mce-style="font-size: 14px;">Please click</span></span></div></a>
<div style="line-height:10px;font-size:1px">&nbsp;</div></div>
<div style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
<div align="center"><div style="border-top: 0px solid transparent; width:100%;">&nbsp;</div></div></div>
<div style="padding-right: 10px; padding-left: 10px; padding-top: 0px; padding-bottom: 10px;">
<div style="font-size:12px;text-align: center;line-height:18px;color:#B8B8C0;text-align: center;"><p style="margin: 0;font-size: 12px;line-height: 18px;text-align: center;"><span style="color: rgb(255, 255, 255); font-size: 12px; line-height: 18px;">If the button doesn\'t work please paste this link in your browser <br/>'.$row["feedback_url_students"].'</span></p></div>
</div></div></div></div></div></div></div></div></body></html>';
                    
                    // Always set content-type when sending HTML email
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    
                    // More headers
                    //$headers .= 'From: <emilse.e@vsstechnology.com>' . "\r\n";
                    //$headers .= 'Cc: myboss@example.com' . "\r\n";
                    
                    mail($to,$subject,$message,$headers);
                }
            }
           /*$r3 = array("participants" =>$r2,
		   "attendance"=>$r4);*/
		   
            echo json_encode($result->num_rows, JSON_UNESCAPED_UNICODE);

            $conn->close();

     }else {
        echo "Not called properly with username parameter!";
     }
?>