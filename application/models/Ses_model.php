<?php
	if (!defined('BASEPATH'))
	exit('No direct script access allowed');
	
	class Ses_Model extends CI_Model {
		
		function __construct() {
			parent::__construct();
		}
		
		function sendSes($accesskey,$secretkey,$region,$from,$sender_name,$to,$sub,$msg)
		{
			if(empty($accesskey)) return "Accesskey is empty";
			if(empty($secretkey)) return "Secretkey is empty";
			if(empty($region)) return "Region is empty";
			if(empty($from)) return "Sender is empty";
			if(empty($to)) return "Receipient is empty";
			if(empty($sub)) return "Mail Subject is empty";
			if(empty($msg)) return "Mail body is empty";
			
			$date = gmdate('D, d M Y H:i:s e');
			$plainText = $this->strip_html_tags($msg);

			$parameters = array("Action"=>"SendEmail","Destination.ToAddresses.member1"=>"$to","Source"=>"$sender_name <$from>","Message.Subject.Data"=>"$sub","Message.Body.Html.Data"=>"$msg","Message.Body.Text.Data"=>"$plainText","Message.Body.Html.Charset"=>"UTF-8","Message.Body.Text.Charset"=>"UTF-8","Message.Subject.Charset"=>"UTF-8");

			$query = implode('&', $this->getParametersEncoded($parameters));

			$auth = 'AWS3-HTTPS AWSAccessKeyId='.$accesskey;
			$sig = base64_encode(hash_hmac('sha256', $date, $secretkey, true));
			$auth .= ',Algorithm=HmacSHA256,Signature='.$sig;
			$url = 'https://'.$region.'/';

			$headers = array();
			$headers[] = 'Date: ' . $date;
			$headers[] = 'Host: ' . $region;
			$headers[] = 'X-Amzn-Authorization: ' . $auth;

			$curl_handler = curl_init();;
			curl_setopt($curl_handler, CURLOPT_URL, $url);
			curl_setopt($curl_handler, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $query);
			curl_setopt($curl_handler, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl_handler, CURLOPT_URL, $url);
			curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curl_handler, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl_handler, CURLOPT_SSL_VERIFYPEER, 0);

			$response = curl_exec($curl_handler);
			$err = curl_error($curl_handler);
			curl_close($curl_handler);
			$tmp = " ";
			if($err)
			{
				$tmp .= " Error Occurred "	. print_r($err, true);
			}
			else
			{
				$tmp .= " Response is " . print_r($response, true);
			}

			//$myfile = file_put_contents('/var/www/html/logs/seslogs.txt', $tmp, FILE_APPEND | LOCK_EX);// un comment this line if you want to keep log of the sent records
		}

		function strip_html_tags($str)
		{
		    $str = preg_replace('/(<|>)\1{2}/is', '', $str);
		    $str = preg_replace(
		        array(// Remove invisible content
		            '@<head[^>]*?>.*?</head>@siu',
		            '@<style[^>]*?>.*?</style>@siu',
		            '@<script[^>]*?.*?</script>@siu',
		            '@<noscript[^>]*?.*?</noscript>@siu',
		            ),
		        "", //replace above with nothing
		        $str );
		    $str = $this->replaceWhitespace($str);
		    $str = strip_tags($str);
		    return $str;
		}

		function replaceWhitespace($str) 
		{
		    $result = $str;
		    foreach (array(
		    "  ", " \t",  " \r",  " \n",
		    "\t\t", "\t ", "\t\r", "\t\n",
		    "\r\r", "\r ", "\r\t", "\r\n",
		    "\n\n", "\n ", "\n\t", "\n\r",
		    ) as $replacement) {
		    $result = str_replace($replacement, $replacement[0], $result);
		    }
		    return $str !== $result ? $this->replaceWhitespace($result) : $result;
		}

		function encodeRecipients($recipient)
		{
			if (is_array($recipient)) {
				return join(', ', array_map(array($this, 'encodeRecipients'), $recipient));
			}

			if (preg_match("/(.*)<(.*)>/", $recipient, $regs)) {
				$recipient = '=?' . 'UTF-8' . '?B?'.base64_encode($regs[1]).'?= <'.$regs[2].'>';
			}
			return $recipient;
		}

		function getParametersEncoded($parameters) {
			$params = array();
			foreach ($parameters as $var => $value) {
				if(is_array($value)) {
					foreach($value as $v) {
						$params[] = $var.'='.$this->customUrlEncode($v);
					}
				} else {
					$params[] = $var.'='.$this->customUrlEncode($value);
				}
			}
			sort($params, SORT_STRING);
			return $params;
		}

		function customUrlEncode($var) {
			return str_replace('%7E', '~', rawurlencode($var));
		}
}