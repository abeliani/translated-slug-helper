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

use Abeliani\SlugHelper\Filter\FilterWords;
use Abeliani\SlugHelper\Filter\ReplaceSpaces;
use Abeliani\SlugHelper\SlugHelper;
use Abeliani\StringTranslator\Drivers\Core\DriverInterface;
use Abeliani\TranslatedSlugHelper\Tests\TranslatedSlugHelperTest;

/**
 * Class TranslatedSlugHelper
 *
 * Performs the translation of the transmitted string in accordance
 * with the received driver and the specified translation map
 */
class TranslatedSlugHelper
{
    private string $sourceLang;
    private SlugHelper $slugger;
    private DriverInterface $driver;

    /**
     * Simple using offline transliteration driver
     *
     *      $slug = new TranslatedSlugHelper(
     *          new Settings('ru'),
     *          new TranslatedBy(Translit::class)
     *      );
     *
     *      $slug->from('Привет мир!', 'en');
     *      // privet-mir
     *
     * Using drivers chain. This allows the next driver to be called if this fails
     *
     *      $chain = [
     *          new TranslatedBy(SomeOnlineDriver::class),
     *          new TranslatedBy(Translit::class),
     *      ];
     *
     *      // We also passed filter option to remove articles (a, an) from slug
     *      $settings = new Settings('ru', ['a', 'an']);
     *      $slug = new TranslatedSlugHelper($settings, ...$chain);
     *
     *      $slug->from('Это велосипед', 'en');
     *      if online driver is allows // this-is-bicycle
     *      if the first fails a text will be processed by next driver // eto-velosiped
     *
     * @param Settings $settings
     * @param TranslatedBy ...$translatedBy
     *
     * @see TranslatedSlugHelperTest for getting examples
     */
    public function __construct(Settings $settings, TranslatedBy ...$translatedBy)
    {
        $this->implementChainOfDrivers(...$translatedBy);
        $this->driver = $translatedBy[0]->getDriver();

        $this->sourceLang = $settings->getSourceLang();
        $this->slugger = new SlugHelper(options: [
            FilterWords::class => $settings->getFilterWords(),
            ReplaceSpaces::class => $settings->getWordsDivider(),
        ]);
    }

    public function from(string $text, string $toLang): string
    {
        $translated = $this->driver->handle($text, $this->sourceLang, $toLang);

        return $this->slugger->__invoke($translated);
    }

    private function implementChainOfDrivers(TranslatedBy ...$translatedBy): void
    {
        for ($di = 0, $si = 1; array_key_exists($si, $translatedBy); $di++, $si++) {
            $proxyDriver = $translatedBy[$di]->getDriverProxySuccessor();
            $proxyDriver->setDriver($translatedBy[$si]->getDriver());
        }
    }
}
