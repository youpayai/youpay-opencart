<?php 
class ControllerPaymentYoupay extends Controller {
	private $error = array(); 

	public function index() { 
		$this->language->load('payment/youpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('youpay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');

		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_order_status_hold'] = $this->language->get('entry_order_status_hold');		
		$this->data['entry_total'] = $this->language->get('entry_total');	
		$this->data['entry_username'] = $this->language->get('entry_username');	
		$this->data['entry_password'] = $this->language->get('entry_password');	
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/youpay', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/youpay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');	

		if (isset($this->request->post['youpay_total'])) {
			$this->data['youpay_total'] = $this->request->post['youpay_total'];
		} else {
			$this->data['youpay_total'] = $this->config->get('youpay_total'); 
		}

		if (isset($this->request->post['youpay_order_status_id'])) {
			$this->data['youpay_order_status_id'] = $this->request->post['youpay_order_status_id'];
		} else {
			$this->data['youpay_order_status_id'] = $this->config->get('youpay_order_status_id'); 
		} 

		if (isset($this->request->post['youpay_order_status_hold_id'])) {
			$this->data['youpay_order_status_hold_id'] = $this->request->post['youpay_order_status_hold_id'];
		} else {
			$this->data['youpay_order_status_hold_id'] = $this->config->get('youpay_order_status_hold_id'); 
		} 

		if (isset($this->request->post['youpay_username'])) {
			$this->data['youpay_username'] = $this->request->post['youpay_username'];
		} else {
			$this->data['youpay_username'] = $this->config->get('youpay_username'); 
		} 

		if (isset($this->request->post['youpay_password'])) {
			$this->data['youpay_password'] = $this->request->post['youpay_password'];
		} else {
			$this->data['youpay_password'] = $this->config->get('youpay_password'); 
		} 

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['youpay_geo_zone_id'])) {
			$this->data['youpay_geo_zone_id'] = $this->request->post['youpay_geo_zone_id'];
		} else {
			$this->data['youpay_geo_zone_id'] = $this->config->get('youpay_geo_zone_id'); 
		} 

		$this->load->model('localisation/geo_zone');						

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['youpay_status'])) {
			$this->data['youpay_status'] = $this->request->post['youpay_status'];
		} else {
			$this->data['youpay_status'] = $this->config->get('youpay_status');
		}

		if (isset($this->request->post['youpay_sort_order'])) {
			$this->data['youpay_sort_order'] = $this->request->post['youpay_sort_order'];
		} else {
			$this->data['youpay_sort_order'] = $this->config->get('youpay_sort_order');
		}

		$this->template = 'payment/youpay.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/youpay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>