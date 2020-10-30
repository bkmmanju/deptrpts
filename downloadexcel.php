<?php
require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/excellib.class.php");
global $DB,$CFG;

$context = context_system::instance();
$PAGE->set_context($context);
$startdate = optional_param('strtdate','',PARAM_RAW);
$enddate = optional_param('endate','',PARAM_RAW);
$siteloc = optional_param('slocation','',PARAM_RAW);
$sitecat = optional_param('scategory','',PARAM_RAW);
$userloc = optional_param('ulocation','',PARAM_RAW);
$userid = optional_param('userselect','',PARAM_RAW);
$status = optional_param('status','',PARAM_RAW);
$rownum = 1;
if($status === "site"){

 $result=site_export($startdate,$enddate,$siteloc,$sitecat);

}else if($status === "user") {

$result=user_exceldownload($startdate,$enddate,$userloc,$userid);

}else if($status == "course") {

}

	$filename = "textexcel.xls";
// Creating a workbook.
	$workbook = new \MoodleExcelWorkbook("-");
// Sending HTTP headers.
	$workbook->send($filename);
// Creating the first worksheet.
	$sheettitle = get_string('report', 'scorm');
	$myxls = $workbook->add_worksheet($sheettitle);
    // Format types.
	$format = $workbook->add_format();
	$format->set_bold(0);
	$formatbc = $workbook->add_format();
	$formatbc->set_bold(1);
	$formatbc->set_align('center');
	$formatb = $workbook->add_format();
	$formatb->set_bold(1);
	$formaty = $workbook->add_format();
	$formaty->set_bg_color('yellow');
	$formatc = $workbook->add_format();
	$formatc->set_align('center');
	$formatr = $workbook->add_format();
	$formatr->set_bold(1);
	$formatr->set_color('red');
	$formatr->set_align('center');
	$formatg = $workbook->add_format();
	$formatg->set_bold(1);
	$formatg->set_color('green');
	$formatg->set_align('center');
	$colnum = 0;
	$headers=array(get_string('serial','local_deptrpts'),
					get_string('fullname','local_deptrpts'),
					get_string('email','local_deptrpts'),
					get_string('coursename','local_deptrpts'),
					get_string('enrolmentdate','local_deptrpts'),
					get_string('completiondate','local_deptrpts'),
					get_string('completionstatus','local_deptrpts'),
					get_string('coursegrade','local_deptrpts'));
	foreach ($headers as $item) {
		$myxls->write(0, $colnum, $item, $formatbc);
		$colnum++;
	}
	$rownum = 1;

	foreach ($result as $key => $tablearray) {
		$i = 0;
		if($key > 0){
			foreach ($tablearray as $tdata) {
				$myxls->write_string($rownum, $i++, $tdata);
			}
			$rownum++;
		}
	}
	$workbook->close();
	exit;

