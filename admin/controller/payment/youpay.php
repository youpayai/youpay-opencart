<?php
class ControllerPaymentYoupay extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/youpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if($this->checkConnection($this->request->post['youpay_username'], $this->request->post['youpay_password'])){
				$this->model_setting_setting->editSetting('youpay', $this->request->post);

				$this->session->data['success'] = $this->language->get('text_success');

				$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true));
			}else{
				$this->error['warning'] = $this->language->get('error_connection');
			}
		}

		$data['heading_title'] = $this->language->get('heading_title');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		}elseif(isset($this->session->data['success_token'])){
			$data['error_warning'] = $this->session->data['success_token'];
			unset($this->session->data['success_token']);
		}else {
			$data['error_warning'] = '';
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_authorization'] = $this->language->get('text_authorization');
		$data['text_sale'] = $this->language->get('text_sale');

		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_username'] = $this->language->get('entry_username');
		$data['entry_password'] = $this->language->get('entry_password');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_order_status_hold'] = $this->language->get('entry_order_status_hold');
		$data['entry_total'] = $this->language->get('entry_total');

		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_order_status_hold'] = $this->language->get('help_order_status_hold');
		$data['help_order_status'] = $this->language->get('help_order_status');
		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_reset'] = $this->language->get('button_reset');


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/youpay', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('payment/youpay', 'token=' . $this->session->data['token'], true);
		$data['reset'] = $this->url->link('payment/youpay/resetToken', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

		if (isset($this->request->post['youpay_total'])) {
			$data['youpay_total'] = $this->request->post['youpay_total'];
		} else {
			$data['youpay_total'] = $this->config->get('youpay_total');
		}

		if (isset($this->request->post['youpay_username'])) {
			$data['youpay_username'] = $this->request->post['youpay_username'];
		} else {
			$data['youpay_username'] = $this->config->get('youpay_username');
		}

		if (isset($this->request->post['youpay_password'])) {
			$data['youpay_password'] = $this->request->post['youpay_password'];
		} else {
			$data['youpay_password'] = $this->config->get('youpay_password');
		}

		if (isset($this->request->post['youpay_order_status_id'])) {
			$data['youpay_order_status_id'] = $this->request->post['youpay_order_status_id'];
		} else {
			$data['youpay_order_status_id'] = $this->config->get('youpay_order_status_id');
		}
		
		if (isset($this->request->post['youpay_order_status_hold_id'])) {
			$data['youpay_order_status_hold_id'] = $this->request->post['youpay_order_status_hold_id'];
		} else {
			$data['youpay_order_status_hold_id'] = $this->config->get('youpay_order_status_hold_id');
		}		

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['youpay_geo_zone_id'])) {
			$data['youpay_geo_zone_id'] = $this->request->post['youpay_geo_zone_id'];
		} else {
			$data['youpay_geo_zone_id'] = $this->config->get('youpay_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['youpay_status'])) {
			$data['youpay_status'] = $this->request->post['youpay_status'];
		} else {
			$data['youpay_status'] = $this->config->get('youpay_status');
		}

		if (isset($this->request->post['youpay_sort_order'])) {
			$data['youpay_sort_order'] = $this->request->post['youpay_sort_order'];
		} else {
			$data['youpay_sort_order'] = $this->config->get('youpay_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/youpay.tpl', $data));
	}

	public function checkConnection($youpay_email, $youpay_password){

		$this->load->model('payment/youpay');

		require_once '../vendor/autoload.php';

		if (empty($this->client)) {
			$this->client = new Client();
		}

		//check if token and store_id are saved
		if($this->model_payment_youpay->getToken() && $this->model_payment_youpay->getStoreID()){
			$this->client->setToken($this->config->get('oupay_token'));
			$this->client->setStoreID($this->config->get('youpay_store_id'));
		}

		//token and store_id are not saved, authenticate with youpay API
		//authenticate client
		// $youpay_email = $this->config->get('payment_youpay_username');
		// $youpay_password = $this->config->get('payment_youpay_password');
		$youpay_domain = $_SERVER['SERVER_NAME'];

		$response = $this->client->auth($youpay_email, $youpay_password, $youpay_domain, 'opencart');
		if($response->status_code!=200){
			return false;
		}else{

			$access_token = $response->access_token;
			$store_id = $response->store_id;
			$this->client->setToken($access_token);
			$this->client->setStoreID($store_id);
			//save token and store id
			$this->model_payment_youpay->setToken($access_token);
			$this->model_payment_youpay->setStoreID($store_id);

			return true;
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/youpay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function resetToken(){
		$this->load->model('payment/youpay');
		$this->load->language('payment/youpay');

		$this->model_payment_youpay->resetToken();
		$this->session->data['success_token'] = $this->language->get('text_success_token');
		$this->response->redirect($this->url->link('payment/youpay', 'token=' . $this->session->data['token'], true));
	}
}