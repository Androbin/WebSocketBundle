<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Tests\DependencyInjection;

use Gos\Bundle\WebSocketBundle\DependencyInjection\Configuration;
use Gos\Bundle\WebSocketBundle\DependencyInjection\Factory\Authentication\SessionAuthenticationProviderFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration([]), []);

        self::assertEquals(self::getBundleDefaultConfig(), $config);
    }

    public function testConfigWithSessionAuthenticationProviderWithDefaultConfig(): void
    {
        $extraConfig = [
            'authentication' => [
                'providers' => [
                    'session' => [
                        'session_handler' => null,
                        'firewalls' => null,
                    ],
                ],
                'storage' => [
                    'type' => Configuration::AUTHENTICATION_STORAGE_TYPE_IN_MEMORY,
                    'pool' => null,
                    'id' => null,
                ],
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([new SessionAuthenticationProviderFactory()]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithSessionAuthenticationProviderWithArrayOfFirewalls(): void
    {
        $extraConfig = [
            'authentication' => [
                'providers' => [
                    'session' => [
                        'session_handler' => null,
                        'firewalls' => [
                            'dev',
                            'main',
                        ],
                    ],
                ],
                'storage' => [
                    'type' => Configuration::AUTHENTICATION_STORAGE_TYPE_IN_MEMORY,
                    'pool' => null,
                    'id' => null,
                ],
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([new SessionAuthenticationProviderFactory()]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithSessionAuthenticationProviderWithStringFirewall(): void
    {
        $extraConfig = [
            'authentication' => [
                'providers' => [
                    'session' => [
                        'session_handler' => null,
                        'firewalls' => 'main',
                    ],
                ],
                'storage' => [
                    'type' => Configuration::AUTHENTICATION_STORAGE_TYPE_IN_MEMORY,
                    'pool' => null,
                    'id' => null,
                ],
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([new SessionAuthenticationProviderFactory()]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithSessionAuthenticationProviderWithInvalidFirewallType(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "gos_web_socket.authentication.providers.session.firewalls": The firewalls node must be an array, a string, or null');

        $extraConfig = [
            'authentication' => [
                'providers' => [
                    'session' => [
                        'firewalls' => true,
                    ],
                ],
                'storage' => [
                    'type' => Configuration::AUTHENTICATION_STORAGE_TYPE_IN_MEMORY,
                    'pool' => null,
                    'id' => null,
                ],
            ],
        ];

        (new Processor())->processConfiguration(new Configuration([new SessionAuthenticationProviderFactory()]), [$extraConfig]);
    }

    public function testConfigWithAuthenticationStorageUsingPsrCache(): void
    {
        $extraConfig = [
            'authentication' => [
                'storage' => [
                    'type' => Configuration::AUTHENTICATION_STORAGE_TYPE_PSR_CACHE,
                    'pool' => 'cache.websocket',
                    'id' => null,
                ],
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithAuthenticationStorageUsingServiceStorage(): void
    {
        $extraConfig = [
            'authentication' => [
                'storage' => [
                    'type' => Configuration::AUTHENTICATION_STORAGE_TYPE_SERVICE,
                    'pool' => null,
                    'id' => 'app.authentication.storage.driver.custom',
                ],
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithAuthenticationStorageUsingPsrCacheAndNoCachePoolConfigured(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "gos_web_socket.authentication.storage": A cache pool must be set when using the PSR cache storage');

        $extraConfig = [
            'authentication' => [
                'storage' => [
                    'type' => Configuration::AUTHENTICATION_STORAGE_TYPE_PSR_CACHE,
                ],
            ],
        ];

        (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);
    }

    public function testConfigWithAuthenticationStorageUsingServiceStorageAndNoIdConfigured(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "gos_web_socket.authentication.storage": A service ID must be set when using the service storage');

        $extraConfig = [
            'authentication' => [
                'storage' => [
                    'type' => Configuration::AUTHENTICATION_STORAGE_TYPE_SERVICE,
                ],
            ],
        ];

        (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);
    }

    public function testConfigWithAuthenticationStorageUsingUnsupportedStorageType(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The value "unsupported" is not allowed for path "gos_web_socket.authentication.storage.type". Permissible values: "in_memory", "psr_cache", "service"');

        $extraConfig = [
            'authentication' => [
                'storage' => [
                    'type' => 'unsupported',
                ],
            ],
        ];

        (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);
    }

    public function testConfigWithAServer(): void
    {
        $extraConfig = [
            'server' => [
                'host' => '127.0.0.1',
                'port' => 8080,
                'tls' => [
                    'enabled' => false,
                    'options' => [],
                ],
                'origin_check' => false,
                'ip_address_check' => false,
                'keepalive_ping' => false,
                'keepalive_interval' => 30,
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithAServerAndPubSubRouterWithoutArrayResources(): void
    {
        $extraConfig = [
            'server' => [
                'host' => '127.0.0.1',
                'port' => 8080,
                'origin_check' => false,
                'ip_address_check' => false,
                'keepalive_ping' => false,
                'keepalive_interval' => 30,
                'router' => [
                    'resources' => [
                        'example.yaml',
                    ],
                ],
            ],
        ];

        $normalizedExtraConfig = [
            'server' => [
                'host' => '127.0.0.1',
                'port' => 8080,
                'tls' => [
                    'enabled' => false,
                    'options' => [],
                ],
                'origin_check' => false,
                'ip_address_check' => false,
                'keepalive_ping' => false,
                'keepalive_interval' => 30,
                'router' => [
                    'resources' => [
                        [
                            'resource' => 'example.yaml',
                            'type' => null,
                        ],
                    ],
                ],
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $normalizedExtraConfig),
            $config
        );
    }

    public function testConfigWithAServerAndPubSubRouterWithArrayResources(): void
    {
        $extraConfig = [
            'server' => [
                'host' => '127.0.0.1',
                'port' => 8080,
                'tls' => [
                    'enabled' => false,
                    'options' => [],
                ],
                'origin_check' => false,
                'ip_address_check' => false,
                'keepalive_ping' => false,
                'keepalive_interval' => 30,
                'router' => [
                    'resources' => [
                        [
                            'resource' => 'example.yaml',
                            'type' => null,
                        ],
                    ],
                ],
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithServerAndTlsEnabled(): void
    {
        $extraConfig = [
            'server' => [
                'host' => '127.0.0.1',
                'port' => 8080,
                'tls' => [
                    'enabled' => true,
                    'options' => [
                        'verify_peer' => false,
                    ],
                ],
                'origin_check' => false,
                'ip_address_check' => false,
                'keepalive_ping' => false,
                'keepalive_interval' => 30,
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithAllowedOriginsList(): void
    {
        $extraConfig = [
            'server' => [
                'host' => '127.0.0.1',
                'port' => 8080,
                'tls' => [
                    'enabled' => false,
                    'options' => [],
                ],
                'origin_check' => true,
                'ip_address_check' => false,
                'keepalive_ping' => false,
                'keepalive_interval' => 30,
            ],
            'origins' => [
                'websocket-bundle.localhost',
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithInvalidOriginsList(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "gos_web_socket.origins.0": "localhost" is added by default');

        $extraConfig = [
            'server' => [
                'host' => '127.0.0.1',
                'port' => 8080,
                'origin_check' => true,
                'ip_address_check' => false,
                'keepalive_ping' => false,
                'keepalive_interval' => 30,
            ],
            'origins' => [
                'localhost',
                'websocket-bundle.localhost',
            ],
        ];

        (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);
    }

    public function testConfigWithBlockedIpAddressList(): void
    {
        $extraConfig = [
            'server' => [
                'host' => '127.0.0.1',
                'port' => 8080,
                'tls' => [
                    'enabled' => false,
                    'options' => [],
                ],
                'origin_check' => false,
                'ip_address_check' => true,
                'keepalive_ping' => false,
                'keepalive_interval' => 30,
            ],
            'blocked_ip_addresses' => [
                '192.168.1.1',
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithPingServices(): void
    {
        $extraConfig = [
            'ping' => [
                'services' => [
                    [
                        'name' => 'doctrine_service',
                        'type' => Configuration::PING_SERVICE_TYPE_DOCTRINE,
                        'interval' => 30,
                    ],
                    [
                        'name' => 'pdo_service',
                        'type' => Configuration::PING_SERVICE_TYPE_PDO,
                        'interval' => 15,
                    ],
                ],
            ],
        ];

        $config = (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);

        self::assertEquals(
            array_merge(self::getBundleDefaultConfig(), $extraConfig),
            $config
        );
    }

    public function testConfigWithUnsupportedPingServiceType(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The value "no_support" is not allowed for path "gos_web_socket.ping.services.0.type". Permissible values: "doctrine", "pdo"');

        $extraConfig = [
            'ping' => [
                'services' => [
                    [
                        'name' => 'no_support_service',
                        'type' => 'no_support',
                    ],
                ],
            ],
        ];

        (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);
    }

    public function testConfigWithInvalidPingInterval(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The value 0 is too small for path "gos_web_socket.ping.services.0.interval".');

        $extraConfig = [
            'ping' => [
                'services' => [
                    [
                        'name' => 'doctrine_service',
                        'type' => Configuration::PING_SERVICE_TYPE_DOCTRINE,
                        'interval' => 0,
                    ],
                ],
            ],
        ];

        (new Processor())->processConfiguration(new Configuration([]), [$extraConfig]);
    }

    protected static function getBundleDefaultConfig(): array
    {
        return [
            'authentication' => [
                'storage' => [
                    'type' => Configuration::AUTHENTICATION_STORAGE_TYPE_IN_MEMORY,
                    'pool' => null,
                    'id' => null,
                ],
            ],
            'server' => [
                'tls' => [
                    'enabled' => false,
                    'options' => [],
                ],
                'origin_check' => false,
                'ip_address_check' => false,
                'keepalive_ping' => false,
                'keepalive_interval' => 30,
            ],
            'origins' => [],
            'blocked_ip_addresses' => [],
        ];
    }
}
