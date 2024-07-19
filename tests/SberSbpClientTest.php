<?php

use Rud99\SberSbp\Client;
use Rud99\SberSbp\Dto\Order;
use Rud99\SberSbp\Dto\OrderItem;
use Rud99\SberSbp\LaravelCacheAdapter;
use Tests\TestCase;

/**
 * vendor/bin/phpunit --verbose --filter test_is_instantiated
 * В сценариях тестирования есть условия -- https://api.developer.sber.ru/product/PlatiQR/doc/v1/QR_API_doc541
 */
class SberSbpClientTest extends TestCase
{
    private Client $__oClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->__oClient = new Client(
            env('SBER_SBP_TERMINAL_ID'),
            env('SBER_SBP_MEMBER_ID'),
            env('SBER_SBP_CLIENT_ID'),
            env('SBER_SBP_CLIENT_SECRET'),
            env('SBER_SBP_CERT_PATH'),
            env('SBER_SBP_CERT_PASSWORD')
        );

        $this->__oClient->setCache(new LaravelCacheAdapter());
    }

    public function test_is_instantiated()
    {
        $this->assertInstanceOf(Client::class, $this->__oClient);
    }

    //---

    public function test_create_order()
    {
        $oOrder = $this->__oClient->create(
            new Order(
                "2",
                "test",
                date("Y-m-d\TH:i:s\Z"),
                10,
                [
                    new OrderItem("test", "test", 1, 10),
                ]
            )
        );

        $this->assertEquals("000000", $oOrder->getErrorCode(), "Код ошибки создания заказа");
    }

    //---

    public function test_revoke_order()
    {
        $oRevoke = $this->__oClient->revoke("27df07b97ecd4bbfbba5bbbc5086744c");

        $this->assertEquals("000000", $oRevoke->getErrorCode(), "Код ошибки отмены неоплаченного заказа");
    }

    //---

    /**
     * Похоже, можно ревёрсить любую операцию, даже ревёрс
     */
    public function test_cancel_order_reverse()
    {
        $oCancel = $this->__oClient->cancel("27df07b97ecd4bbfbba5bbbc5086744c", "91cc352d-ddd9-4a2f-93df-51d89c6a2ed0-e15d170f-5792", Client::CANCEL_OPERATION_TYPE_REVERSE, 5, "test", "43012165");

        $this->assertEquals("000000", $oCancel->getErrorCode(), "Код ошибки отмены заказа");
    }

    //---

    /**
     * Для отмены нужны: operation_id, auth_code, получать записи журнала операций по дате заказа, далее фильтровать по внешнему id заказа
     * ! фильтр даты по московскому времени, т.е. нужно переводить дату заказа, но на тесте срабатывает без перевода
     */
    public function test_cancel_order_refund()
    {
        $oCancel = $this->__oClient->cancel("43012165", "27df07b97ecd4bbfbba5bbbc5086744c", "91cc352d-ddd9-4a2f-93df-51d89c6a2ed0-e15d170f-5792", Client::CANCEL_OPERATION_TYPE_REFUND, 5, "test");

        $this->assertEquals("000000", $oCancel->getErrorCode(), "Код ошибки отмены заказа");
    }

    //---

    public function test_get_order_status()
    {
        $oStatus = $this->__oClient->status("1268286", "71ce0e8a2bfc4335a77f6575ed39cd27");

        /*
         * если заказ PAID, а в истории операций есть REFUND, то вернуть не получилось, нужно искать другой способ
         *
        collect($oStatus->getOrderOperationParams())->first(
            function ($oItem) {
                return "REFUND" == $oItem->getOperationType();
            }
        );
        */

        $this->assertEquals("000000", $oStatus->getErrorCode(), "Код ошибки статуса заказа");

        $this->assertEquals(Order::STATE_PAID, $oStatus->getOrderState());
    }

    //---

    public function test_get_registry_registry()
    {
        // в регистрах нет ожидающих оплаты
        $oRegistry = $this->__oClient->registry(Client::REGISTRY_TYPE_REGISTRY, "2023-03-16 14:00:00", "2023-03-16 15:00:00");

        $this->assertEquals("000000", $oRegistry->getErrorCode(), "Код ошибки регистра операций");
    }

    //---

    public function test_get_registry_quantity()
    {
        $oRegistry = $this->__oClient->registry(Client::REGISTRY_TYPE_QUANTITY, "today", "now");

        $this->assertEquals("000000", $oRegistry->getErrorCode(), "Код ошибки регистра операций");
    }

    public function test_notification()
    {
        [
            "rqTm" => $rqTm,
            "rqUID" => $rqUid,
        ] = $this->postJson(
            "payment/notify",
            [
                "rqUid" => "bc13cA5CE261D2661d99f1fD1Bb049Ac",
                "rqTm" => "2022-03-15T15:52:01Z",
                "memberId" => "00000003",
                "idQR" => "4000101124",
                "tid" => "20163714",
                "orderId" => "bb072868e59e4f06a5ecbc44baa0e63c",
                "partnerOrderNumber" => "1268344",
                "orderState" => "PAID",
                "operationId" => "767fa5f8d7aa4f0fad504bea782518f8",
                "operationDateTime" => "2020-03-19T19:00:39Z",
                "operationType" => "PAY",
                "responseCode" => "00",
                "rrn" => "004207370593",
                "operationSum" => 165 * 100,
                "operationCurrency" => "643",
                "authCode" => "370694",
                "responseDesc" => "ResponseDesc",
                "clientName" => "Иван Иванович И.",
            ]
        )
            ->json();

        $this->assertEquals([$rqTm, $rqUid], ["2022-03-15T15:52:01Z", "bc13cA5CE261D2661d99f1fD1Bb049Ac"]);
    }
}
