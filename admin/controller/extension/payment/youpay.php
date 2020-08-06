<?php
class ControllerExtensionPaymentYoupay extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/youpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_youpay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		}elseif(isset($this->session->data['success_token'])){
			$data['error_warning'] = $this->session->data['success_token'];
			unset($this->session->data['success_token']);
		}else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/youpay', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/youpay', 'user_token=' . $this->session->data['user_token'], true);
		$data['reset'] = $this->url->link('extension/payment/youpay/resetToken', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_youpay_total'])) {
			$data['payment_youpay_total'] = $this->request->post['payment_youpay_total'];
		} else {
			$data['payment_youpay_total'] = $this->config->get('payment_youpay_total');
		}

		if (isset($this->request->post['payment_youpay_username'])) {
			$data['payment_youpay_username'] = $this->request->post['payment_youpay_username'];
		} else {
			$data['payment_youpay_username'] = $this->config->get('payment_youpay_username');
		}

		if (isset($this->request->post['payment_youpay_password'])) {
			$data['payment_youpay_password'] = $this->request->post['payment_youpay_password'];
		} else {
			$data['payment_youpay_password'] = $this->config->get('payment_youpay_password');
		}

		if (isset($this->request->post['payment_youpay_fees'])) {
			$data['payment_youpay_fees'] = $this->request->post['payment_youpay_fees'];
		} else {
			$data['payment_youpay_fees'] = $this->config->get('payment_youpay_fees');
		}


		if (isset($this->request->post['payment_youpay_order_status_id'])) {
			$data['payment_youpay_order_status_id'] = $this->request->post['payment_youpay_order_status_id'];
		} else {
			$data['payment_youpay_order_status_id'] = $this->config->get('payment_youpay_order_status_id');
		}
		
		if (isset($this->request->post['payment_youpay_order_status_hold_id'])) {
			$data['payment_youpay_order_status_hold_id'] = $this->request->post['payment_youpay_order_status_hold_id'];
		} else {
			$data['payment_youpay_order_status_hold_id'] = $this->config->get('payment_youpay_order_status_hold_id');
		}		

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_youpay_geo_zone_id'])) {
			$data['payment_youpay_geo_zone_id'] = $this->request->post['payment_youpay_geo_zone_id'];
		} else {
			$data['payment_youpay_geo_zone_id'] = $this->config->get('payment_youpay_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_youpay_status'])) {
			$data['payment_youpay_status'] = $this->request->post['payment_youpay_status'];
		} else {
			$data['payment_youpay_status'] = $this->config->get('payment_youpay_status');
		}

		if (isset($this->request->post['payment_youpay_sort_order'])) {
			$data['payment_youpay_sort_order'] = $this->request->post['payment_youpay_sort_order'];
		} else {
			$data['payment_youpay_sort_order'] = $this->config->get('payment_youpay_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/youpay', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/youpay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function resetToken(){
		$this->load->model('extension/payment/youpay');
		$this->load->language('extension/payment/youpay');

		$this->model_extension_payment_youpay->resetToken();
		$this->session->data['success_token'] = $this->language->get('text_success_token');
		$this->response->redirect($this->url->link('extension/payment/youpay', 'user_token=' . $this->session->data['user_token'], true));
	}
}