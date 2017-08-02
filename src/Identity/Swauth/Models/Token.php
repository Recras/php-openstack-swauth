<?php declare(strict_types=1);

namespace Recras\OpenStack\Identity\Swauth\Models;

use OpenStack\Common\Resource\OperatorResource;
use Psr\Http\Message\ResponseInterface;

class Token extends OperatorResource implements \OpenStack\Common\Auth\Token
{
    const TOKEN_HEADER = 'X-Auth-Token';
    const TOKEN_EXPIRES_HEADER = 'X-Auth-Token-Expires';

    public $id;

    public $expires;

    public function getId(): string
    {
        return $this->id;
    }

    public function hasExpired(): bool
    {
        return $this->expires <= new \DateTimeImmutable('now');
    }

    public function populateFromArray(array $data): self
    {
        throw new BadMethodCallException(self::class . '::populateFromArray: not implemented');
    }

    public function populateFromResponse(ResponseInterface $response): self
    {
        $this->id = $response->getHeaderLine(self::TOKEN_HEADER);
        $expires_seconds = $response->getHeaderLine(self::TOKEN_EXPIRES_HEADER);
        $this->expires = (new \DateTimeImmutable)->add(new \DateInterval('PT' . $expires_seconds . 'S'));

        return $this;
    }
}
