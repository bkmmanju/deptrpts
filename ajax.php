
<?php
require_once('../../config.php');
require_once('lib.php');
global $DB,$CFG;

$context = context_system::instance();
$PAGE->set_context($context);
$type = optional_param('type','',PARAM_RAW);
$startdt = optional_param('startdate','',PARAM_RAW);
$enddt = optional_param('enddate','',PARAM_RAW);
$siteloc = optional_param('sitelocation','',PARAM_RAW);
$userloc = optional_param('userlocation','',PARAM_RAW);
$courseloc = optional_param('courselocation','',PARAM_RAW);
$sitecat = optional_param('sitecategory','',PARAM_INT);
$userselect = optional_param('usersearch','',PARAM_RAW);
$courseselect = optional_param('coursesearch','',PARAM_RAW);
$status = optional_param('status','',PARAM_RAW);
$firstload = optional_param('firstload','',PARAM_RAW);
$allcourse = optional_param('allcourse','',PARAM_RAW);
$alluser = optional_param('allusers','',PARAM_RAW);

if(!empty($firstload)){
  echo site_report(null,null,null,null);
}

if($type == "usersearch"){
  $users =$DB->get_records('user',array('deleted'=>'0','suspended'=>'0'));
  $userarray = [];
  foreach ($users as $user) {
        $userarray[]= array(
          'id'=>$user->id,
          'fullname' =>$user->firstname.' '.$user->lastname
        );
  }
   echo json_encode($userarray);
}
if($type == 'coursesearch'){
    $courses =$DB->get_records('course',array('visible'=>'1'));
    $coursearray=[];
    foreach ($courses as $course){
            $coursearray[] = array(
            'id' =>$course->id,
            'name'=>$course->fullname
      );
    }
    echo json_encode($coursearray);  
}
if($status == "startdate" || $status == "enddate" && empty($siteloc) && empty($userloc) && empty($courseloc) && ($userselect <= 1) && ($courseselect <= 1)){
  echo site_report(strtotime($startdt),strtotime($enddt),null,null);
}

if ($status == "sitelocation" || $status == "sitecategory") {
  echo site_report(strtotime($startdt),strtotime($enddt),$siteloc,$sitecat);
}

if($status == "userlocation" || $status == "usersearch"){
echo user_report(strtotime($startdt),strtotime($enddt),$userloc,$userselect);
}

if($status == "courselocation" || $status == "coursesearch"){
    echo course_report(strtotime($startdt),strtotime($enddt),$courseloc,$courseselect);
}

if($status == "allcourses"){
  echo allcourses_report(strtotime($startdt),strtotime($enddt));
}

if($status == "allusers"){
  echo allusers_report(strtotime($startdt),strtotime($enddt));
}