<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Server\App\Registry;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
final class OriginRegistry
{
    /**
     * @var string[]
     */
    private array $origins = [];

    /**
     * @param iterable<string> $origins
     */
    public function __construct(iterable $origins = [])
    {
        foreach ($origins as $origin) {
            $this->addOrigin($origin);
        }
    }

    public function addOrigin(string $origin): void
    {
        $this->origins[] = $origin;
    }

    /**
     * @return string[]
     */
    public function getOrigins(): array
    {
        return $this->origins;
    }
}
