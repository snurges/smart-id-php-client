<?php
namespace Sk\SmartId\Tests\Api;

use Sk\SmartId\Api\AuthenticationResponseValidator;
use Sk\SmartId\Api\Data\AuthenticationHash;
use Sk\SmartId\Api\Data\AuthenticationIdentity;
use Sk\SmartId\Api\Data\CertificateLevelCode;
use Sk\SmartId\Api\Data\SignableData;
use Sk\SmartId\Api\Data\SmartIdAuthenticationResponse;
use Sk\SmartId\Api\Data\SmartIdAuthenticationResult;
use Sk\SmartId\Api\Data\SmartIdSignature;
use Sk\SmartId\Api\Data\VerificationCodeCalculator;
use Sk\SmartId\Tests\Setup;

class SmartIdClientIntegrationTest extends Setup
{

    public function setUp()
    {
        parent::setUp();
        $GLOBALS['identity_number'] = '10101010005';
    }

    /**
   * @after
   */
  public function waitForMobileAppToFinish()
  {
    sleep( 10 );
  }

  /**
   * @test
   */
  public function authenticate_withDocumentNumber()
  {
    $authenticationHash = AuthenticationHash::generate();
    $this->assertNotEmpty( $authenticationHash->calculateVerificationCode() );

    $authenticationResponse = $this->client->authentication()
        ->createAuthentication()
        ->withRelyingPartyUUID( $GLOBALS['relying_party_uuid'] )
        ->withRelyingPartyName( $GLOBALS['relying_party_name'] )
        ->withDocumentNumber( $GLOBALS['document_number'] )
        ->withAuthenticationHash( $authenticationHash )
        ->withCertificateLevel(CertificateLevelCode::QUALIFIED)
        ->authenticate();

    $this->assertAuthenticationResponseCreated( $authenticationResponse, $authenticationHash->getDataToSign() );

    $authenticationResponseValidator = new AuthenticationResponseValidator( self::RESOURCES );
    $authenticationResult = $authenticationResponseValidator->validate( $authenticationResponse );
    $this->assertAuthenticationResultValid( $authenticationResult );
  }

  /**
   * @test
   */
  public function authenticate_withNationalIdentityNumberAndCountryCode()
  {
    $authenticationHash = AuthenticationHash::generate();
    $this->assertNotEmpty( $authenticationHash->calculateVerificationCode() );

    $authenticationResponse = $this->client->authentication()
        ->createAuthentication()
        ->withRelyingPartyUUID($GLOBALS['relying_party_uuid'])
        ->withRelyingPartyName($GLOBALS['relying_party_name'])
        ->withNationalIdentityNumber($GLOBALS['document_number'])
        ->withCountryCode('EE')
        ->withAuthenticationHash( $authenticationHash )
        ->withCertificateLevel(CertificateLevelCode::QUALIFIED)
        ->authenticate();

    $this->assertAuthenticationResponseCreated( $authenticationResponse, $authenticationHash->getDataToSign() );

    $authenticationResponseValidator = new AuthenticationResponseValidator( self::RESOURCES );
    $authenticationResult = $authenticationResponseValidator->validate( $authenticationResponse );
    $this->assertAuthenticationResultValid( $authenticationResult );
  }

  /**
   * @param SmartIdAuthenticationResponse $authenticationResponse
   * @param string $dataToSign
   */
  private function assertAuthenticationResponseCreated( SmartIdAuthenticationResponse $authenticationResponse,
      $dataToSign )
  {
    $this->assertNotNull( $authenticationResponse );
    $this->assertNotEmpty( $authenticationResponse->getEndResult() );
    $this->assertEquals( $dataToSign, $authenticationResponse->getSignedData() );
    $this->assertNotEmpty( $authenticationResponse->getValueInBase64() );
    $this->assertNotNull( $authenticationResponse->getCertificate() );
    $this->assertNotNull( $authenticationResponse->getCertificateInstance() );
    $this->assertNotNull( $authenticationResponse->getCertificateLevel() );
  }

  /**
   * @param SmartIdAuthenticationResult $authenticationResult
   */
  private function assertAuthenticationResultValid( SmartIdAuthenticationResult $authenticationResult )
  {
    $this->assertTrue( $authenticationResult->isValid() );
    $this->assertTrue( empty( $authenticationResult->getErrors() ) );
    $this->assertAuthenticationIdentityValid( $authenticationResult->getAuthenticationIdentity() );
  }

  /**
   * @param AuthenticationIdentity $authenticationIdentity
   */
  private function assertAuthenticationIdentityValid( AuthenticationIdentity $authenticationIdentity )
  {
    $this->assertNotEmpty( $authenticationIdentity->getGivenName() );
    $this->assertNotEmpty( $authenticationIdentity->getSurName() );
    $this->assertNotEmpty( $authenticationIdentity->getIdentityCode() );
    $this->assertNotEmpty( $authenticationIdentity->getCountry() );
  }

  /**
   * @test
   * @group testing
   */
  public function getCertificateAndSignFullExample()
  {
      // Provide data bytes to be signed (Default hash type is SHA-512)
      $dataToSign = new SignableData('Hello World!');
      $verificationCode = VerificationCodeCalculator::calculate($dataToSign->calculateHash());

      // Calculate verification code
      $this->assertEquals('4664', $verificationCode);

      $certificateResponse = $this->client->signing()
          ->getCertificate()
          ->withCountryCode('EE')
          ->withNationalIdentityNumber($GLOBALS['identity_number'])
          ->withCertificateLevel(CertificateLevelCode::QUALIFIED)
          ->fetch();

      $documentNumber = $certificateResponse->getDocumentNumber();

      $signature = $this->client->signing()
          ->createSignature()
          ->withDocumentNumber($documentNumber)
          ->withSignableData($dataToSign)
          ->withCertificateLevel(CertificateLevelCode::QUALIFIED)
          ->sign();

      $this->assertValidSignatureCreated($signature);
  }

  private function assertValidSignatureCreated(SmartIdSignature $signature)
  {
      $this->assertNotNull($signature);
      $this->assertEquals('sha512WithRSAEncryption', $signature->getAlgorithmName());
  }

}