<?php

namespace Rud99\SberSbp\Dto;
class RevokeResult
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    public function getOrderId(): ?string
    {
        return @$this->__aSource["order_id"];
    }

    //---

    public function getOrderState(): ?string
    {
        return @$this->__aSource["order_state"];
    }

    //---

    public function getErrorCode(): string
    {
        return $this->__aSource["error_code"];
    }

    //---

    public function getErrorDescription(): ?string
    {
        return @$this->__aSource["error_description"];
    }
}

