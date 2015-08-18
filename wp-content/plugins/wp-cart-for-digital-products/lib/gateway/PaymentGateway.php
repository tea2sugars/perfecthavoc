<?php
class eStorePaymentGateway
{
    /**
     * Holds the last error encountered
     *
     * @var string
     */
    var $lastError;

    /**
     * Do we need to log IPN results ?
     *
     * @var boolean
     */
    var $logIpn;

    /**
     * File to log IPN results
     *
     * @var string
     */
    var $ipnLogFile;

    /**
     * Payment gateway IPN response
     *
     * @var string
     */
    var $ipnResponse;

    /**
     * Are we in test mode ?
     *
     * @var boolean
     */
    var $testMode;

    /**
     * Field array to submit to gateway
     *
     * @var array
     */
    var $fields = array();

    /**
     * IPN post values as array
     *
     * @var array
     */
    var $ipnData = array();

    /**
     * Payment gateway URL
     *
     * @var string
     */
    var $gatewayUrl;

    /**
     * Initialization constructor
     *
     * @param none
     * @return void
     */
    function __construct()
    {
        // Some default values of the class
        $this->lastError = '';
        $this->logIpn = TRUE;
        $this->ipnResponse = '';
        $this->testMode = FALSE;
    }

    /**
     * Adds a key=>value pair to the fields array
     *
     * @param string key of field
     * @param string value of field
     * @return
     */
    function addField($field, $value)
    {
        $this->fields["$field"] = $value;
    }

    /**
     * Submit Payment Request
     *
     * Generates a form with hidden elements from the fields array
     * and submits it to the payment gateway URL. The user is presented
     * a redirecting message along with a button to click.
     *
     * @param none
     * @return void
     */
    function submitPayment()
    {

        $this->prepareSubmit();

        echo "<html>\n";
        echo "<head><title>Processing Payment...</title></head>\n";
        echo "<body onLoad=\"document.forms['gateway_form'].submit();\">\n";
        echo "<div style=\"text-align:center;\"><h2>Please wait, your order is being processed. You will be redirected to the payment website shortly.</h2></div>\n";
        echo "<form method=\"POST\" name=\"gateway_form\" ";
        echo "action=\"" . $this->gatewayUrl . "\">\n";

        foreach ($this->fields as $name => $value)
        {
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
             echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>";
        }

        echo "<input type=\"submit\" class=\"eStore_checkout_click_here_button\" value=\"$click_text\">";
        echo "</form>";
        echo "</div>";         
    }
    
    /**
     * Perform any pre-posting actions
     *
     * @param none
     * @return none
     */
    function prepareSubmit()
    {
        // Fill if needed
    }

    /**
     * Enables the test mode
     *
     * @param none
     * @return none
     */
    function enableTestMode()
    {

    }

    /**
     * Validate the IPN notification
     *
     * @param none
     * @return boolean
     */
    function validateIpn()
    {
      
    }

    /**
     * Logs the IPN results
     *
     * @param boolean IPN result
     * @return void
     */
    function logResults($success)
    {

        if (!$this->logIpn) return;

        // Timestamp
        $text = '[' . date('m/d/Y g:i A').'] - ';

        // Success or failure being logged?
        $text .= ($success) ? "SUCCESS!\n" : 'FAIL: ' . $this->lastError . "\n";

        // Log the POST variables
        $text .= "IPN POST Vars from gateway:\n";
        foreach ($this->ipnData as $key=>$value)
        {
            $text .= "$key=$value, ";
        }

        // Log the response from the paypal server
        $text .= "\nIPN Response from gateway Server:\n " . $this->ipnResponse;

        // Write to log
        $fp = fopen($this->ipnLogFile,'a');
        fwrite($fp, $text . "\n\n");
        fclose($fp);
    }
}
