<?php
/*
	include/services/inc_services_forms.php

	Provides forms for service management
*/


require("include/accounts/inc_charts.php");


/*
	class: services_form_details

	Generates forms for processing service details
*/
class services_form_details
{
	var $serviceid;			// ID of the service entry
	var $mode;			// Mode: "add" or "edit"

	var $obj_form;


	function execute()
	{
		log_debug("services_form_details", "Executing execute()");

		/*
			Define form structure
		*/
		$this->obj_form = New form_input;
		$this->obj_form->formname = "service_". $this->mode;
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "services/edit-process.php";
		$this->obj_form->method = "post";

		// general
		$structure = NULL;
		$structure["fieldname"] 	= "name_service";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = charts_form_prepare_acccountdropdown("chartid", 2);
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);
		
		$structure = NULL;
		$structure["fieldname"] 	= "description";
		$structure["type"]		= "textarea";
		$this->obj_form->add_input($structure);


		// the service type can only be set at creation time.
		if ($this->mode == "add")
		{
			$structure = form_helper_prepare_radiofromdb("typeid", "SELECT id, name as label FROM service_types ORDER BY name");
			$structure["options"]["req"]	= "yes";
			$this->obj_form->add_input($structure);
		}
		else
		{
			$structure			= NULL;
			$structure["fieldname"]		= "typeid";
			$structure["type"]		= "text";
			$this->obj_form->add_input($structure);
		}

		// define subforms
		$this->obj_form->subforms["service_details"]	= array("name_service", "chartid", "typeid", "description");
		$this->obj_form->subforms["submit"]		= array("submit");


		/*
			Mode dependent options
		*/
		
		if ($this->mode == "add")
		{
			// submit button
			$structure = NULL;
			$structure["fieldname"] 	= "submit";
			$structure["type"]		= "submit";
			$structure["defaultvalue"]	= "Create Service";
			$this->obj_form->add_input($structure);
		}
		else
		{
			// submit button
			if (user_permissions_get("services_write"))
			{
				$structure = NULL;
				$structure["fieldname"] 	= "submit";
				$structure["type"]		= "submit";
				$structure["defaultvalue"]	= "Save Changes";
				$this->obj_form->add_input($structure);
			}
			else
			{
				$structure = NULL;
				$structure["fieldname"] 	= "submit";
				$structure["type"]		= "message";
				$structure["defaultvalue"]	= "<p><i>Sorry, you don't have permissions to make changes to services.</i></p>";
				$this->obj_form->add_input($structure);
			}


			// hidden data
			$structure = NULL;
			$structure["fieldname"] 	= "id_service";
			$structure["type"]		= "hidden";
			$structure["defaultvalue"]	= $this->serviceid;
			$this->obj_form->add_input($structure);
				

			$this->obj_form->subforms["hidden"]	= array("id_service");
		}


		/*
			Load Data
		*/
		if ($this->mode == "add")
		{
			$this->obj_form->load_data_error();
		}
		else
		{
			// load details data
			$this->obj_form->sql_query = "SELECT services.name_service, services.chartid, services.description, service_types.name as typeid FROM `services` LEFT JOIN service_types ON service_types.id = services.typeid WHERE services.id='". $this->serviceid ."' LIMIT 1";
			$this->obj_form->load_data();
		}
	}


	function render_html()
	{
		log_debug("services_form_details", "Executing execute()");
		
		// display form
		$this->obj_form->render_form();
	}
}


/*
	class: services_form_plan

	Generates forms for configuring the service plan information.
*/
class services_form_plan
{
	var $serviceid;			// ID of the service entry

	var $obj_form;


	function execute()
	{
		log_debug("services_form_plan", "Executing execute()");

		/*
			Fetch plan type information
		*/
		$sql_plan_obj		 = New sql_query;
		$sql_plan_obj->string	 = "SELECT services.typeid, service_types.name FROM services LEFT JOIN service_types ON service_types.id = services.typeid WHERE services.id='". $this->serviceid ."' LIMIT 1";
		$sql_plan_obj->execute();
		$sql_plan_obj->fetch_array();



		/*
			Define form structure
		*/
		$this->obj_form = New form_input;
		$this->obj_form->formname = "service_plan";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "services/plan-edit-process.php";
		$this->obj_form->method = "post";


		// general details
		$structure = NULL;
		$structure["fieldname"] 	= "name_service";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


		$structure = form_helper_prepare_radiofromdb("billing_cycle", "SELECT id, name as label FROM billing_cycles");
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);
		
		$structure = NULL;
		$structure["fieldname"] 	= "price";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);



		/*
			Type-specific Form Options
		*/

		switch ($sql_plan_obj->data[0]["name"])
		{
			case "generic_with_usage":
				/*
					GENERIC_WITH_USAGE

					This service is to be used for any non-traffic, non-time accounting service that needs to track usage. Examples of this
					could be counting the number of API requests, size of disk usage on a vhost, etc.
				*/
				

				// custom
				$structure = NULL;
				$structure["fieldname"]		= "plan_information";
				$structure["type"]		= "message";
				$structure["defaultvalue"]	= "<i>This section is where you define what units you wish to bill in, along with the cost of excess units. It is acceptable to leave the price for extra units set to 0.00 if you have some other method of handling excess usage (eg: rate shaping rather than billing). If you wish to create an uncapped/unlimited usage service, set both the price for extra units and the included units to 0.</i>";
				$this->obj_form->add_input($structure);

				$structure = NULL;
				$structure["fieldname"]			= "units";
				$structure["type"]			= "input";
				$structure["options"]["req"]		= "yes";
				$structure["options"]["autoselect"]	= "yes";	
				$this->obj_form->add_input($structure);
		
				$structure = NULL;
				$structure["fieldname"] 	= "included_units";
				$structure["type"]		= "input";
				$structure["options"]["req"]	= "yes";
				$this->obj_form->add_input($structure);
				
				$structure = NULL;
				$structure["fieldname"] 	= "price_extraunits";
				$structure["type"]		= "input";
				$structure["options"]["req"]	= "yes";
				$this->obj_form->add_input($structure);

				$structure = form_helper_prepare_radiofromdb("usage_mode", "SELECT id, description as label FROM service_usage_modes");
				$structure["options"]["req"]		= "yes";
				$this->obj_form->add_input($structure);


				// general
				$structure = form_helper_prepare_radiofromdb("billing_mode", "SELECT id, name as label, description as label1 FROM billing_modes WHERE name NOT LIKE '%advance%'");
				$structure["options"]["req"]		= "yes";

				// replace all the -- joiners with <br> for clarity
				for ($i = 0; $i < count($structure["values"]); $i++)
				{
					$structure["translations"][ $structure["values"][$i] ] = str_replace("--", "<br><i>", $structure["translations"][ $structure["values"][$i] ]);
					$structure["translations"][ $structure["values"][$i] ] .= "</i>";
				}
				
				$this->obj_form->add_input($structure);



				// subforms
				$this->obj_form->subforms["service_plan"]		= array("name_service", "price", "billing_cycle", "billing_mode");
				$this->obj_form->subforms["service_plan_custom"]	= array("plan_information", "units", "included_units", "price_extraunits", "usage_mode");
		
			break;
			
			case "licenses":
				/*
					LICENSES

				*/
				$structure = NULL;
				$structure["fieldname"]		= "plan_information";
				$structure["type"]		= "message";
				$structure["defaultvalue"]	= "<i>For licenses services, the price field and included units specify how much for the number of base licenses. The extra units price field specifies how much for additional licenses.</i>";
				$this->obj_form->add_input($structure);

				$structure = NULL;
				$structure["fieldname"]			= "units";
				$structure["type"]			= "input";
				$structure["options"]["req"]		= "yes";
				$structure["options"]["autoselect"]	= "yes";
				$this->obj_form->add_input($structure);
		
				$structure = NULL;
				$structure["fieldname"] 	= "included_units";
				$structure["type"]		= "input";
				$structure["options"]["req"]	= "yes";
				$this->obj_form->add_input($structure);
				
				$structure = NULL;
				$structure["fieldname"] 	= "price_extraunits";
				$structure["type"]		= "input";
				$structure["options"]["req"]	= "yes";
				$this->obj_form->add_input($structure);

				
				// general
				$structure = form_helper_prepare_radiofromdb("billing_mode", "SELECT id, name as label, description as label1 FROM billing_modes");
				$structure["options"]["req"]		= "yes";
				
				// replace all the -- joiners with <br> for clarity
				for ($i = 0; $i < count($structure["values"]); $i++)
				{
					$structure["translations"][ $structure["values"][$i] ] = str_replace("--", "<br><i>", $structure["translations"][ $structure["values"][$i] ]);
					$structure["translations"][ $structure["values"][$i] ] .= "</i>";
				}

				$this->obj_form->add_input($structure);
		

				
				// subforms
				$this->obj_form->subforms["service_plan"]		= array("name_service", "price", "billing_cycle", "billing_mode");
				$this->obj_form->subforms["service_plan_custom"] = array("plan_information", "units", "included_units", "price_extraunits");
			break;


			
			case "time":
			case "data_traffic":
				/*
					TIME or DATA_TRAFFIC

					Incrementing usage counters.
				*/
				$structure = NULL;
				$structure["fieldname"]		= "plan_information";
				$structure["type"]		= "message";
				$structure["defaultvalue"]	= "<i>This section is where you define what units you wish to bill in, along with the cost of excess units. It is acceptable to leave the price for extra units set to 0.00 if you have some other method of handling excess usage (eg: rate shaping rather than billing). If you wish to create an uncapped/unlimited usage service, set both the price for extra units and the included units to 0.</i>";
				$this->obj_form->add_input($structure);

				$structure = form_helper_prepare_radiofromdb("units", "SELECT id, name as label, description as label1 FROM service_units WHERE typeid='". $sql_plan_obj->data[0]["typeid"] ."' ORDER BY name");
				$structure["options"]["req"]		= "yes";
				$structure["options"]["autoselect"]	= "yes";
				$this->obj_form->add_input($structure);
		
				$structure = NULL;
				$structure["fieldname"] 	= "included_units";
				$structure["type"]		= "input";
				$structure["options"]["req"]	= "yes";
				$this->obj_form->add_input($structure);
				
				$structure = NULL;
				$structure["fieldname"] 	= "price_extraunits";
				$structure["type"]		= "input";
				$structure["options"]["req"]	= "yes";
				$this->obj_form->add_input($structure);
				
				// general
				$structure = form_helper_prepare_radiofromdb("billing_mode", "SELECT id, name as label, description as label1 FROM billing_modes WHERE name NOT LIKE '%advance%'");
				$structure["options"]["req"]		= "yes";

				// replace all the -- joiners with <br> for clarity
				for ($i = 0; $i < count($structure["values"]); $i++)
				{
					$structure["translations"][ $structure["values"][$i] ] = str_replace("--", "<br><i>", $structure["translations"][ $structure["values"][$i] ]);
					$structure["translations"][ $structure["values"][$i] ] .= "</i>";
				}
		
				$this->obj_form->add_input($structure);


				
				// subforms
				$this->obj_form->subforms["service_plan"]		= array("name_service", "price", "billing_cycle", "billing_mode");
				$this->obj_form->subforms["service_plan_custom"] = array("plan_information", "units", "included_units", "price_extraunits");
			break;


			case "generic_no_usage":
			default:
				// no extra fields to display

				// general
				$structure = form_helper_prepare_radiofromdb("billing_mode", "SELECT id, name as label, description as label1 FROM billing_modes");
				$structure["options"]["req"]		= "yes";
				
				// replace all the -- joiners with <br> for clarity
				for ($i = 0; $i < count($structure["values"]); $i++)
				{
					$structure["translations"][ $structure["values"][$i] ] = str_replace("--", "<br><i>", $structure["translations"][ $structure["values"][$i] ]);
					$structure["translations"][ $structure["values"][$i] ] .= "</i>";
				}

				$this->obj_form->add_input($structure);


			
				// subforms
				$this->obj_form->subforms["service_plan"]		= array("name_service", "price", "billing_cycle", "billing_mode");
			break;
		}



		// submit button
		if (user_permissions_get("services_write"))
		{
			$structure = NULL;
			$structure["fieldname"] 	= "submit";
			$structure["type"]		= "submit";
			$structure["defaultvalue"]	= "Save Changes";
			$this->obj_form->add_input($structure);
		}
		else
		{
			$structure = NULL;
			$structure["fieldname"] 	= "submit";
			$structure["type"]		= "message";
			$structure["defaultvalue"]	= "<p><i>Sorry, you don't have permissions to make changes to services.</i></p>";
			$this->obj_form->add_input($structure);
		}


		// hidden data
		$structure = NULL;
		$structure["fieldname"] 	= "id_service";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->serviceid;
		$this->obj_form->add_input($structure);
		
		// define subforms
		$this->obj_form->subforms["hidden"]		= array("id_service");
		$this->obj_form->subforms["submit"]		= array("submit");



		/*
			Load Data
		*/
		$this->obj_form->sql_query = "SELECT * FROM `services` WHERE id='". $this->serviceid ."' LIMIT 1";
		$this->obj_form->load_data();
	}


	function render_html()
	{
		// display form
		$this->obj_form->render_form();
	}

} // end of services_form_plan







/*
	class: services_form_delete

	Generates forms for deleting unwanted services
*/
class services_form_delete
{
	var $serviceid;			// ID of the service entry

	var $obj_form;


	function execute()
	{
		log_debug("services_form_delete", "Executing execute()");

		/*
			Define form structure
		*/
		$this->obj_form = New form_input;
		$this->obj_form->formname		= "service_delete";
		$this->obj_form->language		= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "services/delete-process.php";
		$this->obj_form->method		= "POST";

		
		// basic details
		$structure = NULL;
		$structure["fieldname"] 	= "name_service";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);
		
		$structure = NULL;
		$structure["fieldname"] 	= "delete_confirm";
		$structure["type"]		= "checkbox";
		$structure["options"]["label"]	= "Yes, I wish to delete this service and realise that once deleted the data can not be recovered.";
		$this->obj_form->add_input($structure);


		// ID
		$structure = NULL;
		$structure["fieldname"]		= "id_service";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->serviceid;
		$this->obj_form->add_input($structure);	


		// submit
		$structure = NULL;
		$structure["fieldname"]		= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Delete Service";
		$this->obj_form->add_input($structure);


		// load data
		$this->obj_form->sql_query = "SELECT name_service FROM services WHERE id='". $this->serviceid ."'";
		$this->obj_form->load_data();


		$this->obj_form->subforms["service_delete"]		= array("name_service", "delete_confirm");
		$this->obj_form->subforms["hidden"]			= array("id_service");
		$this->obj_form->subforms["submit"]			= array("submit");
	}


	function render_html()
	{
		log_debug("services_form_delete", "Executing execute()");
		
		// display form
		$this->obj_form->render_form();
	}
}







?>