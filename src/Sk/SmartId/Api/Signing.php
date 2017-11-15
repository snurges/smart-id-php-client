<?php

namespace Sk\SmartId\Api;

class Signing extends AbstractApi {

    /**
     * Gets an instance of the certificate request builder
     *
     * @return CertificateRequestBuilder
     */
    public function getCertificate()
    {
        $connector = new SmartIdRestConnector($this->client->getHostUrl());
        $sessionStatusPoller = $this->createSessionStatusPoller($connector);
        $builder = new CertificateRequestBuilder($connector, $sessionStatusPoller);
        $this->populateBuilderFields($builder);

        return $builder;
    }

    /**
     * Gets an instance  of the signature request builder
     * @return SignatureRequestBuilder
     */
    public function createSignature()
    {
        $connector = new SmartIdRestConnector($this->client->getHostUrl());
        $sessionStatusPoller = $this->createSessionStatusPoller($connector);
        $builder = new SignatureRequestBuilder($connector, $sessionStatusPoller);
        $this->populateBuilderFields($builder);

        return $builder;
    }

}