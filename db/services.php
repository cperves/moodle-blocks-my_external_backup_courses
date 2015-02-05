<?php

$functions = array(
	'block_my_external_backup_courses_get_courses_zip' => array(
		'classname' => 'block_my_external_backup_courses_external',
		'methodname' => 'get_courses_zip',
		'classpath' => 'blocks/my_external_backup_courses/externallib.php',
		'description' => 'Get a zip of a given course for a given username',
		'type' => 'read',
		'capabilities' => 'block/my_external_backup_courses:can_see_backup_courses',
	),
	'block_my_external_backup_courses_get_courses' => array(
		'classname' => 'block_my_external_backup_courses_external',
		'methodname' => 'get_courses',
		'classpath' => 'blocks/my_external_backup_courses/externallib.php',
		'description' => 'Get the list of courses for a given username',
		'type' => 'read',
		'capabilities' => 'block/my_external_backup_courses:can_see_backup_courses',
	),
);