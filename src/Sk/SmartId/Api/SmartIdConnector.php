<?php
namespace Sk\SmartId\Api;

use Sk\SmartId\Api\Data\AuthenticationSessionRequest;
use Sk\SmartId\Api\Data\AuthenticationSessionResponse;
use Sk\SmartId\Api\Data\CertificateChoiceResponse;
use Sk\SmartId\Api\Data\CertificateRequest;
use Sk\SmartId\Api\Data\NationalIdentity;
use Sk\SmartId\Api\Data\SessionStatus;
use Sk\SmartId\Api\Data\SessionStatusRequest;
use Sk\SmartId\Api\Data\SignatureSessionRequest;
use Sk\SmartId\Api\Data\SignatureSessionResponse;
use Sk\SmartId\Exception\SessionNotFoundException;

interface SmartIdConnector
{
  /**
   * @param string $documentNumber
   * @param AuthenticationSessionRequest $request
   * @return AuthenticationSessionResponse
   */
  function authenticate( $documentNumber, AuthenticationSessionRequest $request );

  /**
   * @param NationalIdentity $identity
   * @param AuthenticationSessionRequest $request
   * @return AuthenticationSessionResponse
   */
  function authenticateWithIdentity( NationalIdentity $identity, AuthenticationSessionRequest $request );

  /**
   * @param SessionStatusRequest $request
   * @throws SessionNotFoundException
   * @return SessionStatus
   */
  function getSessionStatus( SessionStatusRequest $request );

  /**
   * @param string $documentNumber
   * @param CertificateRequest $request
   * @return CertificateChoiceResponse
   */
  function getCertificate($documentNumber, CertificateRequest $request);

    /**
     * @param NationalIdentity $identity
     * @param CertificateRequest $request
     * @return CertificateChoiceResponse
     */
    function getCertificateWithIdentity(NationalIdentity $identity, CertificateRequest $request);

    /**
     * @param string $documentNr
     * @param SignatureSessionRequest $request
     * @return SignatureSessionResponse
     */
    function sign($documentNumber, SignatureSessionRequest $request);

}