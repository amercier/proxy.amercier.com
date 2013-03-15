<?php
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Http\Response;

try {
  require_once realpath(__DIR__ . '/bootstrap.php');

  // ---------------------------------------------------------------------------
  // Check arguments
  // ---------------------------------------------------------------------------

  // Check GET variables
  foreach($_GET as $key => $variable) {
    if(!in_array($key, $config->allowedParams->toArray())) {
      throw new Exception('Unexpected parameter "' . $key . '" found in URL. Please replace all & with %26', 400);
    }
  }

  $inputRequest = new Zend\Http\PhpEnvironment\Request();

  // Detect base path
  $basePath = $inputRequest->getBasePath() == ''
    ? ($inputRequest->getUri()->getHost()==='local'?'/proxy.amercier.com/':'/')
    : $inputRequest->getBasePath().'/';

  $pattern = '/^'.preg_quote($basePath,'/').'/';
  if(!preg_match($pattern, $inputRequest->getRequestUri())) {
    $pattern = '/^'.preg_quote($basePath,'/').'/';
  }

  // Clean url
  $url = $inputRequest->getRequestUri();
  foreach(explode('/',trim($basePath,'/').'/index.php/') as $pathItem) {
    $url = preg_replace('/^\/'.preg_quote($pathItem,'/').'/', '', $url);
  }

  // Remove all custom variables
  foreach($_GET as $key => $variable) {
    $url = preg_replace('/(\?|&)'.$key.'(=[^\?|&]*)?/', '', $url);
  }

  $uri = new Zend\Uri\Uri($url);
  if(!$uri->isValid()) {
    throw new Exception('Invalid URL "' . $url . '"', 400);
  }

  // Referer
  $referer = $inputRequest->getHeader('Referer');
  if($referer === false) {
    throw new Exception('Missing "Referer" HTTP header', 400);
  }
  try {
    $referer = new Zend\Uri\Uri($referer->getFieldValue());
  }
  catch(Exception $e) {
    throw new Exception('Invalid Referer "' . $referer . '": ' . $e->getMessage(), 400);
  }

  // ---------------------------------------------------------------------------
  // Check Referer
  // ---------------------------------------------------------------------------

  // 1. Check that Referer is in config.allowedReferers

  $refererValid = false;
  foreach($config->allowedReferers as $regexp) {
    if(preg_match($regexp, $referer->getHost())) {
      $refererValid = true;
    }
  }
  if(!$refererValid) {
    throw new Exception('Referer "' . $referer->getHost() . '" is not allowed', 400);
  }

  // 2. Ask for a <referer>/proxy.json file, check hosts contains the requested host

  //var_dump($inputRequest->getHeaders()->toArray());

  $refererHeaderName = $inputRequest->getHeaders()->has($config->refererConfigHeaderName)
    ? $inputRequest->getHeaders()->get($config->refererConfigHeaderName)->getFieldValue()
    : '/proxy.json';

  $refererRequest = new Request();
  $refererRequest->setUri($referer->getScheme() . '://' . $referer->getHost() . ($referer->getPort() === null ? '' : ':' . $referer->getPort()) . $refererHeaderName);
  $client = new Client();
  $refererResponse = $client->dispatch($refererRequest);
  if($refererResponse->getStatusCode() === 404) {
    throw new Exception('Referer configuration is missing at "' . $refererRequest->getUri() . '"');
  }

  try {
    $refererConfig = Zend\Json\Json::decode($refererResponse->getBody());
  }
  catch(Exception $e) {
    throw new Exception('Referer configuration should be in JSON format at "' . $refererRequest->getUri() . '"');
  }

  if(!isset($refererConfig->hosts)) {
    throw new Exception('Missing "hosts" in referer configuration file at ' . $refererRequest->getUri());
  }
  if(!in_array($uri->getHost(), $refererConfig->hosts)) {
    throw new Exception('Host "' . $uri->getHost() . '" is not allowed by ' . $refererRequest->getUri());
  }

  // ---------------------------------------------------------------------------
  // Send the request
  // ---------------------------------------------------------------------------

  $request = new Request();
  $request->setUri($url);

  $forbiddenHeaders = $config->forbiddenHeaders->toArray();
  foreach($inputRequest->getHeaders() as $header) {
    if(!in_array(strtolower($header->getFieldName()), $forbiddenHeaders)) {
      $request->getHeaders()->addHeader($header);
    }
  }

  switch($inputRequest->getMethod()) {
    case Request::METHOD_OPTIONS : break;
    case Request::METHOD_GET     : break;
    case Request::METHOD_HEAD    : break;
    case Request::METHOD_POST    : $request->setPost($inputRequest->getPost()); break;
    case Request::METHOD_PUT     : break;
    case Request::METHOD_DELETE  : break;
    case Request::METHOD_TRACE   : break;
    case Request::METHOD_CONNECT : break;
    case Request::METHOD_PATCH   : break;
    case Request::METHOD_PROPFIND: break;
  }
  $request->setMethod($inputRequest->getMethod());

  $client = new Client();
  $response = $client->dispatch($request);

  $output = array(
    'ok' => $response->isSuccess(),
    'requestUri' => $inputRequest->getRequestUri(),
    'basePath' => $basePath,
    'url' => $url,
    'method' => $request->getMethod(),
    'inputRequestHeaders' => $inputRequest->getHeaders()->toArray(),
    'requestHeaders' => $request->getHeaders()->toArray(),
    'headers' => $response->getHeaders()->toArray(),
    'body' => $response->getBody(),
  );
}
catch(Exception $e) {
  $response = new Response();
  $response->setStatusCode($e->getCode() === 0 ? 500 : $e->getCode());
  $response->setContent($e->getMessage());
}

if(isset($_GET['json'])) {

  header('Content-Type: application/json');

  $output = array(
    'ok' => $response->isSuccess(),
    'requestUri' => $inputRequest->getRequestUri(),
    'basePath' => $basePath,
    'url' => $url,
    'method' => $request->getMethod(),
    'inputRequestHeaders' => $inputRequest->getHeaders()->toArray(),
    'requestHeaders' => $request->getHeaders()->toArray(),
    'status' => $response->renderStatusLine(),
    'headers' => $response->getHeaders()->toArray(),
    'body' => $response->getBody(),
  );

  if(isset($_GET['format'])) {
    echo Zend\Json\Json::prettyPrint(\Zend\Json\Json::encode($output));
  }
  else {
    echo Zend\Json\Json::encode($output);
  }
}
else {
  header($response->renderStatusLine());
  foreach($response->getHeaders() as $header) {
    if(!in_array(strtolower($header->getFieldName()), $forbiddenHeaders)) {
      header($header->toString());
    }
  }
  echo $response->getBody();
}