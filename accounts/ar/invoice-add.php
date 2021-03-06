<?php
/*
	accounts/ar/invoices-add.php
	
	access: accounts_ar_wirte

	Provides the form to add a new AR invoice to the system	
*/

// custom includes
require("include/accounts/inc_invoices_forms.php");



class page_output
{
	var $id;
	var $obj_menu_nav;
	var $obj_form_invoice;


	function check_permissions()
	{
		return user_permissions_get("accounts_ar_write");
	}


	function check_requirements()
	{
		// nothing to check
		return 1;
	}


	function execute()
	{
		
		$customer_id = @security_script_input('/^[0-9]*$/', $_GET["customerid"]);
		
		$this->obj_form_invoice			= New invoice_form_details;
		$this->obj_form_invoice->type		= "ar";
		$this->obj_form_invoice->invoiceid	= 0;
		$this->obj_form_invoice->customer_id	= $customer_id;
		$this->obj_form_invoice->processpage	= "accounts/ar/invoice-edit-process.php";
		
		$this->obj_form_invoice->execute();
	}

	function render_html()
	{
		// heading
		print "<h3>ADD INVOICE</h3><br>";
		print "<p>This page provides features to allow you to add new invoices to the system.</p>";

		// display form
		$this->obj_form_invoice->render_html();
	}
	
}

?>
