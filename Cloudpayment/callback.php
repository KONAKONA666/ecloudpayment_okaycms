<?php
chdir ('../../');
require_once('api/Okay.php');
$okay = new Okay();
// Get the order
$order = $okay->orders->get_order(intval($okay->request->post('invoice')));
if(empty($order))
    echo "error";
// Get payment method from this order
$method = $okay->payment->get_payment_method(intval($order->payment_method_id));
if(empty($method))
    die("Unknown payment method");
$payment_settings = $okay->payment->get_payment_settings($order->payment_method_id);
// Verify transaction
$data = array(
    'InvoiceId' => $order->id,
);
$curl = curl_init('https://api.cloudpayments.kz/payments/find');
curl_setopt($curl, CURLOPT_USERPWD, $payment_settings['public_id'].":".$payment_settings['api_pass']);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
$res = json_decode(curl_exec($curl));
curl_close ($curl);

// Проверка оплаты

if (!$res->Success)
    die('Incorrect payment');

if ($res->Model->Status != "Completed" )
    die('Incorrect status ');

// Уже оплачен ?
if($order->paid)
    die('Duplicate payment');
//
//
//////////////////////////////////////
//// Верификация суммы оплаты
//////////////////////////////////////
$total_price = 0;

// Товары
$purchases = $okay->orders->get_purchases(array('order_id'=>intval($order->id)));
foreach($purchases as $purchase)
{
	$price = $okay->money->convert($purchase->price, $method->currency_id, false);
	$price = round($price, 2);
	$total_price += $price*$purchase->amount;
}
// Скидка
if($order->discount)
{
	$total_price *= (100-$order->discount)/100;
	$total_price = round($total_price, 2);
}
// Цена за доставку
if($order->delivery_id && !$order->separate_delivery && $order->delivery_price>0)
{
	$delivery_price = $okay->money->convert($order->delivery_price, $payment_method->currency_id, false);
	$delivery_price =round($delivery_price, 2);
	$total_price += $delivery_price;
}
if($total_price != $res->Model->PaymentAmount)
	die("Incorrect total price");

// Обновление статуса заказа
$okay->orders->update_order(intval($order->id), array('paid'=>1));

// списания и оповещение
$okay->orders->close(intval($order->id));
$okay->notify->email_order_user(intval($order->id));
$okay->notify->email_order_admin(intval($order->id));
function logg($str)
{
    file_put_contents('payment/Cloudpayment/log.txt', file_get_contents('payment/Cloudpayment/log.txt')."\r\n".date("m.d.Y H:i:s").' '.$str);
}