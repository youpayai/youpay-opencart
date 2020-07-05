<?php

use YouPaySDK\OrderItem;
use YouPaySDK\Order;
use YouPaySDK\Client;
use YouPaySDK\Receiver;

class ControllerPaymentYoupay extends Controller {
	protected function index() {
		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->data['continue'] = $this->url->link('checkout/success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/youpay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/youpay.tpl';
		} else {
			$this->template = 'default/template/payment/youpay.tpl';
		}

		$this->render();
	}

	public function confirm() {

		$this->load->model('checkout/order');
		$this->load->model('payment/youpay');
		$this->load->model('catalog/product');

		require_once 'vendor/autoload.php';

		if (empty($this->client)) {
			$this->client = new Client();
		}

		//check if token and store_id are saved
		if($this->model_payment_youpay->getToken() && $this->model_payment_youpay->getStoreID()){
			$this->client->setToken($this->config->get('youpay_token'));
			$this->client->setStoreID($this->config->get('youpay_store_id'));

		}else{
			//token and store_id are not saved, authenticate with youpay API
			//authenticate client
			$youpay_email = $this->config->get('youpay_username');
			$youpay_password = $this->config->get('youpay_password');
			$youpay_domain = $_SERVER['SERVER_NAME'];

			$response = $this->client->auth($youpay_email, $youpay_password, $youpay_domain, 'opencart');
			$access_token = $response->access_token;
			$store_id = $response->store_id;
			$this->client->setToken($access_token);
			$this->client->setStoreID($store_id);
			//save token and store id
			$this->model_payment_youpay->setToken($access_token);
			$this->model_payment_youpay->setStoreID($store_id);

		}

		$order_id = $this->session->data['order_id'];
		//get order items
		$order_items = array();
		foreach ($this->model_checkout_order->getOrderProducts($order_id) as $order_product) {
			$product_data = $this->model_catalog_product->getProduct($order_product['product_id']);
			$order_items[] = OrderItem::create(
				array(
					'src'           => HTTPS_SERVER . 'image/' . $product_data['image'],
					'product_id'    => (int)$order_product['product_id'],
					'order_item_id' => (int)$order_product['order_product_id'],
					'title'         => $product_data['name'],
					'quantity'      => (int)$order_product['quantity'],
					'price'         => $order_product['price'],
					'total'         => $this->tax->calculate($order_product['price'], $product_data['tax_class_id'], $this->config->get('config_tax'))
				)
			);
		}

		$order_data = $this->model_checkout_order->getOrder($order_id);
		//build receiver
		$youpay_receiver = 
			array(
				'name'		=> $order_data['firstname'] . " " . $order_data['lastname'],
				'email'		=> $order_data['email'],
				'phone'		=> $order_data['telephone'],
				'address_1' => $order_data['shipping_address_1'],
				'suburb'	=> $order_data['shipping_city'],
				'state'		=> $order_data['shipping_zone'],
				'postcode'  => $order_data['shipping_postcode'],
				'country'   => $order_data['shipping_country']

			
		);

		$shipping = $order_data['total'] - $this->cart->getSubTotal();
						
		$youpay_order = array(
			'order_id'    => $order_id,
			'receiver'	  => $youpay_receiver,
			'title'       => 'Appliance Central Order #' . $order_id,
			'order_items' => $order_items,
			'extra_fees'  => $shipping,
			'sub_total'   => $this->cart->getSubTotal(),
			'total'       => (float)$order_data['total']
		);

		try {
			$response = $this->client->createOrderFromArray($youpay_order);

		} catch (\Exception $exception) {
			var_dump($exception->getMessage());
			die();
		}
		
		if($response->url){
			$this->session->data['youpay_link'] = $response->url;
			$this->session->data['youpay_order_id'] = $response->id;
		}

		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('youpay_order_status_hold_id'));

		$json = array();

		$json['youpay_link'] = "https://youpay.link/".$response->url;

		$this->response->setOutput(json_encode($json));
	}

	public function callback(){
		if (isset($_REQUEST['order_id'])) {
			$youpay_order_id = $_REQUEST['order_id'];
		} else {
			$youpay_order_id = 0;
		}

		require_once 'vendor/autoload.php';
		$this->load->model('checkout/order');

		if (empty($this->client)) {
			$this->client = new Client();
		}

		$this->client->setToken($this->config->get('youpay_token'));
		$this->client->setStoreID($this->config->get('youpay_store_id'));

		$order = $this->client->getOrder($youpay_order_id);
		if($order){
			$store_order_id = (int)$order->store_order_id;

			if($order->completed){
				$payment_status_text = "Order succesfully paid via YouPay app.";
				//order compeletd, mark order as paid
				//$this->model_checkout_order->confirm($store_order_id, $this->config->get('youpay_order_status_id'), $payment_status_text);
				$this->model_checkout_order->update($store_order_id, $this->config->get('youpay_order_status_id'), 'Your order has been paid via YouPay', true);

				$this->redirect((((HTTPS_SERVER) ? HTTPS_SERVER : HTTP_SERVER) . 'index.php?route=payment/youpay/success'));
			}else{
				echo 'order not completed';
			}
		}

	}

	public function success(){
		$this->language->load('payment/youpay');

		$this->document->setTitle($this->language->get('heading_title'));

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/youpay_success.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/youpay_success.tpl';
		} else {
			$this->template = 'default/template/payment/youpay_success.tpl';
		}

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'			
		);

		$this->response->setOutput($this->render());

	}
}
?>