# block my_external_privatesfiles : Download private files from other moodle platforms

my_external_backup_courses is a Moodle block that enable a user to retrieve course backup files from an external moodle

## Features

## Security warning
* This plugin use a capability block/my_external_backup_courses:can_retrieve_courses that enable webservice account to donload backup files of other users
* To improve security it is strongly recommended to generate token with IPrestriction on server side IPs

## Download



## Installation

### Block installation
Install block on blocks directory in the current moodle and in each external moodle you need to connect to
for each moodle concerned :

* create role for webservice
  * add the protocol rest capability to this role webservice/rest:use
  * add capabilility block/my_external_backup_courses:can_retrieve_courses
  * add capbility block/my_external_backup_courses:can_see_backup_courses
 * Create a user account for webservice account 
* assign role on system context for this newly created account
* Under webservice administration :
  * Under Site administration -> Plugins -> Web Services -> External services, add a new custom service
    * check Enabled
    * ckeck Authorised users only
    * check  Can download files
    * select capability block/my_external_backup_courses:can_see_backup_courses
  * once created add funtions to the new custom external service
    * core_webservice_get_site_info
    * block_my_external_backup_courses_get_courses
    * block_my_external_backup_courses_get_courses_zip
  *  add the webservice user account created previously to the authorized users of the new custom service
  * Under Site administration -> Plugins -> Web Services -> Manage Tokens
    * create a new token, restrited on your php server(s) for the custom external sservice previously created
    * This token will be one to enter in the block parameters off block_my_external_backup_courses 

### Block setting
Under Plugins -> Blocks -> Download courses from other moodle platforms
  * in my_external_backup_course | external_moodles enter the key/value list of moodles/token to connect to
    * The format is a php list [moodle_url=>external_moodle_token_for_webservice_account,...]
  * in my_external_backup_course | search_roles enter roles to include in course search simple quote delimited text shortname separated by commas
  * in my_external_backup_course | filename
  * in my_external_backup_course | includesitename
  * in my_external_backup_course | sitenamelength  

## Contributions

Contributions of any form are welcome. Github pull requests are preferred.

File any bugs, improvements, or feature requiests in our [issue tracker][issues].

## License
* http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
* http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
[my_external_private_files_github]: 
[issues]: 