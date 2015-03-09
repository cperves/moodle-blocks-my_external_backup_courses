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

function block_my_external_backup_courses_get_all_users_courses($username, $onlyactive = false, $fields = NULL, $sort = 'visible DESC,sortorder ASC') {
	global $DB;

	$config = get_config('my_external_backup_courses');
    // Guest account does not have any courses
    $user_record = $DB->get_record('user', array('username' => $username));
	if (!$user_record) { 
		throw new block_my_external_backup_courses_invalid_username_exception('user with username not found');
	}

    $userid = $user_record->id;
	
    if (isguestuser($userid) or empty($userid)) {
        return(array());
    }

    $basefields = array('id', 'category', 'sortorder',
            'shortname', 'fullname', 'idnumber',
            'startdate', 'visible',
            'groupmode', 'groupmodeforce');

    if (empty($fields)) {
        $fields = $basefields;
    } else if (is_string($fields)) {
        // turn the fields from a string to an array
        $fields = explode(',', $fields);
        $fields = array_map('trim', $fields);
        $fields = array_unique(array_merge($basefields, $fields));
    } else if (is_array($fields)) {
        $fields = array_unique(array_merge($basefields, $fields));
    } else {
        throw new coding_exception('Invalid $fileds parameter in enrol_get_my_courses()');
    }
    if (in_array('*', $fields)) {
        $fields = array('*');
    }

    $orderby = "";
    $sort    = trim($sort);
    if (!empty($sort)) {
        $orderby = "ORDER BY $sort";
    }

    $params = array();

    if ($onlyactive) {
        $subwhere = "WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)";
        $params['now1']    = round(time(), -2); // improves db caching
        $params['now2']    = $params['now1'];
        $params['active']  = ENROL_USER_ACTIVE;
        $params['enabled'] = ENROL_INSTANCE_ENABLED;
    } else {
        $subwhere = "";
    }

    $coursefields = 'c.' .join(',c.', $fields);
    list($ccselect, $ccjoin) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
	$roles = $config->search_roles;
	if(empty($roles)){
		return false;
	}	
	$roles = explode(',', $roles);
	$new_formatted_roles = array();
	foreach($roles as $key=>$role){
		$new_formatted_roles[] = '\''.$role.'\'';
	}
	if(count($new_formatted_roles)==0){
		return false;
	}
	
    //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
    $sql = "SELECT $coursefields $ccselect
              	FROM {course} c
              	INNER JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = ".CONTEXT_COURSE.")
              	INNER JOIN (
              		SELECT ra.contextid AS contextid, usr.firstname AS firstname, usr.lastname AS lastname FROM {role_assignments} ra 
	    			INNER JOIN {role} r ON (r.id = ra.roleid and r.shortname IN (".implode(',',$new_formatted_roles)."))
	    			INNER JOIN {user} usr ON (ra.userid = usr.id AND usr.id = $userid)
	    			) AS u ON (u.contextid = ctx.id)
	    		WHERE c.id <> ".SITEID
    		." UNION 
    		SELECT $coursefields $ccselect
    			FROM {course} c
    			INNER JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = ".CONTEXT_COURSE.")
    			INNER JOIN {enrol} e ON e.enrol = 'category' AND e.courseid = c.id
    			INNER JOIN (SELECT cctx.path, ra.userid, MIN(ra.timemodified) AS estart, ra.roleid as roleid
			    			FROM {course_categories} cc
			    			JOIN {context} cctx ON (cctx.instanceid = cc.id AND cctx.contextlevel = ".CONTEXT_COURSECAT.")
			    			JOIN {role_assignments} ra ON (ra.contextid = cctx.id)
			    			JOIN {role} ro ON (ra.roleid = ro.id and ro.shortname in ($config->search_roles))
			    			GROUP BY cctx.path, ra.userid, ra.roleid
              	) cat ON (ctx.path LIKE cat.path || '/%')
			    INNER JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = cat.userid)
			    INNER JOIN {user} u ON u.id = cat.userid AND u.id = ue.userid
			    INNER JOIN {role} r ON r.id = cat.roleid AND r.shortname IN ($config->search_roles)
				WHERE u.id = $userid AND c.id <> ".SITEID
          ." $orderby";

    $courses = $DB->get_records_sql($sql, $params);
    return $courses;
}

function block_my_external_backup_courses_print_content() {
	global $OUTPUT;
	$output = '';
	$external_moodles = get_config('my_external_backup_courses', 'external_moodles');
	if ($external_moodles && !empty($external_moodles)) {
		$external_moodles = split(';', $external_moodles);
		if (count($external_moodles)>0) {
			$backup_courses_url = new moodle_url('/blocks/my_external_backup_courses/index.php');
			$output = $OUTPUT->single_button($backup_courses_url, get_string('downloadcourses', 'block_my_external_backup_courses'));
		}
	}
	return $output;
	
}

function block_my_external_backup_courses_rest_call_external_courses_client($domainname, $token, $functionname, $params=array(), $restformat='json', $method='get') {
	global $CFG;
	require_once($CFG->dirroot.'/blocks/my_external_backup_courses/locallib.php');
	require_once($CFG->dirroot.'/lib/filelib.php');
	require_once($CFG->dirroot.'/webservice/lib.php');
	$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
	$curl = new curl;
	//if rest format == 'xml', then we do not add the param for backward compatibility with Moodle < 2.2
	$restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
	if ($method == 'get') {
		$resp = $curl->get($serverurl . $restformat, $params);
	} else if ($method == 'post') {
		$resp = $curl->post($serverurl . $restformat, $params);
	}
	$resp = json_decode($resp);
	//check if errors encountered
	if (!isset($resp)) {
		throw new Exception($resp);
	}
	if (isset($resp->errorcode)) {
		if($resp->exception == 'block_my_external_backup_courses_invalid_username_exception'){
			throw new block_my_external_backup_courses_invalid_username_exception($resp->debuginfo);		
		}
		throw new Exception($resp->debuginfo);
	}
	return $resp;
}

function block_my_external_backup_courses_download_external_backup_courses($domainname, $token, $courseid,$filetoken) {
	global $CFG, $USER;
	
	$functionname = 'block_my_external_backup_courses_get_courses_zip';
	$username = $USER->username;
	$params = array('username' => $username, 'courseid' => intval($courseid));
	$file_returned = block_my_external_backup_courses_rest_call_external_courses_client($domainname, $token, $functionname, $params, $restformat='json', $method='post');
	if(empty($file_returned)){
		throw new Excpetion('file retrieve : no response');
	}
	$filename = external_backup_course_filename($domainname, $token, $courseid);

	// DOWNLOAD File
	$url = $domainname . '/blocks/my_external_backup_courses/get_user_backup_course_webservice.php'; //NOTE: normally you should get this download url from your previous call of core_course_get_contents()
	$url .= '?token=' . $token; //NOTE: in your client/app don't forget to attach the token to your download url
	$url .= '&filerecordid='.$file_returned->filerecordid;
	//serve file
	block_my_external_backup_courses_download_backup_course($url, $filename,$domainname,$filetoken);
}

function block_my_external_backup_courses_external_backup_course_sitename($domainname, $token) {
		$site_info = NULL;
		try {
			$site_info = block_my_external_backup_courses_rest_call_external_courses_client($domainname, $token, 'core_webservice_get_site_info');
		} catch (Exception $e) {
			throw new Exception('site name can \'t be retrieved : '.$e->getMessage());
		}
		$sitename = $site_info->sitename;
		if (!isset($sitename)) {
			throw new Exception('site name can \'t be retrieved');
		}
		return $sitename;	
}
function block_my_external_backup_courses_external_backup_course_formatted_sitename($domainname, $token) {
	$site_info = NULL;
	try {
		$site_info = block_my_external_backup_courses_rest_call_external_courses_client($domainname, $token, 'core_webservice_get_site_info');
	} catch (Exception $e) {
		throw new Exception('site name can \'t be retrieved : '.$e->getMessage());
	}
	$sitename = $site_info->sitename;
	if (!isset($sitename)) {
		throw new Exception('site name can \'t be retrieved');
	}
	//transform sitename
	$sitename = preg_replace('/[^a-zA-Z0-9-]/', '_', $sitename);
	//passit to 150characters
	try {
		$sitenamelength = isset($config->sitenamelength) && !empty($config->sitenamelength) ? (int)$config->sitenamelength : strlen($sitename);
		$sitename = substr($sitename, 0, $sitenamelength);
	} catch (Exception $ex) {
		//Nothing to do keep sitename original length
	}
	return $sitename;
}

function external_backup_course_filename($domainname, $token, $courseid) {
	$config = get_config('my_external_backup_courses');
	$includesitename = (bool)(isset($config->includesitename) ? $config->includesitename : 0);
	$sitename = '';
	if ($includesitename) {
		$sitename = block_my_external_backup_courses_external_backup_course_formatted_sitename($domainname, $token);
		
	}
	$userdate = usergetdate(time());
	$formatteddate = $userdate['year'].'-'.$userdate['mon'].'-'.$userdate['mday'].'-'.$userdate['hours'].':'.$userdate['minutes'].':'.$userdate['seconds'];
	$filename = $config->filename;
	if (empty($filename)) {
		$filename = 'my_backup_course';
	}
	$filename .= '_'.$courseid;
	$filename .= (empty($sitename) ? '' : '_'.$sitename).'_'.$formatteddate.'.zip';
	return $filename;
}

function block_my_external_backup_courses_download_backup_course($url, $filename,$domainname,$filetoken) {
	
	ignore_user_abort(true);
	set_time_limit(0);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $r = curl_exec($ch);
    curl_close($ch);
    $array_response=json_decode($r);
    if (!isset($array_response)) {
	    header('Expires: 0'); // no cache
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
	    header('Cache-Control: private', false);
	    header('Content-Type: application/force-download');
	    header('Content-Disposition: attachment; filename="'.$filename.'"');
	    header('Content-Transfer-Encoding: binary');
	    header('Content-Length: ' . strlen($r)); // provide file size
	    header('Connection: close');
	    echo $r;
	    remove_file_token($domainname,$filetoken);	    
    } else {
    	remove_file_token($domainname,$filetoken);
    	throw new Exception($array_response->error);
    }
}

function block_my_external_backup_courses_get_file_token_for_page(){
	return 'fileToken_'.random_string(10);
}

function block_my_external_backup_courses_is_downloading($domainname,$filetoken){
	if( !isset($_SESSION['USER']->external_backup_course)){
		return false;
	}
	if(!array_key_exists($domainname, $_SESSION['USER']->external_backup_course)){
		return false;
	}
	if(!array_key_exists($filetoken, $_SESSION['USER']->external_backup_course[$domainname])){
		return false;
	}
	return true;
	
}

function block_my_external_backup_courses_put_file_token($domainname,$filetoken) {
	if( !isset($_SESSION['USER']->external_backup_course)){
		$_SESSION['USER']->external_backup_course= array();
	}
	if(!array_key_exists($domainname, $_SESSION['USER']->external_backup_course)){
		$_SESSION['USER']->external_backup_course[$domainname]=array();
	}
	$_SESSION['USER']->external_backup_course[$domainname][$filetoken]=$filetoken;
}
function remove_file_token($domainname,$filetoken) {
	if(isset($_SESSION['USER']->external_backup_course) && array_key_exists($filetoken, $_SESSION['USER']->external_backup_course[$domainname]) ){
		unset($_SESSION['USER']->external_backup_course[$domainname][$filetoken]);
	}
}

class block_my_external_backup_courses_invalid_username_exception extends moodle_exception {
	/**
	 * Constructor
	 * @param string $debuginfo some detailed information
	 */
	function __construct($debuginfo=null) {
		parent::__construct('invalidusername', 'debug', '', null, $debuginfo);
	}
}