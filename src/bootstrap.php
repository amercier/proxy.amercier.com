<?php 
/**
 * Application initialization
 */

// Bootstrapping
chdir(__DIR__);
$loader = require_once 'vendor/autoload.php';

// Constants
if(!defined('APPLICATION_ENV')) {
  define('APPLICATION_ENV', getenv('APPLICATION_ENV') === 'false' ? 'production' : getenv('APPLICATION_ENV'));
}
if(APPLICATION_ENV === 'development') {
  define('MIN', '');
}
else {
  define('MIN', '.min');
}

// Config
$config = new Zend\Config\Config(include 'config/application.config.php', true);
foreach(array_diff(scandir('config/autoload'), array('.','..')) as $filename) {
  if(preg_match('/\.php$/', $filename)) {
    $config->merge(new Zend\Config\Config(include 'config/autoload/' . $filename));
  } 
}