<?php

namespace Room11\DOMUtils;

/**
 * Gets and sets the default charset used by DOMUtils functions.
 *
 * When the supplied $charSet is empty, just return the current value. When a new value is supplied, change the
 * default and return the old one.
 *
 * @param string $charSet
 * @return string
 */
function default_charset(string $charSet = ''): string
{
    static $defaultCharSet = 'UTF-8';

    $result = $defaultCharSet;

    if ($charSet !== '') {
        $defaultCharSet = $charSet;
    }

    return $result;
}

/**
 * Loads a HTML string into a DOMDocument object, with error handling and character set normalization.
 *
 * @param string $html
 * @param int $options
 * @param string $charSet
 * @return \DOMDocument
 * @throws LibXMLFatalErrorException
 */
function domdocument_load_html(string $html, int $options = 0, string $charSet = ''): \DOMDocument
{
    if (!preg_match('#^\s*<\?xml#i', $html)) {
        if ($charSet === '') {
            $charSet = default_charset();
        }

        $html = '<?xml encoding="' . $charSet . '" ?>' . $html;
    }

    $internalErrors = null;

    try {
        $internalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($html, $options);

        /** @var \LibXMLError $error */
        foreach (libxml_get_errors() as $error) {
            if ($error->level === LIBXML_ERR_FATAL) {
                throw new LibXMLFatalErrorException($error);
            }
        }

        return $dom;
    } finally {
        if ($internalErrors !== null) {
            libxml_use_internal_errors($internalErrors);
        }
    }
}

/**
 * Processes a set of HTML strings into DOMDocument objects and invokes a callback for each one.
 *
 * A DOMDocument object will be passed to the first argument of the callback, and an array of LibXMLError objects
 * for the document to the second. The third argument is a boolean which is true if the document could not be loaded.
 * The callback may return boolean false to break the iteration. Any other return value will be ignored.
 *
 * @param string[] $htmlStrings
 * @param callable $callback
 * @param int $options
 * @param string $charSet
 * @return \DOMDocument
 * @throws LibXMLFatalErrorException
 */
function domdocument_process_html_docs(array $htmlStrings, callable $callback, int $options = 0, string $charSet = '')
{
    if ($charSet === '') {
        $charSet = default_charset();
    }

    $internalErrors = null;

    try {
        $internalErrors = libxml_use_internal_errors(true);

        foreach ($htmlStrings as $html) {
            if (!preg_match('#^\s*<\?xml#i', $html)) {
                $html = '<?xml encoding="' . $charSet . '" ?>' . $html;
            }

            libxml_clear_errors();

            $dom = new \DOMDocument();
            $dom->loadHTML($html, $options);

            /** @var \LibXMLError[] $errors */
            $errors = libxml_get_errors();

            // don't foreach so we don't confuse the callback if it does something weird relying on the array pointer
            $fatal = false;
            for ($i = 0; isset($errors[$i]); $i++) {
                if ($errors[$i]->level === LIBXML_ERR_FATAL) {
                    $fatal = true;
                    break;
                }
            }

            if ($callback($dom, $errors, $fatal) === false) {
                break;
            }
        }
    } finally {
        if ($internalErrors !== null) {
            libxml_use_internal_errors($internalErrors);
        }
    }
}

/**
 * Execute an xpath query against the supplied XPath index, document or node, and return the first matching DOMElement
 *
 * @param \DOMXPath|\DOMDocument|\DOMNode $target
 * @param string $query
 * @param \DOMNode $contextNode
 * @return \DOMElement
 * @throws \InvalidArgumentException
 * @throws ElementNotFoundException
 */
function xpath_get_element($target, string $query, \DOMNode $contextNode = null): \DOMElement
{
    if ($target instanceof \DOMXPath) {
        $xpath = $target;
    } else if ($target instanceof \DOMDocument) {
        $xpath = new \DOMXPath($target);
    } else if ($target instanceof \DOMNode) {
        $contextNode = $target;
        $xpath = new \DOMXPath($target->ownerDocument);
    } else {
        throw new \InvalidArgumentException('Invalid target supplied: must be a DOMXPath, DOMDocument or DOMElement');
    }
    
    $results = $xpath->query($query, $contextNode);
    if ($results->length < 1) {
        throw new ElementNotFoundException('Element matching ' . $query . ' was not found');
    }

    foreach ($results as $result) {
        if ($result instanceof \DOMElement) {
            return $result;
        }
    }

    throw new ElementNotFoundException('No DOMElement nodes matching ' . $query . ' were found');
}

/**
 * Execute an xpath query against the supplied XPath index, document or node, and return all matching DOMElements
 *
 * @param \DOMXPath|\DOMDocument|\DOMNode $target
 * @param string $query
 * @param \DOMNode $contextNode
 * @return \DOMElement[]
 * @throws \InvalidArgumentException
 * @throws ElementNotFoundException
 */
function xpath_get_elements($target, string $query, \DOMNode $contextNode = null): array
{
    if ($target instanceof \DOMXPath) {
        $xpath = $target;
    } else if ($target instanceof \DOMDocument) {
        $xpath = new \DOMXPath($target);
    } else if ($target instanceof \DOMNode) {
        $contextNode = $target;
        $xpath = new \DOMXPath($target->ownerDocument);
    } else {
        throw new \InvalidArgumentException('Invalid target supplied: must be a DOMXPath, DOMDocument or DOMElement');
    }

    $results = $xpath->query($query, $contextNode);
    if ($results->length < 1) {
        throw new ElementNotFoundException('Element matching ' . $query . ' was not found');
    }

    $return = [];

    foreach ($results as $result) {
        if ($result instanceof \DOMElement) {
            $return[] = $result;
        }
    }

    return $return;
}

function xpath_html_class(string $className): string
{
    return "contains(concat(' ', normalize-space(@class), ' '), ' {$className} ')";
}
