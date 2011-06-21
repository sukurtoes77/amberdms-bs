<?php
/*
	accounts/ar/credit-payments.php
	
	access: account_ar_view

	Credit notes are never refunded directly, rather credits go into the customer's credit pool
	and from there can be assigned to invoices - either manually or automatically.
*/

// custom includes
require("include/accounts/inc_credits.php");
require("include/accounts/inc_invoices_items.php");
require("include/accounts/inc_charts.php");


class page_output
{
	var $id;
	var $obj_menu_nav;
	var $obj_table_items;


	function page_output()
	{
		$this->requires["css"][]	= "include/accounts/css/invoice-items-edit.css";

		// fetch variables
		$this->id = @security_script_input('/^[0-9]*$/', $_GET["id"]);

		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Credit Details", "page=accounts/ar/credit-view.php&id=". $this->id ."");
		$this->obj_menu_nav->add_item("Credit Items", "page=accounts/ar/credit-items.php&id=". $this->id ."");
		$this->obj_menu_nav->add_item("Credit Payments/Refund", "page=accounts/ar/credit-payments.php&id=". $this->id ."", TRUE);
		$this->obj_menu_nav->add_item("Credit Journal", "page=accounts/ar/credit-journal.php&id=". $this->id ."");
		$this->obj_menu_nav->add_item("Export Credit Note", "page=accounts/ar/credit-export.php&id=". $this->id ."");

		if (user_permissions_get("accounts_ar_write"))
		{
			$this->obj_menu_nav->add_item("Delete Credit Note", "page=accounts/ar/credit-delete.php&id=". $this->id ."");
		}
	}



	function check_permissions()
	{
		return user_permissions_get("accounts_ar_view");
	}



	function check_requirements()
	{
		// verify that the credit
		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT id FROM account_ar_credit WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if (!$sql_obj->num_rows())
		{
			log_write("error", "page_output", "The requested credit note (". $this->id .") does not exist - possibly the credit has been deleted.");
			return 0;
		}

		unset($sql_obj);


		return 1;
	}


	function execute()
	{
		// nothing todo
		return 1;
	}

	function render_html()
	{
		// heading
		print "<h3>CREDIT NOTE ITEMS</h3><br>";
		print "<p>This page shows all the items belonging to the credit and allows you to edit them.</p>";
		
		// display summary box
		credit_render_summarybox("ar_credit", $this->id);

		// informational box
		format_msgbox("open", "<p>All credit note refunds go against the customer's credit pool - once there, refunds can be made or the customer can use the credit as payment for their next invoice.</p>");
	}
	
}

?>