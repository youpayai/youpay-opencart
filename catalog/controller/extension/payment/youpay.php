<?php

use YouPaySDK\OrderItem;
use YouPaySDK\Order;
use YouPaySDK\Client;
use YouPaySDK\Receiver;

class ControllerExtensionPaymentYoupay extends Controller {
	public function index() {

		return $this->load->view('extension/payment/youpay');

	}

	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'youpay') {
			$this->load->model('catalog/product');
			$this->load->model('account/order');
			$this->load->model('checkout/order');
			$this->load->model('extension/payment/youpay');


			require_once 'vendor/autoload.php';
			if (empty($this->client)) {
				$this->client = new Client();
			}

			//check if token and store_id are saved
			if($this->model_extension_payment_youpay->getToken() && $this->model_extension_payment_youpay->getStoreID()){
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
				$this->model_extension_payment_youpay->setToken($access_token);
				$this->model_extension_payment_youpay->setStoreID($store_id);

			}

			$order_id = $this->session->data['order_id'];
			//get order items
			$order_items = array();
			foreach ($this->model_account_order->getOrderProducts($order_id) as $order_product) {
				$product_data = $this->model_catalog_product->getProduct($order_product['product_id']);
				//check for special
				if($product_data['special']){
					$price = $product_data['special'];
				}else{
					$price = $product_data['price'];
				}
				$order_items[] = OrderItem::create(
					array(
						'src'           => HTTPS_SERVER . 'image/' . $product_data['image'],
						'product_id'    => (int)$order_product['product_id'],
						'order_item_id' => (int)$order_product['order_product_id'],
						'title'         => $product_data['name'],
						'quantity'      => (int)$order_product['quantity'],
						'price'         => $price,
						'total'         => $this->tax->calculate($price, $product_data['tax_class_id'], $this->config->get('config_tax'))
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
				'title'       => $this->config->get('config_name'). " order #" . $order_id,
				'order_items' => $order_items,
				'extra_fees'  => $shipping,
				'sub_total'   => $this->cart->getSubTotal(),
				'total'       => (float)$order_data['total']
			);


			try {
				$response = $this->client->createOrderFromArray($youpay_order);

			} catch (\Exception $exception) {
				var_dump($exception->getMessage());
			}

			if($response->url){
				$this->session->data['youpay_link'] = $response->url;
				$this->session->data['youpay_order_id'] = $response->id;
			}

			//get YouPayJS
			$this->session->data['youpay_js_url'] = $this->client->getCheckoutJSUrl();

			$youpay_comment = "YouPay Link: ".$response->url;
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('youpay_order_status_hold_id'), $youpay_comment);
		
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}

	public function callback() {
		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
			$youpay_order_id = $this->request->get['youpay_id'];
		} else {
			echo "Order not found";
			return;
		}

		require_once 'vendor/autoload.php';
		$this->load->model('checkout/order');
		$this->load->model('catalog/product');

		$this->language->load('extension/payment/youpay');

		if (empty($this->client)) {
			$this->client = new Client();
		}

		$this->client->setToken($this->config->get('youpay_token'));
		$this->client->setStoreID($this->config->get('youpay_store_id'));

		$youpay_order = $this->client->getOrder($youpay_order_id);

		if($youpay_order && $youpay_order->completed){

			$order_data = $this->model_checkout_order->getOrder($order_id);
			if($order_data){
				$payment_status_text = $this->language->get('text_order_complete');
				// $this->model_checkout_order->update($store_order_id, $this->config->get('youpay_order_status_id'), $payment_status_text, true);
				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('youpay_order_status_id'), $payment_status_text, true, true);

				$this->response->redirect($this->url->link('extension/payment/youpay/success', '', true));

			}else{
				echo "Order not found";
				return;
			}

		}else{
			echo "YouPay order not found or not completed";
			return;
		}
	}

	public function success(){
		$this->language->load('extension/payment/youpay');


		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['text_message'] = $this->language->get('text_payment_complete');

		$data['button_continue'] = $this->language->get('button_continue');

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');


		$this->response->setOutput($this->load->view('common/success', $data));
	}
}
