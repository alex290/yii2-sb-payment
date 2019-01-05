Платежный шлюз Сбербанк
=======================
Платежный шлюз Сбербанк

Установка
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist alex290/yii2-sb-payment "*"
```

or add

```
"alex290/yii2-sb-payment": "*"
```

to the require section of your `composer.json` file.


Регистрация платежа
-----


	<?php
	
	
	use yii\helpers\Html;
	use alex290\sbpayment\Payment;
	use yii\helpers\Url;
	
	
	$sbPayment = new Payment();
	$sbPayment->userName = 'login-api'; // логин api мерчанта
	$sbPayment->password = 'password'; // пароль api мерчанта
	$sbPayment->orderNumber = 121; //Номер ордера в Вашем магазине
	
	$sbPayment->returnUrl = Url::home(true).'/payment/success'; //Страница ваозврата после оплаты
	$sbPayment->failUrl = Url::home(true).'/payment/fail'; //Страница неудачной оплаты
	
	$sbPayment->amount = 4654; // Сумма в копейках
	
	
	/**
     * Сервера
     * тестовый - 'https://3dsec.sberbank.ru/payment/rest/'
     * рабочий - 'https://securepayments.sberbank.ru/payment/rest/'
     */
	
    $sbPayment->server = 'https://3dsec.sberbank.ru/payment/rest/';
	
	$regOrder = $sbPayment->register(); //Отправка данных на сервер сбербанка и получение данных для отправки платежа
	
	?>



Создаем кнопку дла проведения платежа
-----

	<?php if ($regOrder): ?>
	
	<?= Html::a('Оплатить сумму', $regOrder['formUrl'], ['class' => 'btn btn-secondary', 'target'=> "_blank"]) ?>
	
	<?php endif; ?>


После оплаты страница перенаправит на страницу по адресу payment/success?orderId=0c0f9700-7b0c-78f3-889b-713404b38c28&lang=ru

где orderId это номер заказа в платёжном шлюзе. Уникален в пределах шлюза.

на этой странице делаем в контроллере 

	<?php
	
	namespace app\controllers;
	
	use app\models\Order;
	use Yii;
	
	class PaymentController extends \yii\web\Controller
	{
		public function actionSuccess()
		{
			$orderPayId = \Yii::$app->request->get('orderId');
			return $this->render('success', ['orderPayId' => $orderPayId]);
		}
	    
	    
		public function actionFail($param) 
		{
    		
			return $this->render('fail');
	
		}
	}

и во вьюшке success.php получем полную информацию о текущем платеже

	<?php
	
	
	use yii\helpers\Html;
	use alex290\sbpayment\Payment;
	
	
	$this->title = 'Платеж завершён';
	
	$sbPayment = new Payment();
	
	$sbPayment->userName = 'login-api';
	$sbPayment->password = 'password';
	
	$getOrder = $sbPayment->getOrderInfo($orderPayId); //получем полную информацию о текущем платеже
	?>
