<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2013 unistra  {@link http://unistra.fr}
 * @author Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
defined('MOODLE_INTERNAL') || die;
global $DB;

if ($hassiteconfig) {
	$roles = $DB->get_records('role', array('archetype'=>'editingteacher'));
	$arrayofroles = array();
	$defaultarrayofroles = array();
	
	foreach($roles as $role) {
		$arrayofroles[$role->id] = $role->shortname;
		if ($role->shortname=='editingteacher') {
			$defaultarrayofroles[$role->id] = $role->shortname;
		}
	}
	
	$settings->add(new admin_setting_configtext("my_external_backup_courses/search_roles", 
		get_string("roles_included_in_external_courses_search", "block_my_external_backup_courses"), 
		get_string("roles_included_in_external_courses_search_Desc", "block_my_external_backup_courses"),'\'editingteacher\'' 
		));
	$settings->add(new admin_setting_configtextarea("my_external_backup_courses/external_moodles",
		get_string('external_moodle', 'block_my_external_backup_courses'), 
		get_string('external_moodleDesc', 'block_my_external_backup_courses'), '' 
	));
	$settings->add(new admin_setting_configtext('my_external_backup_courses/filename', 
		get_string('filename', 'block_my_external_backup_courses'),
		get_string('filename_desc', 'block_my_external_backup_courses'),
		 'my_backup_course'
	));
	$settings->add(new admin_setting_configcheckbox('my_external_backup_courses/includesitename',
		get_string('includesitename', 'block_my_external_backup_courses'), 
		get_string('includesitename_desc', 'block_my_external_backup_courses'), 
		1
	));
	$settings->add(new admin_setting_configtext('my_external_backup_courses/sitenamelength', 
		get_string('sitenamelength', 'block_my_external_backup_courses'), 
		get_string('sitenamelength_desc', 'block_my_external_backup_courses'), 
		'150'
	));
}
