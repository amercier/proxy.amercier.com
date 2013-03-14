<?php
use Zend\Http\Request;
use Zend\Http\Client;

try {
  require_once realpath(__DIR__ . '/bootstrap.php');

  // Check GET variables
  foreach($_GET as $key => $variable) {
    if(!in_array($key, $config->allowedParams->toArray())) {
      throw new Exception('Unexpected parameter "' . $key . '" found in URL. Please replace all & with %26');
    }
  }

  $inputRequest = new Zend\Http\PhpEnvironment\Request();

  // Detect base path
  $basePath = $inputRequest->getBasePath() == ''
    ? ($inputRequest->getUri()->getHost()==='local'?'/proxy.amercier.com/':'/')
    : $inputRequest->getBasePath().'/';

  // request uri - base path = url
  $url = preg_replace('/^'.preg_quote($basePath,'/').'(index\.php\/)?/', '', $inputRequest->getRequestUri());

  // Remove all custom variables
  foreach($_GET as $key => $variable) {
    $url = preg_replace('/(\?|&)'.$key.'(=[^\?|&]*)?/', '', $url);
  }

  // Send the request
  $request = new Request();
  $request->setUri($url);

  $client = new Client();
  $response = $client->dispatch($request);

  $output = array(
    'ok' => $response->isSuccess(),
    'requestUri' => $inputRequest->getRequestUri(),
    'basePath' => $basePath,
    'url' => $url,
    'headers' => $response->getHeaders(),
    'body' => $response->getBody(),
  );
}
catch(Exception $e) {
  $output = array(
    'ok' => false,
    'requestUri' => $inputRequest->getRequestUri(),
    'basePath' => $basePath,
    'url' => $url,
    'error' => $e->getMessage()
  );
}

if(isset($_GET['json']) || !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  header('Content-Type: application/json');
  if(isset($_GET['format'])) {
    echo Zend\Json\Json::prettyPrint(\Zend\Json\Json::encode($output));
  }
  else {
    echo Zend\Json\Json::encode($output);
  }
}
else {
  return $output;
}