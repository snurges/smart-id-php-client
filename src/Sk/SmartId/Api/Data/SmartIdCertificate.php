<?php

namespace Sk\SmartId\Api\Data;

class SmartIdCertificate {

    private $certificate;

    private $documentNumber;

    private $certificateLevel;

    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;
    }

    public function getCertificate()
    {
        return $this->certificate;
    }

    public function getDocumentNumber()
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber($documentNumber)
    {
        $this->documentNumber = $documentNumber;
    }

    public function getCertificateLevel()
    {
        return $this->certificateLevel;
    }

    public function setCertificateLevel($certificateLevel)
    {
        $this->certificateLevel = $certificateLevel;
    }

}