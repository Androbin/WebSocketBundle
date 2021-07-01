<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Client\Auth;

use Gos\Bundle\WebSocketBundle\Client\ClientStorageInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ratchet\ConnectionInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class WebsocketAuthenticationProvider implements WebsocketAuthenticationProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ClientStorageInterface $clientStorage;

    /**
     * @var string[]
     */
    private array $firewalls;

    /**
     * @param string[] $firewalls
     */
    public function __construct(ClientStorageInterface $clientStorage, array $firewalls = [])
    {
        if (empty($firewalls)) {
            $firewalls = ['main'];
        }

        $this->clientStorage = $clientStorage;
        $this->firewalls = $firewalls;
    }

    public function authenticate(ConnectionInterface $conn): TokenInterface
    {
        $token = $this->getToken($conn);

        $identifier = $this->clientStorage->getStorageId($conn);

        $this->clientStorage->addClient($identifier, $token);

        $this->logger?->info(
            sprintf(
                '%s connected',
                method_exists($token, 'getUserIdentifier') ? $token->getUserIdentifier() : $token->getUsername()
            ),
            [
                'connection_id' => $conn->resourceId,
                'session_id' => $conn->WAMP->sessionId,
                'storage_id' => $identifier,
            ]
        );

        return $token;
    }

    private function getToken(ConnectionInterface $connection): TokenInterface
    {
        $token = null;

        if (isset($connection->Session) && $connection->Session) {
            foreach ($this->firewalls as $firewall) {
                if (false !== $serializedToken = $connection->Session->get('_security_'.$firewall, false)) {
                    /** @var TokenInterface $token */
                    $token = unserialize($serializedToken);

                    break;
                }
            }
        }

        if (null === $token) {
            $token = new AnonymousToken($this->firewalls[0], 'anon-'.$connection->WAMP->sessionId);
        }

        return $token;
    }
}
