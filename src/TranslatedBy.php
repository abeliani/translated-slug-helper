<?php

/**
 * This file is part of the TranslatedSlugHelper Project.
 *
 * @package     TranslatedSlugHelper
 * @author      Anatolii Belianin <belianianatoli@gmail.com>
 * @license     See LICENSE.md for license information
 * @link        https://github.com/abeliani/translated-slug-helper
 */

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
    private ?ProxyDriver $proxySuccessor = null;

    /**
     * Creating instance of StringTranslator driver with implemented Psr interfaces
     * and proxying successor of driver to make chain of driver calls
     *
     * @param string $driverClass namespace of StringTranslator driver
     * @param array $driverOpts the driver options
     *
     * @throws \ReflectionException|\LogicException
     */
    public function __construct(string $driverClass, array $driverOpts = [])
    {
        if (!class_exists($driverClass)) {
            throw new \LogicException(sprintf('Driver class not found: %s', $driverClass));
        }

        $this->driver = $this->initDriver($driverClass, $driverOpts);
    }

    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    public function getDriverProxySuccessor(): ?ProxyDriver
    {
        return $this->proxySuccessor;
    }

    /**
     * @throws \ReflectionException
     */
    protected function initDriver(string $driverClass, array $driverOpts = []): DriverInterface
    {
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

        return $reflector->newInstanceArgs($args ?? []);
    }
}
