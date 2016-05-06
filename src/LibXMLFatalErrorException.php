<?php

namespace Room11\DOMUtils;

class LibXMLFatalErrorException extends \Exception
{
    private $libXMLError;

    public function __construct(\LibXMLError $libXMLError)
    {
        parent::__construct($libXMLError->message, $libXMLError->code);

        $this->libXMLError = $libXMLError;
    }

    public function getLibXMLError(): \LibXMLError
    {
        return $this->libXMLError;
    }
}
