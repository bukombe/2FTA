<?php 
/** 
SMS Authentication (c)2013 Andy Dixon - andy@dixon.io - www.dixon.io 
PHP Wrapper Class for the SMS Authentication system 
 **/ 

class smsAuth 
{ 

    private $username; 
    private $password; 
    private $tl_user; 
    private $tl_pass; 
    private $authProviderUrl; 

    function __construct($user = '', $pass = '',$authProvider='http://www.CHANGEME.com/authProvider/') 
    { 
        if ($user == '') throw new smsAuthException('No user specified', 10001); 
        if ($pass == '') throw new smsAuthException('No secret specified', 10002); 
        $this->username = $user; 
        $this->password = $pass; 
        $this->authProviderUrl = $authProvider; 
    } 

    function textlocal($user = '', $pass = '') 
    { 
        if ($user == '') throw new smsAuthException('No TextLocal user specified', 10003); 
        if ($pass == '') throw new smsAuthException('No TextLocal password specified', 10004); 
        $this->tl_user = $user; 
        $this->tl_pass = $pass; 
    } 

    function sendAuthenticator($custUsername = '', $custNumber = '', $senderID = 'AuthOmatic') 
    { 
        if ($custUsername == '') throw new smsAuthException('No Customer Username defined', 10005); 
        if ($custNumber == '') throw new smsAuthException('No mobile number specified', 10006); 
//Generate Payload 
        $payload['number'] = $custNumber; 
        $payload['username'] = $custUsername; 
        $payload['sendername'] = $senderID; 
        $payload['tl_user'] = $this->tl_user; 
        $payload['tl_pass'] = $this->tl_pass; 
        $payload = json_encode($payload); 
        if (!$payload) throw new smsAuthException('JSON Encode Fubar', 10099); 
        $data = "requestHash=" . $this->__encrypt($payload, $this->password); 
        $data .= "&uid=" . $this->username; 
        $data .= "&func=tokenRequester"; 
        $ch = curl_init('http://www.kerrupt.com/authProvider/index.php'); 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $result = curl_exec($ch); 
        curl_close($ch); 
        if (strpos($result, "REQUEST_MADE") > 0) return true; 
        echo $data; 
        throw new smsAuthException('cURL response mismatch: ' . trim($result), 10100); 
    } 

    function testAuth($custUsername = '', $password = '') 
    { 
        if ($custUsername == '') throw new smsAuthException('No Customer Username defined', 10005); 
        if ($password == '') throw new smsAuthException('No password specified', 10007); 
        $data = "u=" . $custUsername; 
        $data .= "&p=" . $password; 
        $data .= "&apuid=" . $this->username; 
        $data .= "&func=authChallenge"; 
        $ch = curl_init($this->authProviderUrl); 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $result = curl_exec($ch); 
        curl_close($ch); 
        if (strpos($result, "ACCEPTED") > 0) return true; 
        return false; 
    } 

    private function __encrypt($text, $key) 
    { 
        $iv = date('dmY'); 
        $bit_check = 8; 
        $text_num = str_split($text, $bit_check); 
        $text_num = $bit_check - strlen($text_num[count($text_num) - 1]); 
        for ($i = 0; $i < $text_num; $i++) { 
            $text = $text . chr($text_num); 
        } 
        $cipher = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'cbc', ''); 
        mcrypt_generic_init($cipher, $key, $iv); 
        $encrypted = mcrypt_generic($cipher, $text); 
        mcrypt_generic_deinit($cipher); 
        return base64_encode($encrypted); 
    } 
} 

class smsAuthException extends Exception 
{ 
    public function __construct($message, $code = 0, Exception $previous = null) 
    { 
        parent::__construct($message, $code, $previous); 
    } 

    public function __toString() 
    { 
        return __CLASS__ . ": [{$this->code}]: {$this->message}n"; 
    } 
} 