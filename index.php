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
global $DB,$CFG,$USER, $SESSION,$OUTPUT;  
//require_login(true);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($CFG->wwwroot . '/local/deptrpts/index.php');
$title = get_string('title','local_deptrpts');
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/custom.js'), true);
// $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/jquery.dataTables.min.js'),true);
// $PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/deptrpts/css/jquery.dataTables.min.css'),true);

$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/deptrpts/css/select2.min.css'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/select2.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/chart.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/table2excel.js'),true);

// $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/dataTables.buttons.min.js'),true);
// $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/buttons.print.min.js'),true);
// $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/buttons.colVis.min.js'),true);
// $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/buttons.flash.min.js'),true);
// $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/buttons.html5.min.js'),true);
// $PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/deptrpts/css/buttons.dataTables.min.css'),true);
// $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/jszip.min.js'),true);
// $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/pdfmake.min.js'),true);
// $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/local/deptrpts/js/vfs_fonts.js'),true);

echo $OUTPUT->header();
echo filter_ajax_page();

echo $OUTPUT->footer();
