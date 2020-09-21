<?php
class ModelPaymentYouPay extends Model {
	public function getMethod($address, $total) {
		$this->load->language('/payment/youpay');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('youpay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('youpay_total') > 0 && $this->config->get('youpay_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('youpay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'youpay',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('youpay_sort_order')
			);
		}

		return $method_data;
	}

	public function setToken($token){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'youpay' AND `key` = 'youpay_token'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($token) . "', serialized = '0', `code` = 'youpay', `key` = 'youpay_token', store_id = '0'");
	}

	public function getToken(){
		$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `key` = 'youpay_token'");

		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return null;	
		}
	}

	public function setStoreID($store_id){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'youpay' AND `key` = 'youpay_store_id'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($store_id) . "', serialized = '0', `code` = 'youpay', `key` = 'youpay_store_id', store_id = '0'");
		
	}

	public function getStoreID(){
		$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `key` = 'youpay_store_id'");

		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return null;	
		}
	}	
}