<?php 
require_once('class.smsAuthentication.php'); 
//Example use of the SMS Authentication System 
//Requires PHP5 with cURL and mcrypt 

  

$customerUsername='TestUsername'; // The clients ID (username, etc., your information) 
$customerMobileNumber = '447000000000'; // Somehow you have this from the username 
$provider = new smsAuth('1327804','parsnip123'); //Your login details for the API (foreign details) 

  

if(!@$_POST['password']) { // Bad way, but this is an example 
//Create encrypted request and send it to the server. This will send the SMS request to the phone 
$provider->sendAuthenticator($customerUsername, $customerMobileNumber); 
?> 
<form method='post'> 
Enter the code sent to your mobile:<input type='text' name='password'> 
<input type='submit'> 
</form> 
<?php 
} else { 
//Test password against customer username 
$result = $provider->testAuth($customerUsername,$_POST['password']); 
if($result) {?> 
<h1>Authentication successful</h1> 
<?php } else { ?> 
<h1>Authentication Failed</h1> 
<?php 
} 
}