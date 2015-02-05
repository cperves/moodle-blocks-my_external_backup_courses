<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2012 unistra  {@link http://unistra.fr}
 * @author Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2013121100;  
$plugin->requires  = 2012061700;       // Requires this Moodle version
$plugin->component = 'block_my_external_backup_courses'; // Full name of the plugin (used for diagnostics)
$plugin->cron = 14400;