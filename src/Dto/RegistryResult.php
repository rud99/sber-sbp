<?php

namespace Rud99\SberSbp\Dto;
class RegistryResult
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

    public function getTid(): ?string
    {
        return @$this->__aSource["tid"];
    }

    //---

    public function getIdQR(): ?string
    {
        return @$this->__aSource["idQR"];
    }

    //---

    public function getErrorCode(): string
    {
        return $this->__aSource["errorCode"];
    }

    //---

    public function getErrorDescription(): ?string
    {
        return @$this->__aSource["errorDescription"];
    }

    //---

    public function getQuantityData(): ?RegistryQuantityData
    {
        return isset($this->__aSource["quantityData"]) ? new RegistryQuantityData($this->__aSource["quantityData"]) : null;
    }

    //---

    public function getRegistryData(): ?RegistryRegistryData
    {
        return isset($this->__aSource["registryData"]) ? new RegistryRegistryData($this->__aSource["registryData"]) : null;
    }
}

//---

class RegistryQuantityData
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

    public function getTotalCount(): int
    {
        return $this->__aSource["totalCount"];
    }

    //---

    public function getTotalPaymentAmount(): int
    {
        return $this->__aSource["totalPaymentAmount"];
    }

    //---

    public function getTotalRefundAmount(): int
    {
        return $this->__aSource["totalRefundAmount"];
    }

    //---

    public function getTotalAmount(): int
    {
        return $this->__aSource["totalAmount"];
    }
}

//---

class RegistryRegistryData
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

    /**
     * @return RegistryOrderParam[]
     */
    public function getOrderParams(): array
    {
        return array_map(
            function (array $aItem) {
                return new RegistryOrderParam($aItem);
            },
            isset($this->__aSource["orderParams"]) ? $this->__aSource["orderParams"]["orderParam"] : []
        );
    }
}

//---

class RegistryOrderParam
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

    public function getOrderId(): string
    {
        return $this->__aSource["orderId"];
    }

    //---

    public function getPartnerOrderNumber(): string
    {
        return $this->__aSource["partnerOrderNumber"];
    }

    //---

    public function getAmount(): int
    {
        return $this->__aSource["amount"];
    }

    //---

    public function getCurrency(): string
    {
        return $this->__aSource["currency"];
    }

    //---

    public function getOrderCreateDate(): string
    {
        return $this->__aSource["orderCreateDate"];
    }

    //---

    public function getOrderState(): string
    {
        return $this->__aSource["orderState"];
    }

    //---

    public function getOrderOperationParams(): array
    {
        return array_map(
            function (array $aItem) {
                return new RegistryOrderOperationParam($aItem);
            },
            isset($this->__aSource["orderOperationParams"]) ? $this->__aSource["orderOperationParams"]["orderOperationParam"] : []
        );
    }
}

//---

class RegistryOrderOperationParam
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

    public function getOperationId(): string
    {
        return $this->__aSource["operationId"];
    }

    //---

    public function getOperationDateTime(): string
    {
        return $this->__aSource["operationDateTime"];
    }

    //---

    public function getRrn(): string
    {
        return $this->__aSource["rrn"];
    }

    //---

    public function getOperationType(): string
    {
        return $this->__aSource["operationType"];
    }

    //---

    public function getOperationSum(): int
    {
        return $this->__aSource["operationSum"];
    }

    //---

    public function getOperationCurrency(): string
    {
        return $this->__aSource["operationCurrency"];
    }

    //---

    public function getAuthCode(): string
    {
        return $this->__aSource["authCode"];
    }

    //---

    public function getResponseCode(): string
    {
        return $this->__aSource["responseCode"];
    }

    //---

    public function getResponseDesc(): ?string
    {
        return @$this->__aSource["responseDesc"];
    }

    //---

    public function getSbpOperationParams(): ?RegistrySbpOperationParams
    {
        return isset($this->__aSource["sbpOperationParams"]) ? new RegistrySbpOperationParams($this->__aSource["sbpOperationParams"]) : null;
    }
}

//---

class RegistrySbpOperationParams
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

    public function getSbpOperationId(): string
    {
        return $this->__aSource["sbpOperationId"];
    }

    //---

    public function getSbpPayerId(): string
    {
        return $this->__aSource["sbpPayerId"];
    }
}
