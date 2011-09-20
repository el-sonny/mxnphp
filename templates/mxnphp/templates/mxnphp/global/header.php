<div class="userInfo">
	<p>
		<?php
			if(isset($this->config->logo))
				$this->print_img_tag($this->config->logo,'logo','img','logo');
			else{
				echo "<p class='site-name'>".$this->config->site_name."</p>";
			}
		?>
		<a href='/security/logout'>LOG OUT</a>
	</p>
</div>