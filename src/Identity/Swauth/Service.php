<?php declare(strict_types=1);

namespace Recras\OpenStack\Identity\Swauth;

use GuzzleHttp\ClientInterface;
use OpenStack\Common\Auth\IdentityService;
use OpenStack\Common\Service\AbstractService;
use Recras\OpenStack\Identity\Swauth\Models\ServiceCatalog;
use Recras\OpenStack\Identity\Swauth\Models\Token;

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

        if (!empty($values['cachedToken'])) {
            return $this->reuseToken($values['cachedToken']);
        } else {
            return $this->createNewToken($values);
        }
    }

    private function reuseToken(array $cachedToken): array
    {
        $token = $this->generateTokenFromCache($cachedToken);

        if ($token->hasExpired()) {
            throw new \RuntimeException(sprintf('Cached token has expired on "%s".', $token->expires->format(\DateTime::ISO8601)));
        }
        $base_uri = $token->catalog->base_uri;

        return [
            $token,
            $base_uri,
        ];
    }

    private function createNewToken(array $values): array
    {
        $response = $this->execute(self::TOKEN_DEFINITION, array_intersect_key($values, self::TOKEN_DEFINITION['params']));
        $token = $this->model(Token::class, $response);
        $base_uri = ($this->model(ServiceCatalog::class, $response))->base_uri;

        return [
            $token,
            $base_uri,
        ];
    }

    /**
     * Generates authentication token from cached token using `$token->export()`
     *
     * @param array $cachedToken {@see \Recras\OpenStack\Identity\Swauth\Models\Token::export}
     *
     * @return Token
     */
    public function generateTokenFromCache(array $cachedToken): Token
    {
        return $this->model(Token::class)->populateFromArray($cachedToken);
    }

    /**
     * Generates a new authentication token
     *
     * @param array $options {@see params from TOKEN_DEFINITION}
     *
     * @return Token
     */
    public function generateToken(array $options = []): Token
    {
        $response = $this->execute(self::TOKEN_DEFINITION, $options);
        return $this->model(Token::class, $response);
    }
}
