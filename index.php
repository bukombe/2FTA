<?php
//Database Connection Details
$username = "root";
$password = "";
$database = "mydatabase";
$server = 'localhost';

//textlocal login details
$tl_username = "jeanpaulbukombe@gmail.com";
$tl_password = "Bukombejean@456";

//Message Prefix
$msgPrefix = "Your password is: ";

mysql_connect($server, $username, $password);
@mysql_select_db($database) or die("Database Error");

$function = $_REQUEST['func'];
print_r($_REQUEST);
call_user_func($function);

function tokenRequester()
{
    global $tl_username;
    global $tl_password;
    global $msgPrefix;
    $requestHash = $_REQUEST['requestHash']; // This is the encrypted data
    $providerUID = $_REQUEST['uid']; // This is the User's identifier (eg ID or username)
    $query = "SELECT * FROM authProviderUsers WHERE apuid='" . addslashes($providerUID) . "'";
    $query .= " AND enabled=1 LIMIT 1"; // Get the decode secret
    $result = mysql_query($query);
    if (mysql_numrows($result) < 1) die ('ERROR_UID_NO_SERVICE');
// Decrypt the request data
    $data = @json_decode(decrypt($requestHash, mysql_result($result, 0, "secret"), date('dmY'), 8), true);
    if (!$data) die('ERROR_DECRYPT_FAILED');
    if ($data['number'] && $data['username'] && $data['sendername']) { // Validate Decrypted data
        $password = generatePassword(8); // Create a password 8 digits long
// Send the SMS
        $pdata = "uname=" . urlencode($tl_username) . "&pword=" . urlencode($tl_password) . "&message=" . urlencode($msgPrefix . $password) . "&from=" . urlencode($data['sendername']) . "&selectednums=" . $data['number'] . "&info=1";
// Send the POST request with cURL
        $ch = curl_init('http://www.txtlocal.com/sendsmspost.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $pdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
// id, authProviderId, username, password, unixtime, valid,used
        $query = "INSERT INTO authRequests VALUES ('','" . $providerUID . "','" . $data['username'] . "','" . $password . "'," . time() . ",'1',0)";
        mysql_query($query);
        mysql_close();
        die('OK_REQUEST_MADE');
    }
}

function authChallenge()
{
    $username = $_REQUEST['u'];
    $password = $_REQUEST['p'];
    $apuid = $_REQUEST['apuid'];
    $query = "SELECT * FROM authRequests WHERE authProviderId='" . addslashes($apuid) . "' AND username='" . addslashes($username) . "' AND password='" . addslashes($password) . "' AND valid=1 LIMIT 1";
    $result = mysql_query($query);
    if (mysql_numrows($result) < 1) die ('ERROR_CHALLENGE_REJECTED');
    $query = "UPDATE authRequests SET valid=0 WHERE id=" . mysql_result($result, 0, "id") . ";";
    mysql_query($query);
    $query = "UPDATE authRequests SET used=" . time() . " WHERE id=" . mysql_result($result, 0, "id") . ";";
    mysql_query($query);
    die('CHALLENGE_ACCEPTED');
}

function encrypt($text, $key, $iv, $bit_check)
{
    $text_num = str_split($text, $bit_check);
    $text_num = $bit_check - strlen($text_num[count($text_num) - 1]);
    for ($i = 0; $i < $text_num; $i++) {
        $text = $text . chr($text_num);
    }
    $cipher = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'cbc', '');
    mcrypt_generic_init($cipher, $key, $iv);
    $decrypted = mcrypt_generic($cipher, $text);
    mcrypt_generic_deinit($cipher);
    return base64_encode($decrypted);
}

function decrypt($encrypted_text, $key, $iv, $bit_check)
{
    $cipher = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'cbc', '');
    mcrypt_generic_init($cipher, $key, $iv);
    $decrypted = mdecrypt_generic($cipher, base64_decode($encrypted_text));
    mcrypt_generic_deinit($cipher);
    $last_char = substr($decrypted, -1);
    for ($i = 0; $i < $bit_check - 1; $i++) {
        if (chr($i) == $last_char) {
            $decrypted = substr($decrypted, 0, strlen($decrypted) - $i);
            break;
        }
    }
    return $decrypted;
}

function generatePassword($length = 8)
{
    $password = "";
    $possible = "12346789abcdfghjkmnpqrtvwxyzABCDFGHJKLMNPQRTVWXYZ";
    $maxlength = strlen($possible);
    if ($length > $maxlength) {
        $length = $maxlength;
    }

    $i = 0;

    while ($i < $length) {

        $char = substr($possible, mt_rand(0, $maxlength - 1), 1);

        if (!strstr($password, $char)) {
            $password .= $char;
            $i++;
        }

    }

    return $password;

}

print_r($_REQUEST); 