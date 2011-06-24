<?php
abstract class contact_controler extends controler{

	protected function do_contact(){
		$this->email_subject = 'Electronic Contact from '.$this->config->http_address ;
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
		return preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $email);
	}
}