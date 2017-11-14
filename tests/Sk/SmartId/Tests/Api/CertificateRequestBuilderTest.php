<?php

use Sk\SmartId\Api\CertificateRequestBuilder;
use Sk\SmartId\Api\Data\CertificateChoiceResponse;
use Sk\SmartId\Api\Data\CertificateLevelCode;
use Sk\SmartId\Api\Data\SessionCertificate;
use Sk\SmartId\Api\Data\SessionSignature;
use Sk\SmartId\Api\Data\SessionStatus;
use Sk\SmartId\Api\Data\SessionStatusCode;
use Sk\SmartId\Api\Data\SmartIdCertificate;
use Sk\SmartId\Api\SessionStatusPoller;
use Sk\SmartId\Tests\Api\DummyData;
use Sk\SmartId\Tests\Rest\SmartIdConnectorSpy;
use Sk\SmartId\Tests\Setup;

class CertificateRequestBuilderTest extends Setup {

    /**
     * @var SmartIdConnectorSpy
     */
    private $connector;

    /**
     * @var SessionStatusPoller
     */
    private $sessionStatusPoller;

    /**
     * @var CertificateRequestBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->connector = new SmartIdConnectorSpy();
        $this->sessionStatusPoller = new SessionStatusPoller($this->connector);
        $this->connector->sessionStatusToRespond = $this->createCertificateSessionStatusCompleteResponse();
        $this->connector->certificateChoiceToRespond = $this->createCertificateChoiceResponse();
        $this->builder = new CertificateRequestBuilder($this->connector, $this->sessionStatusPoller);
    }

    /**
     * @group testing
     * @test
     */
    public function getCertificate()
    {
        $certificate = $this->builder->withRelyingPartyUUID('relying-party-uuid')
            ->withRelyingPartyName('relying-party-name')
            ->withCountryCode('EE')
            ->withNationalIdentityNumber('31111111111')
            ->withCertificateLevel(CertificateLevelCode::QUALIFIED)
            ->fetch();

        var_dump($certificate);
    }

    private function assertCertificateResponseValid(SmartIdCertificate $certificate)
    {
        $this->assertNotNull($certificate);
        $this->assertNotNull($certificate->getCertificate());
        dd($certificate);
        $cert = $certificate->getCertificate();
    }

    private function createCertificateSessionStatusCompleteResponse()
    {
        $signature = new SessionSignature();
        $signature->setValue( 'c2FtcGxlIHNpZ25hdHVyZQ0K' );
        $signature->setAlgorithm( 'sha512WithRSAEncryption' );

        $certificate = new SessionCertificate();
        $certificate->setCertificateLevel( CertificateLevelCode::QUALIFIED );
        $certificate->setValue( DummyData::CERTIFICATE );

        $status = new SessionStatus();
        $status->setState( SessionStatusCode::COMPLETE )
            ->setResult( DummyData::createSessionEndResult() )
            ->setSignature( $signature )
            ->setCert( $certificate );

        return $status;
    }

    private function createCertificateChoiceResponse()
    {
        $response = new CertificateChoiceResponse();
        $response->setSessionID('97f5058e-e308-4c83-ac14-7712b0eb9d86');

        return $response;
    }
}
