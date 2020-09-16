<div id="youpay-create-link-container">
	<div class="youpay-create-link-logo">
		<img src="/image/youpay-logo.png" alt="YouPay">
	</div>
	
	<div class="youpay-create-link-content">
		<h3>You've chosen YouPay as your payment method</h3>
		
		<p>When you click the "Confirm &amp; Create YouPay Link" button you'll be given a secure YouPay link to share with your Payer. Simply send that link to the person you want to pay for you and ask them to make the payment. Read more about how YouPay works by <a href="#" data-toggle="modal" data-target="#youPayModal">clicking here</a>.</p>
		
		<input type="button" value="Confirm &amp; Create YouPay Link" id="button-confirm" class="button youpay-link" />
	</div>
</div>

<!-- <div class="buttons">
  <div class="pull-right">
    <input type="button" value="{{ button_confirm }}" id="button-confirm" data-loading-text="{{ text_loading }}" class="btn btn-primary" />
  </div>
</div> -->
<script type="text/javascript"><!--
$('#button-confirm').on('click', function() {
	$.ajax({
		url: 'index.php?route=extension/payment/youpay/confirm',
		dataType: 'json',
		beforeSend: function() {
			$('#button-confirm').button('loading');
		},
		complete: function() {
			$('#button-confirm').button('reset');
		},
		success: function(json) {
			if (json['redirect']) {
				location = json['redirect'];	
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
//--></script>
