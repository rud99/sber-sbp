<?php

namespace Rud99\SberSbp\Dto;
class StatusResult
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

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

    //---

    public function getSbpOperationParams(): ?StatusSbpOperationParams
    {
        return isset($this->__aSource["sbp_operation_params"]) ? new StatusSbpOperationParams($this->__aSource["sbp_operation_params"]) : null;
    }

    //---

    /**
     * @return StatusOrderOperationParam[]
     */
    public function getOrderOperationParams(): array
    {
        return array_map(//
            function ($aItem) {
                return new StatusOrderOperationParam($aItem);
            },
            $this->__aSource["order_operation_params"] ?? []
        );
    }
}

//---

class StatusSbpOperationParams
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

    public function getSbpOperationId(): string
    {
        return $this->__aSource["sbp_operation_id"];
    }

    //---

    public function getSbpMaskedPayerId(): string
    {
        return $this->__aSource["sbp_masked_payer_id"];
    }
}

//---

class StatusOrderOperationParam
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    public function getRrn(): string
    {
        return $this->__aSource["rrn"];
    }

    //---

    public function getAuthCode(): string
    {
        return $this->__aSource["auth_code"];
    }

    //---

    public function getOperationId(): string
    {
        return $this->__aSource["operation_id"];
    }

    //---

    public function getResponseCode(): string
    {
        return $this->__aSource["response_code"];
    }

    //---

    public function getOperationType(): string
    {
        return $this->__aSource["operation_type"];
    }

    //---

    public function getOperationSum(): int
    {
        return $this->__aSource["operation_sum"];
    }

    //---

    public function getOperationCurrency(): string
    {
        return $this->__aSource["operation_currency"];
    }

    //---

    public function getOperationDateTime(): string
    {
        return $this->__aSource["operation_date_time"];
    }

    //---

    public function getResponseDesc(): ?string
    {
        return @$this->__aSource["response_desc"];
    }
}
