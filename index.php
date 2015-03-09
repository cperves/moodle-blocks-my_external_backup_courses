<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2013 unistra  {@link http://unistra.fr}
 * @author     Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @author Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/blocks/my_external_backup_courses/locallib.php');
require_once($CFG->dirroot.'/local/toolslib.php');

require_login();
$url_token_checker =$CFG->wwwroot.'/blocks/my_external_backup_courses/ajax/token_checker.php';
$filetoken = block_my_external_backup_courses_get_file_token_for_page();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/my_external_backup_courses/index.php');
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('externalmoodlecourselist', 'block_my_external_backup_courses'));
$PAGE->set_heading(get_string('externalmoodlecourselist', 'block_my_external_backup_courses'));
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('externalmoodlecourselist', 'block_my_external_backup_courses'));
//adding js scripts for file token
get_jquery();
$PAGE->requires->js('/blocks/my_external_backup_courses/script.js');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('externalmoodlecourselist','block_my_external_backup_courses'));
echo $OUTPUT->box_start('my_external_backup_course_helpandtool');
echo $OUTPUT->box_start('my_external_backup_course_help');
echo html_writer::tag('span', get_string('externalmoodlehelpsection','block_my_external_backup_courses'));
echo $OUTPUT->box_end();
$restore_backup_into_newcourse = $DB->get_record('block', array('name'=>'restore_backup_into_newcourse'));
if($restore_backup_into_newcourse && $restore_backup_into_newcourse->visible == 1){
	echo $OUTPUT->box_start('restore_backup_into_newcourse');
	echo $OUTPUT->single_button(new moodle_url('/blocks/restore_backup_into_newcourse/restorefile_newcourse.php'), get_string('restore','block_restore_backup_into_newcourse'));
	echo $OUTPUT->box_end();
}

echo $OUTPUT->box_end();
$external_moodles_cfg = get_config('my_external_backup_courses', 'external_moodles');// dmainname1,token1;domainname2;token2;...
$ws_params = array('username' => $USER->username);

if ($external_moodles_cfg && !empty($external_moodles_cfg)) {
	//extract key/value
	$external_moodles = explode(';', $external_moodles_cfg);
	$nbr_opened_external_moodles=0;
	foreach($external_moodles as $key_value) {
		if(!empty($key_value)){
			$key_value = explode(',',$key_value);
			$domainname = $key_value[0]; 
			$token = $key_value[1]; 
			$serveroptions = array();
			$serveroptions['token'] = $token;
			$serveroptions['domainname'] = $domainname;
			$serveroptions['filetoken'] = $filetoken;
			$validusername=true;
			try{
				$sitename = block_my_external_backup_courses_external_backup_course_sitename($domainname, $token);
				try{
					$courses = block_my_external_backup_courses_rest_call_external_courses_client($domainname, $token, 
					'block_my_external_backup_courses_get_courses', $ws_params);
				}catch(block_my_external_backup_courses_invalid_username_exception $uex){
					$courses==array();
					$validusername=false;
				}
				$nbr_opened_external_moodles+=1;
				if($validusername && count($courses)>0){
					echo $OUTPUT->box_start('my_external_backup_course_notice');
					echo html_writer::tag('span', get_string('downloadinprogressnotice','block_my_external_backup_courses'), array('class'=> 'error'));
					echo $OUTPUT->box_end();
				}
				echo html_writer::start_tag('div', array('class' => 'mform my_external_backup_course_form'));
				echo html_writer::start_tag('fieldset');
				echo html_writer::tag('legend', $sitename);
				if($validusername && count($courses)==0){
					echo $OUTPUT->box_start('external_backup_courses_item');
					echo html_writer::tag('div', get_string('nocourses'), array('class' => 'external_backup_course_name'));
					echo $OUTPUT->box_end();
				}
				if(!$validusername){
					echo $OUTPUT->box_start('external_backup_courses_item');
					echo html_writer::tag('div', get_string('invalidusername','block_my_external_backup_courses'), array('class' => 'external_backup_course_name'));
					echo $OUTPUT->box_end();
				}
				foreach($courses as $course) {
		        	if (isset($course->fullname)) {
		        		$serveroptions['courseid'] = $course->id;
		        		echo $OUTPUT->box_start('external_backup_courses_item');
		        		echo html_writer::tag('div', $course->fullname . ' : ', array('class' => 'external_backup_course_name'));
		        		$js_actions=new component_action('click', 'checkTokenAndSubmit',array($course->id,$domainname,$filetoken,$url_token_checker,get_string('downloadinprogress','block_my_external_backup_courses')));
		        		$button = new single_button(new moodle_url('/blocks/my_external_backup_courses/retrievefile.php', $serveroptions), get_string('Download', 'block_my_external_backup_courses'));
		        		$button->add_action($js_actions);
						echo $OUTPUT->render($button);
						echo $OUTPUT->box_end();
					}
				}				
				echo html_writer::end_tag('fieldset');
				echo html_writer::end_tag('div');
			}catch(Exception $ex){
				continue;
			}
		}
	}
	if($nbr_opened_external_moodles==0){
		echo html_writer::start_tag('div', array('class' => 'notice'));
		echo get_string('noexternalmoodleconnected','block_my_external_backup_courses');
		echo html_writer::end_tag('div');
	}	
}else{
	echo html_writer::start_tag('div', array('class' => 'notice'));
	echo get_string('noexternalmoodleconnected','block_my_external_backup_courses');
	echo html_writer::end_tag('div');
}

echo $OUTPUT->footer();