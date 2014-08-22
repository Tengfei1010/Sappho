<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * @Author:tutengfei
 * 2014/08/19
 * ---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 * ---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 *
 */

/**  define error report parameter * */
define('ENVIRONMENT', 'development');

/*
 * 
 * The Type of ENVIRONMENT:
 * E_ERROR | E_WARNING | E_PARSE | E_NOTICE
 * Your can select type of ENVIROMENT.
 * 
 */
if (defined('ENVIRONMENT')) {

    switch (ENVIRONMENT) {
        case 'development':
            error_reporting(E_ALL);
            break;
        case 'testing':
            error_reporting(E_ALL);
            break;
        case 'production':
            error_reporting(0);
            break;

        default:
            exit('The application environment is not set correctly.');
    }
}

/*
 * ---------------------------------------------------------------
 * SAPPHO FOLDER NAME
 * ---------------------------------------------------------------
 *
 * This variable must contain the name of your "saphho" folder.
 * Include the path if the folder is not in the same  directory
 * as this file.
 *
 */

$spahho_path = 'sappho';

/*
 * ---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 * ---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server.  If
 * you do, use a full server path. 
 * 
 *
 */
$application_folder = 'application';

// Set the current directory correctly for CLI requests
if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

if (realpath($spahho_path) !== FALSE) {
    $spahho_path = realpath($spahho_path) . '/';
}

// ensure there's a trailing slash
$spahho_path = rtrim($spahho_path, '/') . '/';

// Is the spahho path correct?
if (!is_dir($spahho_path)) {
    exit("Your spahho folder path does not appear to be set correctly. Please open the following file and correct this: " . pathinfo(__FILE__, PATHINFO_BASENAME));
}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// The PHP file extension
// this global constant is deprecated.
define('EXT', '.php');

// Path to the sappho folder
define('BASEPATH', str_replace("\\", "/", $spahho_path));

// Path to the front controller (this file)
define('FCPATH', str_replace(SELF, '', __FILE__));

// Name of the "spahho folder"
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));


// The path to the "application" folder
if (is_dir($application_folder)) {
    define('APPPATH', $application_folder . '/');
} else {
    if (!is_dir(BASEPATH . $application_folder . '/')) {
        exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: " . SELF);
    }

    define('APPPATH', BASEPATH . $application_folder . '/');
}

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 *
 */
require_once BASEPATH.'core/Sappho.php';

/* End of file index.php */
/* Location: ./index.php */





?>
