<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once($CFG->dirroot.'/blocks/my_external_backup_courses/locallib.php');
$domainname = required_param('domainname', PARAM_RAW);
$courseid = required_param('courseid', PARAM_RAW);
$filetoken= required_param('filetoken', PARAM_RAW);
require_login();
if(!block_my_external_backup_courses_is_downloading($domainname,$filetoken)){
	echo 'OK';
	exit();
}else{
	print_error(get_string('downloadinprogress','block_my_external_backup_courses'));
}