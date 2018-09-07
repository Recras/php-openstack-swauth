<?php
/**
 * Based on https://php-opencloudopenstack.readthedocs.io/en/latest/services/identity/v3/tokens.html?#initialize-open-stack-using-cached-authentication-token
 */
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

$identidyService = SwauthService::factory($httpClient);
$options = [
    'authUrl' => $base_uri,
    'username' => 'test:tester',
    'key' => 'testing',
    'identityService' => $identityService,
];

$token = json_decode(file_get_contents('token.json'), true);

// Inject cached token to params if token is still fresh
if ((new \DateTimeImmutable($token['expires'])) > (new \DateTimeImmutable('now'))) {
    $options['cachedToken'] = $token;
}

$openstack = new OpenStack($options);
