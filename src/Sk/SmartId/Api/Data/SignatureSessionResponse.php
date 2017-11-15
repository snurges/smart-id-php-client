<?php

namespace Sk\SmartId\Api\Data;

class SignatureSessionResponse extends PropertyMapper {

    /**
     * @var string
     */
    private $sessionID;

    /**
     * @return string
     */
    public function getSessionID()
    {
        return $this->sessionID;
    }

    /**
     * @param string $sessionID
     */
    public function setSessionID( $sessionID )
    {
        $this->sessionID = $sessionID;
    }

}