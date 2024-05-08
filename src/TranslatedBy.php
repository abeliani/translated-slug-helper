<?php

declare(strict_types=1);

namespace Abeliani\TranslatedSlugHelper;

use Abeliani\StringTranslator\Drivers\Core\DriverInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

class TranslatedBy
{
    private DriverInterface $driver;
    private ?DriverInterface $proxySuccessor = null;

    /**
     * @throws \ReflectionException
     */
    public function __construct(string $driverClass, array $driverOpts = [])
    {
        if (!class_exists($driverClass)) {
            throw new \LogicException(sprintf('Driver class not found: %s', $driverClass));
        }

        $reflector = new \ReflectionClass($driverClass);
        $params = $reflector->getConstructor()->getParameters();

        foreach ($params as $param) {
            switch ($param->getType()->getName()) {
                case ClientInterface::class:
                    $args[] = new Client();
                    break;
                case RequestInterface::class:
                    $args[] = new Request('GET', new Uri());
                    break;
                case DriverInterface::class:
                    $args[] = $this->proxySuccessor = new ProxyDriver();
                    break;
                default:
                    if (!array_key_exists($param->getName(), $driverOpts)) {
                        if ($param->isDefaultValueAvailable()) {
                            $args[] = $param->getDefaultValue();
                            break;
                        }
                        if ($param->allowsNull()) {
                            $args[] = null;
                            break;
                        }

                        $msg = sprintf('Driver `%s` has no argument `%s`', $driverClass, $param->getName());
                        throw new \LogicException($msg);
                    }

                    $args[] = $driverOpts[$param->getName()];
            }
        }

        $this->driver = $reflector->newInstanceArgs($args ?? []);
    }

    public function getDriverInstance(): DriverInterface
    {
        return $this->driver;
    }

    public function getDriverProxySuccessorInstance(): ?ProxyDriver
    {
        return $this->proxySuccessor;
    }
}
