# Модуль оплаты CloudPayment для Okay CMS

## Установка

  * [Скачайте архив модуля](https://github.com/KONAKONA666/ecloudpayment_okaycms/blob/master/Cloudpayment.zip), распакуйте его и скопируйте его содержимое в корневая_директорая_Okay CMS/payment.

## Настройка

  * Войдите в личный кабинет администратора, откройте Настройки - Оплата и создайте новый вид оплаты, при этом выбрать Cloudpayment в качестве платежного модуля
  * Указать параметры Public Id, API PASSWORD
  * Установить флажок Активен
  * Остальные параметры в соответствии с конкретными предпочтениями

После этого доступен новый модуль оплаты.

Далее вам нужно отредактировать шаблон вашей темы интернет-магазина. Ниже приведены изменения для шаблона по умолчанию, который находится по пути:

```bash
design/okay_shop/html/payments_form.tpl
```

Отредактируйте данный шаблон и добавьте в его конец следующий код:

```html
{elseif $payment_module == "Cloudpayment"}

    <button id="payButton">Открыть форму оплаты</button>
    <script src="https://widget.cloudpayments.kz/bundles/cloudpayments"></script>
    <script type="text/javascript">
        var payHandler = function () {
            var widget = new cp.CloudPayments();
            widget.charge({
                    publicId: '{$payment_settings['public_id']}',
                    description: 'Оплата в okaycms',
                    amount: {$order->total_price},
                    currency: 'KZT',
                    invoiceId: '{$order->id}',
                    accountId: '{$order->email}',
                },
                function (options) { // success
                    $.post('{$ipn_url}', {
                        'invoice': options['invoiceId'],
                    }, function (data) {
                        window.location.href = '{$success_url}';
                    });

                },
                function (reason, options) { // fail
                    window.location.href = '{$fail_ur}';
                });
        };
        $("#payButton").on("click", payHandler); //кнопка "Оплатить"
    </script>

{/if}
```



## PUBLIC ID and API PASSWORD

  * Войдите в личный кабинет по адресу https://merchant.cloudpayments.kz
  * В меню слева выберите пункт Сайты
  * Создайте или выберите уже существующий магазин
  * Public ID - это Public Id, Пароль для API - это API PASSWORD


## Тестовые данные

https://cloudpayments.kz/Docs/Test 

## Валюты

Коды валют должны соответствовать этой ссылке https://cloudpayments.kz/Docs/Directory#currencies

