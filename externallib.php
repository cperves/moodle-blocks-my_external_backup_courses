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
require_once("$CFG->libdir/externallib.php");

class block_my_external_backup_courses_external extends external_api {
	public static function get_courses_zip($username, $courseid) {
		global $DB, $CFG;
		require_once($CFG->dirroot.'/blocks/my_external_backup_courses/locallib.php');
		require_once('backup_external_courses_helper.class.php');
		$params = self::validate_parameters(self::get_courses_zip_parameters(),
			array('username' => $username, 'courseid' => $courseid));

		require_capability('block/my_external_backup_courses:can_retrieve_courses', context_system::instance());
		$usercourses = get_all_users_courses($params['username']);

		$usercourse_ids = array();
		foreach($usercourses as $usercourse) {
			$usercourse_ids[] = $usercourse->id;
		}
		//User is not the owner of the course
		if (!in_array($params['courseid'], $usercourse_ids)) {
			throw new Exception(get_string('notcourseowner', 'block_my_external_backup_courses'));
		}

		$user_record = $DB->get_record('user', array('username' => $params['username']));
		if (!$user_record) { 
			throw new invalid_username_exception('user with username not found');
		}
		$res = backup_external_courses_helper::run_external_backup($params['courseid'], $user_record->id);
		if(empty($res) || $res === false){
			throw new Exception('Backup course can\'t be created');
		}
		
		$source = 'block_my_external_backup_courses';	    

        $DB->execute('UPDATE {files} set source=:source where id=:id', 
        	array('source' => $source, 'id' => $res['file_record_id']));

		return array('filename' => $res['filename'], 'filerecordid' => $res['file_record_id']);
	}

	public static function get_courses_zip_parameters() {
		return new external_function_parameters(
			array(
				'username' 	=> new external_value(PARAM_TEXT, ''),
				'courseid' 	=> new external_value(PARAM_INT, ''),
			)
		);
	}

	public static function get_courses_zip_returns() {
		return new external_single_structure(
			array(
				'filename'		=> new external_value(PARAM_RAW, 'file_name'),
				'filerecordid'	=> new external_value(PARAM_INT, 'file_record_id'),
			)
		);
	}
	
	public static function get_courses($username) {
		global $CFG;
		require_once($CFG->dirroot.'/blocks/my_external_backup_courses/locallib.php');
		
		$params = self::validate_parameters(self::get_courses_parameters(),
			array('username' => $username));

		require_capability('block/my_external_backup_courses:can_see_backup_courses', context_system::instance());
		$usercourses = get_all_users_courses($params['username']);
		
		//create return value
        $coursesinfo = array();
        foreach($usercourses as $usercourse) {
        	$courseinfo = array();
        	$courseinfo['id'] = $usercourse->id;
        	$courseinfo['category'] = $usercourse->category;
        	$courseinfo['sortorder'] = $usercourse->sortorder;
            $courseinfo['shortname'] = $usercourse->shortname;
            $courseinfo['fullname'] = $usercourse->fullname;
            $courseinfo['idnumber'] = $usercourse->idnumber;
            $courseinfo['startdate'] = $usercourse->startdate;
            $courseinfo['visible'] = $usercourse->visible;
            $courseinfo['groupmode'] = $usercourse->groupmode;
            $courseinfo['groupmodeforce'] = $usercourse->groupmodeforce;
            $coursesinfo[] = $courseinfo;
        }
        
        return $coursesinfo;
	}
	
	public static function get_courses_parameters() {
		return new external_function_parameters(
			array(
				'username' => new external_value(PARAM_TEXT, ''),
			)
		);
	}

	public static function get_courses_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'id' => new external_value(PARAM_INT, 'course id'),
					'shortname' => new external_value(PARAM_TEXT, 'course short name'),
					'category' => new external_value(PARAM_INT, 'category id'),
					'sortorder' => new external_value(PARAM_INT, 'sort order into the category', VALUE_OPTIONAL),
					'fullname' => new external_value(PARAM_TEXT, 'full name'),
					'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
					'startdate' => new external_value(PARAM_INT, 'timestamp when the course start'),
					'visible' => new external_value(PARAM_INT, '1: available to student, 0:not available', VALUE_OPTIONAL),
 					'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible', VALUE_OPTIONAL),
					'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no',  VALUE_OPTIONAL)
				), 'course'
        	)
        );
	}
}