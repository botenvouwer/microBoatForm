<?php

	/**
	* ~form.class.php
	* 
	* version 2
	* 
	* description,
	* 	Build quict and easy forms with this form class it can also validate the forms
	* 
	* Example create form from array,
	*
	*	$form = array();
	*	
	*	$form['headers'] = array('id' => 'thisform', 'type' => 2, 'action' => 'urltosend','level' => '', 'param' => '', 'name' => 'formname', 'description' => 'dit is een form', 'headers' => array(
	*	
	*	1 => array('subname', 'Fill this out top be awesome'),
	*	2 => array('subname2', 'Fill this in to')
	*	 
	*	));
	*	
	*	$form['elements'] = array(
	*		1 => array(
	*			0 => array(1, 'dinkie1', 0, 1, 'Fill in your name'),
	*			1 => array(2, 'dinkie2', 0, 0, 'Fill in your name'),
	*			2 => array(3, 'dinkie3', 0, 0, 'Fill in your name')
	*		),
	*		2 => array(
	*			0 => array(4, 'dinkie4', 17, 0, 'Fill in your name'),
	*			1 => array(5, 'dinkie5', 17, 0, 'Fill in your name')
	*		)
	*	);
	*	
	*	$form['options'] = array(
	*		4 => array(
	*			1 => array('deze', 'value'),
	*			2 => array('deze', 'value'),
	*			3 => array('deze', 'value')
	*		),
	*		5 => array(
	*			1 => array('deze', 'value'),
	*			2 => array('deze', 'value'),
	*			3 => array('deze', 'value')
	*		)
	*	);
	*
	* 
	* You can extend the form elements by declaring a class:
	* 
	* 	class formNameofElement extends formElement{
	*		public $id = 0;
	*		public $name = '';
	*		public $required = 0;
	*		public $description = '';
	*		protected $value = '';
	*		protected $reqclass = '';
	*		protected $star = '';
	*		protected $formid = '';
	* 
	* 		function get_html(){
	*			//you own element definition
	*		}
	*	}
	* 
	* 	form input elemetns in this file are:
	* 
	* 	to do:
	* 		
	* 		- update de database naar nieuwe standaard
	* 		- alle benodigde form inputs aanmaken
	* 
	* 	long term to do:
	* 
	* 		- make Object Interfaces wich is implemented by formElement
	* 
	**/
	
	class microBoatForm{
		
		private $form = '';
		private $load = false;
		private $id = '';
		public $name = '';
		public $description = '';
		private $submit_type = '';
		private $action = '';
		private $level = '';
		private $param = '';
		public $headers = array();
		public $elements = array();
		private $options = array();
		public $submit_list = array();
		public $header;
		public $header_table = 'form';
		public $headers_table = 'form_titles';
		public $elements_table = 'form_elements';
		public $options_table = 'form_options';
		
		function __construct(){
			
		}
		
		function load_db($id){
			
			//headers
			$db = new db();
			$form = array();
			if((!$id) || (!is_numeric($id))){
				throw new Exception('Give valid ID on second param');
			}
			
			$query = "SELECT * FROM `$this->header_table` WHERE `id` = :id";
			$param = array(
				array(':id', $id)
			);
			$query = $db->prepareQuery($query, $param);
			$form['headers'] = $query->fetch();
			
			//body form elements
			$query = "SELECT `id`, `title`, `description` FROM `$this->headers_table` WHERE `form_id` = :id";
			$param = array(
				array(':id', $id)
			);
			$query = $db->prepareQuery($query, $param);
			
			$form['elements'] = array();
			$form['options'] = array();
			while($row = $query->fetch()){
				$form['headers']['headers'][$row['id']] = array($row['title'], $row['description']);
				
				$subquery = "SELECT `id`, `title_id`, `name`, `type`, `required`, `description` FROM `$this->elements_table` WHERE `form_id` = :id AND `title_id` = :title_id ORDER BY `order`";
				$param = array(
					array(':id', $id, 'int'),
					array(':title_id', $row['id'], 'int')
				);
				$subquery = $db->prepareQuery($subquery, $param);
				
				
				while($subrow = $subquery->fetch()){
					$form['elements'][$row['id']][] = array($subrow['id'],$subrow['name'],$subrow['type'],$subrow['required'],$subrow['description']);
					
					$count = "SELECT COUNT(*) FROM `$this->options_table` WHERE `form_element_id` = :id";
					$param = array(
						array(':id', $subrow['id'])
					);
					$count = $db->prepareQuery($count, $param);
					$count = $count->fetchColumn();
						
					if($count > 0){
						$option_q = "SELECT `id`, `name`, `value` FROM `$this->options_table` WHERE `form_element_id` = :id";
						$param = array(
							array(':id', $subrow['id'])
						);
						$option_q = $db->prepareQuery($option_q, $param);
						
						$form['options'][$subrow['id']] = array();
						while($opt = $option_q->fetch()){
							$form['options'][$subrow['id']][$opt['id']] = array($opt['name'], $opt['value']);
						}
					}
				}
			}
			//echo '<dialog id="form_window" hide="false" max="false" minheight="300" minwidth="300" height="700" width="700" ><pre>'.print_r($form, true).'</pre></dialog>';
			$this->load($form);
		}
		
		function load(&$form){
			
			if(!is_array($form)){
				throw new Exception('Load error: Please give form array or database table');
			}
			
			//validate and set headers
			if(!isset($form['headers'])){
				throw new Exception('Header error: no header found');
			}
			elseif(!$form['headers']){
				throw new Exception('Header error: no header found');
			}
			elseif(!isset($form['headers']['id'])){
				throw new Exception('Header error: no header id found');
			}
			elseif(!$form['headers']['id']){
				throw new Exception('Header error: no header id found');
			}
			elseif(!isset($form['headers']['type'])){
				throw new Exception('Header error: no header type found');
			}
			elseif(!$form['headers']['type']){
				throw new Exception('Header error: no header type found');
			}
			elseif(!isset($form['headers']['action'])){
				throw new Exception('Header error: no header action found');
			}
			elseif(!$form['headers']['action']){
				throw new Exception('Header error: no header action found');
			}
			elseif(!isset($form['headers']['headers'])){
				throw new Exception('Header error: no header headers found');
			}
			elseif(!$form['headers']['headers']){
				throw new Exception('Header error: no header headers found');
			}

			$this->id = $form['headers']['id'];
			$this->action = $form['headers']['action'];
			$this->level = $form['headers']['level'];
			$this->param = $form['headers']['param'];
			$this->name = $form['headers']['name'];
			$this->description = $form['headers']['description'];
			$this->headers = $form['headers']['headers'];
			$this->set_submit($form['headers']['type']);
			
			if(!isset($form['elements'])){
				throw new Exception('Element error: No elements found');
			}
			
			//loop trough form elements and validate them
			foreach($form['elements'] as $key => $subset){
				$a = 0;
				foreach($subset as $element){
					if(isset($form['options'][$element[0]])){
						$this->add_element($key,$element[0],$element[1],$element[2],$element[3],$element[4],$form['options'][$element[0]],$a++);
					}
					else{
						$this->add_element($key,$element[0],$element[1],$element[2],$element[3],$element[4],false,$a++);
					}
					
				}
			}
			
			unset($form);
		}
		
		function set_submit($type){
			$class = 'form'.ucfirst($type).'Submit';
			if(class_exists($class)){
				$this->header = new $class($this->id, $this->action, $this->level, $this->param);
				$this->submit_type = $type;
			}
			else{
				throw new Exception("Header error: type '$class' does not exist");
			}
		}
		
		function set_id($value){
			$this->id = $value;
			$this->header->id = $value;
			foreach($this->elements as $elements){
				foreach($elements as $element){
					$element->formid = $value;
				}
			}
		}
		
		function set_action($value){
			$this->action = $value;
			$this->header->action = $value;
		}
		
		function set_level($value){
			$this->level = $value;
			$this->header->level = $value;
		}
		
		function set_param($value){
			$this->param = $value;
			$this->header->param = $value;
		}
		
		function validate(){
			
			if($this->submit_type == 'javascript'){
				throw new Exception("validate error: type '{$this->submit_type}' can not validate on client side");
			}
			
			$this->list = '';
			$go = true;
			foreach($this->elements as $elements){
				foreach($elements as $element){
					if(!$element->validateMe()){
						$go = false;
						if($this->submit_type == 'ajax'){
							$this->list .= "<load id='error_{$this->id}_$element->id'>$element->error</load>";
						}
					}
					else{
						if($this->submit_type == 'ajax'){
							$this->list .= "<load id='error_{$this->id}_$element->id'></load>";
						}
					}
				}
			}
			
			if(!$go){
				if($this->submit_type == 'ajax'){
					echo $this->list;
				}
				elseif($this->submit_type == 'post'){
					return $this->get_html();
				}
				else{
					throw new Exception("validate error: type '{$this->submit_type}' Is not supported for validation!");
				}
				exit;
			}
			else{
				// to delete old error messages
				echo $this->list;
			}
			
		}
		
		function get_html(){
			
			//return print_r($this->elements, true);
			
			$html = '';
			
			//loop trough headers and add form part inside subset fieldsets
			$form = '';
			$a = 0;
			foreach($this->headers as $key => $title){
				
				//make form elements
				$elements = '';
				foreach($this->elements[$key] as $element){
					$elements .= $element->get_html();
				}
				
				$desc = '';
				if($title[1]){
					$desc = "<p><sup>$title[1]</sup></p>";
				}
				
				$legend = '';
				if($title[0]){
					$legend = "<legend class='form_legend'>$title[0]</legend>";
				}
				
				$form .= "
					<fieldset class='form_fieldset'>
						$legend
						$desc
						<table>
							$elements
						</table>
					</fieldset>
				";
				$a++;
			}
			
			$this->header->form = ($a > 1 ? $form : "<table>$elements</table>");
			
			//Use header to make base form and submit
			$submit = $this->header->get_html();
			
			if($a == 1){
				$html = "
						<div id='form_{$this->id}_div' class='generated_form'>
							<fieldset class=''>
								<legend class='form_legend'>$this->name</legend>
								<p>$this->description <br>Velden met <span style='color:red;'>*</span> zijn verplicht</p>
								$submit
							</fieldset>
						</div>
				";
			}
			else{
				$html = "
						<div id='form_{$this->id}_div' class='generated_form'>
							<h2>$this->name</h2>
							<p>$this->description <br>Velden met <span style='color:red;'>*</span> zijn verplicht</p>
							$submit
						</div>
				";
			}	
				
			return $html;
			
		}
		
		function add_header($id, $title, $desc){
			$this->headers[$id] = array($title, $desc);
		}
		
		function add_element($key = null, $id = null, $name = null, $type = null, $req = false, $description = '', $options, $order = 0){
			
			$class = 'form'.ucfirst($type).'Element';
			if(class_exists($class)){
				$class = new $class($this->id, $id, $name, $req, $description);
				if($options){
					$class->options = $options;
				}
			}
			else{
				throw new Exception("Element error: type '$class' does not exist");
			}
			
			if(!isset($this->elements[$key])){
				$this->elements[$key] = array();
			}
			
			if(!isset($this->elements[$key][$order])){
				$this->elements[$key][$order] = $class;
			}
			else{
				$ob[$order] = $class;
				array_splice($this->elements[$key], $order, 0, $ob );
			}					
		}
	}
	
#--------------------- Default Form Submits --------------------------------------------------------------------------------------------------------------
/*

	All the form submit methods. Write you own if these are no good for you.

*/	
	
	class microBoatFormSubmit{
		
		public $id = 0;
		public $action = '';
		public $level = '';
		public $param = '';
		public $form = '';
		public $btn_name = 'opslaan';
		
		function __construct($id, $action, $level, $param){
			$this->id = $id;
			$this->action = $action;
			$this->level = $level;
			$this->param = $param;
		}
		
	}
	
	class formAjaxSubmit extends microBoatFormSubmit{
		
		function get_html(){
			return "
				<form id='form_{$this->id}'>
					$this->form
					<input type='button' value='$this->btn_name' class='btn' action='$this->action' level='$this->level' param='$this->param' form='form_$this->id' />
				</form>
			";
		}
	}
	
	class formPostSubmit extends microBoatFormSubmit{
		
		function get_html(){
			return "
				<form action='$this->action' method='post' id='form_{$this->id}'>
					$this->form
					<input sype='submit' value='$this->btn_name' />
				</form>
			";
		}
	}
	
	class formJavascriptSubmit extends microBoatFormSubmit{
		
		#- nog niet af
		function get_html(){
			return "
				
			";
		}
	}
	
	
#--------------------- Default Form Elements --------------------------------------------------------------------------------------------------------------
/*

	Here are all the default form objects declared you get with php form class. Each form object can be used as a form element. It can validate itself 
	and create its html representation. 
	To add more form elements simply extend the root object formElement and take a name like formObjectElement (with the capitals). you must start a 
	name with 'form' and then take a name for your form element/object like Adres then behind that add Element. In case of the example that would than 
	be formAdresElement.
	you have to create a get_html() method for each new form element you write. 

*/	
	
	class formElement{
		
		public $id = 0;
		public $name = '';
		public $required = 0;
		public $description = '';
		protected $value = '';
		protected $reqclass = '';
		protected $star = '';
		public $formid = '';
		public $error = '';
		
		function __construct($formid, $id, $name, $required, $description){
			$this->id = $id;
			$this->name = $name;
			$this->required = $required;
			$this->description = $description;
			$this->formid = $formid;
			
			if($this->get_value()){
				$this->value = 'value="'.$this->get_value().'"';
			}
			if($this->required){
				$this->reqclass = ' required';
				$this->star = '<span class="required_star">*</span>';
			}
		}
		
		function validateMe(){
			
			if($this->required){
				if(!$this->get_value()){
					$this->error = 'Vul '.$this->name.' in';
					return false;	
				}
			}
			return true;
			
		}
		
		function get_value(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id])){
				return $_REQUEST["form_{$this->formid}"][$this->id];
			}
			else{
				return false;
			}
		}
		
		function set_value($value){
			$_REQUEST["form_{$this->formid}"][$this->id] = $value;
			$this->value = 'value="'.$value.'"';
		}
		
	}
	
	class formOptionsElement extends formElement{
		
		public $options;
		
	}
	
	class formSelectElement extends formOptionsElement{
		
		private $opt_html = '';
		
		function get_html(){
			
			$this->opt_html .= "<option value='' ></option>";
			foreach($this->options as $key => $option){
				$selected = ($this->get_value_key() ==  $key ? ' selected' : '' );
				$this->opt_html .= "<option value='$key'$selected >$option[0]</option>";
			}
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><select id='form_{$this->formid}_$this->id' title='$this->description' class='form_{$this->formid}$this->reqclass' $this->reqclass name='form_{$this->formid}[$this->id]' >$this->opt_html</select></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function get_value(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id])){
				if(isset($this->options[$_REQUEST["form_{$this->formid}"][$this->id]][1])){
					return $this->options[$_REQUEST["form_{$this->formid}"][$this->id]][1];
				}
				else{
					return false;
				}
			}
			else{
				return false;
			}
		}	
			
		function get_value_key(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id])){
				return $_REQUEST["form_{$this->formid}"][$this->id];
			}
			else{
				return false;
			}
		}
		
		function validateMe(){
			
			if($this->required){
				if(!$this->get_value()){
					$this->error = 'Selecteer één van de opties';
					return false;	
				}
			}
			return true;
			
		}
		
	}
	
	class formMultipleElement extends formOptionsElement{
		
		private $opt_html = '';
		
		function get_html(){
			
			$array = $this->get_value();
			foreach($this->options as $key => $option){
				$selected = (isset($array[$key]) ? ' selected' : '' );
				$this->opt_html .= "<option value='$key'$selected >$option[0]</option>";
			}
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><select id='form_{$this->formid}_$this->id' title='$this->description' class='form_{$this->formid}$this->reqclass' $this->reqclass name='form_{$this->formid}[$this->id][]' multiple >$this->opt_html</select></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			
			if($this->required){
				if(!$this->get_value()){
					$this->error = 'Kies op zijn minst één optie';
					return false;	
				}
			}
			return true;
			
		}
		
		function get_value(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id])){
				$array = array();
				foreach($_REQUEST["form_{$this->formid}"][$this->id] as $key){
					$array[$key] = $this->options[$key][1];
				}
				return $array;
			}
			else{
				return false;
			}
		}
		
	}
	
	class formCheckElement extends formOptionsElement{
		
		private $opt_html = '';
		
		function get_html(){
			
			$array = $this->get_value();
			foreach($this->options as $key => $option){
				$selected = (isset($array[$key]) ==  $key ? ' checked' : '' );
				$this->opt_html .= "<li><input type='checkbox'$selected id='form_{$this->formid}_chek_$key' name='form_{$this->formid}[$this->id][]' title='$this->description' class='form_{$this->formid}$this->reqclass' $this->reqclass  value='$key'> <label for='form_{$this->formid}_chek_$key' >$option[0]</label></li>";
			}
			
			return "
				<tr>
					<td><label title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>				
				<tr>
					<td colspan='3'><ul style='list-style:none;'>$this->opt_html</ul></td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			
			if($this->required){
				if(!$this->get_value()){
					$this->error = 'Selecteer één van de opties';
					return false;	
				}
			}
			return true;
			
		}
		
		function get_value(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id])){
				$array = array();
				foreach($_REQUEST["form_{$this->formid}"][$this->id] as $key){
					$array[$key] = $this->options[$key][1];
				}
				return $array;
			}
			else{
				return false;
			}
		}
		
	}
	
	class formRadioElement extends formOptionsElement{
		
		private $opt_html = '';
		
		function get_html(){
			
			foreach($this->options as $key => $option){
				$selected = '';
				// good solution to chek for booleans and seperate numbers form booleans
				if(!is_bool($this->get_value())){
					$selected = ($this->get_value() ==  $option[1] ? ' checked' : '' );
				}
				$this->opt_html .= "<li><input type='radio'$selected id='form_{$this->formid}_opt_$key' name='form_{$this->formid}[$this->id]' title='$this->description' class='form_{$this->formid}$this->reqclass' $this->reqclass  value='$option[1]'> <label for='form_{$this->formid}_opt_$key' >$option[0]</label></li>";
			}
			
			return "
				<tr>
					<td><label title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>				
				<tr>
					<td colspan='3'><ul style='list-style:none;'>$this->opt_html</ul></td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function get_value(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id])){
				return $_REQUEST["form_{$this->formid}"][$this->id];
			}
			else{
				return false;
			}
		}
		
		function validateMe(){
			
			if($this->required){
				if(!$this->get_value()){
					$this->error = 'Selecteer één van de opties';
					return false;	
				}
			}
			return true;
			
		}
		
	}
	
	//--
	
	class formTextElement extends formElement{
		
		function get_html(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><input id='form_{$this->formid}_$this->id' title='$this->description' type='text' class='form_{$this->formid}$this->reqclass' $this->reqclass name='form_{$this->formid}[$this->id]'$this->value /></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
	}
	
	class formNameElement extends formElement{
		
		function get_html(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><input id='form_{$this->formid}_$this->id' title='$this->description' type='text' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='35' placeholder='Kees' name='form_{$this->formid}[$this->id]'$this->value /></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			if(!parent::validateMe()){
				return false;	
			}
			
			return true;
		}
		
	}
	
	class formANameElement extends formElement{
		
		function get_html(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><input id='form_{$this->formid}_$this->id' title='$this->description' type='text' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='35' placeholder='van Buren' name='form_{$this->formid}[$this->id]'$this->value /></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			if(!parent::validateMe()){
				return false;	
			}
			
			return true;
		}
		
	}	
	
	class formPostcodeElement extends formElement{
		
		function get_html(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><input id='form_{$this->formid}_$this->id' title='$this->description' type='text' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='6' placeholder='8322RD' name='form_{$this->formid}[$this->id]'$this->value /></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			if(!parent::validateMe()){
				return false;	
			}
			
			if(!preg_match("#^[0-9]{4}\s?[a-z]{2}$#i", $this->get_value())){
				$this->error = 'Dit is geen postcode';
				return false;	
			}
			
			return true;
		}
		
	}	
	
	class formAdresElement extends formElement{
		
		function get_html(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><input id='form_{$this->formid}_$this->id' title='$this->description' type='text' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='6' placeholder='8322RD' name='form_{$this->formid}[$this->id]'$this->value /></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			if(!parent::validateMe()){
				return false;	
			}
			
			if(!preg_match("/^([a-z ]{3,}) ([0-9]{1,})([a-z]*)$/i", $this->get_value())){
				$this->error = 'Dit is geen adres';
				return false;	
			}
			
			return true;
		}
		
	}
	
	class formEmailElement extends formElement{
		
		function get_html(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><input id='form_{$this->formid}_$this->id' title='$this->description' type='email' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='100' placeholder='kees@live.nl' name='form_{$this->formid}[$this->id]'$this->value /></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			if(!parent::validateMe()){
				return false;	
			}
			
			if(!filter_var($this->get_value(), FILTER_VALIDATE_EMAIL)){
				$this->error = 'Onjuist email adres';
				return false;	
			}
			
			return true;
		}
		
	}
	
	class formPassElement extends formElement{
		
		function get_html(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><input id='form_{$this->formid}_$this->id' title='$this->description' type='password' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='30' placeholder='*****' name='form_{$this->formid}[$this->id]'$this->value /></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			if(!parent::validateMe()){
				return false;	
			}
			
			return true;
		}
		
	}
	
	class formPassrepeatElement extends formElement{
		
		function get_html(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_{$this->id}_1' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><input id='form_{$this->formid}_{$this->id}_1' title='$this->description' type='password' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='30' placeholder='*****' name='form_{$this->formid}[{$this->id}_1]'$this->value /></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
				<tr>
					<td><label for='form_{$this->formid}_{$this->id}_2' title='$this->description' >{$this->star}Herhaal {$this->name}:</label></td>
					<td><input id='form_{$this->formid}_{$this->id}_2' title='$this->description' type='password' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='30' placeholder='*****' name='form_{$this->formid}[{$this->id}_2]'$this->value /></td>
					<td></td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function get_value(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id.'_1'])){
				return $_REQUEST["form_{$this->formid}"][$this->id.'_1'];
			}
			else{
				return false;
			}
		}
		
		function validateMe(){
			
			
			if(!$this->get_value()){
				$this->error = 'Vul '.$this->name.' in';
				return false;	
			}
			elseif(!$_REQUEST["form_{$this->formid}"][$this->id.'_2']){
				$this->error = 'Herhaal '.$this->name;
				return false;	
			}
			elseif($this->get_value() != $_REQUEST["form_{$this->formid}"][$this->id.'_2']){
				$this->error = $this->name . ' komt niet overeen';
				return false;
			}
			
			return true;
		}
		
	}
	
	class formTextboxElement extends formElement{
		
		function get_html(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td colspan='2' id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
				<tr>
					<td></td>
					<td colspan='2' ><textarea id='form_{$this->formid}_$this->id' title='$this->description' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='500' placeholder='' name='form_{$this->formid}[$this->id]' >$this->value</textarea></td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			if(!parent::validateMe()){
				return false;	
			}
			
			return true;
		}
		
	}
	
	class formRatingElement extends formElement{
		
		function get_html(){
			//<td colspan='2' ><textarea id='form_{$this->formid}_$this->id' title='$this->description' class='form_{$this->formid}$this->reqclass' $this->reqclass maxlength='500' placeholder='' name='form_{$this->formid}[$this->id]' >$this->value</textarea></td>
			$rating = make_rating(0);
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td>$rating</td>
					<td colspan='2' id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		function validateMe(){
			if(!parent::validateMe()){
				return false;	
			}
			
			return true;
		}
		
	}
	
?>