<?php
/**
 * konse-konse-web-service-consume-php
 *
 * @author Chinyanta Mwenya <chinyanta@oneziko.com>
 * @license https://github.com/Chizzoz/konse-konse-web-service-consume-php/blob/main/LICENSE MIT
 * @link https://github.com/Chizzoz/konse-konse-web-service-consume-php
 */

require_once realpath(__DIR__ . '/vendor/autoload.php');

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

/**
 * cgrate
 *
 * @param  string $mobile_number
 * @param  mixed $amount
 * @param  string $process
 * @return object
 */
function cgrate($mobile_number, $amount, $process = null)
{
    // XML cURL POST Request Payment
    function doXMLCurl($url, $postXML, $host)
    {
        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Accept-Encoding: gzip,deflate",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"\"",
            "Content-length: " . strlen($postXML),
            "Host: " . $host,
            "Connection: Keep-Alive",

        );
        $CURL = curl_init();

        curl_setopt($CURL, CURLOPT_URL, $url);
        curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($CURL, CURLOPT_ENCODING, "");
        curl_setopt($CURL, CURLOPT_MAXREDIRS, 10);
        curl_setopt($CURL, CURLOPT_TIMEOUT, 0);
        curl_setopt($CURL, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($CURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($CURL, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($CURL, CURLOPT_POSTFIELDS, $postXML);
        curl_setopt($CURL, CURLOPT_HTTPHEADER, $headers);
        $xmlResponse = curl_exec($CURL);
        curl_close($CURL);

        return $xmlResponse;
    }
    // Get live or test credentials from .env file || Alternatively, you can replace with your values
    // cGrate test username
    $cgrate_username = $_ENV['USERNAME'];
    // cGrate test password
    $cgrate_password = $_ENV['PASSWORD'];
    // live processCustomerPayment URL
    $PAYMENT_URL = $_ENV['PAYMENT_URL'];
    // live Host
    $HOST = $_ENV['HOST'];

    // Unique timestamp
    $timestamp = date('YmdGis', time());
    // Unique reference number
    $reference_number = "OZ" . $timestamp;
    // Establish issuerName from mobile number
    if (preg_match("/^26095\d{7}$/", $mobile_number) || preg_match("/^26075\d{7}$/", $mobile_number)) {
        $issuerName = "Zamtel";
    } elseif (preg_match("/^26096\d{7}$/", $mobile_number) || preg_match("/^26076\d{7}$/", $mobile_number)) {
        $issuerName = "MTN";
    } elseif (preg_match("/^26097\d{7}$/", $mobile_number) || preg_match("/^26077\d{7}$/", $mobile_number)) {
        $issuerName = "Airtel";
    } else {
        return json_encode(array("responseMessage" => "Invalid Mobile Number! Use Airtel, MTN or Zamtel number starting with 260!"));
    }
    if ($process == "disburse") {
// Request body
$REQUEST_BODY = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:kon="http://konik.cgrate.com">
    <soapenv:Header>
        <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:UsernameToken wsu:Id="UsernameToken-1" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <wsse:Username>$cgrate_username</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">$cgrate_password</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <kon:processCashDeposit>
            <transactionAmount>$amount</transactionAmount>
            <!--Optional:-->
            <customerAccount>$mobile_number</customerAccount>
            <!--Optional:-->
            <issuerName>$issuerName</issuerName>
        </kon:processCashDeposit>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    } else {
// Request body
$REQUEST_BODY = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:kon="http://konik.cgrate.com">
    <soapenv:Header>
        <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:UsernameToken wsu:Id="UsernameToken-1" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <wsse:Username>$cgrate_username</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">$cgrate_password</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <kon:processCustomerPayment>
            <transactionAmount>$amount</transactionAmount>
            <!--Optional:-->
            <customerMobile>$mobile_number</customerMobile>
            <!--Optional:-->
            <paymentReference>$reference_number</paymentReference>
        </kon:processCustomerPayment>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }
    try {
        $full_response = doXMLCurl($PAYMENT_URL, $REQUEST_BODY, $HOST);
        $response_xml = new \SimpleXMLElement(strstr($full_response, '<'), LIBXML_NOERROR);
    } catch (\Exception $e) {
        $json_response = json_encode(["responseMessage" => $e->getMessage()]);
        return $json_response;
    }
    // initiate response array
    $response_array = [];
    
    foreach ($response_xml->xpath('//*[name()=\'return\']/*') as $return) {
        // assign values to response array
        $response_array[$return->getName()] = strval($return);
    }
    // set payment reference
    $response_array["paymentReference"] = $reference_number;
    // set amount
    $response_array["amount"] = (double) $amount;
    // set mobile number
    $response_array["mobileNumber"] = $mobile_number;
    // response
    $json_response = json_encode($response_array);

    return $json_response;
}
