<?php

class w2dc_stripe extends w2dc_payment_gateway
{
	public $secret_key;
	public $publishable_key;

    /**
	 * Initialize the Stripe gateway
	 *
	 * @param none
	 * @return void
	 */
	public function __construct() {
        parent::__construct();

        $this->secret_key = get_option('w2dc_stripe_live_secret');
        $this->publishable_key = get_option('w2dc_stripe_live_public');
        
        if (get_option('w2dc_stripe_test'))
        	$this->enableTestMode();
	}

    /**
     * Enables the test mode
     *
     * @param none
     * @return none
     */
    public function enableTestMode() {
        $this->secret_key = get_option('w2dc_stripe_test_secret');
        $this->publishable_key = get_option('w2dc_stripe_test_public');
    }
    
    public function name() {
    	return __('Stripe', 'W2DC');
    }

    public function description() {
    	return __('One time payment by Stripe. After successful transaction listing will become active and raised up.', 'W2DC');
    }
    
    public function buy_button() {
    	return '<img src="' . W2DC_PAYMENTS_RESOURCES_URL . 'images/stripe.png" />';
    }
    
    public function submitPayment($invoice) {
    	include_once W2DC_PAYMENTS_PATH . 'classes/gateways/stripe/lib/Stripe.php';

		Stripe::setApiKey($this->secret_key);

		$token = $_POST['stripe_token'];

		$customer = Stripe_Customer::create(array(
				'email' => $_POST['stripe_email'],
				'card' => $token
		));

		try {
			$charge = Stripe_Charge::create(array(
					'customer' => $customer->id,
					'amount' => $invoice->taxesPrice(false)*100,
					'currency' => get_option('w2dc_payments_currency')
			));
		} catch(Stripe_CardError $e) {
			$body = $e->getJsonBody();
			$err = $body['error'];
			$invoice->logMessage($err['message']);
			return false;
		} catch (Stripe_InvalidRequestError $e) {
			$invoice->logMessage("Invalid parameters were supplied to Stripe's API");
			return false;
		} catch (Stripe_AuthenticationError $e) {
			$invoice->logMessage("Authentication with Stripe's API failed");
			return false;
		} catch (Stripe_ApiConnectionError $e) {
			$invoice->logMessage("Network communication with Stripe failed");
			return false;
		} catch (Stripe_Error $e) {
			$invoice->logMessage("Transaction failed");
			return false;
		} catch (Exception $e) {
			$invoice->logMessage("Transaction failed");
			return false;
		}

		if (w2dc_create_transaction(
				$this->name(),
				$invoice->post->ID,
				'Completed',
				$charge->id,
				$charge->amount/100,
				0,
				$charge->currency,
				1,
				$charge
		)) {
			if ($invoice->item_object->complete()) {
				$invoice->setStatus('paid');
				$transaction_data = array();
				$keys = $charge->keys();
				foreach ($keys AS $k)
					if (is_string($charge->offsetGet($k)))
						$transaction_data[] = $k . ' = ' . esc_attr($charge->offsetGet($k));
				$invoice->logMessage(sprintf(__('Payment successfully completed. Transaction data: %s', 'W2DC'), implode('; ', $transaction_data)));
			}
		}
	}
}
