<?php

namespace Rud99\SberSbp;
final class Util
{
    private const DATE_FORMAT = "Y-m-d\TH:i:s\Z";

    public static function prepareDate(string $sDateTime): string
    {
        return date(self::DATE_FORMAT, strtotime($sDateTime));
    }

    //---

    /**
     * Заказы по владивостокскому времени, а журнал операций по московскому
     */
    public static function formatDate(string $sSourceDateTime, string $sTargetTimeZone = "Europe/Moscow"): string
    {
        $oTargetDateTime = date_create($sSourceDateTime, timezone_open(date_default_timezone_get()));

        date_timezone_set($oTargetDateTime, timezone_open($sTargetTimeZone));

        return date_format($oTargetDateTime, self::DATE_FORMAT);
    }

    //---

    public static function sanitizeString(string $sInput, int $nLimit): string
    {
        return mb_strimwidth(preg_replace("#([!\"\#\$%&'()*+\-/<=>?@\[\]^_`{|}~])#", "\\\\$1", preg_replace("/[^\S\r\n]+/", " ", preg_replace("/[^([:alnum:][:punct:])]/u", " ", strip_tags($sInput)))), 0, $nLimit, "...");
    }
}


