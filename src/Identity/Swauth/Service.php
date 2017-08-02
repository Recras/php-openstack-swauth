<?php declare(strict_types=1);

namespace Recras\OpenStack\Identity\Swauth;

use Recras\OpenStack\Identity\Swauth\Models\Token;
use Recras\OpenStack\Identity\Swauth\Models\ServiceCatalog;

use GuzzleHttp\ClientInterface;
use OpenStack\Common\Service\AbstractService;
use OpenStack\Common\Auth\IdentityService;
use OpenStack\Common\Api\Operation;
use OpenStack\Common\Api\ApiInterface;

class Service extends AbstractService implements IdentityService
{
    const TOKEN_DEFINITION = [
        'method' => 'GET',
        'path' => 'auth/v1.0',
        'params' => [
            'username' => [
                'location' => 'header',
                'sentAs' => 'X-Auth-User',
                'type' => 'string',
                'required' => true,
            ],
            'key' => [
                'location' => 'header',
                'sentAs' => 'X-Auth-Key',
                'type' => 'string',
                'required' => true,
            ],
        ],
    ];

    public static function factory(ClientInterface $client): self
    {
        return new static($client, new Api());
    }

    /**
     * @inheritdoc
     */
    public function authenticate(array $values): array
    {
        $response = $this->execute(self::TOKEN_DEFINITION, array_intersect_key($values, self::TOKEN_DEFINITION['params']));

        $token = $this->model(Token::class, $response);

        $serviceCatalog = $this->model(ServiceCatalog::class, $response);
        return [
            $token,
            $serviceCatalog->base_uri,
        ];
    }
}
