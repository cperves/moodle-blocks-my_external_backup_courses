<?php
$capabilities = array(
	'block/my_external_backup_courses:addinstance' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
	'block/my_external_backup_courses:can_retrieve_courses' => array(
		'riskbitmask' => RISK_PERSONAL,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
				'manager' => CAP_INHERIT
		),
	),
	'block/my_external_backup_courses:can_see_backup_courses' => array(
		'riskbitmask' => RISK_PERSONAL,
		'captype' => 'read',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array(
				'manager' => CAP_INHERIT
		),
	),
);
?>