<?php

return array(

	// Typical Database configuration
	'pdo/sqlite' => array(
		'dsn' => 'sqlite:/'.realpath(__DIR__.'/..').'/ci_test.sqlite',
		'dbdriver' => 'pdo',
		'pdodriver' => 'sqlite',
	),

	// Database configuration with failover
	'pdo/sqlite_failover' => array(
		'dsn' => 'sqlite:/'.realpath(__DIR__.'/..').'/not_exists.sqlite',
		'dbdriver' => 'pdo',
		'pdodriver' => 'sqlite',
		'failover' => array(
			array(
				'dsn' => 'sqlite:/'.realpath(__DIR__.'/..').'/ci_test.sqlite',
				'dbdriver' => 'pdo',
				'pdodriver' => 'sqlite',
			),
		),
	),
);