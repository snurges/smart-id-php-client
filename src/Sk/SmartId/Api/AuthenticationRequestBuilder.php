<?php
namespace Sk\SmartId\Api;

use Sk\SmartId\Api\Data\AuthenticationHash;
use Sk\SmartId\Api\Data\AuthenticationSessionRequest;
use Sk\SmartId\Api\Data\AuthenticationSessionResponse;
use Sk\SmartId\Api\Data\NationalIdentity;
use Sk\SmartId\Api\Data\SessionStatus;
use Sk\SmartId\Api\Data\SignableData;
use Sk\SmartId\Api\Data\SmartIdAuthenticationResponse;
use Sk\SmartId\Exception\InvalidParametersException;
use Sk\SmartId\Exception\TechnicalErrorException;

class AuthenticationRequestBuilder extends SmartIdRequestBuilder
{

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
   * @var SignableData
   */
  private $dataToSign;

  /**
   * @var AuthenticationHash
   */
  private $authenticationHash;

  /**
   * @var string
   */
  private $nonce;

  /**
   * @param SmartIdConnector $connector
   * @param SessionStatusPoller $sessionStatusPoller
   */
  public function __construct( SmartIdConnector $connector, SessionStatusPoller $sessionStatusPoller )
  {
    parent::__construct( $connector, $sessionStatusPoller );
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
   * @param SignableData $dataToSign
   * @return $this
   */
  public function withSignableData( SignableData $dataToSign )
  {
    $this->dataToSign = $dataToSign;
    return $this;
  }

  /**
   * @param AuthenticationHash $authenticationHash
   * @return $this
   */
  public function withAuthenticationHash( AuthenticationHash $authenticationHash )
  {
    $this->authenticationHash = $authenticationHash;
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
   * @param string $displayText
   * @return $this
   */
  public function withDisplayText( $displayText )
  {
    $this->displayText = $displayText;
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
   * @return SmartIdAuthenticationResponse
   */
  public function authenticate()
  {
    $this->validateParameters();
    $request = $this->createAuthenticationSessionRequest();
    $response = $this->getAuthenticationResponse( $request );
    $sessionStatus = $this->getSessionStatusPoller()->fetchFinalSessionStatus( $response->getSessionID() );
    $this->validateSessionStatus( $sessionStatus );
    $authenticationResponse = $this->createSmartIdAuthenticationResponse( $sessionStatus );
    return $authenticationResponse;
  }

  /**
   * @return AuthenticationSessionRequest
   */
  private function createAuthenticationSessionRequest()
  {
    $request = new AuthenticationSessionRequest();
    $request->setRelyingPartyUUID( $this->getRelyingPartyUUID() )
        ->setRelyingPartyName( $this->getRelyingPartyName() )
        ->setCertificateLevel( $this->certificateLevel )
        ->setHashType( $this->getHashTypeString() )
        ->setHash( $this->getHashInBase64() )
        ->setDisplayText( $this->displayText )
        ->setNonce( $this->nonce );
    return $request;
  }

  /**
   * @param AuthenticationSessionRequest $request
   * @return AuthenticationSessionResponse
   */
  private function getAuthenticationResponse( AuthenticationSessionRequest $request )
  {
    if ( strlen( $this->documentNumber ) )
    {
      return $this->getConnector()->authenticate( $this->documentNumber, $request );
    }
    else
    {
      $identity = $this->getNationalIdentity();
      return $this->getConnector()->authenticateWithIdentity( $identity, $request );
    }
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
    if ( !$this->isSignableDataSet() && !$this->isAuthenticationHashSet() )
    {
      throw new InvalidParametersException( 'Signable data or hash with hash type must be set' );
    }
  }

  /**
   * @return bool
   */
  private function isSignableDataSet()
  {
    return isset( $this->dataToSign );
  }

  /**
   * @return bool
   */
  private function isAuthenticationHashSet()
  {
    return isset( $this->authenticationHash );
  }

  /**
   * @param SessionStatus $sessionStatus
   * @throws TechnicalErrorException
   */
  private function validateSessionStatus( SessionStatus $sessionStatus )
  {
    if ( $sessionStatus->getSignature() === null )
    {
      throw new TechnicalErrorException( 'Signature was not present in the response' );
    }
    if ( $sessionStatus->getCert() === null )
    {
      throw new TechnicalErrorException( 'Certificate was not present in the response' );
    }
  }

  /**
   * @param SessionStatus $sessionStatus
   * @return SmartIdAuthenticationResponse
   */
  private function createSmartIdAuthenticationResponse( SessionStatus $sessionStatus )
  {
    $sessionResult = $sessionStatus->getResult();
    $sessionSignature = $sessionStatus->getSignature();
    $sessionCertificate = $sessionStatus->getCert();

    $authenticationResponse = new SmartIdAuthenticationResponse();
    $authenticationResponse->setEndResult( $sessionResult->getEndResult() )
        ->setSignedData( $this->getDataToSign() )
        ->setValueInBase64( $sessionSignature->getValue() )
        ->setAlgorithmName( $sessionSignature->getAlgorithm() )
        ->setCertificate( $sessionCertificate->getValue() )
        ->setCertificateLevel( $sessionCertificate->getCertificateLevel() );
    return $authenticationResponse;
  }

  /**
   * @return string
   */
  private function getDataToSign()
  {
    if ( isset( $this->authenticationHash ) )
    {
      return $this->authenticationHash->getDataToSign();
    }
    return $this->dataToSign->getDataToSign();
  }
}