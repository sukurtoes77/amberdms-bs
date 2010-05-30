<?php
/*
	admin/config_integration.php
	
	access: admin users only

	Options and configuration for service billing.
*/

class page_output
{
	var $obj_form;


	function check_permissions()
	{
		return user_permissions_get("admin");
	}

	function check_requirements()
	{
		// nothing to do
		return 1;
	}


	function execute()
	{
		/*
			Define form structure
		*/
		
		$this->obj_form = New form_input;
		$this->obj_form->formname = "config_services";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "admin/config_services-process.php";
		$this->obj_form->method = "post";


		/*
			usage services data source configuration
		
			We limit this to enabled servers, since it could be misused to try breaking into SQL databases
			if run by untrusted administrators.
		*/		
		if ($GLOBALS["config"]["dangerous_conf_options"] == "enabled")
		{
			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_TRAFFIC_MODE";
			$structure["type"]					= "radio";
			$structure["values"]					= array("internal", "external");
			$structure["translations"]["internal"]			= "Use an internal database for usage records, uploaded via the SOAP API";
			$structure["translations"]["external"]			= "Use an external SQL database for fetching usage records (such as a netflow DB)";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_TYPE";
			$structure["type"]					= "radio";
			$structure["values"]					= array("mysql_netflow_daily");
			$structure["translations"]["mysql_netflow_daily"]	= "MySQL Netflow Daily Tables (traffic_YYYYMMDD)";
			$structure["options"]["autoselect"]			= "yes";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_HOST";
			$structure["type"]					= "input";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_NAME";
			$structure["type"]					= "input";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_USERNAME";
			$structure["type"]					= "input";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_PASSWORD";
			$structure["type"]					= "input";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);


			// set javascript actions
			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "default", "SERVICE_TRAFFIC_DB_TYPE", "hide");
			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "default", "SERVICE_TRAFFIC_DB_HOST", "hide");
			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "default", "SERVICE_TRAFFIC_DB_NAME", "hide");
			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "default", "SERVICE_TRAFFIC_DB_USERNAME", "hide");
			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "default", "SERVICE_TRAFFIC_DB_PASSWORD", "hide");

			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "external", "SERVICE_TRAFFIC_DB_TYPE", "show");
			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "external", "SERVICE_TRAFFIC_DB_HOST", "show");
			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "external", "SERVICE_TRAFFIC_DB_NAME", "show");
			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "external", "SERVICE_TRAFFIC_DB_USERNAME", "show");
			$this->obj_form->add_action("SERVICE_TRAFFIC_MODE", "external", "SERVICE_TRAFFIC_DB_PASSWORD", "show");
		

			// add subform
			$this->obj_form->subforms["config_usage_traffic"]	= array("SERVICE_TRAFFIC_MODE", "SERVICE_TRAFFIC_DB_TYPE", "SERVICE_TRAFFIC_DB_HOST", "SERVICE_TRAFFIC_DB_NAME", "SERVICE_TRAFFIC_DB_USERNAME", "SERVICE_TRAFFIC_DB_PASSWORD");
		}
		else
		{
			//
			// explain that the configuration is locked and tell the user the current source of records.
			//
			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_TRAFFIC_MSG";
			$structure["type"]					= "message";

			if (sql_get_singlevalue("SELECT value FROM config WHERE name='SERVICE_TRAFFIC_MODE' LIMIT 1") == "internal")
			{
				$structure["defaultvalue"]			= "<p>Using internal database for usage records (this configuration is locked by the system administrator)</p>";
			}
			else
			{
				$structure["defaultvalue"]			= "<p>Use external database for usage records (this configuration is locked by the system administrator)</p>";
			}

			$structure["options"]["css_row_class"]			= "table_highlight_info";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);


			$this->obj_form->subforms["config_usage_traffic"]	= array("SERVICE_TRAFFIC_MSG");
		}



		/*
			cdr services data source configuration
		
			We limit this to enabled servers, since it could be misused to try breaking into SQL databases
			if run by untrusted administrators.
		*/		
		if ($GLOBALS["config"]["dangerous_conf_options"] == "enabled")
		{
			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_CDR_MODE";
			$structure["type"]					= "radio";
			$structure["values"]					= array("internal", "external");
			$structure["translations"]["internal"]			= "Use an internal database for usage records, uploaded via the SOAP API";
			$structure["translations"]["external"]			= "Use an external SQL database for fetching usage records.";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_CDR_DB_TYPE";
			$structure["type"]					= "radio";
			$structure["values"]					= array("mysql_asterisk");
			$structure["translations"]["mysql_asterisk"]		= "MySQL-based Asterisk CDR Database";
			$structure["options"]["autoselect"]			= "yes";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_CDR_DB_HOST";
			$structure["type"]					= "input";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_CDR_DB_NAME";
			$structure["type"]					= "input";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_CDR_DB_USERNAME";
			$structure["type"]					= "input";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);

			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_CDR_DB_PASSWORD";
			$structure["type"]					= "input";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);


			// set javascript actions
			$this->obj_form->add_action("SERVICE_CDR_MODE", "default", "SERVICE_CDR_DB_TYPE", "hide");
			$this->obj_form->add_action("SERVICE_CDR_MODE", "default", "SERVICE_CDR_DB_HOST", "hide");
			$this->obj_form->add_action("SERVICE_CDR_MODE", "default", "SERVICE_CDR_DB_NAME", "hide");
			$this->obj_form->add_action("SERVICE_CDR_MODE", "default", "SERVICE_CDR_DB_USERNAME", "hide");
			$this->obj_form->add_action("SERVICE_CDR_MODE", "default", "SERVICE_CDR_DB_PASSWORD", "hide");

			$this->obj_form->add_action("SERVICE_CDR_MODE", "external", "SERVICE_CDR_DB_TYPE", "show");
			$this->obj_form->add_action("SERVICE_CDR_MODE", "external", "SERVICE_CDR_DB_HOST", "show");
			$this->obj_form->add_action("SERVICE_CDR_MODE", "external", "SERVICE_CDR_DB_NAME", "show");
			$this->obj_form->add_action("SERVICE_CDR_MODE", "external", "SERVICE_CDR_DB_USERNAME", "show");
			$this->obj_form->add_action("SERVICE_CDR_MODE", "external", "SERVICE_CDR_DB_PASSWORD", "show");
		

			// add subform
			$this->obj_form->subforms["config_usage_cdr"]	= array("SERVICE_CDR_MODE", "SERVICE_CDR_DB_TYPE", "SERVICE_CDR_DB_HOST", "SERVICE_CDR_DB_NAME", "SERVICE_CDR_DB_USERNAME", "SERVICE_CDR_DB_PASSWORD");
		}
		else
		{
			//
			// explain that the configuration is locked and tell the user the current source of records.
			//
			$structure = NULL;
			$structure["fieldname"]					= "SERVICE_CDR_MSG";
			$structure["type"]					= "message";

			if (sql_get_singlevalue("SELECT value FROM config WHERE name='SERVICE_CDR_MODE' LIMIT 1") == "internal")
			{
				$structure["defaultvalue"]			= "<p>Using internal database for usage records (this configuration is locked by the system administrator)</p>";
			}
			else
			{
				$structure["defaultvalue"]			= "<p>Use external database for usage records (this configuration is locked by the system administrator)</p>";
			}

			$structure["options"]["css_row_class"]			= "table_highlight_info";
			$structure["options"]["no_translate_fieldname"]		= "yes";
			$this->obj_form->add_input($structure);


			$this->obj_form->subforms["config_usage_cdr"]		= array("SERVICE_CDR_MSG");
		}







		// migration mode options
		$structure = NULL;
		$structure["fieldname"]				= "SERVICE_MIGRATION_MODE";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "When enabled, provides additional options to service creation to create a part usage period.";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		
		// misc
		$structure = NULL;
		$structure["fieldname"]				= "SERVICE_PARTPERIOD_MODE";
		$structure["type"]				= "radio";
		$structure["values"]				= array("seporate", "merge");

		$structure["translations"]["seporate"]		= "Invoice a partial period (eg new customer signup) in a seporate invoice.";
		$structure["translations"]["merge"]		= "Add the additional period to next month's invoice.";

		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);



		// submit section
		$structure = NULL;
		$structure["fieldname"]				= "submit";
		$structure["type"]				= "submit";
		$structure["defaultvalue"]			= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["config_migration"]		= array("SERVICE_MIGRATION_MODE");
		$this->obj_form->subforms["config_misc"]		= array("SERVICE_PARTPERIOD_MODE");
		$this->obj_form->subforms["submit"]			= array("submit");

		if (error_check())
		{
			// load error datas
			$this->obj_form->load_data_error();
		}
		else
		{
			// fetch all the values from the database
			$sql_config_obj		= New sql_query;
			$sql_config_obj->string	= "SELECT name, value FROM config ORDER BY name";
			$sql_config_obj->execute();
			$sql_config_obj->fetch_array();

			foreach ($sql_config_obj->data as $data_config)
			{
				$this->obj_form->structure[ $data_config["name"] ]["defaultvalue"] = $data_config["value"];
			}

			unset($sql_config_obj);
		}


	}



	function render_html()
	{
		// Title + Summary
		print "<h3>SERVICE CONFIGURATION</h3><br>";
		print "<p>Options and configuration for services and billing.</p>";

		// display the form
		$this->obj_form->render_form();
	}

	
}

?>