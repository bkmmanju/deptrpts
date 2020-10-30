<?php
// This file is part of the Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Handles uploading files
 *
 * @package    local_deptrpts
 * @copyright  mallamma<mallamma@elearn10.com>
 * @copyright  Dhruv Infoline Pvt Ltd <lmsofindia.com>
 * @license    http://www.lmsofindia.com 2017 or later
 */
require_once('../../config.php');
require_once('lib.php');
global $DB, $USER, $SESSION,$OUTPUT;  
require_login(true);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($CFG->wwwroot . '/local/deptrpts/test.php');
$title = get_string('title','local_deptrpts');
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->jquery();
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/deptrpts/css/select2.min.css'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/select2.min.js'),true);
echo $OUTPUT->header();
// //allcourse  button report.
allcourses_report1();
function allcourses_report1(){
  global $DB;
  $courses=$DB->get_records('course');
  foreach ($courses as $course) {
        $listusers = enrolled_users_count_course(null,null,null,$course->id);
  }
}


echo $OUTPUT->footer();

