<?php

namespace alex290\sbpayment;

use yii\base\Component;
use Yii;

class Payment extends Component {
    /*
     * Логин служебной учётной записи продавца. 
     * При передаче логина и пароля для аутентификации 
     * в платёжном шлюзе параметр token передавать не нужно.
     */

    public $userName = 'login-api';

    /*
     * Пароль служебной учётной записи продавца. 
     * При передаче логина и пароля для аутентификации 
     * в платёжном шлюзе параметр token передавать не нужно.
     */
    public $password = 'password';

    /*
     * Значение, которое используется для аутентификации продавца 
     * при отправке запросов в платёжный шлюз. 
     * При передаче этого параметра параметры userName и pаssword передавать не нужно
     */
    public $token = null;

    /**
     * Сервера
     * тестовый - 'https://3dsec.sberbank.ru/payment/rest/'
     * рабочий - 'https://securepayments.sberbank.ru/payment/rest/'
     */
    public $server = 'https://3dsec.sberbank.ru/payment/rest/';

    /*
     * Сумма платежа в минимальных единицах валюты (копейки, центы и т. п.).
     */
    public $amount; // Сумма платежа

    /*
     * Номер (идентификатор) заказа в системе магазина, 
     * уникален для каждого магазина в пределах системы - до 30 символов. 
     * Если номер заказа генерируется на стороне платёжного шлюза, 
     * этот параметр передавать необязательно.
     */
    public $orderNumber;


    /*
     * Код валюты платежа ISO 4217. Единственное допустимое значение - 643.
     */
    public $currency = 643;

    /*
     * Адрес, на который требуется перенаправить пользователя в случае успешной оплаты. 
     * Адрес должен быть указан полностью, включая используемый протокол 
     * (например, https://test.ru вместо test.ru).
     */
    public $returnUrl = 'http://test.ru/payment/success';


    /*
     * Адрес, на который требуется перенаправить пользователя 
     * в случае неуспешной оплаты. Адрес должен быть указан полностью, 
     * включая используемый протокол (например, https://test.ru вместо test.ru)
     */
    public $failUrl = 'http://test.ru/payment/fail';

    /*
     * Создание нового ордера в сбербанке - register.do
     */
    public function register() {

        if ($this->token == null) {
            $aut = 'userName=' . $this->userName . '&password=' . $this->password;
        } else {
            $aut = 'token=' . $this->token;
        }

        $dataUrl = 'amount=' . $this->amount . '&' .
                'orderNumber=' . $this->orderNumber . '&' .
                'currency=' . $this->currency . '&' .
                'returnUrl=' . $this->returnUrl . '&' .
                'failUrl=' . $this->failUrl;



        $data = $aut . '&' . $dataUrl;

        return $this->getRequest($data, 'register.do');
        
    }
    
    /*
     * Получение статуса заказа
     */
    public function getOrderInfo($orderId){
        if ($this->token == null) {
            $aut = 'userName=' . $this->userName . '&password=' . $this->password;
        } else {
            $aut = 'token=' . $this->token;
        }
        
        $data = $aut . '&' . 'orderId='.$orderId;
        
        return $this->getRequest($data, 'getOrderStatusExtended.do');
    }
    
    protected function getRequest($param, $type) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->server.$type);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
        
        $response = curl_exec($curl);
        
        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl));
        }
        curl_close($curl);
        if ($response === false) {
            Yii::error('Error RBS. Connection error with payment gateway.');
        } else {
            $response = json_decode($response, true);
            if (!empty($response['errorCode'])) {
                Yii::error('Error RBS [' . $response['errorCode'] . '] ' . $response['errorMessage']);
            } else {
                return $response;
            }
        }
        return null;
    }

}
