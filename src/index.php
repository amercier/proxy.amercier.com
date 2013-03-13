<?php
try {
  require_once realpath(__DIR__ . '/bootstrap.php');
  
  $output = array(
    'ok' => true,
  );
}
catch(Exception $e) {
  $output = array(
    'ok' => false,
    'error' => $e->getMessage()
  );
}

if(isset($_GET['json']) || !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  header('Content-Type: application/json');
  echo \Zend\Json\Json::encode($output);
}
else {
  return $output;
}