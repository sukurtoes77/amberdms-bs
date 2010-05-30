<?php
/*
	bankstatement-csv.php
	
	access: "accounts_import_statement" group members

	Allows user to assign names to CSV columns so the transactions can be assigned
*/

class page_output
{
	var $obj_form;
	var $num_col;
	var $example_array;
	
	function check_permissions()
	{
		return user_permissions_get('accounts_import_statement');
	}

	function check_requirements()
	{
		// nothing todo
		return 1;
	}


	function execute()
	{
		/*
			Define fields and column examples
		*/
		$this->num_col	= count($_SESSION["csv_array"][0]);
		$values_array	= array("transaction_type", "other_party", "particulars", "code", "reference", "amount", "date");
		
		$this->obj_form			= New form_input;
		$this->obj_form->formname	= "bankstatement_csv";
		
		//for each entry in the sub array, create a drop down menu
		for ($i=1; $i<=$this->num_col; $i++)
		{
			$name	= "column".$i;
			$structure 			= NULL;
			$structure["fieldname"]		= $name;
			$structure["type"]		= "dropdown";
			$structure["values"]		= $values_array;

			$this->obj_form->add_input($structure);
		}

		// hidden
		$structure			= NULL;
		$structure["fieldname"]		= "num_cols";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->num_col;
		$this->obj_form->add_input($structure);

		// submit
		$structure 			= NULL;
		$structure["fieldname"]		= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Apply";
		$this->obj_form->add_input($structure);
	



		/*
			populate an array of examples
			create one for each entry in the sub arrays
		*/
		for ($i=0; $i<$this->num_col; $i++)
		{		
		    //check for example in each array
		    //start from the bottom to find examples- this ensures more accurate data
		    //do not create an example if no data is present in any of the columns
		    for ($j=count($_SESSION["csv_array"])-1; $j>0; $j--)
		    {		    
			if ($_SESSION["csv_array"][$j][$i] != "")
			{
			    $this->example_array[$i+1]	= $_SESSION["csv_array"][$j][$i];
			    break;
			}
			
		    }
		}



		/*
			Load error data (if any)
		*/
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
		
	} 

		

	/*
		Output: HTML format
	*/
	function render_html()
	{
		// Title + Summary
		print "<h3>CSV COLUMN CLARIFICATION</h3><br>";
		print "<p>As all banks arrange their CSV files in different order, please indicate, using the examples, what information each column stores. Please note examples may be from different transactions. Not all columns need be given a label, however, the Date, Transaction Type, Other Party, and Amount labels must be used. Two columns may not be given the same label.</p>";
	
		// display the form
		print "<form class=\"form_standard\" action=\"accounts/import/bankstatement-csv-process.php\" method=\"post\" enctype=\"multipart/form-data\">";
		
		print "<table class=\"form_table\">";
		
		    print "<tr class=\"header\">";
			print "<td><b>Column</b></td>";
			print "<td><b>Example</b></td>";
			print "<td><b>Field</b></td>";
		    print "</tr>";
		    for($i=1; $i<=$this->num_col; $i++)
		    {
			if (isset($this->example_array[$i]))
			{
				$name = "column".$i;
				$name_error = $name."-error";
				if (isset($_SESSION["error"][$name_error]))
				{
				print "<tr class=\"form_error\">";
				}
				else
				{
				print "<tr id=\"".$name."\">";
				}
				print "<td>";
				print "Column ".$i;
				print "</td>";
				print "<td>";
				print $this->example_array[$i];
				print "</td>";
				print "<td>";
				$this->obj_form->render_field($name);
				print "</td>";
				print "</tr>";
			}
		    }
		    
		    print "<tr class=\"header\">";
			print "<td colspan=\"3\"><b>Apply Choices</b></td>";
		    print "</tr>";
		    
		    print "<tr id=\"submit\">";
			print "<td colspan=\"3\">";
			$this->obj_form->render_field("num_cols");
			$this->obj_form->render_field("submit");
			print "</td>";
		    print "</tr>";
		print "</table>";
		print "</form>";
	}	

} // end class page_output

?>