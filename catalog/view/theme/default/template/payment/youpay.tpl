<div class="buttons">
	<img src="/image/youpay.png"><br/>
	You've choosen YouPay as your payment method.<br/>
	When you click the <i>Create YouPay Link</i> button you'll be given a secure YouPay link to share with your payer.</br>
	Simply send that link to the person you want to pay for you and ask them to make the payment.<br/>
	Read more about how YouPay works <a href="https://youpay.ai/help" target="_blank">here</a>.
  <div class="right">
    <input type="button" value="Create YouPay Link" id="button-confirm" class="button" />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
	$.ajax({ 
		type: 'get',
		url: 'index.php?route=payment/youpay/confirm',
		success: function(data) {
			 // console.log(data);
			location = '<?php echo $continue;?>';
		}		
	});
});
//--></script> 
