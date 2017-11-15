<?php

namespace Sk\SmartId\Api;

use Sk\SmartId\Api\Data\NationalIdentity;
use Sk\SmartId\Api\Data\SessionStatus;
use Sk\SmartId\Api\Data\SignableData;
use Sk\SmartId\Api\Data\SignatureSessionRequest;
use Sk\SmartId\Api\Data\SmartIdSignature;
use Sk\SmartId\Exception\TechnicalErrorException;

class SignatureRequestBuilder extends SmartIdRequestBuilder {

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

    /**
     * @param SignableData $dataToSign
     * @return $this
     */
    public function withSignableData( SignableData $dataToSign )
    {
        $this->dataToSign = $dataToSign;
        return $this;
    }

    /**
     * @return SmartIdSignature
     */
    public function sign()
    {
        $this->validateParameters();
        $request = $this->createSignatureSessionRequest();
        $response = $this->getConnector()->sign($this->documentNumber, $request);
        $sessionStatus = $this->getSessionStatusPoller()->fetchFinalSessionStatus($response->getSessionID());
        $this->validateResponse($sessionStatus);
        $signature = $this->createSmartIdSignature($sessionStatus);

        return $signature;
    }

    private function createSmartIdSignature(SessionStatus $status)
    {
        $sessionSignature = $status->getSignature();

        $signature = new SmartIdSignature();
        $signature->setValueInBase64($sessionSignature->getValue())
            ->setAlgorithmName($sessionSignature->getAlgorithm())
            ->setDocumentNumber($status->getResult()->getDocumentNumber());

        return $signature;
    }

    /**
     * @return SignatureSessionRequest
     */
    public function createSignatureSessionRequest()
    {
        $request = new SignatureSessionRequest();
        $request->setRelyingPartyUUID( $this->getRelyingPartyUUID() )
            ->setRelyingPartyName( $this->getRelyingPartyName() )
            ->setCertificateLevel( $this->certificateLevel )
            ->setHashType( $this->getHashTypeString() )
            ->setHash( $this->getHashInBase64() )
            ->setDisplayText( $this->displayText )
            ->setNonce( $this->nonce );

        return $request;
    }

    private function validateResponse(SessionStatus $status)
    {
        if ($status->getSignature() === null)
        {
            throw new TechnicalErrorException( 'Signature was not present in the response' );
        }
    }

}