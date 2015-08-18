<?php

/**
 * Authorize.net Class
 *
 * Integrate the Authorize.net payment gateway in your site using this
 * easy to use library. Just see the example code to know how you should
 * proceed. Also, remember to read the readme file for this class.
 *
 * @package     Payment Gateway
 * @category	Library
 * @author      Md Emran Hasan <phpfour@gmail.com>
 * @link        http://www.phpfour.com
 */

include_once ('PaymentGateway.php');

class Authorize extends eStorePaymentGateway
{
    /**
     * Login ID of authorize.net account
     *
     * @var string
     */
    var $login;

    /**
     * Secret key from authorize.net account
     *
     * @var string
     */
    var $secret;

    /**
	 * Initialize the Authorize.net gateway
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
        parent::__construct();

        // Some default values of the class
		$this->gatewayUrl = 'https://secure.authorize.net/gateway/transact.dll';
		if(defined('WP_ESTORE_PATH')){
			$this->ipnLogFile = WP_ESTORE_PATH.'authorize.ipn_results.log';
		}else{
			$this->ipnLogFile = 'authorize.ipn_results.log';
		}
		// Populate $fields array with a few default
		$this->addField('x_Version',        '3.1');
        $this->addField('x_Show_Form',      'PAYMENT_FORM');
		$this->addField('x_Relay_Response', 'TRUE');
	}

    /**
     * Enables the test mode
     *
     * @param none
     * @return none
     */
    function enableTestMode()
    {
        $this->testMode = TRUE;
        $this->addField('x_Test_Request', 'TRUE');
        $this->gatewayUrl = 'https://test.authorize.net/gateway/transact.dll';
    }

    /**
     * Set login and secret key
     *
     * @param string user login
     * @param string secret key
     * @return void
     */
    function setUserInfo($login, $key)
    {
        $this->login  = $login;
        $this->secret = $key;
    }

    /**
     * Prepare a few payment information
     *
     * @param none
     * @return void
     */
    function prepareSubmit()
    {
        $this->addField('x_Login', $this->login);
        $this->addField('x_fp_sequence', $this->fields['x_Invoice_num']);
        $this->addField('x_fp_timestamp', time());

        $data = $this->fields['x_Login'] . '^' .
                $this->fields['x_Invoice_num'] . '^' .
                $this->fields['x_fp_timestamp'] . '^' .
                $this->fields['x_Amount'] . '^';

        $this->addField('x_fp_hash', $this->hmac($this->secret, $data));
    }

    function submitPayment()
    {

        $this->prepareSubmit();

        //print_r($this->fields);

        echo "<html>\n";
        echo "<head><title>Processing Payment...</title></head>\n";
        echo "<body onLoad=\"document.forms['gateway_form'].submit();\">\n";
        echo "<div style=\"text-align:center;\"><h2>Please wait, your order is being processed. You will be redirected to the payment website shortly.</h2></div>\n";
        echo "<form method=\"POST\" name=\"gateway_form\" ";
        echo "action=\"" . $this->gatewayUrl . "\">\n";

        foreach ($this->fields as $name => $value)
        {
             $pos = strpos($name, "x_line_item");
             if ($pos !== false)
             {
                 $name = "x_line_item";
             }
             echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
        }

        echo '<p style="text-align:center;"><img src="./images/payment-loader.gif" alt="Processing Order..." /></p>';
        echo "<p style=\"text-align:center;\"><br/><br/>If you are not automatically redirected to the payment website within 5 seconds...<br/><br/>\n";
        echo "<input type=\"submit\" value=\"Click Here\"></p>\n";

        echo "</form>\n";
        echo "</body></html>\n";
    }

    function submitPayment2($click_text="Click Here")
    {
        $this->prepareSubmit();

        echo "<div style=\"text-align:center;\">";
        echo "<form id=\"gateway_form\" method=\"POST\" name=\"gateway_form\" action=\"" . $this->gatewayUrl . "\">";

        foreach ($this->fields as $name => $value)
        {
             $pos = strpos($name, "x_line_item");
             if ($pos !== false)
             {
                 $name = "x_line_item";
             }
             echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
        }

        echo "<input type=\"submit\" value=\"$click_text\">";
        echo "</form>";
        echo "</div>";         
    }
        
    /**
	 * Validate the IPN notification
	 *
	 * @param none
	 * @return boolean
	 */
	function validateIpn()
	{
	    foreach ($_REQUEST as $field=>$value)
		{
			$this->ipnData["$field"] = $value;
		}

        $invoice    = intval($this->ipnData['x_invoice_num']);
        $pnref      = $this->ipnData['x_trans_id'];
        $amount     = doubleval($this->ipnData['x_amount']);
        $result     = intval($this->ipnData['x_response_code']);
        $respmsg    = $this->ipnData['x_response_reason_text'];

        $md5source  = $this->secret . $this->login . $this->ipnData['x_trans_id'] . $this->ipnData['x_amount'];
        $md5        = md5($md5source);

		if ($result == '1')
		{
		 	// Valid IPN transaction.
		 	$this->logResults(true);
		 	return true;
		}
		else if ($result != '1')
		{
		 	$this->lastError = $respmsg;
			$this->logResults(false);
			return false;
		}
        else if (strtoupper($md5) != $this->ipnData['x_MD5_Hash'])
        {
            $this->lastError = 'MD5 mismatch';
            $this->logResults(false);
            return false;
        }
	}

    /**
     * RFC 2104 HMAC implementation for php.
     *
     * @author Lance Rushing
     * @param string key
     * @param string date
     * @return string encoded hash
     */
    function hmac ($key, $data)
    {
       $b = 64; // byte length for md5

       if (strlen($key) > $b) {
           $key = pack("H*",md5($key));
       }

       $key  = str_pad($key, $b, chr(0x00));
       $ipad = str_pad('', $b, chr(0x36));
       $opad = str_pad('', $b, chr(0x5c));
       $k_ipad = $key ^ $ipad ;
       $k_opad = $key ^ $opad;

       return md5($k_opad  . pack("H*", md5($k_ipad . $data)));
    }
}
