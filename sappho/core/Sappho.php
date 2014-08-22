<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

/**
 * Sappho extends from  CodeIgniter.
 * Sappho is CodeIgniter's extention.
 * Sappho supports develop webservice,bootstrap,MongoDB and ORM application.
 * Sappho is much quicker than CodeIgniter. 
 * @package		Sappho
 * @author		Tutengfei.kevin
 * @since		Version 1.0
 * 
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


/**
 * Sappho Version
 *
 * @var string
 *
 */
define('SAPPHO_VERSION', '1.0.0');

/**
 * Sappho Branch (Core = TRUE, Reactor = FALSE)
 *
 * @var boolean
 *
 */
define('CI_CORE', FALSE);

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */

require(BASEPATH . 'core/Common.php');

/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */
/*
 *  精简 使用方法
  if (defined('ENVIRONMENT') AND file_exists(APPPATH . 'config/' . ENVIRONMENT . '/constants.php')) {
  require(APPPATH . 'config/' . ENVIRONMENT . '/constants.php');
  } else {
  require(APPPATH . 'config/constants.php');
  }
 */

require(APPPATH . 'config/constants.php');

/*
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 * ------------------------------------------------------
 */

set_error_handler('_exception_handler');

//The function of set_magic_quotes_runtime  has been DEPRECATED as of PHP 5.3.0. Relying on this feature is highly discouraged.
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    @set_magic_quotes_runtime(0); // Kill magic quotes
}

/*
 * ------------------------------------------------------
 *  Set the subclass_prefix
 * ------------------------------------------------------
 *
 * Normally the "subclass_prefix" is set in the config file.
 * The subclass prefix allows Sappho to know if a core class is
 * being extended via a library in the local application
 * "libraries" folder. Since Sappho allows config items to be
 * overriden via data set in the main index. php file,
 * before proceeding we need to know if a subclass_prefix
 * override exists.  If so, we will set this value now,
 * before any classes are loaded
 * Note: Since the config file data is cached it doesn't
 * hurt to load it here.
 * 
 */
if (isset($assign_to_config['subclass_prefix']) AND $assign_to_config['subclass_prefix'] != '') {
    get_config(array('subclass_prefix' => $assign_to_config['subclass_prefix']));
}

/*
 * ------------------------------------------------------
 *  Set a liberal script execution time limit
 * ------------------------------------------------------
 */
if (function_exists("set_time_limit") == TRUE AND @ini_get("safe_mode") == 0) {
    @set_time_limit(300);
}

/**
 * The time class is for click
 */
$BM = & load_class('Timer', 'core');
$BM->mark('total_execution_time_start');
$BM->mark('loading_time:_base_classes_start');

/*
 * ------------------------------------------------------
 *  Instantiate the hooks class of Sappho
 * ------------------------------------------------------
 */
$EXT = & load_class('Hooks', 'core');

/*
 * ------------------------------------------------------
 *  Is there a "pre_system" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('pre_system');

/*
 * ------------------------------------------------------
 *  Instantiate the config class
 * ------------------------------------------------------
 */
$CFG = & load_class('Config', 'core');

// Do we have any manually set config items in the index.php file?
if (isset($assign_to_config)) {
    $CFG->_assign_to_config($assign_to_config);
}

/*
 * ------------------------------------------------------
 *  Instantiate the UTF-8 class
 * ------------------------------------------------------
 *
 * Note: Order here is rather important as the UTF-8
 * class needs to be used very early on, but it cannot
 * properly determine if UTf-8 can be supported until
 * after the Config class is instantiated.
 *
 */

$UNI = & load_class('Utf8', 'core');


/*
 * ------------------------------------------------------
 *  Instantiate the URI class
 * ------------------------------------------------------
 */
$URI = & load_class('URI', 'core');


/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 * ------------------------------------------------------
 */
$RTR = & load_class('Router', 'core');
$RTR->_set_routing();

// Set any routing overrides that may exist in the main index file
if (isset($routing)) {
    $RTR->_set_overrides($routing);
}

/*
 * ------------------------------------------------------
 *  Instantiate the output class
 * ------------------------------------------------------
 */
$OUT = & load_class('Output', 'core');

/*
 * ------------------------------------------------------
 * 	Is there a valid cache file?  If so, we're done...
 * ------------------------------------------------------
 */
if ($EXT->_call_hook('cache_override') === FALSE) {
    if ($OUT->_display_cache($CFG, $URI) == TRUE) {
        exit;
    }
}

/*
 * -----------------------------------------------------
 * Load the security class for xss and csrf support
 * -----------------------------------------------------
 */
$SEC = & load_class('Security', 'core');

/*
 * ------------------------------------------------------
 *  Load the Input class and sanitize globals
 * ------------------------------------------------------
 */
$IN = & load_class('Input', 'core');

/*
 * ------------------------------------------------------
 *  Load the Language class
 * ------------------------------------------------------
 */
$LANG = & load_class('Lang', 'core');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 *
 */
// Load the base controller class
require BASEPATH . 'core/Controller.php';

function &get_instance() {
    return SH_Controller::get_instance();
}

//Load developer own definition of a controller class
//config item from appliction/config.php 
if (file_exists(APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php')) {
    require APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php';
}

// Load the local application controller
// Note: The Router class automatically validates the controller path using the router->_validate_request().
// If this include fails it means that the default controller in the Routes.php file is not resolving to something valid.
if (!file_exists(APPPATH . 'controllers/' . $RTR->fetch_directory() . $RTR->fetch_class() . '.php')) {
    show_error('Unable to load your default controller. Please make sure the controller specified in your Routes.php file is valid.');
}

include(APPPATH . 'controllers/' . $RTR->fetch_directory() . $RTR->fetch_class() . '.php');

// Set a mark point for benchmarking
$BM->mark('loading_time:_base_classes_end');

/*
 * ------------------------------------------------------
 *  Security check
 * ------------------------------------------------------
 *
 *  None of the functions in the app controller or the
 *  loader class can be called via the URI, nor can
 *  controller functions that begin with an underscore
 */
$class = $RTR->fetch_class();
$method = $RTR->fetch_method();

if (!class_exists($class) OR strncmp($method, '_', 1) == 0 OR in_array(strtolower($method), array_map('strtolower', get_class_methods('SH_Controller')))
) {
    if (!empty($RTR->routes['404_override'])) {
        $x = explode('/', $RTR->routes['404_override']);
        $class = $x[0];
        $method = (isset($x[1]) ? $x[1] : 'index');
        if (!class_exists($class)) {
            if (!file_exists(APPPATH . 'controllers/' . $class . '.php')) {
                show_404("{$class}/{$method}");
            }

            include_once(APPPATH . 'controllers/' . $class . '.php');
        }
    } else {
        show_404("{$class}/{$method}");
    }
}

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('pre_controller');

/*
 * ------------------------------------------------------
 *  Instantiate the requested controller
 * ------------------------------------------------------
 */
// Mark a start point so we can benchmark the controller
$BM->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_start');

$SH = new $class();

/*
 * ------------------------------------------------------
 *  Is there a "post_controller_constructor" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_controller_constructor');

/*
 * ------------------------------------------------------
 *  Call the requested method
 * ------------------------------------------------------
 */
// Is there a "remap" function? If so, we call it instead
if (method_exists($SH, '_remap')) {
    $SH->_remap($method, array_slice($URI->rsegments, 2));
} else {
    // is_callable() returns TRUE on some versions of PHP 5 for private and protected
    // methods, so we'll use this workaround for consistent behavior
    if (!in_array(strtolower($method), array_map('strtolower', get_class_methods($SH)))) {
        // Check and see if we are using a 404 override and use it.
        if (!empty($RTR->routes['404_override'])) {
            $x = explode('/', $RTR->routes['404_override']);
            $class = $x[0];
            $method = (isset($x[1]) ? $x[1] : 'index');
            if (!class_exists($class)) {
                if (!file_exists(APPPATH . 'controllers/' . $class . '.php')) {
                    show_404("{$class}/{$method}");
                }

                include_once(APPPATH . 'controllers/' . $class . '.php');
                unset($SH);
                $SH = new $class();
            }
        } else {
            show_404("{$class}/{$method}");
        }
    }

    // Call the requested method.
    // Any URI segments present (besides the class/function) will be passed to the method for convenience
    call_user_func_array(array(&$SH, $method), array_slice($URI->rsegments, 2));
}


// Mark a benchmark end point
$BM->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_end');

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
if ($EXT->_call_hook('display_override') === FALSE) {
    $OUT->_display();
}

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_system');

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
	$EXT->_call_hook('post_system');

/*
 * ------------------------------------------------------
 *  Close the DB connection if one exists
 * SH_DB is define DB.php
 * ------------------------------------------------------
 */
	if (class_exists('SH_DB') AND isset($SH->db))
	{
		$SH->db->close();
	}


/* End of file Sappho.php */
/* Location: ./sappho/core/Sappho.php */
?>
