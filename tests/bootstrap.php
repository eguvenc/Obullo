<?php

// Prevent session cookies
ini_set('session.use_cookies', 0);

// Enable Composer autoloader
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

// require dirname(__FILE__) . '/getallheaders.php';
// 
// Register test classes
$autoloader->addPsr4('Tests\\', 'tests/');