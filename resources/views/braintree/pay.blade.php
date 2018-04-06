
@extends('layouts.app') 

@push('head')


<script src="https://www.paypalobjects.com/api/checkout.js" data-version-4 log-level="warn"></script>
<script src="https://js.braintreegateway.com/web/3.31.0/js/client.min.js"></script>
<script src="https://js.braintreegateway.com/web/3.31.0/js/paypal-checkout.min.js"></script>

<script src="https://js.braintreegateway.com/web/dropin/1.9.4/js/dropin.min.js"></script>


<script>
$(document).ready(function(){
	braintree.dropin.create({
		authorization: '{{ $braintree_key }}',
		container: '#dropin-container',
		paypal: {
			flow: 'vault'
		}
	}, function (createErr, instance) {
		$('.submit_button').click(function () {
			var that = this;
			instance.requestPaymentMethod(function (err, payload) {
				if (err) {
					console.log(err);
				} else {
					console.log(payload);
					$.ajax({
						url: '/controller/finishTransaction', 
                        type: 'post', 
                        data: {
                            id: $(that).data().id,
                            bill: $(that).data().bill,
                        	_token: $('meta[name="csrf-token"]').attr('content'),
                        	payment_methode_nonce: payload.nonce
                    	}, 
						success: function(data) {
							alert('Communication successful! (' + data + ')');
						location.reload();
                        	//console.log(data);
                    	},
                    	error: function(err) {
                        	console.log(err);
                        	alert('Paiement Failed : ' + err);
                    	}
					});
				}
			});
		});
	});
});
  
  
</script>

<style>
.button_div {
    padding: 20px, 20px, 20px, 20px;
    margin: 20px, 20px, 20px, 20px;
}
</style>

@endpush 



@section('content')


<div id="dropin-container"></div>
  <!--  <button id="submit-button" class="button">Request payment method</button>-->

	<div class="grid-x grid-padding-x">
        <div class="medium-12 cell">
        	<div class="grid-x grid-padding-x">
        		<!-- Tab contents -->
        		<div class="medium-6 cell">
        			<div class="tabs-content" style="border: 2px solid #e6e6e6" data-tabs-content="commands">
                    	@foreach($suppliers as $key => $supplier)
            				<div class="tabs-panel <?php if ($key == 0) echo 'is-active'; ?>" id="{{ $supplier->id }}_pnl">
            					<div class="grid-x grid-padding-x">
            						<div class="cell medium-12">
                    					<label for="{{ $supplier->id }}_supplier_name_textbox">Supplier : </label>
                    					<input type="text" class="text" id="{{ $supplier->id }}_supplier_name_textbox" readonly
                    						value="{{ $supplier->name }}">
                					</div>
                						
            						<div class="cell medium-12">
                    					<label for="{{ $supplier->id }}_bill_textbox">Bill : </label>
                    					<input type="text" class="text" id="{{ $supplier->id }}_bill_textbox" readonly
                    						value="{{ $supplier->bill }}">
                					</div>
                						
                                    <div class="medium-12 cell grid-x grid-padding-x">
        	                            <div class="medium-3 cell input-group">
                                            <div class="input-group-button">
                                                <div class="button_div">
                                                    <button type="button" class="button submit_button" data-id="{{ $supplier->id }}" data-bill="{{ $supplier->bill }}">Pay</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
            						<!--  <div class="cell medium-12">
            							<div class="grid-x align-right">
                        					<button 
                        						type="button" 
                        						class="button cell medium-4 supplier_pay_button" 
                        						id="{{ $supplier->id }}_add_button" 
                        						data-commandid="{{ $supplier->id }}">
                        						Pay
                        					</button>
                    					</div>
                					</div> <!-- -->
            					</div>
                            </div>
                        @endforeach
        			</div>
        		    <!-- End tab contents -->
        		</div>
        		<div class="medium-6 cell">
        			<!-- Tabs -->
        			<ul class="tab_alternate_colors vertical tabs" data-tabs id="commands">
                    	@foreach($suppliers as $key => $supplier)
                      		<li class="tabs-title 
                      		<?php if ($key == 0) echo 'is-active'; ?>
                      		"><a href="#{{ $supplier->id }}_pnl">{{ $supplier->name }}</a></li>
                    	@endforeach
                    </ul>
                    <!-- End tabs -->
        		</div>
        	</div>
        </div>
        

    </div>

@endsection

