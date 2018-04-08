<?php

class w2dc_bank_transfer extends w2dc_payment_gateway
{
	public function __construct()
	{
        parent::__construct();
        
        $this->logIpn = FALSE;
	}

    public function name() {
    	return __('Bank transfer', 'W2DC');
    }

    public function description() {
    	return __('Print invoice and transfer the payment (bank transfer information included)', 'W2DC');
    }
    
    public function buy_button()
    {
    	return '<img src="' . W2DC_PAYMENTS_RESOURCES_URL . 'images/bank.png" />';
    }
    
    public function submitPayment($invoice) {
    	w2dc_addMessage(__('You chose bank transfer payment gateway, now print invoice and transfer the payment', 'W2DC'));
    }
}
