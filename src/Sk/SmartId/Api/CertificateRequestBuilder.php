<?php

namespace Sk\SmartId\Api;

use Sk\SmartId\Api\Data\CertificateRequest;
use Sk\SmartId\Api\Data\NationalIdentity;
use Sk\SmartId\Exception\InvalidParametersException;

class CertificateRequestBuilder extends SmartIdRequestBuilder {

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $nationalIdentityNumber;

    /**
     * @var NationalIdentity
     */
    private $nationalIdentity;

    /**
     * @var string
     */
    private $documentNumber;

    /**
     * @var string
     */
    private $certificateLevel;

    /**
     * @var string
     */
    private $nonce;

    /**
     * @param string $relyingPartyUUID
     * @return $this
     */
    public function withRelyingPartyUUID( $relyingPartyUUID )
    {
        parent::withRelyingPartyUUID( $relyingPartyUUID );
        return $this;
    }

    /**
     * @param string $relyingPartyName
     * @return $this
     */
    public function withRelyingPartyName( $relyingPartyName )
    {
        parent::withRelyingPartyName( $relyingPartyName );
        return $this;
    }

    /**
     * @param string $documentNumber
     * @return $this
     */
    public function withDocumentNumber( $documentNumber )
    {
        $this->documentNumber = $documentNumber;
        return $this;
    }

    /**
     * @param NationalIdentity $nationalIdentity
     * @return $this
     */
    public function withNationalIdentity( NationalIdentity $nationalIdentity )
    {
        $this->nationalIdentity = $nationalIdentity;
        return $this;
    }

    /**
     * @param string $countryCode
     * @return $this
     */
    public function withCountryCode( $countryCode )
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @param string $nationalIdentityNumber
     * @return $this
     */
    public function withNationalIdentityNumber( $nationalIdentityNumber )
    {
        $this->nationalIdentityNumber = $nationalIdentityNumber;
        return $this;
    }

    /**
     * @param string $certificateLevel
     * @return $this
     */
    public function withCertificateLevel( $certificateLevel )
    {
        $this->certificateLevel = $certificateLevel;
        return $this;
    }

    /**
     * @param string $nonce
     * @return $this
     */
    public function withNonce( $nonce )
    {
        $this->nonce = $nonce;
        return $this;
    }

    public function fetch()
    {
        $this->validateParameters();

    }

    private function createCertificateRequest()
    {
        $request = new CertificateRequest();
    }

    /**
     * @throws InvalidParametersException
     */
    protected function validateParameters()
    {
        parent::validateParameters();
        if ( !isset( $this->documentNumber ) && !$this->hasNationalIdentity() )
        {
            throw new InvalidParametersException( 'Either document number or national identity must be set' );
        }
    }

}