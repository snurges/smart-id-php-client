<?php

namespace Sk\SmartId\Api\Data;

use Sk\SmartId\Exception\TechnicalErrorException;

class SmartIdSignature {

    /**
     * @var string
     */
    private $valueInBase64;

    /**
     * @var string
     */
    private $algorithmName;

    /**
     * @string
     */
    private $documentNumber;

    /**
     * @return string
     */
    public function getValueInBase64()
    {
        return $this->valueInBase64;
    }

    /**
     * @param string $valueInBase64
     * @return $this
     */
    public function setValueInBase64( $valueInBase64 )
    {
        $this->valueInBase64 = $valueInBase64;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlgorithmName()
    {
        return $this->algorithmName;
    }

    /**
     * @param string $algorithmName
     * @return $this
     */
    public function setAlgorithmName( $algorithmName )
    {
        $this->algorithmName = $algorithmName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentNumber()
    {
        return $this->documentNumber;
    }

    /**
     * @param string $documentNumber
     * @return $this
     */
    public function setDocumentNumber($documentNumber)
    {
        $this->documentNumber = $documentNumber;
    }

    /**
     * @throws TechnicalErrorException
     * @return string
     */
    public function getValue()
    {
        if ( base64_decode( $this->valueInBase64, true ) === false )
        {
            throw new TechnicalErrorException( 'Failed to parse signature value in base64. Probably incorrectly 
                encoded base64 string: ' . $this->valueInBase64 );
        }
        return base64_decode( $this->valueInBase64, true );
    }

}