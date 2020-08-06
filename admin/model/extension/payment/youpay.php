<?php
class ModelExtensionPaymentYoupay extends Model {
		
	public function resetToken(){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'payment_youpay' AND `key` = 'payment_youpay_token'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'payment_youpay' AND `key` = 'payment_youpay_store_id'");
	}
}
