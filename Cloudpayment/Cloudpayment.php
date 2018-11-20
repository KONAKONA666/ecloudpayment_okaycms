<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 19.11.2018
 * Time: 16:47
 */
require_once('api/Okay.php');
class Cloudpayment extends Okay
{
    public function checkout_form($order_id, $button_text = null)
    {
        if(empty($button_text))
            $button_text = 'Checkout with cloudpayment';
        $order = $this->orders->get_order((int)$order_id);
        $purchases = $this->orders->get_purchases(array('order_id'=>intval($order->id)));
        $payment_method = $this->payment->get_payment_method($order->payment_method_id);
        $currency = $this->money->get_currency(intval($payment_method->currency_id));
        $payment_settings = $this->payment->get_payment_settings($payment_method->id);
        $ipn_url = $this->config->root_url.'/payment/Cloudpayment/callback.php';
        $success_url = $this->config->root_url.'/order/'.$order->url;
        $fail_url = $this->config->root_url.'/order/'.$order->url;
        $verification_url = "https://api.cloudpayments.kz/payments/find";
        $res['verification_url'] = $verification_url;
        $res['currency'] = $currency;
        $res['order'] = $order;
        $res['payment_settings'] = $payment_settings;
        $res['ipn_url'] = $ipn_url;
        $res['success_url'] = $success_url;
        $res['fail_url'] = '/';
        $res['paymnent'] = $this->payment;
        $res['purchases'] = $purchases;
        $res['payment_method'] = $payment_method;
        return $res;
    }
}