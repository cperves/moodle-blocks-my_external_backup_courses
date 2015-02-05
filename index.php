<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2013 unistra  {@link http://unistra.fr}
 * @author     Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @author		Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */

require('../../config.php');
require_once($CFG->dirroot.'/blocks/my_external_backup_courses/locallib.php');
require_once($CFG->dirroot.'/local/toolslib.php');

require_login();
$url_token_checker =$CFG->wwwroot.'/blocks/my_external_backup_courses/ajax/token_checker.php';
$filetoken = get_file_token_for_page();

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
$external_moodles = get_config('my_external_backup_courses', 'external_moodles');
$ws_params = array('username' => $USER->username);

if ($external_moodles && !empty($external_moodles)) {
	eval('$external_moodles='.$external_moodles.';');
	if (is_array($external_moodles)) {
		$nbr_opened_external_moodles=0;
		foreach($external_moodles as $domainname => $token) {
			$serveroptions = array();
			$serveroptions['token'] = $token;
			$serveroptions['domainname'] = $domainname;
			$serveroptions['filetoken'] = $filetoken;
			$validusername=true;
			try{
				$sitename = external_backup_course_sitename($domainname, $token);
				try{
					$courses = rest_call_external_courses_client($domainname, $token, 
					'block_my_external_backup_courses_get_courses', $ws_params);
				}catch(invalid_username_exception $uex){
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
}

echo $OUTPUT->footer();
