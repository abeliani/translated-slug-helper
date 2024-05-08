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

use Abeliani\StringTranslator\Drivers\Core\Driver;
use Abeliani\StringTranslator\Drivers\Core\DriverInterface;

/**
 * Class ProxyDriver
 *
 * Allows setting a successor after creating a driver object
 */
class ProxyDriver extends Driver
{
    private DriverInterface $driver;

    public function setDriver(DriverInterface $driver): void
    {
        $this->driver = $driver;
    }

    protected function processing(string $text, string $from, string $to): ?string
    {
        return $this->driver->processing($text, $from, $to);
    }
}
