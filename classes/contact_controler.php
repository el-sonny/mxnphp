<?php
abstract class contact_controler extends controler{

	public function do_contact(){
		$this->email_subject = 'Electronic Contact from '$this->config->http_address ;
		$this->referring_page = $this->config->http_address;
		$text = '';
		foreach ($_POST as $key => $value){
			$value = $this->cleanPosUrl($value);
			if($key != 'sendContactEmail')
				$text = $text." <br />".$key.": ".$value;
		}
		if(isset($_POST['Email'])){
			if($this->check_email_address($_POST['Email'])){
				$to = $this->contact_email;
				$subject = $this->email_subject.': '.$_POST['Name'];
				$subject = utf8_decode($subject);
				
				$message = $text;
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$headers .= "From: ".utf8_decode($this->cleanPosUrl($_POST['Name']))." <".$_POST['Email'].">\r\n";
				$headers .= 'To: '.$this->recipient_name.' <'.$this->contact_email.'>'."\r\n";
				$mailit = mail($to,$subject,$message,$headers);
				if ( @$mailit ) {
					echo "success";
				}
				else {
					echo "fail";
				}
			}else
				echo "invalid email";
		}else{
			echo "null email";
		}
	}
	protected function cleanPosUrl ($str) {
		return stripslashes($str);
	}
	protected function check_email_address($email) {
		// First, we check that there's one @ symbol, and that the lengths are right
		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
			// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
			return false;
		}
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
			 if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
				return false;
			}
		}    
		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
					return false; // Not enough parts to domain
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
					return false;
				}
			}
		}
		return true;
	}
}