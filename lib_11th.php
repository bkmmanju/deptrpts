<?php
global $CFG,$DB;
require_once($CFG->libdir.'/completionlib.php');
require_once("$CFG->libdir/gradelib.php");
require_once("$CFG->dirroot/lib/completionlib.php");
require_once($CFG->dirroot . '/grade/querylib.php');
//here creating site-report function.
function site_report($startdt,$enddt,$siteloc,$sitecat){
  global $DB,$OUTPUT; 
  //here creating the data for cards. 
  $carddata=get_course_from_category($startdt,$enddt,$siteloc,$sitecat);
  $data='';
  $counter=0;
  $title=array(get_string('enrolledcourses','local_deptrpts'),
    get_string('coursecompletion','local_deptrpts'),
    get_string('badgesearned','local_deptrpts'),
    get_string('certificateearned','local_deptrpts'));
  $icon=array("<i class='fa fa-check-square-o' aria-hidden='true'></i>",
    "<i class='fa fa-certificate' aria-hidden='true'></i>",
    "<i class='fa fa-list' aria-hidden='true'></i>",
    "<i class='fa fa-address-card-o' aria-hidden='true'></i>");
  $color=array("bg-primary","bg-info","bg-success","bg-danger");
  if(!empty($carddata)){
  foreach ($carddata as $card) {
    $data.=filter_top_cards($icon[$counter],$title[$counter],$card,$color[$counter]);
    $counter++;
  }
  }
  //here creating pie charts..
  if(!empty($sitecat)){
    $categories = $DB->get_records('course_categories',array('id'=>$sitecat));
  }else{
    $categories = $DB->get_records('course_categories',array('visible'=>1));
  }
  $sitecategory="";
  $counter=1;
  foreach ($categories as $category) {
    $site=course_completion_stats(null,null,$category->id,null,null);
    $catdata=$site[0].','.$site[1].','.$site[2];
    $catname=$site[3];
    if(!empty($site[0]) || !empty($site[1]) || !empty($site[2])){
    $sitecategory.= filter_categorywise_chart($counter,$catdata,$catname);
    $counter++;
    }
 
  }
  //here creating year wise graphs to enroll and complete.
  if(!empty($sitecat)){
  $yeargraph = get_yearwisecategory_info($sitecat);
  $enrolllabel="";
  $enrolldata="";
  $counter=1;
  if(!empty($yeargraph['enrol'])){
    foreach ($yeargraph['enrol'] as $enkey => $envalue) {
      if($counter==1){
        $enrolllabel="'".$enkey."'";
        $enrolldata=$envalue;
      }else{
        $enrolllabel=$enrolllabel.','."'".$enkey."'";
        $enrolldata=$enrolldata.','.$envalue;
      }
      $counter++;
    }
  }

  $completelabel="";
  $completedata="";
  $counter=1;
  if(!empty($yeargraph['complete'])){
    foreach ($yeargraph['complete'] as $cmkey => $cmvalue) {
      if($counter==1){
        $completelabel="'".$cmkey."'";
        $completedata=$cmvalue;
      }else{
        $completelabel=$completelabel.','."'".$cmkey."'";
        $completedata=$completedata.','.$cmvalue;
      }
      $counter++;
    }
  }
    $graph=filter_yearly_enrol_completiongraph($enrolllabel,$enrolldata,$completelabel,$completedata);
  }else{
    $emptygraph=full_site_yearly_graphs();

      $graph=filter_yearly_enrol_completiongraph($emptygraph[0],$emptygraph[1],$emptygraph[2],$emptygraph[3]);
  }
  //here creating datatable to site-report.
  $datatablearray=[];
  $courses=$DB->get_records('course',array('category'=>$sitecat));
  foreach ($courses as $course) {
    $enroled=get_enroled_userdata($course->id);
    if(!empty($enroled)){
      foreach ($enroled as $enkey => $envalue) {
        $user=$DB->get_record('user',array('id'=>$envalue[0]));
        if(!empty($user)){
            if(!empty($envalue[1])){
              $enroldate=date('d-m-Y', $envalue[1]);
            }else{
              $enroldate="-";
            }
            $fullname=$user->firstname.' '.$user->lastname;
            $email=$user->email;
            $coursename=$course->fullname;
            $completion=$DB->get_record_sql("SELECT timecompleted FROM {course_completions} WHERE course=".$course->id." AND userid=".$envalue[0]." AND timecompleted IS NOT NULL");
              if(!empty($completion)){
                $completiondate=date('d-m-Y',$completion->timecompleted);
                }else
                {
                  $completiondate="-";
                }
                $cinfo = new completion_info($course);
                $iscomplete = $cinfo->is_course_complete($user->id);
                if(!empty($iscomplete)){

                  $status=get_string('complet','local_deptrpts');
                }else
                {
                  $status=get_string('notcomplete','local_deptrpts');
                }
                $grade = grade_get_course_grades($course->id, $envalue[0]);
                $grd = $grade->grades[$envalue[0]]; 
                $cgrade=$grd->str_grade;

                $datatablearray[]=array("counter"=>$counter,"username"=>$fullname,"emailid"=>$email,"coursefullname"=>$coursename,"enrolledtime"=>$enroldate,"completiontime"=>$completiondate,"completionstatus"=>$status,"coursegrade"=>$cgrade);
                $counter++;                
        }
      }
    }
  }
  $retarray=[];
  $retarray['tabledata']=$datatablearray;
  $sitetable=site_datatable($retarray);
  //returning final html.
  $fhtml="";
  $fhtml.=$data;
  $fhtml.=$sitecategory;
  $fhtml.=$graph;
  $fhtml.=$sitetable;
  echo $fhtml;
}
//here creating user-report.
function user_report($startdt,$enddt,$userloc,$userid){
  global $DB;
  $coursecount = user_course_count($userid,$startdt,$enddt,$userloc);
  $badgecount= user_badge_count($userid,$startdt,$enddt,$userloc);
  $certificatecount=user_course_certificate($userid,$startdt,$enddt,$userloc);
  $completioncount=user_course_completion($userid,$startdt,$enddt,$userloc);
  $title=array(get_string('enrolledcourses','local_deptrpts'),
    get_string('coursecompletion','local_deptrpts'),
    get_string('badgesearned','local_deptrpts'),
    get_string('certificateearned','local_deptrpts'));
  $icon=array("<i class='fa fa-check-square-o' aria-hidden='true'></i>",
    "<i class='fa fa-certificate' aria-hidden='true'></i>",
    "<i class='fa fa-list' aria-hidden='true'></i>",
    "<i class='fa fa-address-card-o' aria-hidden='true'></i>");
  $color=array("bg-primary","bg-info","bg-success","bg-danger");
  $html='';
  $html.=filter_top_cards($icon[0],$title[0],$coursecount,$color[0]);
  $html.=filter_top_cards($icon[1],$title[1],$completioncount,$color[1]);
  $html.=filter_top_cards($icon[2],$title[2],$badgecount,$color[2]);
  $html.=filter_top_cards($icon[3],$title[3],$certificatecount,$color[3]);
  
  //here creating year wise graphs to user.
  $ycomplete=get_user_yearly_completion($userid);
  $temp1=1;
  $ydata='';
  $ylabel='';
  foreach ($ycomplete as $ykey => $yvalue) {
    if($temp1 == 1){
      $ydata = $yvalue;
      $ylabel = "'".$ykey."'";
    }else{
      $ydata = $ydata.','.$yvalue;
      $ylabel = $ylabel.','."'".$ykey."'";
    }
    $temp1++;
  }

  $yenrol=get_enrolled_course_yearly($userid);
  $temp2=1;
  $crdata='';
  $crlabel='';
  if(!empty($yenrol)){
    foreach ($yenrol as $crkey => $crvalue) {
    if($temp2 == 1){
      $crdata = $crvalue;
      $crlabel = "'".$crkey."'";

    }else{
      $crdata = $crdata.','.$crvalue;
      $crlabel = $crlabel.','."'".$crkey."'";
    }
    $temp2++;
    }
  }

  $yearlygraph=filter_yearly_enrol_completiongraph($crlabel,$crdata,$ylabel,$ydata);

  //here getting data from month wise graphs to user.
  //enrollment data.
  $infoenroll = get_course_enrolled_info($userid);
  $count = 1;
  $menrol = '';
  foreach ($infoenroll as $mkey => $mvalue) {
    if($count == 1){
      $menrol = $mvalue;
    }else{
      $menrol =$menrol.','.$mvalue;
    }
    $count++;
  }
//completion data.
$completion=get_course_completion($userid);
$temp=1;
$mcomplete='';
foreach ($completion as $ckey => $cvalue) {
  if($temp == 1){
    $mcomplete = $cvalue;
  }else{
    $mcomplete =$mcomplete.','.$cvalue;
  }
  $temp++;
}
$montlygraph=filter_monthly_enrol_completiongraph($menrol,$mcomplete);

  $fhtml="";
  $fhtml.=$html;
  $fhtml.=$yearlygraph;
  $fhtml.=$montlygraph;
  echo $fhtml;
}

//here creating course-report 
function course_report($startdt,$enddt,$courseloc,$courseid){
  global $DB;
  $enrolled = $DB->get_records_sql("

    SELECT c.id, u.id

    FROM {course} c
    JOIN {context} ct ON c.id = ct.instanceid
    JOIN {role_assignments} ra ON ra.contextid = ct.id
    JOIN {user} u ON u.id = ra.userid
    JOIN {role} r ON r.id = ra.roleid

    where c.id = ".$courseid."");
  $enrolluser = count($enrolled);
  $badgecount= get_badges_earned($courseid);
  $certificatecount =get_course_cerficatescount($courseid);
  $completioncount =course_complition_count($courseid);
  $title=array(get_string('enrolledusers','local_deptrpts'),
    get_string('userscompleted','local_deptrpts'),
    get_string('badges','local_deptrpts'),
    get_string('certificates','local_deptrpts'));
  $icon=array("<i class='fa fa-check-square-o' aria-hidden='true'></i>",
    "<i class='fa fa-certificate' aria-hidden='true'></i>",
    "<i class='fa fa-list' aria-hidden='true'></i>",
    "<i class='fa fa-address-card-o' aria-hidden='true'></i>");
  $color=array("bg-primary","bg-info","bg-success","bg-danger");
  $html='';
  $html.=filter_top_cards($icon[0],$title[0],$enrolluser,$color[0]);
  $html.=filter_top_cards($icon[1],$title[1],$completioncount,$color[1]);
  $html.=filter_top_cards($icon[2],$title[2],$badgecount,$color[2]);
  $html.=filter_top_cards($icon[3],$title[3],$certificatecount,$color[3]);
  //here getting data of yearwise to course.
    $yearlygraph=yearwise_course_enrollment_data($courseid);
    global $DB;
     $completiondata = $DB->get_records_sql('SELECT id,timecompleted FROM {course_completions}  WHERE course = "'.$courseid.'" AND timecompleted is not null');
      $completiondate=[];
     foreach ($completiondata as $comkey => $comvalue) {
      $completiondate[]=date('Y', $comvalue->timecompleted);
     }
     //completion data.
     $completionyear = array_count_values($completiondate);
     $complabel="";
    $compdata="";
    $counter=1;
    if(!empty($completionyear)){
    foreach ($completionyear as $cmkey => $cmvalue) {
    if($counter==1){
    $complabel="'".$cmkey."'";
    $compdata=$cmvalue;
    }else{
    $complabel=$enrolllabel.','."'".$cmkey."'";
    $compdata=$erolldata.','.$cmvalue;
    }
    $counter++;
    }
    }
     $enrolldates=get_enroled_userdata($courseid);
      $convdate=[];
      if(!empty($enrolldates)){
        foreach ($enrolldates as $condate) { 
        $convdate[]=date('Y', $condate[1]);

      }
      }
      //enrollment.
      $userenrolconvyear = array_count_values($convdate);
      $enrolllabel ="";
      $enrolldata ="";
      $counter=1;
      if(!empty($userenrolconvyear)){
      foreach ($userenrolconvyear as $enkey => $envalue) {
      if($counter==1){
      $enrolllabel="'".$enkey."'";
      $enrolldata=$envalue;
      }else{
      $enrolllabel=$enrolllabel.','."'".$enkey."'";
      $erolldata=$erolldata.','.$envalue;
      }
      $counter++;
      }   
}
  $cyearlygraph=filter_yearly_enrol_completiongraph($enrolllabel,$enrolldata,$complabel,$compdata);
  //here getting data from monthwise graphs to course.
    global $DB;
    $completiondata=$DB->get_records_sql('SELECT id,timecompleted FROM {course_completions}   WHERE course = "'.$courseid.'" AND timecompleted is not null');
    $completeddate=[];
    foreach ($completiondata as $ckey => $cvalue) {
        $completeddate[]=$cvalue->timecompleted;    
    }
    $completearray=[];
    foreach ($completeddate as $singledate) {
        $completearray[]=date("m",$singledate);
    }
    $months = array_count_values($completearray);
    $montharray=[];
    $marray = array('Jan','Feb','Mar','Apl','May','Jun','Jul','Aug','Sep','Act','Nov','Dec');
    for ($i=01; $i < 13; $i++) { 
    if ($i <= 9) {
      $i = '0'.$i;
    }
    if(array_key_exists($i, $months)){
      $montharray[$marray[$i-1]] = $months[$i];
    }else{
      $montharray[$marray[$i-1]] = 0;
    }
    }
    //here getting enroll course data.
    $enrolldate=get_enroled_userdata($courseid);
    $emptyarray=[];
    if (!empty($enrolldate)) {
      foreach($enrolldate as $singledate){
      $enrol=$singledate[1];
      $emptyarray[]=date('m', $singledate[1]);
    }
    }
    $enrolcourse = array_count_values($emptyarray);
    $enrollarray=[];
    $enrolldate = array('Jan','Feb','Mar','Apl','May','Jun','Jul','Aug','Sep','Act','Nov','Dec');
    for($i=01; $i< 13; $i++){
      if($i <= 9){
        $i = '0'.$i;
      }
    if(array_key_exists($i, $enrolcourse)){
      $enrollarray[$enrolldate[$i-1]] = $enrolcourse[$i];
    }else{
      $enrollarray[$enrolldate[$i-1]] = 0;
    }
    }
    $count =1;
    $menroll = '';
    foreach ($enrollarray as $mkey => $mvalue) {
        if($count == 1){
          $menroll = $mvalue;
        }else{
          $menroll = $menroll.','.$mvalue;
        }
        $count++;
    }
    $temp = 1;
    $mcomplete ='';
    foreach ($montharray as $ckey => $cvalue) {
      if($temp == 1){
        $mcomplete = $cvalue;
      }else{
        $mcomplete = $mcomplete.','.$cvalue;

      }
      $temp++;
    }

    $cmontlygraph=filter_monthly_enrol_completiongraph($menroll,$mcomplete);

    $fhtml="";
    $fhtml.=$html;
    $fhtml.=$cyearlygraph;
    $fhtml.=$cmontlygraph;
    echo $fhtml;
}

//here creating filter parts.
function filter_ajax_page(){
  global $DB,$OUTPUT;
    //creating city dropdown html.
  $location=$DB->get_records_sql('SELECT  DISTINCT city FROM {user} WHERE city!=" "');
  $city='';
  $city .= html_writer::start_tag('option');
  $city .= get_string('selectlocation','local_deptrpts');
  $city .= html_writer::end_tag('option');
  if(!empty($location)){
    foreach ($location as $ct) {
      $city .= html_writer::start_tag('option',array('value'=>$ct->city));
      $city .= $ct->city;
      $city .= html_writer::end_tag('option');
    }
  }
    //city dropdown ends.
    //creating category dropdown.
  $categories=$DB->get_records_sql('SELECT id, name FROM {course_categories} WHERE visible = 1');
  $category='';
  $category .= html_writer::start_tag('select', array('id'=>'site-category','class'=>'custom-select'));
  $category .= html_writer::start_tag('option');
  $category .= get_string('selectcategory','local_deptrpts');
  $category .= html_writer::end_tag('option');
  if(!empty($categories)){
    foreach ($categories as $cat) {
      $category .= html_writer::start_tag('option',array('value'=>$cat->id));
      $category .= $cat->name;
      $category .= html_writer::end_tag('option');
    }
  }
  $category .= html_writer::end_tag('select');
  //category dropdown end.

  //creating usersearch dropdown.
  $uhtml='';
  $uhtml .= html_writer::start_tag('select', array('id'=>'usersearch'));
  $uhtml .= html_writer::end_tag('select')

  //creating coursesearch dropdown.
  $chtml='';
  $chtml .= html_writer::start_tag('select', array('id'=>'coursesearch'));
  $chtml .= html_writer::end_tag('select');

  $filterarray =[

    'filters' =>array(
      array("hasfilter"=>1,
        'filtertitle'=>get_string('sitereport','local_deptrpts'),
        'headingid'=>"headingOne",
        'href'=>"collapseOne",
        'icon'=>'<i class="fa fa-bars" aria-hidden="true"></i>',
        'input1title'=>get_string('selectlocation','local_deptrpts'),
        'input1html'=>$city,
        'input1id'=>'site_location',
        'input2title'=>get_string('selectcategory','local_deptrpts'),
        'input2html'=>$category
      ),

      array("hasfilter"=>1,
        'filtertitle'=>get_string('userreport','local_deptrpts'),
        'headingid'=>"headingTwo",
        'href'=>"collapseTwo",
        'icon'=>'<i class="fa fa-user-circle-o" aria-hidden="true"></i>',
        'input1title'=>get_string('selectlocation','local_deptrpts'),
        'input1html'=>$city,
        'input1id'=>'user_location',
        'input2title'=>get_string('selectuser','local_deptrpts'),
        'input2html'=>$uhtml
      ),
      array("hasfilter"=>1,
        'filtertitle'=>get_string('coursereport','local_deptrpts'),
        'headingid'=>"headingThree",
        'href'=>"collapseThree",
        'icon'=>'<i class="fa fa-book" aria-hidden="true"></i>',
        'input1title'=>get_string('selectlocation','local_deptrpts'),
        'input1html'=>$city,
        'input1id'=>'course_location',
        'input2title'=>get_string('selectcourse','local_deptrpts'),
        'input2html'=>$chtml
      ),
    )];
    return $OUTPUT->render_from_template('local_deptrpts/filter_page', $filterarray);
  }

//creating the rightside part..
function filter_top_cards($icon,$cardtitle,$data,$color){
  global $DB, $OUTPUT;
  $cardarray=[
    'cards' =>array("hascard"=>1,
      'icon' =>$icon,
      'cardtitle' =>strtoupper($cardtitle),
      'data' =>$data,
      'color' =>$color)];

    return $OUTPUT->render_from_template('local_deptrpts/card', $cardarray);
  }

//creating the category wise pie charts..
function filter_categorywise_chart($counter,$categorydata,$categoryname){
  global $DB, $OUTPUT;
  $chartarray=[
    'charts' =>array("counter"=>$counter,
      "chartdata"=>$categorydata,
      "categoryname"=>strtoupper($categoryname)

    )];
    return $OUTPUT->render_from_template('local_deptrpts/category', $chartarray);

  }
//creating the enrol and complete year wise graphs.
function filter_yearly_enrol_completiongraph($enrolllabel,$enrolldata,$completelabel,
  $completedata){
  global $DB, $OUTPUT;
  $grapharray=[
    'graph' =>array("hasgraph" =>1,
      "enrollabel" =>$enrolllabel,
      "enroldata" =>$enrolldata,
      "completionlabel" =>$completelabel,
      "completiondata" =>$completedata
    )];
    return $OUTPUT->render_from_template('local_deptrpts/year_graphs', $grapharray);
  }
//creating the enrol and complete month wise graphs.
function filter_monthly_enrol_completiongraph($enrolldata,$completiondata){
  global $DB, $OUTPUT;

  $monthlyarray=[
    'graph' =>array("hasgraph" =>1,
      "enroldata" =>$enrolldata,
      "completiondata" =>$completiondata
    )];
    return $OUTPUT->render_from_template('local_deptrpts/month_graphs', $monthlyarray);
  }
//creating datatable to site-report.
function site_datatable($sitedatatable){
    global $DB, $OUTPUT;
  return $OUTPUT->render_from_template('local_deptrpts/sitedatatable', $sitedatatable);
  }
//here getting courses from the given categoryid.
function get_course_from_category($startdt,$enddt,$siteloc,$sitecat){
  global $DB,$CFG;
  require_once($CFG->libdir.'/completionlib.php');
  $sql='';
  $sql.="SELECT DISTINCT({course}.id) FROM {course} 
  LEFT JOIN {enrol} ON {course}.id = {enrol}.courseid
  LEFT JOIN {user_enrolments} ON {enrol}.id = {user_enrolments}.enrolid
  LEFT JOIN {user} ON {user_enrolments}.userid = {user}.id
  LEFT JOIN {role_assignments} ON {user}.id = {role_assignments}.userid
  WHERE {course}.visible = 1 AND {course}.id != 1";
  if(!empty($stime) && !empty($stime)){
    $sql.=" AND {course}.timecreated BETWEEN ".$stime." AND ".$etime." ";
  }
  if(!empty($categoryid)){
    $sql.=" AND {course}.category = ".$categoryid."";
  }
  if(!empty($department)){
    $sql.=" AND {user}.department = ".$department."";
  }
  if(!empty($role)){
    $sql.=" AND {role_assignments}.roleid = ".$role."";
  }
  if(!empty($city)){
    $sql.=" AND {user}.city = ".$city."";
  }
  $courses = $DB->get_records_sql($sql);

//here i am getting all courses from this category.
//here intializing counts of completion,enrolled,badges,certificates.  
  $totalcomplition=0;
  $totalenrolled=0;
  $totalbadges=0;
  $totalcertificates=0;
  if(!empty($courses)){

    foreach ($courses as $course) {

//here i am getting total user enroll into this course.
      $totalenrolled = $totalenrolled + count(all_enrolled_usersdata($course->id));
//here i am geetting total badge count for this category.
//$allusers=all_enrolled_usersdata($course->id);
      $enrolled = $DB->get_records_sql("

        SELECT c.id, u.id

        FROM {course} c
        JOIN {context} ct ON c.id = ct.instanceid
        JOIN {role_assignments} ra ON ra.contextid = ct.id
        JOIN {user} u ON u.id = ra.userid
        JOIN {role} r ON r.id = ra.roleid

        where c.id = ".$course->id."");

//here i am getting single user using foreach loop.
      foreach ($enrolled as $user) {

//here getting complition status.
        $cinfo = new completion_info($course);

        $iscomplete = $cinfo->is_course_complete($user->id);
//here check course is complete or not. 
        if(!empty($iscomplete)){
          $totalcomplition = $totalcomplition + 1;
        }
  //here i am getting all badge count related to this category.
        $sql='SELECT * FROM {badge_issued} JOIN {badge} ON {badge_issued}.badgeid={badge}.id WHERE {badge_issued}.userid='.$user->id.' AND {badge}.courseid='.$course->id.'';
        $badgecount = count($DB->get_records_sql($sql));
        $totalbadges = $totalbadges+$badgecount;

// here i am getting all certificate count related to this category.
        $data='SELECT * FROM {simplecertificate} JOIN {simplecertificate_issues} ON {simplecertificate}.id={simplecertificate_issues}.certificateid WHERE {simplecertificate_issues}.userid='.$user->id.' AND {simplecertificate}.course='.$course->id.'';
        $certificatecount = count($DB->get_records_sql($data));
        $totalcertificates = $totalcertificates+$certificatecount;
      }
    }
  }
  $returnarray = array('enrolcount'=>$totalenrolled,'completioncount'=>$totalcomplition,'badgecount'=>$totalbadges,'certificatecount'=>$totalcertificates);
  return $returnarray ;
}

//This function will return number of users enrolled into this course.- paarameters with returning function.
function all_enrolled_usersdata($courseid)
{
  global $DB,$CFG;
  $allenrolleduser=enrol_get_course_users($courseid);
  $listofusers =[];
  foreach ($allenrolleduser as $user) {
    $listofusers[] = $user->id;
  }
  sort($listofusers);
  return  $listofusers;
}

//defining function,here passing the userid.
function user_course_count($userid,$startdate,$enddate,$city)
{
  global $DB;
  $course=enrol_get_users_courses($userid);
  return count($course);
}

//here return the no of the counting badge.
function user_badge_count($userid,$startdate,$enddate,$city)
{
  global $DB;
  $concat="";
  if (!empty($startdate) && !empty($enddate)) {
    $concat.=" AND BETWEEN dateissued '$startdate' AND '$enddate'";

  }
  $badge=$DB->get_records_sql("SELECT * FROM {badge_issued} WHERE userid=".$userid.$concat."");
  return count($badge);
}
// this function is user certificate.
function user_course_certificate($userid,$startdate,$enddate,$city)
{
  global $DB;
  $certificate=$DB->get_records_sql("SELECT * FROM {simplecertificate_issues} WHERE userid=".$userid."");
  return count($certificate);
}

//This function is used to completion of course
function user_course_completion($userid,$startdate,$enddate,$city)
{
  global $DB;
  $ret=0;
  $completion=$DB->get_records_sql("SELECT * FROM {course_completions} WHERE userid=".$userid." AND timecompleted IS NOT NULL");
  $ret = count($completion);
  return  $ret;
}


//this function using for count badges..
function get_badges_earned($courseid){
  global $DB;
  $badgeid=$DB->get_records_sql('SELECT id FROM {badge} WHERE courseid = "'.$courseid.'" ');
  $totalbadge=0;
  if(!empty($badgeid)){
    foreach ($badgeid as $id => $badge) {
      $badgeissued=$DB->get_records_sql('SELECT * FROM {badge_issued} WHERE badgeid ="'.$id.'"');
      $totalbadge = $totalbadge+count($badgeissued);

    } 
  }
  return $totalbadge;
}
//this function using for count-certificates.
function get_course_cerficatescount($courseid){
  global $DB;
  $certificateid=$DB->get_records_sql('SELECT id FROM {simplecertificate} WHERE course = "'.$courseid.'"');
  $totalcertificate=0;
  if(!empty($certificateid)){
    foreach ($certificateid as $cid => $cvalue) {
      $cerficateissued=$DB->get_records_sql('SELECT * FROM {simplecertificate_issues} WHERE certificateid = "'.$cid.'"');
      $totalcertificate = $totalcertificate+count($cerficateissued);
    }
  }
  return $totalcertificate;
}

//course complition-count.
function course_complition_count($courseid){
  global $DB;
  $course=$DB->get_record('course', array('id'=>$courseid));
  $cinfo = new completion_info($course);
  $enrolled = $DB->get_records_sql("

    SELECT c.id, u.id

    FROM {course} c
    JOIN {context} ct ON c.id = ct.instanceid
    JOIN {role_assignments} ra ON ra.contextid = ct.id
    JOIN {user} u ON u.id = ra.userid
    JOIN {role} r ON r.id = ra.roleid

    where c.id = ".$courseid."");
  $totalcomplition=0;
  foreach($enrolled as $singleuser){
    $iscomplete = $cinfo->is_course_complete($singleuser->id);
//here check course is complete or not. 
    if(!empty($iscomplete)){
      $totalcomplition = $totalcomplition + 1;
    }
  }
  return $totalcomplition;
}

//This function will return no of completed,inprogress, not started users of all courses present in particular category.
function course_completion_stats($start=null,$end=null,$categoryid=null,$city=null)
{
  global $DB;
  $courses=$DB->get_records('course', array('category'=>$categoryid));
  $categoryname=$DB->get_field('course_categories', 'name', array('id'=>$categoryid));
  $completed=0;
  $inprogress=0;
  $notstarted=0;
  foreach ($courses as $course) {
    $courseid=$course->id;
//manjunath: getting total count of users.
    $sql='';
    if(!empty($start)&& !empty($end)){
      $sql.=" AND c.timeenrolled between $start AND $end";
    }
    if(!empty($city)){
      $sql .=" AND u.city LIKE '%$city%'";
    }

    $totalquery = "SELECT *
    FROM {course_completions} c
    INNER JOIN {user} u ON u.id = c.userid
    INNER JOIN {role_assignments} ra ON u.id = ra.userid
    WHERE (c.course = '$courseid')
    $sql";
    $records = count($DB->get_records_sql($totalquery));
    $totalcount = $records;
//manjunath: coures completed users count.
    $completedquery="SELECT DISTINCT(mcc.userid) FROM {course_completions} mcc 
    JOIN {course} mc ON mc.id = mcc.course
    JOIN {user} mu ON mcc.userid=mu.id
    RIGHT JOIN {role_assignments} mrc ON mrc.userid = mcc.userid
    WHERE mcc.course= ".$courseid." AND mcc.timecompleted is not null
    $sql
    ";

    $completedrecords = count($DB->get_records_sql($completedquery));
    $completedcount = $completedrecords;

//manjunath: course in progress users count.
    $progressquery="SELECT DISTINCT(mcc.userid) FROM {course_completions} mcc 
    JOIN {course} mc ON mc.id = mcc.course
    JOIN {user} mu ON mcc.userid=mu.id
    RIGHT JOIN {role_assignments} mrc ON mrc.userid = mcc.userid
    WHERE mcc.course= ".$courseid." AND (mcc.timestarted != 0)
    AND (mcc.timecompleted is null)
    $sql
    ";

    $progessrecords = count($DB->get_records_sql($progressquery));
    $progresscount = $progessrecords;

//manjunath: course not started users count
    $notstartedquery = "SELECT DISTINCT(mcc.userid) FROM {course_completions} mcc 
    JOIN {course} mc ON mc.id = mcc.course
    JOIN {user} mu ON mcc.userid=mu.id
    RIGHT JOIN {role_assignments} mrc ON mrc.userid = mcc.userid
    WHERE mcc.course= ".$courseid." AND (mcc.timestarted = 0)
    AND (mcc.timecompleted is null)
    $sql
    ";

    $notstartedrecords = count($DB->get_records_sql($notstartedquery));
    $notstartedcount = $notstartedrecords;

    $completed = $completed + $completedcount;
    $inprogress= $inprogress + $progresscount;
    $notstarted=$notstarted + $notstartedcount;

  }
  return array($completed,$inprogress,$notstarted,$categoryname);

}

//this function used to getting yearwise category information.
function get_yearwisecategory_info($categoryid){
  global $DB;
//here  getting all course from category.
  $courses=$DB->get_records('course', array('category'=>$categoryid));
  $enrolled=[];
  $complition=[];
  foreach ($courses as $course) {
    $data=$course->id;
    $result=get_yearwisegraph($data, $start_date=null, $end_date=null, $city=null, $institution=null, $department=null);
    foreach ($result as $rkey => $rvalue) {
      if($rkey=='enrolldata'){
        foreach ($rvalue as $rrkey => $rrvalue) {
          if(array_key_exists($rrkey, $enrolled)){
            $enrolled[$rrkey] += $rrvalue;
          }else{
            $enrolled[$rrkey]=$rrvalue;

          }

        }

      }
      if($rkey=='completiondata'){
        foreach ($rvalue as $ckey => $cvalue) {
          if(array_key_exists($ckey, $complition)){
            $complition[$ckey] += $cvalue;
          }else{

            $complition[$ckey]=$cvalue;
          }

        }
      }
    }
  }
  return array('enrol'=>$enrolled,'complete'=>$complition);
}

//get_yearwisegraph(7, $start_date=0, $end_date=0, $city=0, $institution=0, $department=0);

function get_yearwisegraph($course_id=null, $start_date=null, $end_date=null, $city=null, $institution=null, $department=null){
  global $DB;
  $convdate=[];
  $allenrolldates = all_enrolled_usersdata_date($course_id);

  foreach ($allenrolldates as $condate) { 
    $convdate[]=date('Y', $condate);
  }
  $userenrolconvyear = array_count_values($convdate);
  $cmpletiondate=[];
  $completiondatequery = "SELECT id,timecompleted FROM {course_completions} WHERE course = $course_id AND timecompleted IS NOT NULL";
  $coursecompledate = $DB->get_records_sql($completiondatequery);
  foreach ($coursecompledate as $cdate) {
    $cmpletiondate[]=date('Y',$cdate->timecompleted);
  }
  $usercompleconvyear = array_count_values($cmpletiondate);
  $returnarray = array('enrolldata'=>$userenrolconvyear,
    'completiondata'=>$usercompleconvyear);
  return $returnarray;

}
//This function will return number of users enrolled into this course.- paarameters with returning function.
function all_enrolled_usersdata_date($courseid)
{
  global $DB,$CFG;
  $allenrolleduser=enrol_get_course_users($courseid);
  $listofusers =[];
  foreach ($allenrolleduser as $user) {
    $listofusers[] = $user->uetimecreated;
  }
  sort($listofusers);
  return  $listofusers;
}

//this function is used to year wise course completion data.
function get_user_yearly_completion($userid){
  global $DB;
  $year=$DB->get_records_sql("SELECT id, timecompleted FROM {course_completions} WHERE userid=".$userid." AND timecompleted IS NOT NULL");
  $emptyarray=[];
  foreach ($year as  $yearcompleted) {
    $singleyear=$yearcompleted->timecompleted;
    $emptyarray[] = date('Y',$singleyear);
  }
  $years = array_count_values($emptyarray);
  return $years;
}

//this function is used to month wise course completion data.
function get_enrolled_course_yearly($userid){
  global $DB;
  $enrolled=$DB->get_records_sql("SELECT timecreated FROM {user_enrolments} WHERE userid=".$userid."");
  $enrolledarray=[];
  foreach ($enrolled as $courseenrolled) {
    $single=$courseenrolled->timecreated;
    $enrolledarray[]= date('Y',$single);
  }
  $years = array_count_values($enrolledarray);
  return $years;
}

//This function is used to detail of enrolled course
function get_course_enrolled_info($userid)
{
  global $DB;
  // here getting enrolement time.  
  $information=$DB->get_records_sql("SELECT timecreated FROM {user_enrolments} WHERE userid=".$userid."");
  $abc=[];
  //here getting single value.
  foreach($information as $info){
    $singleinfo=$info->timecreated ;
    $abc[]=date("m",$singleinfo);
  }
  // this function is used to count the array value.
  $months = array_count_values($abc);
  $montharray=[];
  $marray = array('Jan','Feb','Mar','Apl','May','Jun','Jul','Aug','Sep','Act','Nov','Dec');
  for ($i=01; $i < 13; $i++) { 
    if($i <= 9){
      $i = '0'.$i;
    }
      // this function is used to check whether a specified key is present in an array or not. 
    if (array_key_exists($i,$months)){
      $montharray[$marray[$i-1]] = $months[$i];
    }else{
      $montharray[$marray[$i-1]] = 0;
    }
  }
  return $montharray;
}
//this function is used to completion of course.
function get_course_completion($userid)
{
  global $DB;
  // here getting enrolement time.  
  $information=$DB->get_records_sql("SELECT id,timecompleted FROM {course_completions} WHERE userid=".$userid." AND timecompleted IS NOT NULL");
  $abc=[];
  //here getting single value.
  foreach($information as $info){
    $singleinfo=$info->timecompleted ;
    $abc[]=date("m",$singleinfo);
  }
  // this function is used to count the array value.
  $months = array_count_values($abc);
  $montharray=[];
  $marray = array('Jan','Feb','Mar','Apl','May','Jun','Jul','Aug','Sep','Act','Nov','Dec');
  for ($i=01; $i < 13; $i++) { 
    if($i <= 9){
      $i = '0'.$i;
    }
      // this function is used to check whether a specified key is present in an array or not. 
    if (array_key_exists($i,$months)){
      $montharray[$marray[$i-1]] = $months[$i];
    }else{
      $montharray[$marray[$i-1]] = 0;
    }
  }
  return $montharray;
}

//this function is used to getting enrolment data from course-yearlygraphs.
function yearwise_course_enrollment_data($courseid){
     global $DB;
     $completiondata = $DB->get_records_sql('SELECT id,timecompleted FROM {course_completions}  WHERE course = "'.$courseid.'" AND timecompleted is not null');
    $completiondate=[];
     foreach ($completiondata as $comkey => $comvalue) {
      $completiondate[]=date('Y', $comvalue->timecompleted);
     }
     $completionyear = array_count_values($completiondate);
     $complabel="";
    $compdata="";
    $counter=1;
    if(!empty($completionyear)){
    foreach ($completionyear as $cmkey => $cmvalue) {
    if($counter==1){
    $complabel="'".$cmkey."'";
    $compdata=$cmvalue;
    }else{
    $complabel=$enrolllabel.','."'".$cmkey."'";
    $compdata=$erolldata.','.$cmvalue;
    }
    $counter++;
    }
    }
     $enrolldates=get_enroled_userdata($courseid);
      $convdate=[];
      if(!empty($enrolldates)){
        foreach ($enrolldates as $condate) { 
        $convdate[]=date('Y', $condate[1]);

      }
      }

      $userenrolconvyear = array_count_values($convdate);
      $enrolllabel ="";
      $enrolldata ="";
      $counter=1;
      if(!empty($userenrolconvyear)){
      foreach ($userenrolconvyear as $enkey => $envalue) {
      if($counter==1){
      $enrolllabel="'".$enkey."'";
      $enrolldata=$envalue;
      }else{
      $enrolllabel=$enrolllabel.','."'".$enkey."'";
      $erolldata=$erolldata.','.$envalue;
      }
      $counter++;
      }
      }
}
//this function is used to get enrolled all userdata.
function get_enroled_userdata($courseid)
{
  global $DB,$CFG;
  $allenrolleduser= enrol_get_course_users($courseid);

  foreach ($allenrolleduser as $user) {
    $listofusers[] = array($user->id,$user->uetimecreated);
  }
  if(!empty($listofusers)){
    sort($listofusers);
    return  $listofusers;
  }

//this function is used to get enrol and complete course.
function course_enrollandcompletion_monthly_data($courseid){
    global $DB;
    $completiondata=$DB->get_records_sql('SELECT id,timecompleted FROM {course_completions}   WHERE course = "'.$courseid.'" AND timecompleted is not null');
    $completeddate=[];
    foreach ($completiondata as $ckey => $cvalue) {
        $completeddate[]=$cvalue->timecompleted;
        
    }

    $completearray=[];
    foreach ($completeddate as $singledate) {
        $completearray[]=date("m",$singledate);

    }
    $months = array_count_values($completearray);

    $montharray=[];
    $marray = array('Jan','Feb','Mar','Apl','May','Jun','Jul','Aug','Sep','Act','Nov','Dec');
    for ($i=01; $i < 13; $i++) { 
    if ($i <= 9) {
      $i = '0'.$i;
    }
    if(array_key_exists($i, $months)){
      $montharray[$marray[$i-1]] = $months[$i];
    }else{
      $montharray[$marray[$i-1]] = 0;
    }
    }
    //here getting enroll course data.
    $enrolldate=get_enroled_userdata($courseid);
    $emptyarray=[];
    if (!empty($enrolldate)) {
      foreach($enrolldate as $singledate){
      $enrol=$singledate[1];
      $emptyarray[]=date('m', $singledate[1]);
    }
    }
    $enrolcourse = array_count_values($emptyarray);
    $enrollarray=[];
    $enrolldate = array('Jan','Feb','Mar','Apl','May','Jun','Jul','Aug','Sep','Act','Nov','Dec');
    for($i=01; $i< 13; $i++){
      if($i <= 9){
        $i = '0'.$i;
      }
    if(array_key_exists($i, $enrolcourse)){
      $enrollarray[$enrolldate[$i-1]] = $enrolcourse[$i];
    }else{
      $enrollarray[$enrolldate[$i-1]] = 0;
    }
    }
    //--------
    $count =1;
    $menroll = '';
    foreach ($enrollarray as $mkey => $mvalue) {
        if($count == 1){
          $menroll = $mvalue;
        }else{
          $menroll = $menroll.','.$mvalue;
        }
        $count++;
    }
    $temp = 1;
    $mcomplete ='';
    foreach ($montharray as $ckey => $cvalue) {
      if($temp == 1){
        $mcomplete = $cvalue;
      }else{
        $mcomplete = $mcomplete.','.$cvalue;

      }
      $temp++;
    }
}
}

//this function is used to course from category year wise graphs.
function full_site_yearly_graphs(){
global $DB;
$categories=$DB->get_records('course_categories');
$yenrol=[];
$ycomplete=[];
foreach ($categories as $category) {
$temp = get_yearwisecategory_info($category->id);

if(!empty($temp['enrol']) || !empty($temp['complete'])){
foreach ($temp as $ykey => $yvalue) {
if($ykey=='enrol'){
foreach ($yvalue as $yykey => $yyvalue) {
if(array_key_exists($yykey, $yenrol)){
$yenrol[$yykey] += $yyvalue;
}else{
$yenrol[$yykey]=$yyvalue;
}
}
}
if($ykey=='complete'){
foreach ($yvalue as $cckey => $ccvalue) {
if(array_key_exists($cckey, $ycomplete)){
$ycomplete[$cckey] += $ccvalue;
}else{

$ycomplete[$cckey]=$ccvalue;
          }
        }
      }
    }
  }
}
$enrolllabel="";
$enrolldata="";
$counter=1;
if(!empty($yenrol)){
foreach ($yenrol as $yekey => $yevalue) {
if($counter==1){
$enrolllabel="'".$yekey."'";
$enrolldata=$yevalue;
}else{
$enrolllabel=$enrolllabel.','."'".$yekey."'";
$enrolldata=$enrolldata.','.$yevalue;
}
$counter++;
}
}
$completelabel="";
$completedata="";
$counter=1;
if(!empty($ycomplete)){
foreach ($ycomplete as $yckey => $ycvalue) {
if($counter==1){
$completelabel="'".$yckey."'";
$completedata=$ycvalue;
}else{

$completelabel=$completelabel.','."'".$yckey."'";
$completedata=$completedata;
}
$counter++;
}
}
return array($enrolllabel,$enrolldata,$completelabel,$completedata);
}
