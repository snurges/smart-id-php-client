<?php

namespace Sk\SmartId\Api;

use Sk\SmartId\Api\Data\CertificateParser;
use Sk\SmartId\Api\Data\CertificateRequest;
use Sk\SmartId\Api\Data\NationalIdentity;
use Sk\SmartId\Api\Data\SessionStatus;
use Sk\SmartId\Api\Data\SmartIdCertificate;
use Sk\SmartId\Exception\InvalidParametersException;
use Sk\SmartId\Exception\TechnicalErrorException;

class CertificateRequestBuilder extends SmartIdRequestBuilder {

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
        $request = $this->createCertificateRequest();
        $response = $this->fetchCertificateChoiceSessionResponse($request);
        $sessionStatus = $this->getSessionStatusPoller()->fetchFinalSessionStatus($response->getSessionID());
        $smartIdCertificate = $this->createSmartIdCertificate($sessionStatus);

        return $smartIdCertificate;
    }

    private function createCertificateRequest()
    {
        $request = new CertificateRequest();
        $request->setRelyingPartyUUID($this->getRelyingPartyUUID())
            ->setRelyingPartyName($this->getRelyingPartyName())
            ->setCertificateLevel($this->getCertificateLevel())
            ->setNonce($this->nonce);

        return $request;
    }

    private function createSmartIdCertificate(SessionStatus $status)
    {
        $this->validateCertificateResponse($status);
        $certificate = $status->getCert();

        $smartIdCertificate = new SmartIdCertificate();
        $smartIdCertificate->setCertificate(CertificateParser::parseX509Certificate($certificate->getValue()));
        $smartIdCertificate->setCertificateLevel($certificate->getCertificateLevel());
        $smartIdCertificate->setDocumentNumber($this->getDocumentNumber($status));

        return $smartIdCertificate;
    }

    private function validateCertificateResponse(SessionStatus $status)
    {
        $certificate = $status->getCert();
        if($certificate == null || $certificate->getValue() === '')
        {
            throw new TechnicalErrorException("Certificate was not present in the session status response");
        }
        if($status->getResult()->getDocumentNumber() === '')
        {
            throw new TechnicalErrorException("Document number was not present in the session status response");
        }
    }

    private function fetchCertificateChoiceSessionResponse(CertificateRequest $request)
    {
        if(strlen($this->documentNumber))
        {
            return $this->getConnector()->getCertificate($this->documentNumber, $request);
        }
        else
        {
            $identity = $this->getNationalIdentity();

            return $this->getConnector()->getCertificateWithIdentity($identity, $request);
        }
    }

    /**
     * @return string
     */
    private function getCertificateLevel()
    {
        return $this->certificateLevel;
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

    private function getDocumentNumber(SessionStatus $status)
    {
        $result = $status->getResult();

        return $result->getDocumentNumber();
    }

}