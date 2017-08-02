<?php declare(strict_types=1);

namespace Recras\OpenStack\Identity\Swauth\Models;

use OpenStack\Common\Auth\Catalog;
use OpenStack\Common\Resource\OperatorResource;
use Psr\Http\Message\ResponseInterface;

class ServiceCatalog extends OperatorResource implements Catalog
{
    public $base_uri;

    public function getServiceUrl(string $name, string $type, string $region, string $urlType): string
    {
        if ($type !== 'object-store') {
            throw new \RuntimeException('Swauth only supports "object-store" services');
        }
        return $this->base_uri;
    }
    public function populateFromArray(array $data): self
    {
        throw new BadMethodCallException(self::class . '::populateFromArray: not implemented');
    }

    public function populateFromResponse(ResponseInterface $resp): self
    {
        $this->base_uri = $resp->getHeaderLine('X-Storage-Url');
        return $this;
    }
}

