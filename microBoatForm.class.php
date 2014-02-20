<?php

	/*
		~microBoatForm.class 0.0.3
		
		Description,
		
			The microBoatForm class enables a PHP programmer to easily create a advanced 
			HTML form in a really short time. It can also validate the forms and has the 
			ability to save the form template in json.
	*/

	class microBoatForm{
		
		private $id = 'microBoatForm';
		public $name = 'microBoatForm.class';
		public $description = 'A form made by the microBoatForm.class';
		public $submitType = 'post';
		public $action = '';
		public $param = '';
		public $buttonName = '';
		public $multiple = false;
		protected $order = 0;
		
		public $formParts = null;
		public $formSubs = null;
		
		protected $errors = array();
		public $debugMode = true;
		
		function __construct(){
			$this->action = $_SERVER['PHP_SELF'];
			$this->formParts = new microBoatFormParts();
			$this->formSubs = new microBoatFormSubs();
		}
		
		function setID($id){
			if(!$id){
				$this->error('Unable to set id. Please check the datatype.');
			}
			else{
				$this->id = $id;
				foreach($this->formParts as $formPart){
					$formPart->formid = $id;
				}
			}
		}
		
		private function validateSubmitType($submitType){
			$submitType = strtolower($submitType);
			$prefix = 'mbfs_';
			$class = $prefix.$submitType;
			
			if(class_exists($class)){
				if(is_subclass_of($class, 'microBoatFormSubmit')){
					return $class;
				}
				else{
					$this->error("<i>$class</i> does not extend microBoatFormSubmit.", true);
				}
			}
			else{
				$this->error("Submit type: <i>$submitType</i> is not found.", true);
			}
		}
		
		private function validateElementType($elementType){
			$elementType = strtolower($elementType);
			$prefix = 'mbfe_';
			$class = $prefix.$elementType;
			
			if(class_exists($class)){
				if(is_subclass_of($class, 'microBoatFormElement')){
					return $class;
				}
				else{
					$this->error("<i>$class</i> does not extend microBoatFormElement.");
					return false;
				}
			}
			else{
				$this->error("Submit type: <i>$elementType</i> is not found.");
			}
		}
		
		function loadFromFile($url){
			$file = file_get_contents($url);
			$file = json_decode($file, true);
			$this->loadFromArray($file);
		}
		
		function loadFromArray($stack){
			
			if(isset($stack['header']['id'])){
				$this->setID($stack['header']['id']);
			}
			
			$this->name = (isset($stack['header']['name']) ? $stack['header']['name'] : '');
			$this->description = (isset($stack['header']['']) ? $stack['header'][''] : '');
			
			if($stack['header']['submitType']){
				if($this->validateSubmitType()){
					$this->submitType = $stack['header']['submitType'];
				}
			}
			
			$this->action = (isset($stack['header']['action']) ? $stack['header']['action'] : '');
			$this->param = (isset($stack['header']['param']) ? $stack['header']['param'] : '');
			$this->buttonName = (isset($stack['header']['buttonName']) ? $stack['header']['buttonName'] : '');
			
			if(isset($stack['header']['multiple'])){
				$this->multiple = (is_bool($stack['header']['multiple']) ? $stack['header']['multiple'] : false);
			}
			
			if($this->multiple){
				foreach($stack['header']['form'] as $array){
					$this->addSub($array['header']);
					foreach($array['form'] as $subarray){
						$subarray['classname'] = $array['header']['classname'];
						$this->addPart($subarray);
					}
				}
			}
			else{
				foreach($stack['header']['form'] as $array){
					$this->addPart($array);
				}
			}
		}
		
		function addSub($className = 'sub', $name = '', $description = ''){
			$num = func_num_args();
			
			if(!$className){
				$this->error('Specify a className for the sub like: addSub($className [, $name [,  $description]] or addSub($array)).');
			}
			
			if($num == 1){
				if(is_array($className)){
					$stack = $className;
					$className = (isset($stack['className']) ? $stack['className'] : '');
					$name = (isset($stack['name']) ? $stack['name'] : '');
					$description = (isset($stack['description']) ? $stack['description'] : '');
					if(!$className){
						$this->error('Specify a className for the sub like: addSub($className [, $name [,  $description]] or addSub($array)).');
					}
				}
			}
			
			if(!is_string($className)){
				$this->error('className for sub must be DataType String');
			}
			elseif(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $className)){
				$this->error("<i>$className</i> is not a valid class name.");
			}
			else{
				if(isset($this->formSubs->$className)){
					$this->error("Sub <i>$className</i> already exist");
				}
				else{
					$this->formSubs->$className = new microBoatFormSub($name, $description);
				}
			}
		}
		
		function addPart($stack){
			
			if(is_array($stack)){
				
				if(!isset($stack['id'])){
					$this->error('Can not create part becouse: id is not set.');
				}
				else if(!$stack['id']){
					$this->error('Can not create part becouse: id is not set.');
				}
				else if(!is_string($stack['id'])){
					$this->error('Can not create part becouse: id is not a string.');
				}
				else{
					$check = substr($stack['id'], -2);
					$className = ($check == '[]' ? substr_replace($stack['id'], "", -2) : $stack['id']);
					$id = $stack['id'];
					if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $className)){
						$this->error("Can not create part becouse: <i>$className</i> (id) is not a valid class name");
						return;
					}
				}
				
				if(!isset($stack['type'])){
					$type = 'text';
				}
				else if(!$stack['type']){
					$type = 'text';
				}
				else if(!is_string($stack['type'])){
					$type = 'text';
				}
				else{
					if($elementType = $this->validateElementType($stack['type'])){
						$type = $stack['type'];
					}
					else{
						$this->error('Can not create part becouse: type is not valid.');
						return;
					}
				}
				
				$name = (isset($stack['name']) ? $stack['name'] : '');
				$description = (isset($stack['description']) ? $stack['description'] : '');
				$required = (isset($stack['required']) ? $stack['required'] : false);
				$value = (isset($stack['value']) ? $stack['value'] : '');
				$disabled = (isset($stack['disabled']) ? $stack['disabled'] : false);
				$placeholder = (isset($stack['placeholder']) ? $stack['placeholder'] : '');
				$min = (isset($stack['min']) ? $stack['min'] : '');
				$max = (isset($stack['max']) ? $stack['max'] : '');
				$param = (isset($stack['param']) ? $stack['param'] : '');
				$options = (isset($stack['options']) ? $stack['options'] : '');
				$classNameSub = (isset($stack['classname']) ? $stack['classname'] : '');
				
				$reorder = false;
				if(isset($stack['order'])){
					if(is_numeric($stack['order'])){
						$order = $stack['order'];
						
						if(($stack['order'] - 1) == $this->order){
							$order = $this->order;
							$this->order += 1;
						}
						else{
							$order = $this->order;
							$this->order += 1;
							$reorder = true;
						}
					}
					else{
						$order = $this->order;
						$this->order += 1;
					}
				}
				else{
					$order = $this->order;
					$this->order += 1;
				}
				
				if(isset($this->formParts->$className)){
					$this->error("Can not create part becouse: formpart <i>$className</i> already exist.");
					return;
				}
				else{
					$this->formParts->$className = new $elementType($this->id, $id, $type, $name, $description, $required, $value, $disabled, $placeholder, $min, $max, $param, $classNameSub, $options, $order);
				}
				
				if($reorder){
					$this->reOrder($id, $stack['order']);
				}
				
			}
			else{
				$this->error('Can not create part becouse: Fist agument must be datatype array.');
			}
		}
		
		public function reOrder($id = '', $order = 0){
			
			if(!$id){
				$this->error('Need id to re order.');
				return;
			}
			
			if(!is_numeric($order)){
				$order = 0;
			}	
			
			$num = count(get_object_vars($this->formParts)) - 1;
			if($num < $order){
				$order = $num;
			}
			
			foreach($this->formParts as $formPart){
				if($formPart->id == $id){
					$formPart->order = $order;
				}
				else if($formPart->order >= $order){
					$formPart->order += 1;
				}
			}
			
		}
		
		private function prep(){
			
		}
		
		function getHtml(){
			
		}
		
		function parseJson(){
			
		}
		
		function saveFile(){
			
		}
		
		function isSend(){
			
		}
		
		function validate(){
			
		}
		
		private function error($message, $exit = false){
			
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			
			$line = $trace[1]['line'];
			$file = $trace[1]['file'];
			
			$mode = ($exit ? 'Fatal Error' : 'Notice');
			$error = "<b>$mode</b>:  $message in <b>$file</b> on line <b>$line</b><br>";
			$this->errors[] = $error;
			if($this->debugMode){
				echo (count($this->errors == 1) ? '<br>' : ''). $error;
			}
			
			if($exit){
				exit;
			}
		}
		
		function getErrors(){
			return $this->errors;
		}
		
	}
	
	#--------------------- Sub part class --------------------------------------------------------------------------------------------------------------------
	
	class microBoatFormSub{
		
		public $name;
		public $description;
		
		function __construct($name, $description){
			$this->name = (isset($name) ? $name : '');
			$this->description = (isset($description) ? $description : '');
		}
		
	}
	
	class microBoatFormParts{
		
	}
	
	class microBoatFormSubs{
		
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
		public $id = '';
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
		public $order = 0;
		public $error = '';
		public $options;
		
		function __construct($formid ,$id, $type, $name, $description, $required, $value, $disabled, $placeholder, $min, $max, $param, $classNameSub, $options, $order){
			$this->id = $id;
			$this->type = $type;
			$this->name = $name;
			$this->required = $required;
			$this->description = $description;
			$this->formid = $formid;
			$this->disabled = $disabled;
			$this->placeholder = $placeholder;
			$this->min = $min;
			$this->max = $max;
			$this->param = $param;
			$this->childOfSub = $classNameSub;
			$this->options = $options;
			$this->order = $order;
			
			if($value){
				$this->setValue($value);
			}
			
			if($this->getValue()){
				$this->value = 'value="'.$this->getValue().'"';
			}
			$this->setRequired($required);
		}
		
		function validateMe(){
			if($this->required){
				if(!$this->getValue()){
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
	
	//Multipecoise elements
	class mbfe_selectbox extends microBoatFormElement{
		
		public $options;
		protected $opt_html = '';
		
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
	
	class mbfe_multiple extends microBoatFormElement{
		
		public $options;
		protected $opt_html = '';
		
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
	
	class mbfe_checkbox extends microBoatFormElement{
		
		public $options;
		protected $opt_html = '';
		
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
	
	class mbfe_radiobox extends microBoatFormElement{
		
		public $options;
		protected $opt_html = '';
		
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