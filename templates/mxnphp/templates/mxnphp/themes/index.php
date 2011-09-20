<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8 " />
	<?php 
	$this->print_css_tag();
	$this->print_js_tag('jquery');
	$this->print_js_tag('mxnphp-core');
	$this->print_js_tag('interactions');
	?>
	<title><?php echo $this->config->site_name ?></title>
</head>
<body>
	<div id="overlay"><?php $this->include_template('delete_box','global'); ?></div>	
	<div id="header"><?php	$this->include_template('header','global');?></div>
	<div id='menu'><?php $this->include_template('menu','global'); ?></div>
	<div id="content"><?php	$this->include_template($this->template,$this->location);?></div>
	<?php $this->include_template("messages","global");?>
</body>
</html>