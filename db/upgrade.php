<?php
/**
 * upgrade for block my_external_backup_courses block
 *
 * @package
 * @subpackage
 * @copyright  2015 unistra  {@link http://unistra.fr}
 * @author Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_block_my_external_backup_courses_upgrade($oldversion, $block) {
	global $DB, $CFG;
	//changing external_moodles setting to avoid eval
	if ($oldversion < 2015030501) {
		$external_moodles = get_config('my_external_backup_courses', 'external_moodles');
		$new_external_moodles = '';
		eval('$external_moodles='.$external_moodles.';');
		if (is_array($external_moodles)) {
			foreach($external_moodles as $domainname => $token) {
				$new_external_moodles.= "$domainname,$token;";
			}
			set_config('external_moodles',$new_external_moodles,'my_external_backup_courses');
		}
	}
	if ($oldversion < 2015030502) {
		$search_roles = get_config('my_external_backup_courses', 'search_roles');
		set_config('search_roles',str_replace('\'', '', $search_roles),'my_external_backup_courses');
	}
	return true;
}