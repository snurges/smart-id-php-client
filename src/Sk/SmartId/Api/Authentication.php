<?php
namespace Sk\SmartId\Api;

class Authentication extends AbstractApi
{

  /**
   * @return AuthenticationRequestBuilder
   */
  public function createAuthentication()
  {
    $connector = new SmartIdRestConnector( $this->client->getHostUrl() );
    $sessionStatusPoller = $this->createSessionStatusPoller( $connector );
    $builder = new AuthenticationRequestBuilder( $connector, $sessionStatusPoller );
    $this->populateBuilderFields( $builder );

    return $builder;
  }
}