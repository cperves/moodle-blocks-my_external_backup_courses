<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2013 unistra  {@link http://unistra.fr}
 * @author Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');

class my_external_backup_courses_form extends moodleform {
	function __construct($domainname, $token, $courses) {
        $this->domainname = $domainname;
        $this->token = $token;
        $this->courses = $courses;
        parent::moodleform();
    }
	
	function definition() {
		global $USER;
		
        $mform =& $this->_form;
        $mform->addElement('header', 'backup_courses_header', $this->domainname);
        foreach($this->courses as $course) {
        	if (isset($course->fullname)) {
	        	$grp = array();
	        	$grp[] =& $mform->createElement('button', 'logininfo', get_string('Download', 'block_my_external_backup_courses'));
	        	$mform->addGroup($grp, '$grp', $course->fullname.' :');
        	}        	
        }
	}
}