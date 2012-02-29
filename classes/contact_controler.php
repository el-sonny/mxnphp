<?php
class contact_controler extends controler{

	public function do_contact($name_field="Name",$email_field="Email"){
		$this->email_subject = 'Electronic Contact from '.$this->config->http_address ;
		$this->referring_page = $this->config->http_address;
		
		$text = '';
		foreach ($_POST as $key => $value){
			$value = $this->cleanPosUrl($value);
			if($key != 'sendContactEmail')
				$text = $text." <br />".$key.": ".$value;
		}
		if(isset($_POST[$email_field])){
			if($this->check_email_address($_POST[$email_field])){
				$to = $this->config->contact_email;				
				$subject = $this->email_subject.': '.$_POST[$name_field];
				$subject = utf8_decode($subject);				
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$headers .= "From: ".utf8_decode($this->cleanPosUrl($_POST[$name_field]))." <".$_POST[$email_field].">\r\n";
				$headers .= 'To: '.$this->config->recipient_name.' <'.$this->config->contact_email.'>'."\r\n";
				$mailit = mail($to,$subject,$text,$headers);
				if(@$mailit){
					echo "success";
				}else{
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