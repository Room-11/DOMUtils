# DOM Utils

Utility functions for the PHP DOM extension.

## Required PHP Version

- PHP 7.0+

## Installation

```bash
$ composer require room11/dom-utils
```

## Usage

### default_charset

Gets and sets the default charset used by DOMUtils functions.

```php
default_charset(string $charSet = ''): string
```

When the supplied `$charSet` is empty, just return the current value. When a new value is supplied, change the
default and return the old one.

 - `string $charSet` - The new default character set.

### domdocument_load_html

Loads a HTML string into a DOMDocument object, with error handling and character set normalization.

```php
domdocument_load_html(string $html, int $options = 0, string $charSet = ''): \DOMDocument
```

Loads a HTML string into a DOMDocument object, with error handling and character set normalization.

 - `string $html` - The HTML string to load.
 - `int $options` - A bit mask of LibXML options to use when loading the document.
 - `string $charSet` - The character set to use when loading the document. Defaults to `default_charset()`.

Throws a `LibXMLFatalErrorException` when loading the document fails.

### domdocument_process_html_docs

Processes a set of HTML strings into DOMDocument objects and invokes a callback for each one.

```php
domdocument_process_html_docs($htmlStrings, callable $callback, int $options = 0, string $charSet = ''): \DOMDocument
```

A DOMDocument object will be passed to the first argument of the callback, and an array of LibXMLError objects
for the document to the second. The third argument is a boolean which is true if the document could not be loaded.
The callback may return boolean false to break the iteration. Any other return value will be ignored.

 - `string[] $htmlStrings` - An iterable set of HTML strings to process.
 - `callable(\DOMDocument, \LibXMLError[], bool) $callback` - The callback to invoke for each document.
 - `int $options` - A bit mask of LibXML options to use when loading the documents.
 - `string $charSet` - The character set to use when loading the documents. Defaults to `default_charset()`.
