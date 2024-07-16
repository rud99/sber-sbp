<?php

namespace Rud99\SberSbp\Dto;
class CreateResult
{
    private array $__aSource;

    public function __construct(array $aSource)
    {
        $this->__aSource = $aSource;
    }

    //---

    /**
     * ID заказа в АС ППРБ.Карты
     */
    public function getOrderId(): ?string
    {
        return @$this->__aSource["order_id"];
    }

    //---

    /**
     * Код выполнения запроса
     */
    public function getErrorCode(): string
    {
        return $this->__aSource["error_code"];
    }

    //---

    /**
     * Статус заказа enum: ["PAID", "CREATED", "REVERSED", "REFUNDED", "REVOKED", "DECLINED", "EXPIRED", "AUTHORIZED", "CONFIRMED", "ON_PAYMENT"]
     */
    public function getOrderState(): ?string
    {
        return @$this->__aSource["order_state"];
    }

    //---

    /**
     * Номер заказа в CRM Клиента
     */
    public function getOrderNumber(): ?string
    {
        return @$this->__aSource["order_number"];
    }

    //---

    /**
     * Ссылка на считывание QR code
     */
    public function getOrderFormUrl(): ?string
    {
        return @$this->__aSource["order_form_url"];
    }

    //---

    /**
     * Описание ошибки выполнения запроса
     */
    public function getErrorDescription(): ?string
    {
        return @$this->__aSource["error_description"];
    }
}


