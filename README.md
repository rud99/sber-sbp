# Клиент API Сбер СБП

Клиент API Сбер СБП на основе https://github.com/Dimous/sber-sbp

## Установка

### Установка пакета

```bash
composer require rud99/sber-sbp
```

### Настройка

#### Один из вариантов организации работы с SSL сертификатами:

1. Получаем от Сбера [сертификат](https://api.developer.sber.ru/how-to-use/create_certificate). Имя файла -
   certificate_xxxxxx.p12
2. Сбер выпускает сертификат(хранилище сертификатов) с использованием старого/небезопасного алгоритма типа PKCS12. Чтоб
   его использовать с Curl в библиотеке, необходимо его перевести в новый
   формат. [Реф1](https://forum.clarionlife.net/viewtopic.php?t=4893&start=45), [Реф2](https://stackoverflow.com/questions/72598983/curl-openssl-error-error0308010cdigital-envelope-routinesunsupported)
3. Выполняем преобразование (openssl ver. 1.x.x):
   ```openssl pkcs12 -in certificate_xxxxxx.p12 -nodes | openssl pkcs12 -export -descert -out new_certificate_xxxxxx.p12```
4. Файл(new_certificate_xxxxxx.p12) помещаем в ````storage/app/certs```` !!! ВАЖНО !!!

#### Настройка окружения 
Добавляем в .env
````SBER_SBP_TERMINAL_ID=33188266
   SBER_SBP_MEMBER_ID=0000xxxx
   SBER_SBP_CLIENT_ID=xxxx-xxx-xxxx-xxxx-xxxx
   SBER_SBP_CLIENT_SECRET=xxxxxxx-xxxx-xxxx-xxxx-xxxx
   SBER_SBP_CERT_PATH="certs/new_certificate_xxxxxx.p12" !!! ВАЖНО !!!
   SBER_SBP_CERT_PASSWORD=xxxx
   SBER_SBP_IS_PRODUCTION=true(false) 
   ````

## Использование

### coming soon

## Тестирование !!! Пока не работает !!!
### coming soon

[//]: # (```bash)

[//]: # (composer test)

[//]: # (```)
