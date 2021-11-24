<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Server\App\Dispatcher;

use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

interface TopicDispatcherInterface
{
    public function onSubscribe(ConnectionInterface $conn, Topic $topic, WampRequest $request): void;

    public function onUnSubscribe(ConnectionInterface $conn, Topic $topic, WampRequest $request): void;

    public function onPublish(
        ConnectionInterface $conn,
        Topic $topic,
        WampRequest $request,
        string|array $event,
        array $exclude,
        array $eligible
    ): void;
}
