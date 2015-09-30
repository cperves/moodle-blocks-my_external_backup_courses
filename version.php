<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2012 unistra  {@link http://unistra.fr}
 * @author Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2015093000;  
$plugin->requires  = 2013051405;       // Requires this Moodle version
$plugin->component = 'block_my_external_backup_courses'; // Full name of the plugin (used for diagnostics)
$plugin->cron = 14400;