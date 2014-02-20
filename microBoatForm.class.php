<?php

	/*
		~microBoatForm.class 0.0.2
		
		Description,
		
			The microBoatForm class enables a PHP programmer to easily create a advanced 
			HTML form in a really short time. It can also validate the forms and has the 
			ability to save the form template in json.
	*/

	class microBoatForm{
		
		public $id = 'microBoatForm';
		public $name = 'microBoatForm.class';
		public $description = 'A form made by the microBoatForm.class';
		public $submitType = 'post';
		public $action = '';
		public $param = '';
		public $buttonName = '';
		public $multiple = false;
		
		function __construct(){
			$this->action = $_SERVER['PHP_SELF'];
		}
		
		function loadFromFile($url){
			$file = file_get_contents($url);
			$file = json_decode($file, true);
			$this->loadFromArray($file);
		}
		
		function loadFromArray($stack){
			//laden form - array normalisatie maken
		}
		
		function addSub(){
			
		}
		
		function addPart(){
			
		}
		
		function getHtml(){
			
		}
		
		function validate(){
			
		}
		
	}
	
	#--------------------- Default Form Submits --------------------------------------------------------------------------------------------------------------
	
	//Base
	class microBoatFormSubmit{
		
		public $id = '';
		public $name = '';
		public $description = '';
		public $action = '';
		public $param = '';
		public $multiple = '';
		public $form = '';
		public $buttonName = '';
		private $html = '';
		
		function __construct($id, $name, $description, $action, $param, $multiple, $form, $buttonName){
			$this->id = $id;
			$this->name = $name;
			$this->description = $description;
			$this->action = $action;
			$this->param = $param;
			$this->multiple = $multiple;
			$this->form = $form;
			$this->buttonName = $buttonName;
		}
		
		function getHTML(){
			return "
				<form action='$this->action' method='post' id='$this->id'>
					$this->form
					<input sype='submit' value='$this->buttonName' />
				</form>
			";
		}
	}
	
	//Submits
	class mbfs_post extends microBoatFormSubmit{
		function getHTML(){
			return "
				<form action='$this->action' method='post' id='$this->id'>
					$this->form
					<input sype='submit' value='$this->buttonName' />
				</form>
			";
		}	
	}
	
	class mbfs_ajax extends microBoatFormSubmit{
		function getHTML(){
			return "
				<form id='$this->id'>
					$this->form
					<input sype='button' value='$this->buttonName' />
					<script>
						var elem = document.getElementById($this->id).elements;
						var params = '';
						url = '$this->action';
						for(var i = 0; i < elem.length; i++){
							if (elem[i].tagName == 'SELECT'){
								params += elem[i].name + '=' + encodeURIComponent(elem[i].options[elem[i].selectedIndex].value) + '&';
							}else{
								params += elem[i].name + '=' + encodeURIComponent(elem[i].value) + '&';
							}
						} 
						if (window.XMLHttpRequest){
							xmlhttp=new XMLHttpRequest();
						}else{
							xmlhttp=new ActiveXObject('Microsoft.XMLHTTP');
						}
						xmlhttp.open('POST',url,false);
						xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
						xmlhttp.setRequestHeader('Content-length', params.length);
						xmlhttp.setRequestHeader('Connection', 'close');
						xmlhttp.send(params);
						if(typeof return_$this->id == 'function')
						{
							return_$this->id(xmlhttp.responseText;);
						}
						else{
							alert('return_$this->id is not defined!');
						}					
					</script>
				</form>
			";
		}	
	}
	
	#Using this submit type requires the microBoat webapp.js
	class mbfs_webapp extends microBoatFormSubmit{
		function getHTML(){
			return "
				<form id='$this->id'>
					$this->form
					<input sype='submit' value='$this->buttonName' action='$this->action' form='this' />
				</form>
			";
		}	
	}
	
	#--------------------- Default Form Elements --------------------------------------------------------------------------------------------------------------
	
	//Base
	class microBoatFormElement{
		protected $id = 0;
		public $formid = '';
		public $name = '';
		public $description = '';
		public $disabled = '';
		public $placeholder = '';
		public $min = '';
		public $max = '';
		public $param = '';
		protected $value = '';
		protected $required = false;
		protected $reqclass = '';
		protected $star = '';
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
			$this->setRequired($required);
		}
		
		function validateMe(){
			if($this->required){
				if(!$this->get_value()){
					$this->error = 'Vul '.$this->name.' in!';
					return false;
				}
			}
			return true;
		}
		
		function setRequired($bool){
			if($bool){
				$this->required = true;
				$this->reqclass = ' required';
				$this->star = '<span class="required_star">*</span>';
			}
			else{
				$this->required = false;
				$this->reqclass = '';
				$this->star = '';
			}
		}
		
		function getValue(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id])){
				return $_REQUEST["form_{$this->formid}"][$this->id];
			}
			else{
				return false;
			}
		}
		
		function setValue($value){
			$_REQUEST["form_{$this->formid}"][$this->id] = $value;
			$this->value = 'value="'.$value.'"';
		}
	}
	
	//Multipecoise base
	class microBoatFormOptionsElement extends formElement{
		
		public $options;
		protected $opt_html = '';
		
	}
	
	//Multipecoise elements
	class mbfe_selectbox extends microBoatFormOptionsElement{
		
		function getHtml(){
			
			$this->opt_html .= "<option value='' ></option>";
			foreach($this->options as $key => $option){
				$selected = ($this->getValue_key() ==  $key ? ' selected' : '' );
				$this->opt_html .= "<option value='$key'$selected >$option[0]</option>";
			}
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><select id='form_{$this->formid}_$this->id' title='$this->description' class='form_{$this->formid}$this->reqclass' $this->reqclass name='form_{$this->formid}[$this->id]' >$this->opt_html</select></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$this->error = '';
			
		}
		
		function getValue(){
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
			
		function getValue_key(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id])){
				return $_REQUEST["form_{$this->formid}"][$this->id];
			}
			else{
				return false;
			}
		}
		
		function validateMe(){
			
			if($this->required){
				if(!$this->getValue()){
					$this->error = 'Selecteer één van de opties';
					return false;	
				}
			}
			return true;
			
		}
		
	}
	
	class mbfe_multiple extends microBoatFormOptionsElement{
		
		function getHtml(){
			
			$array = $this->getValue();
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
			
			$this->error = '';
			
		}
		
		function validateMe(){
			
			if($this->required){
				if(!$this->getValue()){
					$this->error = 'Kies op zijn minst één optie';
					return false;	
				}
			}
			return true;
			
		}
		
		function getValue(){
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
	
	class mbfe_checkbox extends microBoatFormOptionsElement{
		
		function getHtml(){
			
			$array = $this->getValue();
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
			
			$this->error = '';
			
		}
		
		function validateMe(){
			
			if($this->required){
				if(!$this->getValue()){
					$this->error = 'Selecteer één van de opties';
					return false;	
				}
			}
			return true;
			
		}
		
		function getValue(){
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
	
	class mbfe_radiobox extends microBoatFormOptionsElement{
		
		function getHtml(){
			
			foreach($this->options as $key => $option){
				$selected = '';
				// good solution to chek for booleans and seperate numbers form booleans
				if(!is_bool($this->getValue())){
					$selected = ($this->getValue() == $option[1] ? ' checked' : '' );
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
			
			$this->error = '';
			
		}
		
		function getValue(){
			if(isset($_REQUEST["form_{$this->formid}"][$this->id])){
				return $_REQUEST["form_{$this->formid}"][$this->id];
			}
			else{
				return false;
			}
		}
		
		function validateMe(){
			
			if($this->required){
				if(!$this->getValue()){
					$this->error = 'Selecteer één van de opties';
					return false;	
				}
			}
			return true;
			
		}
		
	}
	
	//Elements
	class mbfe_text extends microBoatFormElement{
		
		function getHtml(){
			
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
	
	class mbfe_number extends microBoatFormElement{
		
		function getHtml(){
			
			return "
				<tr>
					<td><label for='form_{$this->formid}_$this->id' title='$this->description' >{$this->star}{$this->name}:</label></td>
					<td><input id='form_{$this->formid}_$this->id' title='$this->description' type='number' class='form_{$this->formid}$this->reqclass' $this->reqclass name='form_{$this->formid}[$this->id]'$this->value /></td>
					<td id='error_{$this->formid}_$this->id' class='error' >$this->error</td>
				<tr>
			";
			
			$element->error = '';
			
		}
		
		//todo chekc for int
		
	}
	
	#dutch zipcode
	class mbfe_postcode extends microBoatFormElement{
		
		function getHtml(){
			
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
	
	class mbfe_adres extends microBoatFormElement{
		
		function getHtml(){
			
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
	
	class mbfe_email extends microBoatFormElement{
		
		function getHtml(){
			
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
	
	class mbfe_password extends microBoatFormElement{
		
		function getHtml(){
			
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
	
	class mbfe_repeatpassword extends microBoatFormElement{
		
		function getHtml(){
			
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
	
	class mbfe_textbox extends microBoatFormElement{
		
		function getHtml(){
			
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
	
?>