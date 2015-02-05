<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2013 unistra  {@link http://unistra.fr}
 * @author     Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
require_once('../../config.php');
require_once($CFG->dirroot.'/lib/filelib.php');
require_once('locallib.php');
require_login();
if (isguestuser()) {
    die();
}
$PAGE->set_url('/blocks/my_external_backup_courses/retrievefile.php');
$PAGE->set_course($SITE);
$PAGE->set_title(get_string('pluginname','my_external_backup_courses'));
$PAGE->set_heading(get_string('pluginname','my_external_backup_courses'));
$PAGE->set_pagetype('site-index');
$PAGE->set_pagelayout('frontpage');
require_sesskey();
$token  = required_param('token', PARAM_TEXT);
$domainname = required_param('domainname', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_TEXT);
$filetoken = required_param('filetoken', PARAM_TEXT);

try {
	//put sess key in session
	if(!is_downloading($domainname, $filetoken)){
		put_file_token($domainname, $filetoken);
		download_external_backup_courses($domainname, $token,$courseid,$filetoken);
	}else{
		//already exists download in progress
		echo $OUTPUT->header();
		echo $OUTPUT->box_start('generalbox');
		echo get_string('downloadinprogress','block_my_external_backup_courses');
		echo $OUTPUT->box_end();
		echo $OUTPUT->footer();
	}
} catch(Exception $e) { 
	echo $OUTPUT->header();
	echo $OUTPUT->box_start('generalbox');
	echo $e->getMessage();
	echo $OUTPUT->box_end();
	echo $OUTPUT->footer();
}