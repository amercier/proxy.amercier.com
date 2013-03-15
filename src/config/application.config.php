<?php
return array(
  'allowedParams' => array(
    'json',
    'format',
  ),
  'forbiddenHeaders' => array(
    'host',
    'connection',
    'content-length',
    'content-encoding',
    'transfer-encoding',
  ),
  'allowedReferers' => array(
    '/^local$/', // Local VM/server, just add "<vm ip address> local" to your hosts file
    '/^localhost$/',
    '/^127.0.0.1$/',
    '/\.amercier\.com$/',
  ),
);