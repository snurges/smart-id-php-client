<?php
namespace Sk\SmartId\Api;

use Sk\SmartId\Api\Data\SessionEndResultCode;
use Sk\SmartId\Api\Data\SessionStatus;
use Sk\SmartId\Api\Data\SessionStatusCode;
use Sk\SmartId\Api\Data\SessionStatusRequest;
use Sk\SmartId\Exception\DocumentUnusableException;
use Sk\SmartId\Exception\InterruptedException;
use Sk\SmartId\Exception\SessionTimeoutException;
use Sk\SmartId\Exception\TechnicalErrorException;
use Sk\SmartId\Exception\UserRefusedException;

class SessionStatusPoller
{
  /**
   * @var SmartIdConnector
   */
  private $connector;

  /**
   * In milliseconds
   * @var int
   */
  private $pollingSleepTimeoutMs = 1000;

  /**
   * In milliseconds
   * @var int
   */
  private $sessionStatusResponseSocketOpenTimeoutMs;

  /**
   * @param SmartIdConnector $connector
   */
  public function __construct( SmartIdConnector $connector )
  {
    $this->connector = $connector;
  }

  /**
   * @param string $sessionId
   * @throws TechnicalErrorException
   * @return SessionStatus|null
   */
  public function fetchFinalSessionStatus( $sessionId )
  {
    try
    {
      $sessionStatus = $this->pollForFinalSessionStatus( $sessionId );
      $this->validateResult( $sessionStatus );
      return $sessionStatus;
    }
    catch ( InterruptedException $e )
    {
      throw new TechnicalErrorException( 'Failed to poll session status: ' . $e->getMessage() );
    }
  }

  /**
   * @param string $sessionId
   * @return SessionStatus|null
   */
  private function pollForFinalSessionStatus( $sessionId )
  {
    /** @var SessionStatus $sessionStatus */
    $sessionStatus = null;
    while ( $sessionStatus === null || strcasecmp( SessionStatusCode::RUNNING, $sessionStatus->getState() ) == 0 )
    {
      $sessionStatus = $this->pollSessionStatus( $sessionId );
      if ( $sessionStatus && strcasecmp( SessionStatusCode::COMPLETE, $sessionStatus->getState() ) == 0 )
      {
        break;
      }
      $microseconds = $this->convertMsToMicros( $this->pollingSleepTimeoutMs );
      usleep( $microseconds );
    }
    return $sessionStatus;
  }

  /**
   * @param string $sessionId
   * @return SessionStatus
   */
  private function pollSessionStatus( $sessionId )
  {
    $request = $this->createSessionStatusRequest( $sessionId );
    return $this->connector->getSessionStatus( $request );
  }

  /**
   * @param string $sessionId
   * @return SessionStatusRequest
   */
  private function createSessionStatusRequest( $sessionId )
  {
    $request = new SessionStatusRequest( $sessionId );
    if ( $this->sessionStatusResponseSocketOpenTimeoutMs )
    {
      $request->setSessionStatusResponseSocketOpenTimeoutMs( $this->sessionStatusResponseSocketOpenTimeoutMs );
    }
    return $request;
  }

  /**
   * @param SessionStatus $sessionStatus
   * @throws TechnicalErrorException
   * @throws UserRefusedException
   * @throws SessionTimeoutException
   * @throws DocumentUnusableException
   */
  private function validateResult( SessionStatus $sessionStatus )
  {
    $result = $sessionStatus->getResult();
    if ( $result === null )
    {
      throw new TechnicalErrorException( 'Result is missing in the session status response' );
    }

    $endResult = $result->getEndResult();
    if ( strcasecmp( $endResult, SessionEndResultCode::USER_REFUSED ) == 0 )
    {
      throw new UserRefusedException();
    }
    else if ( strcasecmp( $endResult, SessionEndResultCode::TIMEOUT ) == 0 )
    {
      throw new SessionTimeoutException();
    }
    else if ( strcasecmp( $endResult, SessionEndResultCode::DOCUMENT_UNUSABLE ) == 0 )
    {
      throw new DocumentUnusableException();
    }
    else if ( strcasecmp( $endResult, SessionEndResultCode::OK ) != 0 )
    {
      throw new TechnicalErrorException( 'Session status end result is \'' . $endResult . '\'' );
    }
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
    $this->pollingSleepTimeoutMs = $pollingSleepTimeoutMs;
    return $this;
  }

  /**
   * @param int $milliseconds
   * @return int
   */
  private function convertMsToMicros( $milliseconds )
  {
    $conversionResult = $milliseconds * pow( 10, 3 );
    return $conversionResult > PHP_INT_MAX ? PHP_INT_MAX : $conversionResult;
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