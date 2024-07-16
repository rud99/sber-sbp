<?php

namespace Rud99\SberSbp\Dto;
class CancelResult
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

    public function getOperationSum(): ?int
    {
        return @$this->__aSource["operation_sum"];
    }

    //---

    public function getRrn(): ?string
    {
        return @$this->__aSource["rrn"];
    }

    //---

    public function getOrderId(): ?string
    {
        return @$this->__aSource["order_id"];
    }

    //---

    public function getAuthCode(): ?string
    {
        return @$this->__aSource["auth_code"];
    }

    //---

    public function getErrorCode(): string
    {
        return $this->__aSource["error_code"];
    }

    //---

    public function getOrderStatus(): ?string
    {
        return @$this->__aSource["order_status"];
    }

    //---

    public function getOperationId(): ?string
    {
        return @$this->__aSource["operation_id"];
    }

    //---

    public function getOperationType(): ?string
    {
        return @$this->__aSource["operation_type"];
    }

    //---

    public function getErrorDescription(): ?string
    {
        return @$this->__aSource["error_description"];
    }

    //---

    public function getOperationDateTime(): ?string
    {
        return @$this->__aSource["operation_date_time"];
    }

    //---

    public function getOperationCurrency(): ?string
    {
        return @$this->__aSource["operation_currency"];
    }
}
