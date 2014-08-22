<?php
class TestCls {
	public $testdata;
	function foobar() {
		$this->testdata = 'foobar';
	}
}

// ::CONTROLLER/Test_Mdb:: Performs tests to see if the Mdb class operates properly
class Test_Mdb extends SH_Controller {
	function index() {
		print("<pre>");
		print("Start error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("Start error message: " . $this->mdb->error_message() . "\n");
		$i = 1;
		
		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Delete all from collection ci_mdb_test_set\n");
		$start = microtime(true);
		$result = $this->mdb->delete($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array());
		$stop = microtime(true);
		print("TEST $i: Query took: " . number_format($stop - $start, 4) . "sec \n");
		print("TEST $i: Result: " . json_encode($result) . "\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get all from collection ci_mdb_test_set, there should be no records\n");
		$start = microtime(true);
		$result = $this->mdb->get($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array());
		$stop = microtime(true);
		print("TEST $i: Items in collection: " . count($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Insert 11 data records into ci_mdb_test_set, first should fail because it is blank\n");
		$item0 = array();
		$item1 = array('code' => 'UK', 'language' => 'English', 'name' => 'United Kingdom');
		$item2 = array('code' => 'CA', 'language' => 'English', 'name' => 'Canada', 'history' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus molestie, velit et ullamcorper condimentum, elit felis commodo metus, non sollicitudin erat erat in lacus. Aenean lacus tellus, consequat bibendum aliquam a, ornare sed quam. Vestibulum eget gravida justo. Suspendisse potenti. Maecenas gravida ultrices augue, id mattis felis rhoncus sed. Suspendisse potenti. Nam nunc neque, dignissim sit amet iaculis id, vehicula id ligula. Aliquam at massa a ipsum molestie eleifend vitae nec ligula. Vestibulum quis nisi et mauris ullamcorper hendrerit at et enim. Sed sed tortor erat. Sed non risus orci, sit amet pellentesque lectus. Praesent sodales, orci non auctor iaculis, justo ante dignissim lorem, at tempor nisi dolor at ipsum. Ut massa tortor, lacinia nec tincidunt vel, tempus et mauris. Pellentesque pellentesque urna et nibh molestie ut feugiat felis fermentum. Donec egestas, purus ut dictum vestibulum, magna arcu molestie massa, id gravida mauris sapien in lorem. Aliquam eget aliquet nunc. Proin iaculis elit augue. Phasellus faucibus pellentesque hendrerit. Fusce purus erat, dictum vel rutrum a, sodales ac metus. Aenean ut nulla interdum magna tincidunt scelerisque. Curabitur arcu purus, interdum id iaculis et, feugiat vel neque. Etiam dapibus, nulla id porta posuere, lectus eros luctus sapien, ac porta ipsum ipsum sed est. Sed condimentum posuere semper. Mauris scelerisque dignissim eros, nec accumsan diam commodo et. Proin odio turpis, interdum quis ullamcorper eget, tristique eget dolor. Donec non ipsum felis. In hac habitasse platea dictumst. Mauris sed erat nec sapien mattis vestibulum at fermentum leo. Maecenas sapien risus, tempus eget dapibus nec, venenatis vel ligula. Sed sodales rhoncus porta. Praesent laoreet dapibus ante ut pulvinar. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque sollicitudin leo et est molestie tincidunt. Nunc placerat laoreet rhoncus. Vivamus augue lorem, facilisis quis ornare a, consectetur sed est. Phasellus magna nisl, rhoncus vitae tincidunt tempus, dictum vitae dui. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Cras ut erat orci. Suspendisse quis risus vitae turpis aliquet facilisis ac eu diam. Donec ut risus eget leo malesuada feugiat in at nulla. Aenean vel urna sem. Aliquam accumsan ante vitae massa elementum aliquet. Donec dui lacus, lobortis sed fringilla et, vulputate eu nunc. Suspendisse pulvinar tempor rhoncus. Mauris tincidunt hendrerit felis, eu pellentesque sapien rutrum eget. Sed mollis dignissim arcu sed accumsan. Donec laoreet leo ut sapien facilisis porttitor. Vestibulum sit amet augue et erat mattis sollicitudin. Maecenas quis facilisis diam. Vestibulum quis quam in turpis porta fermentum. Donec ut lectus purus. Aliquam erat volutpat. Integer vel mauris ac nisl luctus tincidunt. Donec dapibus venenatis ante, eget blandit lacus lobortis quis. Vestibulum placerat pellentesque aliquam. Mauris vitae turpis sem, tincidunt scelerisque neque. Donec ut erat lorem. Quisque ultricies sapien a ante aliquet eleifend et ut nisi. In at blandit orci. Integer rutrum vestibulum nunc at sagittis. Pellentesque tempus bibendum nibh non venenatis. Aliquam erat volutpat. Aenean nisi risus, pellentesque vel interdum vitae, vulputate quis urna. Donec vulputate mi eu tortor varius id egestas lacus adipiscing. Quisque sagittis venenatis massa sit amet consequat. Phasellus et nunc at quam volutpat consequat id at ante. Curabitur sagittis nulla in metus lobortis ornare. Maecenas lacinia augue non ante interdum facilisis. Donec nibh purus, rhoncus a convallis vel, vehicula nec metus. Vestibulum et nibh dictum quam iaculis cursus quis vel magna. Maecenas lectus est, condimentum nec vestibulum et, rhoncus id diam. Pellentesque quis orci at eros hendrerit tempus at sit amet nibh. Morbi cursus, nibh ut ultrices fringilla, arcu tellus accumsan elit, aliquet lacinia tortor risus ut nulla. Praesent accumsan bibendum tempus. Sed fringilla, diam in viverra ultricies, ipsum arcu lacinia mi, quis condimentum est dui quis lacus. ');
		$item3 = array('code' => 'US', 'language' => 'English', 'name' => 'United States of America', 'capital' => 'Washington, D.C.');
		$item4 = array('code' => 'DE', 'language' => 'Deutsch', 'name' => 'Germany', 'eu_member' => true);
		$item5 = array('code' => 'AT', 'language' => 'Deutsch', 'name' => 'Austria', 'capital' => 'Vienna');
		$item6 = array('code' => 'RO', 'language' => 'Romana', 'name' => 'Romania', 'continent' => 'Europe');
		$item7 = array('code' => 'PO', 'language' => 'Polski', 'name' => 'Poland');
		$item8 = array('code' => 'ES', 'language' => 'Espanol', 'name' => 'Spain', 'capital' => 'Madrid');
		$item9 = array('code' => 'MX', 'language' => 'Espanol', 'name' => 'Mexico', 'continent' => 'North America');
		$item10 = array('code' => 'BR', 'language' => 'Portugues', 'name' => 'Brazil', 'capital' => 'Brasilia');

		$start = microtime(true);
		$result0 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item0);
		$stop = microtime(true);
		print("TEST $i item 0: ID: " . $result0 . "\n");
		print("TEST $i item 0: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 0: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 0: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result1 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item1);
		$stop = microtime(true);
		print("TEST $i item 1: ID: " . $result1 . "\n");
		print("TEST $i item 1: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 1: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 1: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result2 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item2);
		$stop = microtime(true);
		print("TEST $i item 2: ID: " . $result2 . "\n");
		print("TEST $i item 2: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 2: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 2: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result3 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item3);
		$stop = microtime(true);
		print("TEST $i item 3: ID: " . $result3 . "\n");
		print("TEST $i item 3: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 3: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 3: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result4 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item4);
		$stop = microtime(true);
		print("TEST $i item 4: ID: " . $result4 . "\n");
		print("TEST $i item 4: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 4: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 4: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result5 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item5);
		$stop = microtime(true);
		print("TEST $i item 5: ID: " . $result5 . "\n");
		print("TEST $i item 5: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 5: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 5: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result6 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item6);
		$stop = microtime(true);
		print("TEST $i item 6: ID: " . $result6 . "\n");
		print("TEST $i item 6: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 6: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 6: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result7 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item7);
		$stop = microtime(true);
		print("TEST $i item 7: ID: " . $result7 . "\n");
		print("TEST $i item 7: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 7: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 7: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result8 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item8);
		$stop = microtime(true);
		print("TEST $i item 8: ID: " . $result8 . "\n");
		print("TEST $i item 8: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 8: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 8: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result9 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item9);
		$stop = microtime(true);
		print("TEST $i item 9: ID: " . $result9 . "\n");
		print("TEST $i item 9: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 9: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 9: Error message: " . $this->mdb->error_message() . "\n");
		$start = microtime(true);
		$result10 = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $item10);
		$stop = microtime(true);
		print("TEST $i item 10: ID: " . $result10 . "\n");
		print("TEST $i item 10: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i item 10: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i item 10: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get all from collection ci_mdb_test_set, now there should be 10 results\n");
		$start = microtime(true);
		$result = $this->mdb->get($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array());
		$stop = microtime(true);
		print("TEST $i: Items in collection: " . count($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Using update to insert a new item in the collection ci_mdb_test_set, this is optional\n");
		$item11 = array('code' => 'AR', 'language' => 'Espanol', 'name' => 'Argentina', 'capital' => 'Buenos Aires');
		$start = microtime(true);
		$result11 = $this->mdb->update($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array('code' => 'AR'), $item11, false, true);
		$stop = microtime(true);
		print("TEST $i: Result: " . json_encode($result11) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get all from collection ci_mdb_test_set, now there should be 11 results\n");
		$start = microtime(true);
		$result = $this->mdb->get($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array());
		$stop = microtime(true);
		print("TEST $i: Items in collection: " . count($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get one single item by ID from ci_mdb_test_set [$result6]\n");
		$start = microtime(true);
		$result = $this->mdb->getbyid($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, new MongoId($result6));
		$stop = microtime(true);
		print("TEST $i: Data: " . json_encode($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Testing Update append (appending capital = Bucharest to previous test's record)\n");
		$start = microtime(true);
		$resultx = $this->mdb->updatebyid($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, "{$result['_id']}", array('capital' => 'Bucharest'));
		$stop = microtime(true);
		print("TEST $i: Result: " . json_encode($resultx) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Testing Delkey (removing continent = Europe from previous test's record)\n");
		$start = microtime(true);
		$resultx = $this->mdb->delkeybyid($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, "{$result['_id']}", array('continent'));
		$stop = microtime(true);
		print("TEST $i: Result: " . json_encode($resultx) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get one single item from ci_mdb_test_set where [code = RO], (should see capital = Bucharest)\n");
		$start = microtime(true);
		$result = $this->mdb->getfirst($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array('code' => 'RO'));
		$stop = microtime(true);
		print("TEST $i: Data: " . json_encode($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Testing delete by ID (Poland)\n");
		$start = microtime(true);
		$result = $this->mdb->deletebyid($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, "{$result7}");
		$stop = microtime(true);
		print("TEST $i: Result: " . json_encode($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get one single item by ID from ci_mdb_test_set, we're trying to get the previously deleted record so it should fail\n");
		$start = microtime(true);
		$result = $this->mdb->getbyid($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, $result7);
		$stop = microtime(true);
		print("TEST $i: Data: " . json_encode($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get records: [language != English], sort asc by capital, desc by name\n");
		$start = microtime(true);
		$result = $this->mdb->get($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array('language' => array('$ne' => 'English')), array('capital' => 1, 'name' => -1));
		$stop = microtime(true);
		foreach((array)$result as $r) { print("TEST $i: Data: " . json_encode($r) . "\n"); }
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get records: [language = English], sort asc by name limit 2\n");
		$start = microtime(true);
		$result = $this->mdb->get($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array('language' => 'English'), array('name' => 1), array(), 2);
		$stop = microtime(true);
		foreach((array)$result as $r) { print("TEST $i: Data: " . json_encode($r) . "\n"); }
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get records: [language = Espanol], sort desc by name limit 2\n");
		$start = microtime(true);
		$result = $this->mdb->get($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array('language' => 'Espanol'), array('name' => -1), array(), 2);
		$stop = microtime(true);
		foreach((array)$result as $r) { print("TEST $i: Data: " . json_encode($r) . "\n"); }
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Testing update on multiple records, switch language from Espanol to Spanish\n");
		$start = microtime(true);
		$result = $this->mdb->update($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array('language' => 'Espanol'), array('language' => 'Spanish'));
		$stop = microtime(true);
		print("TEST $i: Result: " . json_encode($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Get when returning a cursor, plus we also test the restrict keys functionality, only name and language should be returned, sorted by language and name\n");
		$start = microtime(true);
		$result = $this->mdb->get($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array(), array('language' => 1, 'name' => 1), array('name', 'language'), 0, true);
		$stop = microtime(true);
		print("TEST $i: Result: " . json_encode($result) . "\n");
		foreach($result as $r) { print("TEST $i: Data: " . json_encode($r) . "\n"); }
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Trying to insert inexistent data\n");
		$start = microtime(true);
		$result = $this->mdb->insert($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, array());
		$stop = microtime(true);
		print("TEST $i: Result: " . json_encode($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Trying to run a getbyid using a fake ID\n");
		$start = microtime(true);
		$result = $this->mdb->getbyid($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, "dlkfjdls");
		$stop = microtime(true);
		print("TEST $i: Result: " . json_encode($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("\n----------------------------------------------------------------------------------------------------------------------\n");
		print("TEST $i: Trying to run a getbyid on a random object\n");
		$start = microtime(true);
		$result = $this->mdb->getbyid($this->mdb->{$this->mdb->mongo_friendly}->ci_mdb_test_set, new TestCls);
		$stop = microtime(true);
		print("TEST $i: Result: " . json_encode($result) . "\n");
		print("TEST $i: Query took: " .  number_format($stop - $start, 4) . "sec\n");
		print("TEST $i: Error is " . (($this->mdb->check_error()) ? 'TRUE' : 'FALSE') . "\n");
		print("TEST $i: Error message: " . $this->mdb->error_message() . "\n");
		$i++;

		print("</pre>");
		exit;
	}
}
