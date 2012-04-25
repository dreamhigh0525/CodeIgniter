<?php

class Directory_helper_test extends CI_TestCase {
	
	public function set_up()
	{
		$this->helper('directory');

		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('testDir'));
		
		$this->_test_dir = vfsStreamWrapper::getRoot();
	}	
	
	public function test_directory_map()
	{
		$structure = array('libraries' => array('benchmark.html' => '', 'database' =>
			array('active_record.html' => '', 'binds.html' => ''), 'email.html' => '', '.hiddenfile.txt' => ''));
		
		vfsStream::create($structure, $this->_test_dir);

		// test default recursive behavior
		$expected = array('libraries' => array('benchmark.html', 'database' =>
			array('active_record.html', 'binds.html'), 'email.html'));
			
		$this->assertEquals($expected, directory_map(vfsStream::url('testDir')));

		// test recursion depth behavior
		$expected = array('libraries');
			
		$this->assertEquals($expected, directory_map(vfsStream::url('testDir'), 1));

		// test detection of hidden files
		$expected = array('libraries' => array('benchmark.html', 'database' =>
			array('active_record.html', 'binds.html'), 'email.html', '.hiddenfile.txt'));
			
		$this->assertEquals($expected, directory_map(vfsStream::url('testDir'), FALSE, TRUE));
  }  
}

/* End of file directory_helper_test.php */