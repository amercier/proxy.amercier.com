<?php
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Http\Response;

try {
  require_once realpath(__DIR__ . '/bootstrap.php');

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

  // Send the request
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