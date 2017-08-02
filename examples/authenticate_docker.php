<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use OpenStack\Common\Transport\Utils as TransportUtils;
use OpenStack\OpenStack;

use Recras\OpenStack\Identity\Swauth\Service as SwauthService;

$base_uri = 'http://127.0.0.1:8090/';

$httpClient = new Client([
    'base_uri' => TransportUtils::normalizeUrl($base_uri),
    'handler' => HandlerStack::create(),
]);

$options = [
    'authUrl' => $base_uri,
    'username' => 'test:tester',
    'key' => 'testing',
    'identityService' => SwauthService::factory($httpClient),
];

$openstack = new OpenStack($options);
try {
    foreach ($openstack->objectStoreV1()->listContainers() as $key => $value) {
        var_dump([$key => $value]);
    }
} catch (GuzzleHttp\Exception\RequestException $e) {
    var_dump([$e->getResponseBodySummary($e->getResponse()), $e->getRequest()]);
}
