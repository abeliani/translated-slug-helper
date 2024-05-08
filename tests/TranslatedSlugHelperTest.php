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

namespace Abeliani\TranslatedSlugHelper\Tests;

use Abeliani\StringTranslator\Drivers\Core\Driver;
use Abeliani\StringTranslator\Drivers\Core\DriverException;
use Abeliani\StringTranslator\Drivers\Translit;
use Abeliani\TranslatedSlugHelper\ProxyDriver;
use Abeliani\TranslatedSlugHelper\Settings;
use Abeliani\TranslatedSlugHelper\TranslatedBy;
use Abeliani\TranslatedSlugHelper\TranslatedSlugHelper;
use Codeception\Test\Unit;

class TranslatedSlugHelperTest extends Unit
{
    public function testTranslitSuccess(): void
    {
        $slug = new TranslatedSlugHelper(
            new Settings('ru'),
            new TranslatedBy(Translit::class)
        );

        $result = $slug->from('Привет мир!', 'en');
        $this->assertEquals('privet-mir', $result);
    }

    public function testSlugWordsDividerSuccess(): void
    {
        $slug = new TranslatedSlugHelper(
            new Settings('ru', [], '+'),
            new TranslatedBy(Translit::class)
        );

        $result = $slug->from('Привет мир!', 'en');
        $this->assertEquals('privet+mir', $result);
    }

    public function testTranslateByChainSuccess(): void
    {
        $successor = new Translit;

        $driver = $this->getMockBuilder(Driver::class)
            ->setConstructorArgs(['successor' => $successor])
            ->getMockForAbstractClass();

        $driver->method('processing')
            ->willThrowException(new DriverException);

        $translatedByException = $this->make(TranslatedBy::class, [
            'getDriver' => $driver,
            'getDriverProxySuccessor' => new ProxyDriver,
        ]);

        $translatedByNextDriver = $this->make(TranslatedBy::class, [
            'getDriver' => $successor,
        ]);

        $slug = new TranslatedSlugHelper(
            new Settings('ru'),
            $translatedByException,
            $translatedByNextDriver,
        );

        $result = $slug->from('Привет!', 'en');
        $this->assertEquals('privet', $result);
    }
}
