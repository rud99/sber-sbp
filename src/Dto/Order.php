<?php

namespace Rud99\SberSbp\Dto;

use Rud99\SberSbp\Util;

class Order
{
    private int $__nTotal;
    /**
     * @var OrderItem[]
     */
    private array $__aItems;
    private string $__sId, $__sDescription, $__sCreationDateTime;

    const
        STATE_PAID = "PAID",
        STATE_CREATED = "CREATED",
        STATE_REVOKED = "REVOKED",
        STATE_EXPIRED = "EXPIRED",
        STATE_REVERSED = "REVERSED",
        STATE_REFUNDED = "REFUNDED",
        STATE_DECLINED = "DECLINED",
        STATE_CONFIRMED = "CONFIRMED",
        STATE_AUTHORIZED = "AUTHORIZED",
        STATE_ON_PAYMENT = "ON_PAYMENT";

    public function __construct(string $sId, string $sDescription, string $sCreationDateTime, int $nTotal, array $aItems)
    {
        $this->__sId = $sId;
        $this->__nTotal = $nTotal;
        $this->__aItems = $aItems;
        $this->__sCreationDateTime = $sCreationDateTime;
        $this->__sDescription = Util::sanitizeString($sDescription, 140);
    }

    //---

    public function getId(): string
    {
        return $this->__sId;
    }

    //---

    /**
     * Итого в копейках (рубли * 100)
     */
    public function getTotal(): int
    {
        return $this->__nTotal;
    }

    //---

    public function getItems(): array
    {
        return $this->__aItems;
    }

    //---

    public function getDescription(): string
    {
        return $this->__sDescription;
    }

    //---

    /**
     * Переводить в Y-m-dTH:i:sZ
     */
    public function getCreationDateTime(): string
    {
        return $this->__sCreationDateTime;
    }
}


