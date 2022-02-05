# konse-konse-web-service-consume-php
Consume cGrate Zambia's 543 Konse Konse Web Service API Using PHP. 543 Konse Konse provide a service called *Merchant Payments*, allowing merhants to accept mobile money and bank payments using a Web Service API.

## 543 Konse Konse Merchant Payments?
Merchant Payments offers the 543 merchant an alternative payment option to receiving cash as means of payment in their shops. The customer pays for goods and services at a 543 merchant using their mobile money or bank account. Currently a 543 merchant can receive payments from all mobile money subscribers – MTN MoMo, Airtel Money and Zamtel Kwacha.

## Prerequisites
1. You have signed a Merchant Agreement with cGrate Zambia Limited. Find out more details here: https://www.543.co.zm/services.php#mpL

2. You have received WSDL files and test API endpoints from cGrate Zambia Limited, allowing you to generate and test Web Services.

3. You possess both *Username* and *Password* to enter in SOAP XML Header Security tag.

## Limitations
The code shared in this repository is currently limited to the following web services:
- processCashDeposit
- processCustomerPayment

Additionally, this code is limited to *Airtel*, *MTN* and *Zamtel* payments, which is set by specifying the `issuerName` value.

This code does not provide you with the test or live URL's used as endpoints to send requests. You can modify `$PAYMENT_URL` and `$HOST` with the test or live values once you have them.

## How To Use This Code
It is assumed you have an existing mobile app, web app or business plan that requires mobile money payments integration. Copy and paste this code into your project and modify accordingly to receive payments from your users. There is no database opinion in this code, you can handle and store data from the response according to your requirements.

You will have to set `$cgrate_username` and `$cgrate_password` with your valid credentials.

## cGrate Function
This code provides a function called `cgrate` which will process a payment for your app. The cgrate function accepts the following parameters:
- $process (Specify `disburse` to send money to a mobile number. By default, if this is `null` or any other value, money will be collected from provided mobile number)
- $amount (This can be an `int` or `decimal`)
- $mobile_number (This is a 12-digit Zambian Mobile Number starting with country code `2609xxxxxxxx`

## What Does The cgrate Function Do?
The `cgrate` function will do the following processes:
1. Filter mobile network operator (MNO) or `issuerName` from provided monile number
2. Create and send a SOAP XML request to 543 Konse Konse Web Service
3. Catch and return exceptions in JSON format
4. Return any failed or successful payment response in JSON format

## Response Formats
### Exception Response
```
{
  "responseMessage": "Exception Message"
}
```

### Server Successful Payment Response
```
{
  "responseCode": 0,
  "responseMessage": "Successful",
  "paymentID": "XXXXXXXXXXXX"
}
```

### Server Failed Payment Response
```
{
  "responseCode": 1xx,
  "responseMessage": "Failure message from server.",
  "paymentID": "XXXXXXXXXXXX"
}
```
