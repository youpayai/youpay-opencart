<?php
class ModelExtensionPaymentYoupay extends Model {
		
	public function resetToken(){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'payment_youpay' AND `key` = 'payment_youpay_token'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'payment_youpay' AND `key` = 'payment_youpay_store_id'");
	}

	public function setToken($token){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'payment_youpay' AND `key` = 'payment_youpay_token'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `value` = '" . $this->db->escape($token) . "', serialized = '0', `code` = 'payment_youpay', `key` = 'payment_youpay_token', store_id = '0'");
	}

	public function getToken(){
		$query = $this->db->query("SELECT value FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `key` = 'payment_youpay_token'");

		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return null;	
		}
	}

	public function setStoreID($store_id){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'payment_youpay' AND `key` = 'payment_youpay_store_id'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `value` = '" . $this->db->escape($store_id) . "', serialized = '0', `code` = 'payment_youpay', `key` = 'payment_youpay_store_id', store_id = '0'");
		
	}

	public function getStoreID(){
		$query = $this->db->query("SELECT value FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `key` = 'payment_youpay_store_id'");

		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return null;	
		}
	}
}
