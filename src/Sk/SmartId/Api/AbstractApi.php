<?php
namespace Sk\SmartId\Api;

use Sk\SmartId\Client;
use Sk\SmartId\Exception\TechnicalErrorException;

abstract class AbstractApi implements ApiInterface
{
  /**
   * @var Client
   */
  protected $client;

    /**
     * In milliseconds
     * @var int
     */
    protected $pollingSleepTimeoutMs = 1000;

    /**
     * In milliseconds
     * @var int
     */
    protected $sessionStatusResponseSocketOpenTimeoutMs;

  /**
   * @param Client $client
   */
  public function __construct( Client $client )
  {
    $this->client = $client;
  }

  protected function createSessionStatusPoller( SmartIdRestConnector $connector )
  {
      $sessionStatusPoller = new SessionStatusPoller( $connector );
      $sessionStatusPoller->setPollingSleepTimeoutMs( $this->pollingSleepTimeoutMs );
      $sessionStatusPoller->setSessionStatusResponseSocketOpenTimeoutMs( $this->sessionStatusResponseSocketOpenTimeoutMs );

      return $sessionStatusPoller;
  }

    /**
     * @param SmartIdRequestBuilder $builder
     */
    protected function populateBuilderFields( SmartIdRequestBuilder $builder )
    {
        $builder->withRelyingPartyUUID( $this->client->getRelyingPartyUUID() )
            ->withRelyingPartyName( $this->client->getRelyingPartyName() );
    }

    /**
     * @param int $pollingSleepTimeoutMs
     * @throws TechnicalErrorException
     * @return $this
     */
    public function setPollingSleepTimeoutMs( $pollingSleepTimeoutMs )
    {
        if ( $pollingSleepTimeoutMs < 0 )
        {
            throw new TechnicalErrorException( 'Timeout can not be negative' );
        }
        $conversionResult = $pollingSleepTimeoutMs * pow( 10, 6 );
        $this->pollingSleepTimeoutMs = ( $conversionResult > PHP_INT_MAX ) ? PHP_INT_MAX : $conversionResult;
        return $this;
    }

    /**
     * @param int $sessionStatusResponseSocketOpenTimeoutMs
     * @throws TechnicalErrorException
     * @return $this
     */
    public function setSessionStatusResponseSocketOpenTimeoutMs( $sessionStatusResponseSocketOpenTimeoutMs )
    {
        if ( $sessionStatusResponseSocketOpenTimeoutMs < 0 )
        {
            throw new TechnicalErrorException( 'Timeout can not be negative' );
        }
        $this->sessionStatusResponseSocketOpenTimeoutMs = $sessionStatusResponseSocketOpenTimeoutMs;
        return $this;
    }

}