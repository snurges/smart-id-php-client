<?php

namespace Sk\SmartId\Api\Data;

class CertificateRequest {

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
    private $certificateLevel;

    /**
     * @var string
     */
    private $nonce;

    /**
     * @return string
     */
    public function getCertificateLevel()
    {
        return $this->certificateLevel;
    }

    /**
     * @param string $certificateLevel
     * @return $this
     */
    public function setCertificateLevel( $certificateLevel )
    {
        $this->certificateLevel = $certificateLevel;
        return $this;
    }

    /**
     * @return string
     */
    public function getRelyingPartyName()
    {
        return $this->relyingPartyName;
    }

    /**
     * @param string $relyingPartyName
     * @return $this
     */
    public function setRelyingPartyName( $relyingPartyName )
    {
        $this->relyingPartyName = $relyingPartyName;
        return $this;
    }

    /**
     * @return string
     */
    public function getRelyingPartyUUID()
    {
        return $this->relyingPartyUUID;
    }

    /**
     * @param string $relyingPartyUUID
     * @return $this
     */
    public function setRelyingPartyUUID( $relyingPartyUUID )
    {
        $this->relyingPartyUUID = $relyingPartyUUID;
        return $this;
    }

    /**
     * @return string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @param string $nonce
     * @return $this
     */
    public function setNonce( $nonce )
    {
        $this->nonce = $nonce;
        return $this;
    }

}