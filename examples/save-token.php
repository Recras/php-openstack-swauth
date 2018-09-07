<?php
/**
 * Based on https://php-opencloudopenstack.readthedocs.io/en/latest/services/identity/v3/tokens.html?#generate-token-and-persist-to-file
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

$openstack = new OpenStack($options);
$token = $identidyService->generateToken($openstackOptions);

// Display token expiry
echo sprintf('Token expires at %s' . PHP_EOL, $token->expires->format('c'));

// Save token to file
file_put_contents('token.json', json_encode($token->export()));
