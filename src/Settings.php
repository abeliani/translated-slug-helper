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

final class Settings
{
    /**
     * DTO for passing params to SlugHelper and StringTranslator
     *
     * @param string $sourceLang StringTranslator - usually two-letters code (eg. en, ru, ge)
     * @param array $filterWords SlugHelper - the words will be remove from slug (eg. articles)
     * @param string $wordsDivider SlugHelper - symbol to replace target text spaces (one-two)
     */
    public function __construct(
        private readonly string $sourceLang,
        private readonly array $filterWords = [],
        private readonly string $wordsDivider = '-',
    ) {
    }

    public function getSourceLang(): string
    {
        return $this->sourceLang;
    }

    public function getWordsDivider(): string
    {
        return $this->wordsDivider;
    }

    public function getFilterWords(): array
    {
        return $this->filterWords;
    }
}
