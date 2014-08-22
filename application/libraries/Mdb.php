<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ::LIBRARY/Mdb:: Provides a CI friendly wrapper for MongoDB
class Mdb {
	// ::ATTRIBUTE/mongo_friendly/string/public:: Stores the friendly name for the selected database
	public $mongo_friendly = '';

	// ::ATTRIBUTE/CI/object/protected:: Pointer to the CI superobject
	protected $CI;

	// ::ATTRIBUTE/mongo_conn/string/protected:: MongoDB connection string, see config for more details
	protected $mongo_conn = 'mongodb://localhost:27017';

	// ::ATTRIBUTE/mongo_db/string/protected:: MongoDB database name, see config for more details
	protected $mongo_db = 'test';

	// ::ATTRIBUTE/mongo_persist/string/protected:: MongoDB persistence string, see config for more details
	protected $mongo_persist = 'cimongodb';

	// ::ATTRIBUTE/mongo_last_error/string/protected:: Last error in human readable form
	protected $mongo_last_error = '';

	// ::ATTRIBUTE/mongo_error/bool/protected:: Last operation resulted in an error?
	protected $mongo_error = false;

	// ::METHOD/__construct($params = array())/public:: Constructor, sets up the MongoDB connection
	public function __construct($params = array()) {
		// set the super object to a local variable for use throughout the class
		$this->CI =& get_instance();

		// set all mongoDB preferences
		foreach (array('mongo_conn', 'mongo_db', 'mongo_friendly', 'mongo_persist') as $key) { $this->{$key} = (isset($params[$key])) ? $params[$key] : $this->CI->config->item($key); }

		// friendly name
		$this->mongo_friendly =  (!isset($this->mongo_friendly) or strlen(trim($this->mongo_friendly)) < 1) ? $this->mongo_db : trim($this->mongo_friendly);

		// create mongoDB connection
		try {
			// connect to server
			$m = new Mongo($this->mongo_conn, array("persist" => $this->mongo_persist));

			// select database
			$this->{$this->mongo_friendly} = $m->{$this->mongo_db};
		} catch (MongoConnectionException $e) {
			show_error('Unable to connect to MongoDB server.');
		} catch (MongoException $e) {
			show_error('MongoDB Error: ' . $e->getMessage());
		}

		log_message('debug', "Mdb Class Initialized");
	}

	// ::METHOD/get(&$collection, $criteria = array(), $sort = array(), $keys = array(), $limit, $return_cursor = false)/public:: Get a list of records matching $criteria from $collection, sort by $sort and limit to $limit.
	// Optionally return only the $keys listed
	// Optionally return the entire cursor instead of a data array. The $keys option will not work when you want the cursor returned.
	// Returns an array (even if empty) or a cursor. Use $this->check_error() to check for errors. The returned data array is indexed by the record '_id' as a string.
	public function get(&$collection, $criteria = array(), $sort = array(), $keys = array(), $limit = 0, $return_cursor = false) {
		// init
		$this->mongo_error = false;
		$this->mongo_last_error = '';
		$results = array();

		// if collection exists, criteria are valid interact with the DB
		if ($this->check_collection($collection)) {
			if ($this->check_criteria($criteria)) {
				try {
					// first create the cursor
					if (count($keys) > 0) { $cursor = $collection->find($criteria, $keys); }
					else { $cursor = $collection->find($criteria); }

					// apply sorting if array is in proper form
					if (is_array($sort) and count($sort)) { $cursor->sort($sort); }

					// apply limit if the limit is valid
					if (ctype_digit((string)$limit) and $limit > 0) { $cursor->limit($limit); }

					//  execute a count (this will cause the cursor to execute)
					$stat = $cursor->count();

					// if we only have to return the cursor, do that now, otherwise return the actual data
					if ($return_cursor) {
						return $cursor;
					} else {
						// build return object
						if ($stat) {
							foreach ($cursor as $obj) { $results[$obj["_id"]->__toString()] = $obj; }
						}
					}
					unset($cursor);
				} catch (MongoException $e) {
					$this->mongo_error = true;
					$this->mongo_last_error = $e->getMessage();
				}
			}
		}
		return $results;
	}

	// ::METHOD/getfirst(&$collection, $criteria = array(), $sort = array())/public:: Get only one record (the first) matching $criteria from $collection, shorthand for $this->get($collection, $criteria, $sort, array(), 1)
	// Returns the record or false if none was found
	public function getfirst(&$collection, $criteria = array(), $sort = array()) {
		$tmp = $this->get($collection, $criteria, $sort, array(), 1);
		if (count($tmp)) { return reset($tmp); }
		else { return false; }
	}

	// ::METHOD/getbyid(&$collection, $_id)/public:: Get the record identified by the $_id provided.
	// Returns the record or false if none was found
	public function getbyid(&$collection, $_id) {
		// init
		$this->mongo_error = false;
		$this->mongo_last_error = '';
		$result = false;

		// validate id
		$_id = $this->valid_id($_id);
		if ($_id !== false) {
			if ($this->check_collection($collection)) {
				try {
					$result = $collection->findOne(array('_id' => $_id));
					if (!is_array($result)) { $result = false; }
				} catch (MongoException $e) {
					$this->mongo_error = true;
					$this->mongo_last_error = $e->getMessage();
				}
			}
		}
		return $result;
	}

	// ::METHOD/update(&$collection, $criteria = array(), $data = array(), $upsert = false)/public:: Update records matching $criteria from $collection with $data
	// Optionally you can elect to insert the data if no records match
	public function update(&$collection, $criteria = array(), $data, $upsert = false) {
		// init
		$this->mongo_error = false;
		$this->mongo_last_error = '';
		$result = false;

		// if collection exists, criteria are valid, data isn't empty interact with the DB
		if ($this->check_collection($collection)) {
			if ($this->check_criteria($criteria)) {
				if ($this->check_data($data)) {
					try {
						// dont update the _id column, just in case your query updates more records then anticipated.
						unset($data['_id']);
						$result = $collection->update($criteria, array('$set' => $data), array('upsert' => $upsert, 'multiple' => true));
					} catch (MongoException $e) {
						$this->mongo_error = true;
						$this->mongo_last_error = $e->getMessage();
						$result = false;
					}
				}
			}
		}
		return $result;
	}

	// ::METHOD/updatebyid(&$collection, $_id, $data = array())/public:: Update record matching $_id from $collection with $data, see $this->update for more info
	public function updatebyid(&$collection, $_id, $data) {
		// init
		$this->mongo_error = false;
		$this->mongo_last_error = '';
		$result = false;

		// validate id
		$_id = $this->valid_id($_id);
		if ($_id !== false) { $result = $this->update($collection, array('_id' => $_id), $data); }
		return $result;
	}

	// ::METHOD/insert(&$collection, $data = array())/public:: Insert record into $collection with $data
	public function insert(&$collection, $data) {
		// init
		$this->mongo_error = false;
		$this->mongo_last_error = '';
		$result = false;

		// if collection exists, data isn't empty interact with the DB
		if ($this->check_collection($collection)) {
			if ($this->check_data($data)) {
				try {
					$result = $collection->insert($data);
					if ($result !== false) { $result = $data['_id']->__toString(); }
				} catch (MongoException $e) {
					$this->mongo_error = true;
					$this->mongo_last_error = $e->getMessage();
					$result = false;
				}
			}
		}
		return $result;
	}

	// ::METHOD/delete(&$collection, $criteria = array())/public:: Delete from $collection records matching $criteria
	public function delete(&$collection, $criteria = array()) {
		// init
		$this->mongo_error = false;
		$this->mongo_last_error = '';
		$result = false;

		// if collection exists, criteria are valid interact with the DB
		if ($this->check_collection($collection)) {
			if ($this->check_criteria($criteria)) {
				try {
					$result = $collection->remove($criteria);
				} catch (MongoException $e) {
					$this->mongo_error = true;
					$this->mongo_last_error = $e->getMessage();
					$result = false;
				}
			}
		}
		return $result;
	}

	// ::METHOD/deletebyid(&$collection, $_id)/public:: Delete from $collection record matching $_id
	public function deletebyid(&$collection, $_id) {
		// init
		$this->mongo_error = false;
		$this->mongo_last_error = '';
		$result = false;

		// validate id
		$_id = $this->valid_id($_id);
		if ($_id !== false) { $result = $this->delete($collection, array('_id' => $_id)); }
		return $result;
	}

	// ::METHOD/delkey(&$collection, $criteria = array(), $keys = array())/public:: Remove from records matching $criteria of $collection the $keys listed
	public function delkey(&$collection, $criteria = array(), $keys) {
		// init
		$this->mongo_error = false;
		$this->mongo_last_error = '';
		$result = false;

		// if collection exists, criteria are valid, data isn't empty interact with the DB
		if ($this->check_collection($collection)) {
			if ($this->check_criteria($criteria)) {
				if ($this->check_data($keys)) {
					try {
						// dont update the _id column, just in case your query updates more records then anticipated.
						unset($keys['_id']);
						$tmp = array();
						foreach ($keys as $k) { $tmp[$k] = 1; }
						$result = $collection->update($criteria, array('$unset' => $tmp), array('multiple' => true));
					} catch (MongoException $e) {
						$this->mongo_error = true;
						$this->mongo_last_error = $e->getMessage();
						$result = false;
					}
				}
			}
		}
		return $result;
	}

	// ::METHOD/updatebyid(&$collection, $_id, $keys = array())/public:: Update record matching $_id from $collection with $data, see $this->update for more info
	public function delkeybyid(&$collection, $_id, $keys) {
		// init
		$this->mongo_error = false;
		$this->mongo_last_error = '';
		$result = false;

		// validate id
		$_id = $this->valid_id($_id);
		if ($_id !== false) { $result = $this->delkey($collection, array('_id' => $_id), $keys); }
		return $result;
	}

	// ::METHOD/check_error()/public:: return the status of the last query
	public function check_error() {
		return $this->mongo_error;
	}

	// ::METHOD/error_message()/public:: return the error message from the last query (if any)
	public function error_message() {
		return $this->mongo_last_error;
	}

	// ::METHOD/valid_id(&$data)/public:: cleans and checks the $_id and returns a MongoId object or false
	public function valid_id($_id) {
		// cleanup the id and make sure it is a legitimate id, first convert to string, (if it's not a string but a MongoID this shuld get you the string part)
		$_id = trim("$_id");
		if (ctype_xdigit($_id) and strlen($_id) == 24) {
			$_id = new MongoId($_id);
			return $_id;
		} else {
			$this->mongo_error = true;
			$this->mongo_last_error = 'The _id value is not an allowed Id';
			return false;
		}
	}

	// ::METHOD/check_collection(&$collection)/protected:: pre-query validation checks on collection
	protected function check_collection(&$collection) {
		// we are not checking if a collection actually exists, since if it doesn't it will be created on the fly
		// we're merely checking that &$collection reflects an object that exists, of course the object could still be the wrong one...
		if (!isset($collection)) {
			$this->mongo_error = true;
			$this->mongo_last_error = 'The collection referenced does not appear to exist';
			return false;
		}
		return true;
	}

	// ::METHOD/check_criteria(&$criteria)/protected:: pre-query validation checks on criteria
	protected function check_criteria(&$criteria) {
		// has to be an array, even if it's a blank one
		if (!is_array($criteria)) {
			$this->mongo_error = true;
			$this->mongo_last_error = 'The criteria need to be an array, even if blank';
			return false;
		}
		return true;
	}

	// ::METHOD/check_data(&$data)/protected:: pre-query validation checks on data
	protected function check_data(&$data) {
		// has to be an array, and has to be non-empty
		if (!is_array($data) or count($data) < 1) {
			$this->mongo_error = true;
			$this->mongo_last_error = 'The data array needs to be an array and cannot be empty';
			return false;
		}
		return true;
	}
}