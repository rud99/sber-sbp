<?php

namespace Rud99\SberSbp\Dto;

use Rud99\SberSbp\Util;

class OrderItem
{
    private int $__nTotal, $__nQuantity;
    private string $__sTitle, $__sDescription;

    public function __construct(string $sTitle, string $sDescription, int $nQuantity, int $nTotal)
    {
        $this->__nTotal = $nTotal;
        $this->__nQuantity = $nQuantity;
        $this->__sTitle = Util::sanitizeString($sTitle, 256);
        $this->__sDescription = Util::sanitizeString($sDescription, 512);
    }

    //---

    public function getTitle(): string
    {
        return $this->__sTitle;
    }

    //---

    /**
     * Стоимость в копейках (рубли * 100)
     */
    public function getTotal(): int
    {
        return $this->__nTotal;
    }

    //---

    public function getQuantity(): int
    {
        return $this->__nQuantity;
    }

    //---

    public function getDescription(): string
    {
        return $this->__sDescription;
    }
}

