<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ::LIBRARY/MON_Session:: Extends the regular CI_Session class to provide session storage for MongoDB
class MY_Session extends CI_Session {
	// ::METHOD/__construct($params = array())/public:: Constructor, initializes the session, reads existing session or sets up a new one
	function __construct($params = array()) {
		log_message('debug', "Starting Session Class in MongoDB compatibility mode");

		// Set the super object to a local variable for use throughout the class
		$this->CI =& get_instance();

		// Set all the session preferences, which can either be set
		// manually via the $params array above or via the config file
		foreach (array('sess_encrypt_cookie', 'sess_use_database', 'sess_table_name', 'sess_expiration', 'sess_match_ip', 'sess_match_useragent', 'sess_cookie_name', 'cookie_path', 'cookie_domain', 'sess_time_to_update', 'time_reference', 'cookie_prefix', 'encryption_key') as $key) { $this->$key = (isset($params[$key])) ? $params[$key] : $this->CI->config->item($key); }

		// Load the string helper so we can use the strip_slashes() function
		$this->CI->load->helper('string');

		// Do we need encryption? If so, load the encryption class
		if ($this->sess_encrypt_cookie == TRUE) { $this->CI->load->library('encrypt'); }

		// Are we using a database?  If so, load it, if we haven't already
		if ($this->sess_use_database === TRUE AND $this->sess_table_name != '' AND !isset($this->CI->mdb)) {
			log_message('debug', 'Strange, the Mdb object does not exist...');
			$this->CI->load->library('Mdb');
		}

		// Set the "now" time.  Can either be GMT or server time, based on the
		// config prefs.  We use this to set the "last activity" time
		$this->now = $this->_get_time();

		// Set the session length. If the session expiration is
		// set to zero we'll set the expiration two years from now.
		if ($this->sess_expiration == 0) { $this->sess_expiration = (60*60*24*365*2); }

		// Set the cookie name
		$this->sess_cookie_name = $this->cookie_prefix.$this->sess_cookie_name;

		// Run the Session routine. If a session doesn't exist we'll
		// create a new one.  If it does, we'll update it.
		if (!$this->sess_read()) { $this->sess_create(); }
		else { $this->sess_update(); }

		// Delete 'old' flashdata (from last request)
	   	$this->_flashdata_sweep();

		// Mark all new flashdata as old (data will be deleted before next request)
	   	$this->_flashdata_mark();

		// Delete expired sessions if necessary
		$this->_sess_gc();

		log_message('debug', "Session routines successfully run");
	}

	// ::METHOD/sess_read()/public:: Attempts to read an existing session, returns false if none exists
	function sess_read() {
		// Fetch the cookie
		$session = $this->CI->input->cookie($this->sess_cookie_name);

		// No cookie?  Goodbye cruel world!...
		if ($session === FALSE) {
			log_message('debug', 'A session cookie was not found.');
			return FALSE;
		}

		// Decrypt the cookie data
		if ($this->sess_encrypt_cookie == TRUE) {
			$session = $this->CI->encrypt->decode($session);
		} else {
			// encryption was not used, so we need to check the md5 hash
			$hash = substr($session, strlen($session)-32); // get last 32 chars
			$session = substr($session, 0, strlen($session)-32);

			// Does the md5 hash match?  This is to prevent manipulation of session data in userspace
			if ($hash !==  md5($session.$this->encryption_key)) {
				log_message('error', 'The session cookie data did not match what was expected. This could be a possible hacking attempt.');
				$this->sess_destroy();
				return FALSE;
			}
		}

		// Unserialize the session array
		$session = $this->_unserialize($session);

		// Is the session data we unserialized an array with the correct format?
		if ( ! is_array($session) OR ! isset($session['session_id']) OR ! isset($session['ip_address']) OR ! isset($session['user_agent']) OR ! isset($session['last_activity'])) {
			$this->sess_destroy();
			return FALSE;
		}

		// Is the session current?
		if (($session['last_activity'] + $this->sess_expiration) < $this->now) {
			$this->sess_destroy();
			return FALSE;
		}

		// Does the IP Match?
		if ($this->sess_match_ip == TRUE AND $session['ip_address'] != $this->CI->input->ip_address()) {
			$this->sess_destroy();
			return FALSE;
		}

		// Does the User Agent Match?
		if ($this->sess_match_useragent == TRUE AND trim($session['user_agent']) != trim(substr($this->CI->input->user_agent(), 0, 50))) {
			$this->sess_destroy();
			return FALSE;
		}

		// Is there a corresponding session in the DB?
		if ($this->sess_use_database === TRUE) {
			// Establish search criteria
			$criteria = array();
			$criteria['session_id'] = $session['session_id'];
			if ($this->sess_match_ip == TRUE) { $criteria['ip_address'] = $session['ip_address']; }
			if ($this->sess_match_useragent == TRUE) { $criteria['user_agent'] = $session['user_agent']; }

			// Get from database
			$rec = $this->CI->mdb->getfirst($this->CI->mdb->{$this->CI->mdb->mongo_friendly}->{$this->sess_table_name}, $criteria);
			if ($this->CI->mdb->check_error()) {
				log_message('error', 'MongoDB Error: ' . $this->CI->mdb->error_message());
			}

			// No result?  Kill it!
			if ($rec === false) {
				$this->sess_destroy();
				return FALSE;
			}

			// Is there custom data?  If so, add it to the main session array
			if (isset($rec['user_data']) AND $rec['user_data'] != '') {
				$custom_data = $this->_unserialize($rec['user_data']);

				if (is_array($custom_data)) {
					foreach ($custom_data as $key => $val) { $session[$key] = $val; }
				}
			}
		}

		// Session is valid!
		$this->userdata = $session;
		unset($session);

		return TRUE;
	}

	// ::METHOD/sess_write()/public:: Write session data to DB
	function sess_write() {
		// Are we saving custom data to the DB?  If not, all we do is update the cookie
		if ($this->sess_use_database === FALSE) {
			$this->_set_cookie();
			return;
		}

		// set the custom userdata, the session data we will set in a second
		$custom_userdata = $this->userdata;
		$cookie_userdata = array();

		// Before continuing, we need to determine if there is any custom data to deal with.
		// Let's determine this by removing the default indexes to see if there's anything left in the array
		// and set the session data while we're at it
		foreach (array('session_id','ip_address','user_agent','last_activity') as $val) {
			unset($custom_userdata[$val]);
			$cookie_userdata[$val] = $this->userdata[$val];
		}

		// Did we find any custom data?  If not, we turn the empty array into a string
		// since there's no reason to serialize and store an empty array in the DB
		if (count($custom_userdata) === 0) {
			$custom_userdata = '';
		} else {
			// Serialize the custom data array so we can store it
			$custom_userdata = $this->_serialize($custom_userdata);
		}

		// Establish criteria
		$criteria = array();
		$criteria['session_id'] = $this->userdata['session_id'];

		// Update database
		$this->CI->mdb->update($this->CI->mdb->{$this->CI->mdb->mongo_friendly}->{$this->sess_table_name}, $criteria, array('last_activity' => $this->userdata['last_activity'], 'user_data' => $custom_userdata));
		if ($this->CI->mdb->check_error()) {
			log_message('error', 'MongoDB Error: ' . $this->CI->mdb->error_message());
		}

		// Write the cookie.  Notice that we manually pass the cookie data array to the
		// _set_cookie() function. Normally that function will store $this->userdata, but
		// in this case that array contains custom data, which we do not want in the cookie.
		$this->_set_cookie($cookie_userdata);
	}

	// ::METHOD/sess_create()/public:: Creates a new, blank session
	function sess_create() {
		$sessid = '';
		while (strlen($sessid) < 32) { $sessid .= mt_rand(0, mt_getrandmax()); }

		// To make the session ID even more secure we'll combine it with the user's IP
		$sessid .= $this->CI->input->ip_address();

		$this->userdata = array(
			'session_id' => md5(uniqid($sessid, TRUE)),
			'ip_address' => $this->CI->input->ip_address(),
			'user_agent' => substr($this->CI->input->user_agent(), 0, 50),
			'last_activity' => $this->now
		);

		// Save the data to the DB if needed
		if ($this->sess_use_database === TRUE) {
			$this->CI->mdb->insert($this->CI->mdb->{$this->CI->mdb->mongo_friendly}->{$this->sess_table_name}, $this->userdata);
			if ($this->CI->mdb->check_error()) {
				log_message('error', 'MongoDB Error: ' . $this->CI->mdb->error_message());
			}
		}

		// Write the cookie
		$this->_set_cookie();
	}

	// ::METHOD/sess_update()/public:: Updates an existing session
	function sess_update() {
		// We only update the session every five minutes by default
		if (($this->userdata['last_activity'] + $this->sess_time_to_update) >= $this->now) { return; }

		// Save the old session id so we know which record to
		// update in the database if we need it
		$old_sessid = $this->userdata['session_id'];
		$new_sessid = '';
		while (strlen($new_sessid) < 32) { $new_sessid .= mt_rand(0, mt_getrandmax()); }

		// To make the session ID even more secure we'll combine it with the user's IP
		$new_sessid .= $this->CI->input->ip_address();

		// Turn it into a hash
		$new_sessid = md5(uniqid($new_sessid, TRUE));

		// Update the session data in the session data array
		$this->userdata['session_id'] = $new_sessid;
		$this->userdata['last_activity'] = $this->now;

		// _set_cookie() will handle this for us if we aren't using database sessions
		// by pushing all userdata to the cookie.
		$cookie_data = NULL;

		// Update the session ID and last_activity field in the DB if needed
		if ($this->sess_use_database === TRUE) {
			// set cookie explicitly to only have our session data
			$cookie_data = array();
			foreach (array('session_id','ip_address','user_agent','last_activity') as $val) { $cookie_data[$val] = $this->userdata[$val]; }

			// Update database
			$this->CI->mdb->update($this->CI->mdb->{$this->CI->mdb->mongo_friendly}->{$this->sess_table_name}, array('session_id' => $old_sessid), array('last_activity' => $this->now, 'session_id' => $new_sessid));
			if ($this->CI->mdb->check_error()) {
				log_message('error', 'MongoDB Error: ' . $this->CI->mdb->error_message());
			}
		}

		// Write the cookie
		$this->_set_cookie($cookie_data);
	}

	// ::METHOD/sess_destroy()/public:: Destroys the current session and all it's data
	function sess_destroy() {
		if ($this->sess_use_database === TRUE AND isset($this->userdata['session_id'])) {
			// Delete from database
			$this->CI->mdb->delete($this->CI->mdb->{$this->CI->mdb->mongo_friendly}->{$this->sess_table_name}, array('session_id' => $this->userdata['session_id']));
			if ($this->CI->mdb->check_error()) {
				log_message('error', 'MongoDB Error: ' . $this->CI->mdb->error_message());
			}
		}

		// Kill the cookie
		setcookie(
			$this->sess_cookie_name,
			addslashes(serialize(array())),
			($this->now - 31500000),
			$this->cookie_path,
			$this->cookie_domain,
			0
		);
	}

	// ::METHOD/_sess_gc()/protected:: Cleans DB records corresponding to expired sessions
	function _sess_gc() {
		if ($this->sess_use_database != TRUE) { return; }

		srand(time());
		if ((rand() % 100) < $this->gc_probability) {
			$expire = $this->now - $this->sess_expiration;

			// Delete from database
			$this->CI->mdb->delete($this->CI->mdb->{$this->CI->mdb->mongo_friendly}->{$this->sess_table_name}, array('last_activity' => array('$lt' => $expire)));
			if ($this->CI->mdb->check_error()) {
				log_message('error', 'MongoDB Error: ' . $this->CI->mdb->error_message());
			}

			log_message('debug', 'Session garbage collection performed.');
		}
	}
}
