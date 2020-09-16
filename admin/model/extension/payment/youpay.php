<?php
class ModelExtensionPaymentYoupay extends Model {
		
	public function resetToken(){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'youpay' AND `key` = 'youpay_token'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'youpay' AND `key` = 'youpay_store_id'");
	}
}
