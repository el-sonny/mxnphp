$(document).ready(function(){
	$("#menu").mxnphpAdminMenu();
	$("#content .menu a").mxnphpAdminSubmenu();	
	$("#content .tabs a").mxnphpAdminTabs();	
	$("#messages a").mxnphpAdminMessages();
	$("a.delete").mxnphpAdminDelete();
	$("select.multi-select-new").mxnphpMultiSelect();
	$("select.multi-select-edit").mxnphpMultiEdit();
});