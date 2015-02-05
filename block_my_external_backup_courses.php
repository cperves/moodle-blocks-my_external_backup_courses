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

class block_my_external_backup_courses extends block_list {
	function init() {
		$this->title = get_string('pluginname', 'block_my_external_backup_courses');
	}
	
	function has_config() {
		return true;
	}
	
	function get_content() {
		global $USER, $CFG;
		require_once($CFG->dirroot.'/blocks/my_external_backup_courses/locallib.php');
		
		if ($this->content !== NULL) {
			return $this->content;
		}
		$this->content =  new stdClass;
		$this->content->items[] = (isguestuser($USER->id) or empty($USER->id) or $USER->id ==0) ? '' : print_block_my_external_backup_courses_content();
		$this->content->icons[] = '';
		$this->content->footer = '';
		return $this->content;
	}
	
	/**
	 * Cron cleanup job.
	 */
	public function cron() {
		global $CFG, $DB;
	
		// find out all stale draft areas (older than 4 days) and purge them
		// those are identified by time stamp of the /. root dir
		mtrace('Deleting my_external_backup_courses files... ', '');
		$old = time();
		$sql = "SELECT *
		FROM {files}
		WHERE source = 'block_my_external_backup_courses'
		AND timecreated < :old";
		$rs = $DB->get_recordset_sql($sql, array('old'=>$old));
		$fs = get_file_storage();
		foreach ($rs as $dir) {
			$fs->delete_area_files($dir->contextid, $dir->component, $dir->filearea, $dir->itemid);
		}
		$rs->close();
		mtrace('done.');
	}
}