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

}