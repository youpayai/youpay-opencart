<?php

use YouPaySDK\OrderItem;
use YouPaySDK\Order;
use YouPaySDK\Client;

class ControllerExtensionPaymentYoupay extends Controller {
	public function index() {

		return $this->load->view('extension/payment/youpay');

	}

	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'youpay') {
			$this->load->model('catalog/product');
			$this->load->model('checkout/order');
			$this->load->model('extension/payment/youpay');


			require_once 'vendor/autoload.php';
			if (empty($this->client)) {
				$this->client = new Client();
			}

			//check if token and store_id are saved
			if($this->model_extension_payment_youpay->getToken() && $this->model_extension_payment_youpay->getStoreID()){
				$this->client->setToken($this->config->get('payment_youpay_token'));
				$this->client->setStoreID($this->config->get('payment_youpay_store_id'));

			}else{
				//token and store_id are not saved, authenticate with youpay API
				//authenticate client
				$youpay_email = $this->config->get('payment_youpay_username');
				$youpay_password = $this->config->get('payment_youpay_password');
				$youpay_domain = $_SERVER['SERVER_NAME'];

				$response = $this->client->auth($youpay_email, $youpay_password, $youpay_domain, 'opencart');
				$access_token = $response->access_token;
				$store_id = $response->store_id;
				$this->client->setToken($access_token);
				$this->client->setStoreID($store_id);
				//save token and store id
				$this->model_extension_payment_youpay->setToken($access_token);
				$this->model_extension_payment_youpay->setStoreID($store_id);

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
						'price'         => $product_data['price'],
						'total'         => $this->tax->calculate($product_data['price'], $product_data['tax_class_id'], $this->config->get('config_tax'))
					)
				);
			}
					
			try {
				$response = $this->client->createOrderFromArray(
					//generate order array
					$youpay_order = array(
						'order_id'    => $order_id,
						'title'       => 'Order #' . $order_id,
						'order_items' => $order_items,
						'extra_fees'  => $this->config->get('payment_youpay_fees'),
						'sub_total'   => $this->cart->getSubTotal(),
						'total'       => $this->cart->getTotal()
					)
				);

			} catch (\Exception $exception) {
				var_dump($exception->getMessage());
			}

			// echo '<pre>';
			// print_r($response);
			// echo '</pre>';
			// die();

			if($response->url){
				$this->session->data['youpay_link'] = $response->url;
				$this->session->data['youpay_order_id'] = $response->id;
			}

			$youpay_comment = "YouPay Link: https://youpay.link/".$response->url;
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_youpay_order_status_hold_id'), $youpay_comment);
		
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}

	public function callback() {
		if (isset($this->request->get['youpay_id'])) {
			$order_id = (int)$this->request->get['youpay_id'];
		} else {
			echo "Order not found";
			return;
		}

		$this->load->model('checkout/order');
		$this->load->model('catalog/product');
		$order_data = $this->model_checkout_order->getOrder($order_id);
		$order_products = $this->model_checkout_order->getOrderProducts($order_id);
		if($order_data){
			
			//create youpay product
			$order_totals = $this->model_checkout_order->getOrderTotals($order_id);

			$youpay_price = 0;
			foreach ($order_totals as $order_total) {
				if($order_total['code']=="total"){
					$youpay_price = (float)$order_total['value'];
				}
			}
			$youpay_product_description = array(
				'1'		=> array(
								'name'				=> 'YouPay',
								'description'		=> 'YouPay',
								'tag'				=> 'YouPay',
								'meta_title'		=> 'YouPay',
								'meta_description'	=> 'YouPay',
								'meta_keyword'		=> 'YouPay',
							)
			);
			$youpay_product_data = array(
				'model'					=> 'youpay',
				'sku'					=> '',
				'upc'					=> '',
				'ean'					=> '',
				'jan'					=> '',
				'isbn'					=> '',
				'mpn'					=> '',
				'location'				=> '',
				'quantity'				=> 9999,
				'minimum'				=> 1,
				'subtract'				=> 0,
				'stock_status_id'		=> 7,
				'date_available'		=> '0000-00-00',
				'date_available'		=> '0000-00-00',
				'manufacturer_id'		=> 0,
				'shipping'				=> 0,
				'price'					=> $youpay_price,
				'points'				=> 0,
				'weight'				=> 1,
				'weight_class_id'		=> 1,
				'length'				=> 1,
				'width' 				=> 1,
				'height'				=> 1,
				'length_class_id'		=> 1,
				'status'				=> 1,
				'tax_class_id'			=> 0,
				'sort_order'			=> 1,
				'product_description'	=> $youpay_product_description,
				'product_store'			=> array(0)
			);

			$youpay_product_id = $this->model_catalog_product->addProduct($youpay_product_data);

			$result = $this->cart->add($youpay_product_id);

			//redirect to checkout
			$this->response->redirect($this->url->link('checkout/checkout'));

		}else{
			echo "Order not found";
			return;
		}

	}
}
