<?php

class w2dc_paypal extends w2dc_payment_gateway
{
    /**
	 * Initialize the Paypal gateway
	 *
	 * @param none
	 * @return void
	 */
	public function __construct() {
        parent::__construct();

        // Some default values of the class
		$this->gatewayUrl = 'https://www.paypal.com/cgi-bin/webscr';
		$this->ipnLogFile = 'paypal.ipn_results.log';

		// Populate $fields array with a few default
		$this->addField('rm', '2');           // Return method = POST
		$this->addField('cmd', '_xclick');
	}

    /**
     * Enables the test mode
     *
     * @param none
     * @return none
     */
    public function enableTestMode() {
        $this->testMode = TRUE;
        $this->gatewayUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    }
    
    public function name() {
    	return __('PayPal', 'W2DC');
    }

    public function description() {
    	return __('One time payment by PayPal. After successful transaction listing will become active and raised up.', 'W2DC');
    }
    
    public function buy_button() {
    	return '<img src="' . W2DC_PAYMENTS_RESOURCES_URL . 'images/paypal.png" />';
    }
    
    public function submitPayment($invoice) {
    	if (get_option('w2dc_paypal_test'))
    		$this->enableTestMode();
    	
    	$this->addField('business', get_option('w2dc_paypal_email'));
    	$this->addField('return', urlencode(w2dc_get_edit_invoice_link($invoice->post->ID)));
    	$this->addField('cancel_return', urlencode(w2dc_get_edit_invoice_link($invoice->post->ID)));
    	$this->addField('notify_url', home_url('ipn_token/'.ipn_token().'/gateway/paypal'));
    	$this->addField('item_name', $invoice->post->post_title);
    	$this->addField('item_number', $invoice->post->ID);
    	$this->addField('currency_code', get_option('w2dc_payments_currency'));
    	$this->addField('amount', $invoice->taxesPrice(false));

    	header("Location: " . $this->buildPaymentLink());
    	die();
    }

    /**
	 * Validate the IPN notification
	 *
	 * @param none
	 * @return boolean
	 */
	public function validateIpn() {
		// parse the paypal URL
		$urlParsed = parse_url($this->gatewayUrl);

		// generate the post string from the _POST vars
		$postString = '';

		foreach ($_POST as $field=>$value)
		{
			$this->ipnData["$field"] = $value;
			
			$postString .= $field .'=' . preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}', urlencode(stripslashes($value))) . '&';
		}

		$postString .="cmd=_notify-validate"; // append ipn command

		// open the connection to paypal
		$fp = fsockopen('ssl://'.$urlParsed['host'], "443", $errno, $errstr, 30);

		if(!$fp)
		{
			// Could not open the connection, log error if enabled
			$this->lastError = "fsockopen error no. $errNum: $errStr";
			$this->logResults(false);

			return false;
		}
		else
		{
			// Post the data back to paypal

			fputs($fp, "POST " . $urlParsed['path'] . " HTTP/1.1\r\n");
			fputs($fp, "Host: " . $urlParsed['host'] . "\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded; charset=windows-1252\r\n");
			fputs($fp, "Content-length: " . strlen($postString) . "\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $postString . "\r\n\r\n");

			// loop through the response from the server and append to variable
			while(!feof($fp))
			{
				$this->ipnResponse .= fgets($fp, 1024);
			}

		 	fclose($fp); // close connection
		}

		if (preg_match("/VERIFIED/", $this->ipnResponse))
		{
		 	// Valid IPN transaction.
		 	$this->logResults(true);
		 	return true;
		}
		else
		{
		 	// Invalid IPN transaction.  Check the log for details.
			$this->lastError = "IPN Validation Failed " . $urlParsed['path'] . ":" . $urlParsed['host'];
			$this->logResults(false);
			return false;
		}
	}
}

function w2dc_handle_ipn_paypal($wp) {
	if (array_key_exists('ipn_token', $wp->query_vars) && $wp->query_vars['ipn_token'] == ipn_token() && array_key_exists('gateway', $wp->query_vars) && $wp->query_vars['gateway'] == 'paypal') {
		$paypal = new w2dc_paypal();
		if (get_option('w2dc_paypal_test'))
			$paypal->enableTestMode();
		
		if ($paypal->validateIpn()) {
			if (strcasecmp($paypal->ipnData['business'], get_option('w2dc_paypal_email')) === 0) {
				if (is_unique_transaction($paypal->ipnData['txn_id'])) {
					$invoice_id = $paypal->ipnData['item_number'];
					if (($invoice = getInvoiceByID($invoice_id)) && floatval($paypal->ipnData['mc_gross']) == $invoice->taxesPrice(false) && $paypal->ipnData['mc_currency'] == get_option('w2dc_payments_currency')) {
						if (w2dc_create_transaction(
								$paypal->name(),
								$paypal->ipnData['item_number'],
								$paypal->ipnData['payment_status'],
								$paypal->ipnData['txn_id'],
								$paypal->ipnData['mc_gross'],
								$paypal->ipnData['mc_fee'],
								$paypal->ipnData['mc_currency'],
								1,
								$paypal->ipnData
						)) {
							if ($paypal->ipnData['payment_status'] == 'Completed') {
								if ($invoice->item_object->complete()) {
									$invoice->setStatus('paid');
									$transaction_data = array();
									foreach ($paypal->ipnData AS $key=>$value)
										$transaction_data[] = $key . ' = ' . esc_attr($value);
									$invoice->logMessage(sprintf(__('Payment successfully completed. Transaction data: %s', 'W2DC'), implode('; ', $transaction_data)));
								} else
									$invoice->logMessage(sprintf(__('Transaction failed, Error: %s', 'W2DC'),  $paypal->lastError));
							}
						}
					}
				}
			}
		}
		die();
	}
}
add_action('parse_request', 'w2dc_handle_ipn_paypal');


// Paypal does not allow you to active your IPN in Paypal if a given URL has GET arguments, so we have to use permalinks


/* function w2dc_handle_ipn_paypal_rewrite_rules($wp_rewrite) {
	$new_rules = array('ipn_token/'.ipn_token().'/gateway/paypal' => 'index.php?ipn_token='.ipn_token().'&gateway=paypal');
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
} */
//add_action('generate_rewrite_rules', 'w2dc_handle_ipn_paypal_rewrite_rules');

function w2dc_handle_ipn_paypal_rewrite_rules($rules) {
	return array('ipn_token/'.ipn_token().'/gateway/paypal' => 'index.php?ipn_token='.ipn_token().'&gateway=paypal') + $rules;
}
add_filter('rewrite_rules_array', 'w2dc_handle_ipn_paypal_rewrite_rules');

function w2dc_ipn_paypal_rewrite_rules() {
	if ($rules = get_option('rewrite_rules'))
		if (!isset($rules['ipn_token/'.ipn_token().'/gateway/paypal'])) {
		    global $wp_rewrite;
		    $wp_rewrite->flush_rules();
		}
}
add_action('wp_loaded', 'w2dc_ipn_paypal_rewrite_rules');
