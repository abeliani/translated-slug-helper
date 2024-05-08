# TranslatedSlugHelper - helps to get a valid url slug translated or transliterated by a driver collection

The package uses libraries **[StringTranslator](https://github.com/abeliani/string-translator.git)** and **[SlugHelper](https://github.com/abeliani/slug-helper.git)**.

## Installation

```bash
composer require abeliani/translated-slug-helper
```

## Examples

### Example of offline driver
Blog posts often require a slug to generate a url. We can use a transliteration driver which simply transliteration the symbol table for example: u-у and vice versa.

```php
$slug = new TranslatedSlugHelper(
    new Settings('ru'),
    new TranslatedBy(Translit::class)
);

print $slug->from('Привет мир!', 'en'); // privet-mir
```

### Example of online driver
Online drivers perform translate a text.

```php
$slug = new TranslatedSlugHelper(
    new Settings('ru', ['a', 'an']), // to remove words from slug (eg. a, an) we can pass them by array
    new TranslatedBy(MyMemory::class, ['apiKey' => 'someapikey'])
);

print $slug->from('Привет мир!', 'en'); // hello-world
```

### Example of driver chain
An online driver will suddenly be unavailable (for example), in this case we can pass a string for translation from one driver to another along the chain.

```php

$chain = [
   new TranslatedBy(MyMemory::class, ['apiKey' => 'someapikey']),
   new TranslatedBy(Translit::class),
];

$slug = new TranslatedSlugHelper(
    new Settings('ru'),
    ...$chain,
);

print $slug->from('Привет мир!', 'en'); // if MyMemory service is available: hello-world | otherwise by transilt driver: privet-mir
```

If you need to change the word separator. you can pass it through settings object

```php
// Passing new divider +
new Settings('ru', [], '+')
```