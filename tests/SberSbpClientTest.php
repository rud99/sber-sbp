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

    /**
     * сценарий 1 (оплата заказа, отмена оплаченного заказа) -- https://api.developer.sber.ru/product/PlatiQR/doc/v1/QR_API_doc541
     */

    public function _positive_case_REVERSE()
    {
        /**
         * выполнить запрос /creation, получить успешный ответ с order_state=CREATED. Заказ создан. Заказ автоматически переводится в статус PAID через минуту после создания;
         */
        $oOrder = $this->__oClient->create(
            new Order(
                rand(),
                "test",
                date("Y-m-d\TH:i:s\Z"),
                10,
                [
                    new OrderItem("test", "test", 1, 10),
                ]
            )
        );

        $this->assertEquals(Order::STATE_CREATED, $oOrder->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oOrder->getErrorCode(), "Код ошибки создания заказа");

        /**
         * выполнить запрос /status в течении минуты после создания, получить успешный ответ с order_state=CREATED (заказ еще не оплачен, передавать товар клиенту нельзя);
         */
        sleep(30);
        $oStatus = $this->__oClient->status($oOrder->getOrderNumber(), $oOrder->getOrderId());

        $this->assertEquals(Order::STATE_CREATED, $oStatus->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oStatus->getErrorCode(), "Код ошибки статуса заказа");

        /**
         * выполнить запрос /status через минуту, получить успешный ответ с order_state=PAID. Заказ оплачен клиентом в его мобильном приложении.
         * Кейс срабатывает если найден ранее созданный заказ со статусом CREATED.
         * При переводе заказа в статус PAID создается набор параметров авторизации order_operation_params (для статуса CREATED order_operation_params не создается);
         */
        sleep(35); // ожидаем еще 35 секунд, чтобы заказ перешёл в статус PAID
        $oStatus = $this->__oClient->status($oOrder->getOrderNumber(), $oOrder->getOrderId());

        $this->assertEquals(Order::STATE_PAID, $oStatus->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oStatus->getErrorCode(), "Код ошибки статуса заказа");

        /**
         * выполнить запрос отмены оплаченного заказа /cancel (заказ в статусе PAID):
         * с operation_type=REVERSE, получить успешный ответ с order_state=REVERSED;
         */
        /**
         * Для отмены нужны: operation_id, auth_code, получать записи журнала операций по дате заказа, далее фильтровать по внешнему id заказа
         * ! фильтр даты по московскому времени, т.е. нужно переводить дату заказа, но на тесте срабатывает без перевода
         */
        $oRegistry = $this->__oClient->registry(Client::REGISTRY_TYPE_REGISTRY, "today", "now");
        $regOrder = collect($oRegistry->getRegistryData()->getOrderParams())->first(
            function ($oItem) use ($oOrder) {
                return $oItem->getOrderId() === $oOrder->getOrderId() && $oItem->getOrderState() === Order::STATE_PAID;
            }
        );
        $opParam = collect($regOrder->getOrderOperationParams())->first(
            function ($oItem) {
                return $oItem->getOperationType() === 'PAY';
            }
        );
        $oCancel = $this->__oClient->cancel(
            $opParam->getAuthCode(),
            $regOrder->getOrderId(),
            $opParam->getOperationId(),
            Client::CANCEL_OPERATION_TYPE_REVERSE,
            $opParam->getOperationSum(),
            "test");
        dump($oCancel);

        $this->assertEquals(Order::STATE_REVERSED, $oCancel->getOrderStatus(), "Статус заказа");
        //!!! код ошибки - 990000 Операция в обработке QR СБП Для внутреннего использования, не должен обрабатываться как ошибка !!!
        $this->assertEquals("990000", $oCancel->getErrorCode(), "Код ошибки отмены заказа");

        $this->assertEquals(Order::STATE_REVERSED, $oStatus->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oStatus->getErrorCode(), "Код ошибки статуса заказа");
    }

    public function test_positive_case_REFUND()
    {
        /**
         * выполнить запрос /creation, получить успешный ответ с order_state=CREATED. Заказ создан. Заказ автоматически переводится в статус PAID через минуту после создания;
         */
        $oOrder = $this->__oClient->create(
            new Order(
                rand(),
                "test",
                date("Y-m-d\TH:i:s\Z"),
                10,
                [
                    new OrderItem("test", "test", 1, 10),
                ]
            )
        );

        $this->assertEquals(Order::STATE_CREATED, $oOrder->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oOrder->getErrorCode(), "Код ошибки создания заказа");

        /**
         * выполнить запрос /status в течении минуты после создания, получить успешный ответ с order_state=CREATED (заказ еще не оплачен, передавать товар клиенту нельзя);
         */
        sleep(30);
        $oStatus = $this->__oClient->status($oOrder->getOrderNumber(), $oOrder->getOrderId());

        $this->assertEquals(Order::STATE_CREATED, $oStatus->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oStatus->getErrorCode(), "Код ошибки статуса заказа");

        /**
         * выполнить запрос /status через минуту, получить успешный ответ с order_state=PAID. Заказ оплачен клиентом в его мобильном приложении.
         * Кейс срабатывает если найден ранее созданный заказ со статусом CREATED.
         * При переводе заказа в статус PAID создается набор параметров авторизации order_operation_params (для статуса CREATED order_operation_params не создается);
         */
        sleep(35); // ожидаем еще 35 секунд, чтобы заказ перешёл в статус PAID
        $oStatus = $this->__oClient->status($oOrder->getOrderNumber(), $oOrder->getOrderId());

        $this->assertEquals(Order::STATE_PAID, $oStatus->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oStatus->getErrorCode(), "Код ошибки статуса заказа");

        /**
         * выполнить запрос отмены оплаченного заказа /cancel (заказ в статусе PAID):
         * с operation_type=REFUND/null, получить успешный ответ с order_state=REFUNDED
         */
        /**
         * Для отмены нужны: operation_id, auth_code, получать записи журнала операций по дате заказа, далее фильтровать по внешнему id заказа
         * ! фильтр даты по московскому времени, т.е. нужно переводить дату заказа, но на тесте срабатывает без перевода
         */
        $oRegistry = $this->__oClient->registry(Client::REGISTRY_TYPE_REGISTRY, "today", "now");
        $regOrder = collect($oRegistry->getRegistryData()->getOrderParams())->first(
            function ($oItem) use ($oOrder) {
                return $oItem->getOrderId() === $oOrder->getOrderId() && $oItem->getOrderState() === Order::STATE_PAID;
            }
        );
        $opParam = collect($regOrder->getOrderOperationParams())->first(
            function ($oItem) {
                return $oItem->getOperationType() === 'PAY';
            }
        );
        $oCancel = $this->__oClient->cancel(
            $opParam->getAuthCode(),
            $regOrder->getOrderId(),
            $opParam->getOperationId(),
            Client::CANCEL_OPERATION_TYPE_REFUND,
            $opParam->getOperationSum(),
            "test");
        dump($oCancel);

        $this->assertEquals(Order::STATE_REFUNDED, $oCancel->getOrderStatus(), "Статус заказа");
        //!!! код ошибки - 990000 Операция в обработке QR СБП Для внутреннего использования, не должен обрабатываться как ошибка !!!
        $this->assertEquals("990000", $oCancel->getErrorCode(), "Код ошибки отмены заказа");

        $oStatus = $this->__oClient->status($oOrder->getOrderNumber(), $oOrder->getOrderId());

        $this->assertEquals(Order::STATE_REFUNDED, $oStatus->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oStatus->getErrorCode(), "Код ошибки статуса заказа");
    }


    /**
     * выполнить запрос /registry
     * с registryType=REGISTRY, получить успешный ответ с перечнем операций за период
     */
    public function test_get_registry_registry()
    {
        $oRegistry = $this->__oClient->registry(Client::REGISTRY_TYPE_REGISTRY, "today", "now");

        $this->assertEquals("000000", $oRegistry->getErrorCode(), "Код ошибки регистра операций");
    }

    /**
     * выполнить запрос /registry
     * с registryType=QUANTITY, получить успешный ответ с агрегированными данными по операциям за период
     */
    public function test_get_registry_quantity()
    {
        $oRegistry = $this->__oClient->registry(Client::REGISTRY_TYPE_QUANTITY, "today", "now");

        $this->assertEquals("000000", $oRegistry->getErrorCode(), "Код ошибки регистра операций");
    }

    /**
     * сценарий 2 (отмена неоплаченного заказа) -- https://api.developer.sber.ru/product/PlatiQR/doc/v1/QR_API_doc541
     */
    public function test_negative_case()
    {
        /**
         * выполнить запрос /creation, получить успешный ответ с order_state=CREATED. Заказ создан
         */
        $oOrder = $this->__oClient->create(
            new Order(
                rand(),
                "test",
                date("Y-m-d\TH:i:s\Z"),
                10,
                [
                    new OrderItem("test", "test", 1, 10),
                ]
            )
        );

        $this->assertEquals(Order::STATE_CREATED, $oOrder->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oOrder->getErrorCode(), "Код ошибки создания заказа");


        /**
         * выполнить запрос /status в течении минуты, получить успешный ответ с order_state=CREATED
         */
        sleep(30);
        $oStatus = $this->__oClient->status($oOrder->getOrderNumber(), $oOrder->getOrderId());
        $this->assertEquals(Order::STATE_CREATED, $oStatus->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oStatus->getErrorCode(), "Код ошибки статуса заказа");

        /**
         * выполнить запрос /revocation, получить успешный ответ с order_state=REVOKED. Неоплаченный заказ отменен (Например, если клиент отказался от оплаты).
         */
        $oRevoke = $this->__oClient->revoke($oStatus->getOrderId());

        $this->assertEquals(Order::STATE_REVOKED, $oRevoke->getOrderState(), "Статус заказа");
        $this->assertEquals("000000", $oRevoke->getErrorCode(), "Код ошибки отмены неоплаченного заказа");
    }

    //    public function test_notification()
//    {
//        [
//            "rqTm" => $rqTm,
//            "rqUID" => $rqUid,
//        ] = $this->postJson(
//            "payment/notify",
//            [
//                "rqUid" => "bc13cA5CE261D2661d99f1fD1Bb049Ac",
//                "rqTm" => "2022-03-15T15:52:01Z",
//                "memberId" => "00000003",
//                "idQR" => "4000101124",
//                "tid" => "20163714",
//                "orderId" => "bb072868e59e4f06a5ecbc44baa0e63c",
//                "partnerOrderNumber" => "1268344",
//                "orderState" => "PAID",
//                "operationId" => "767fa5f8d7aa4f0fad504bea782518f8",
//                "operationDateTime" => "2020-03-19T19:00:39Z",
//                "operationType" => "PAY",
//                "responseCode" => "00",
//                "rrn" => "004207370593",
//                "operationSum" => 165 * 100,
//                "operationCurrency" => "643",
//                "authCode" => "370694",
//                "responseDesc" => "ResponseDesc",
//                "clientName" => "Иван Иванович И.",
//            ]
//        )
//            ->json();
//
//        $this->assertEquals([$rqTm, $rqUid], ["2022-03-15T15:52:01Z", "bc13cA5CE261D2661d99f1fD1Bb049Ac"]);
//    }

//    /**
//     * Похоже, можно ревёрсить любую операцию, даже ревёрс
//     */
//    public function test_cancel_order_reverse()
//    {
//        $oCancel = $this->__oClient->cancel("27df07b97ecd4bbfbba5bbbc5086744c", "91cc352d-ddd9-4a2f-93df-51d89c6a2ed0-e15d170f-5792", Client::CANCEL_OPERATION_TYPE_REVERSE, 5, "test", "43012165");
//
//        $this->assertEquals("000000", $oCancel->getErrorCode(), "Код ошибки отмены заказа");
//    }

}
