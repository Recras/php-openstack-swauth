<?php declare(strict_types=1);

namespace Recras\OpenStack\Identity\Swauth\Models;

use OpenStack\Common\Resource\OperatorResource;
use Psr\Http\Message\ResponseInterface;
use Recras\OpenStack\Identity\Swauth\Models\ServiceCatalog;

class Token extends OperatorResource implements \OpenStack\Common\Auth\Token
{
    const TOKEN_HEADER = 'X-Auth-Token';
    const TOKEN_EXPIRES_HEADER = 'X-Auth-Token-Expires';

    public $id;
    public $expires;
    public $catalog;

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
        $this->id = $data['id'];
        $this->expires = new \DateTimeImmutable($data['expires']);
        $this->catalog = $this->model(ServiceCatalog::class, $data['catalog']);

        return $this;
    }

    public function populateFromResponse(ResponseInterface $response): self
    {
        $this->id = $response->getHeaderLine(self::TOKEN_HEADER);
        $expires_seconds = $response->getHeaderLine(self::TOKEN_EXPIRES_HEADER);
        $this->expires = (new \DateTimeImmutable)->add(new \DateInterval('PT' . $expires_seconds . 'S'));
        $this->catalog = $this->model(ServiceCatalog::class, $response);

        return $this;
    }

    /**
     * Returns a serialized representation of an authentication token.
     *
     * Initialize OpenStack object using $params['cachedToken'] to reduce the amount of HTTP calls.
     *
     * @return array
     */
    public function export(): array
    {
        return [
            'id' => $this->id,
            'expires' => $this->expires->format('c'),
            'catalog' => $this->catalog->export(),
        ];
    }
}
