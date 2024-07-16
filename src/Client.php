<?php

namespace Rud99\SberSbp;

use Rud99\SberSbp\Dto\CancelResult;
use Rud99\SberSbp\Dto\CreateResult;
use Rud99\SberSbp\Dto\Order;
use Rud99\SberSbp\Dto\OrderItem;
use Rud99\SberSbp\Dto\RegistryResult;
use Rud99\SberSbp\Dto\RevokeResult;
use Rud99\SberSbp\Dto\StatusResult;

class Client
{
    private $__oCurlHandle; // у ресурса нет тайп-хинта, а CurlHandle появился в PHP8
    private ICacheAdapter $__oCache; // кол-во запросов ограничено, поэтому лучше сэкономить на получении токенов
    private bool $__bIsProductionMode;
    private string $__sMemberId, $__sClientId, $__sTerminalId, $__sBasicAuthPair;

    const
        REGISTRY_TYPE_REGISTRY = "REGISTRY",
        REGISTRY_TYPE_QUANTITY = "QUANTITY",
        CANCEL_OPERATION_TYPE_REFUND = "REFUND",
        CANCEL_OPERATION_TYPE_REVERSE = "REVERSE";

    private const
        TOKEN_CACHE_TIME = 50, // токен выдаётся на 60 секунд, но для страховки храним 50 (ещё нужно учесть время на запрос)
        BASE_URI = "https://mc.api.sberbank.ru/prod/",
        AUTH_SCOPE_CREATE = "https://api.sberbank.ru/qr/order.create",
        AUTH_SCOPE_STATUS = "https://api.sberbank.ru/qr/order.status",
        AUTH_SCOPE_REVOKE = "https://api.sberbank.ru/qr/order.revoke",
        AUTH_SCOPE_CANCEL = "https://api.sberbank.ru/qr/order.cancel",
        AUTH_SCOPE_REGISTRY = "auth://qr/order.registry";

    public function __construct(string $sTerminalId, string $sMemberId, string $sClientId, string $sClientSecret, string $sCertPath, string $sCertPassword, bool $bIsProductionMode = false)
    {
        $this->__sMemberId = $sMemberId;
        $this->__sClientId = $sClientId;
        $this->__oCurlHandle = curl_init();
        $this->__sTerminalId = $sTerminalId;
        $this->__bIsProductionMode = $bIsProductionMode;
        $this->__sBasicAuthPair = base64_encode($sClientId . ":" . $sClientSecret);

        curl_setopt_array(
            $this->__oCurlHandle,
            [
                CURLOPT_POST => true,
                CURLOPT_FAILONERROR => true,
                CURLOPT_SSLCERTTYPE => "P12",
                CURLOPT_SSLCERT => $sCertPath, // разместить в хранилище сертификатов
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_VERBOSE => !$bIsProductionMode,
                CURLOPT_SSLCERTPASSWD => $sCertPassword,
            ]
        );
    }

    //---

    public function __destruct()
    {
        curl_close($this->__oCurlHandle);
    }

    //---

    public function setCache(ICacheAdapter $oCache): void
    {
        $this->__oCache = $oCache;
    }

    //---

    /**
     * Создать заказ
     * https://api.developer.sber.ru/product/PlatiQR/doc/v1/QR_API_doc522
     */
    public function create(Order $oOrder): CreateResult
    {
        return new CreateResult(
            $this->request(
                $this->__bIsProductionMode ? "/qr/order/v3/creation/" : "/qr/order/stub/v3/creation/",
                [
                    "currency" => "643",
                    "member_id" => $this->__sMemberId,
                    "sbp_member_id" => "100000000111",
                    "order_sum" => $oOrder->getTotal(),
                    "order_number" => $oOrder->getId(),
                    "description" => $oOrder->getDescription(),
                    "order_create_date" => $oOrder->getCreationDateTime(),
                    "order_params_type" => array_map(
                        function (OrderItem $oItem) {
                            return [
                                "position_sum" => $oItem->getTotal(),
                                "position_name" => $oItem->getTitle(),
                                "position_count" => $oItem->getQuantity(),
                                "position_description" => $oItem->getDescription(),
                            ];
                        },
                        $oOrder->getItems()
                    ),
                ],
                $this->getAccessTokenForScope(self::AUTH_SCOPE_CREATE),
            ),
        );
    }

    //---

    /**
     * Состояние заказа
     * Вообще, будет работать хук, этот метод можно использовать для страховки
     *
     * https://api.developer.sber.ru/product/PlatiQR/doc/v1/QR_API_doc523
     *
     * order_state неконсистентен, может быть PAID, а в order_operation_params REFUND (ориентироваться на конечную операцию)
     */
    public function status(string $sOrderId, string $sExternalOrderId): StatusResult
    {
        return new StatusResult(
            $this->request(
                $this->__bIsProductionMode ? "/qr/order/v3/status/" : "/qr/order/stub/v3/status/",
                [
                    "order_id" => $sExternalOrderId,
                    "partner_order_number" => $sOrderId,
                ],
                $this->getAccessTokenForScope(self::AUTH_SCOPE_STATUS),
                [
                    "id_qr" => "tid",
                ],
            ),
        );
    }

    //---

    /**
     * Отмена неоплаченного заказа
     * https://api.developer.sber.ru/product/PlatiQR/doc/v1/QR_API_doc524
     */
    public function revoke(string $sExternalOrderId): RevokeResult
    {
        return new RevokeResult(
            $this->request(
                $this->__bIsProductionMode ? "/qr/order/v3/revocation/" : "/qr/order/stub/v3/revocation/",
                [
                    "order_id" => $sExternalOrderId,
                ],
                $this->getAccessTokenForScope(self::AUTH_SCOPE_REVOKE),
                [
                    "id_qr" => null,
                ],
            ),
        );
    }

    //---

    /**
     * Отмена/возврат финансовой операции
     * https://api.developer.sber.ru/product/PlatiQR/doc/v1/QR_API_doc525
     */
    public function cancel(string $sAuthCode, string $sExternalOrderId, string $sOperationId, string $sOperationType, int $nOperationTotal, string $sOperationDescription, ?string $sPhoneNumber = null): CancelResult
    {
        $aRequestBody = [
            "auth_code" => $sAuthCode,
            "tid" => $this->__sTerminalId, // в данном случае нужен и id_qr и tid, поэтому не патчим
            "operation_currency" => "643",
            "order_id" => $sExternalOrderId,
            "operation_id" => $sOperationId,
            "operation_type" => $sOperationType,
            "cancel_operation_sum" => $nOperationTotal,
            "operation_description" => $sOperationDescription,
        ];

        // если не указывать, вернётся на ту карту, с которой платили
        if (!empty($sPhoneNumber)) {
            $aRequestBody += [
                "sbp_payer_id" => $sPhoneNumber, // по стандарту E.164 вместо +7 --> 007, хотя в документации сказано -- +7
            ];
        }

        return new CancelResult(
            $this->request($this->__bIsProductionMode ? "/qr/order/v3/cancel/" : "/qr/order/stub/v3/cancel/", $aRequestBody, $this->getAccessTokenForScope(self::AUTH_SCOPE_CANCEL))
        );
    }

    //---

    /**
     * Запрос реестра операций
     * https://api.developer.sber.ru/product/PlatiQR/doc/v1/QR_API_doc526
     */
    public function registry(string $sRegistryType, string $sStartPeriod, string $sEndPeriod): RegistryResult
    {
        return new RegistryResult(
            $this->request(
                $this->__bIsProductionMode ? "/qr/order/v3/registry/" : "/qr/order/stub/v3/registry/",
                [
                    "registryType" => $sRegistryType,
                    "endPeriod" => Util::formatDate($sEndPeriod),
                    "startPeriod" => Util::formatDate($sStartPeriod),
                ],
                $this->getAccessTokenForScope(self::AUTH_SCOPE_REGISTRY),
                [
                    "id_qr" => "idQR",
                    "rq_tm" => "rqTm",
                    "rq_uid" => "rqUid",
                ],
            ),
        );
    }

    //---

    /**
     * @throws \Exception
     */
    private function getAccessTokenForScope(string $sScope): string
    {
        if (empty($this->__oCache)) {
            throw new \Exception("Не установлен адаптер кеша!");
        }

        return $this->__oCache->get(
            $sScope,
            self::TOKEN_CACHE_TIME,
            function () use ($sScope) {
                [
                    "access_token" => $sAccessToken,
                ] = $this->request(
                    "/tokens/v3/oauth/",
                    [
                        "scope" => $sScope,
                        "grant_type" => "client_credentials",
                    ],
                );

                return $sAccessToken;
            }
        );
    }

    //---

    private function request(string $sUri, array $aBody, ?string $sAccessToken = null, ?array $aBodyKeyPatches = null): array
    {
        $sRequestId = md5(microtime());
        $bHasAccessToken = !empty($sAccessToken);
        $fPrepareUrl = function (...$aChunks) {
            return implode(
                "/",
                array_map(
                    function ($sItem) {
                        return trim($sItem, "/");
                    },
                    $aChunks
                )
            );
        };

        if ($bHasAccessToken) {
            $aBody += [
                "rq_uid" => $sRequestId,
                "id_qr" => $this->__sTerminalId, // в некоторых запросах это tid/idQR
                "rq_tm" => Util::prepareDate("now"),
            ];
        }

        if (!empty($aBodyKeyPatches)) {
            foreach ($aBody as $sKey => $mValue) {
                if (array_key_exists($sKey, $aBodyKeyPatches)) {
                    // в случае с revoke не должно быть id_qr
                    if (!empty($aBodyKeyPatches[$sKey])) {
                        $aBody[$aBodyKeyPatches[$sKey]] = $mValue;
                    }

                    unset($aBody[$sKey]);
                }
            }
        }

        curl_setopt_array(
            $this->__oCurlHandle,
            [
                CURLOPT_URL => $fPrepareUrl(self::BASE_URI, $sUri),
                CURLOPT_HTTPHEADER => [
                    "RqUID: " . $sRequestId,
                    "Accept: application/json",
                    "X-IBM-Client-Id: " . $this->__sClientId,
                    "Content-Type: " . ($bHasAccessToken ? "application/json" : "application/x-www-form-urlencoded"),
                    "Authorization: " . ($bHasAccessToken ? "Bearer " . $sAccessToken : "Basic " . $this->__sBasicAuthPair),
                ],
                CURLOPT_POSTFIELDS => $bHasAccessToken ? json_encode($aBody, JSON_UNESCAPED_UNICODE) : http_build_query($aBody, "", "&"),
            ]
        );

        $sResponse = curl_exec($this->__oCurlHandle);

        if (curl_errno($this->__oCurlHandle)) {
            throw new \Exception(curl_error($this->__oCurlHandle));
        }

        return json_decode($sResponse ?: "[]", true);
    }
}
