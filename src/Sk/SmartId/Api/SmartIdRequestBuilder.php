<?php
namespace Sk\SmartId\Api;

use Sk\SmartId\Api\Data\NationalIdentity;
use Sk\SmartId\Api\Data\SignableData;
use Sk\SmartId\Exception\InvalidParametersException;

abstract class SmartIdRequestBuilder
{
  /**
   * @var SmartIdConnector
   */
  private $connector;

  /**
   * @var SessionStatusPoller
   */
  private $sessionStatusPoller;

  /**
   * @var string
   */
  private $relyingPartyUUID;

  /**
   * @var string
   */
  private $relyingPartyName;

    /**
     * @var string
     */
    protected $countryCode;

    /**
     * @var string
     */
    protected $nationalIdentityNumber;

    /**
     * @var SignableData
     */
    protected $dataToSign;

    /**
     * @var string
     */
    protected $displayText;

  /**
   * @param SmartIdConnector $connector
   * @param SessionStatusPoller $sessionStatusPoller
   */
  public function __construct( SmartIdConnector $connector, SessionStatusPoller $sessionStatusPoller )
  {
    $this->connector = $connector;
    $this->sessionStatusPoller = $sessionStatusPoller;
  }

  /**
   * @param string $relyingPartyUUID
   * @return $this
   */
  public function withRelyingPartyUUID( $relyingPartyUUID )
  {
    $this->relyingPartyUUID = $relyingPartyUUID;
    return $this;
  }

  /**
   * @param string $relyingPartyName
   * @return $this
   */
  public function withRelyingPartyName( $relyingPartyName )
  {
    $this->relyingPartyName = $relyingPartyName;
    return $this;
  }

  /**
   * @return SmartIdConnector
   */
  public function getConnector()
  {
    return $this->connector;
  }

  /**
   * @return SessionStatusPoller
   */
  public function getSessionStatusPoller()
  {
    return $this->sessionStatusPoller;
  }

  /**
   * @return string
   */
  public function getRelyingPartyUUID()
  {
    return $this->relyingPartyUUID;
  }

  /**
   * @return string
   */
  public function getRelyingPartyName()
  {
    return $this->relyingPartyName;
  }

    /**
     * @return string
     */
    protected function getHashTypeString()
    {
        if ( isset( $this->hashType ) )
        {
            return $this->hashType;
        }
        else if ( isset( $this->authenticationHash ) )
        {
            return $this->authenticationHash->getHashType();
        }
        return $this->dataToSign->getHashType();
    }

    /**
     * @return string
     */
    protected function getHashInBase64()
    {
        if ( isset( $this->authenticationHash ) )
        {
            return $this->authenticationHash->calculateHashInBase64();
        }
        return $this->dataToSign->calculateHashInBase64();
    }

    /**
     * @return NationalIdentity
     */
    protected function getNationalIdentity()
    {
        if ( isset( $this->nationalIdentity ) )
        {
            return $this->nationalIdentity;
        }
        return new NationalIdentity( $this->countryCode, $this->nationalIdentityNumber );
    }

  /**
   * @throws InvalidParametersException
   */
  protected function validateParameters()
  {
    if ( !isset( $this->relyingPartyUUID ) )
    {
      throw new InvalidParametersException( 'Relying Party UUID parameter must be set' );
    }

    if ( !isset( $this->relyingPartyName ) )
    {
      throw new InvalidParametersException( 'Relying Party Name parameter must be set' );
    }
  }

    /**
     * @return bool
     */
    protected function hasNationalIdentity()
    {
        return isset( $this->nationalIdentity )
            || ( strlen( $this->countryCode ) && strlen( $this->nationalIdentityNumber ) );
    }

}