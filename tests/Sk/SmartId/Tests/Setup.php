<?php
namespace Sk\SmartId\Tests;

use PHPUnit\Framework\TestCase;
use Sk\SmartId\Client;

class Setup extends TestCase
{
  const RESOURCES = __DIR__ . '/../../../resources';
  
  /**
   * @var Client
   */
  protected $client;

  protected function setUp()
  {
    $GLOBALS['relying_party_uuid'] = '00000000-0000-0000-0000-000000000000';
    $GLOBALS['relying_party_name'] = 'DEMO';
    $GLOBALS['host_url'] = 'https://sid.demo.sk.ee/smart-id-rp/v1/';

    $this->client = new Client();
    $this->client->setRelyingPartyUUID( $GLOBALS['relying_party_uuid'] )
        ->setRelyingPartyName( $GLOBALS['relying_party_name'] )
        ->setHostUrl( $GLOBALS['host_url'] );
  }
}