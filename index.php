<?php

	include('./microBoatForm.class.php');
	
	$form = new microBoatForm();
	
	$form->setID('myFrom'); // The form id wil be the html id like id='myForm'
	$form->name = 'My form';
	$form->description = 'This is an multiplex form';
	$form->submitType = 'post'; // post default
	$form->action = './index.php'; //The action url or in case of submitmode ajax you requirce the microBoatWebapp.js
	$form->param = ''; // a parameter for the form handler. You can also just add a incisable form element
	$form->multiple = true; // turn on multi form
	
	$form->addSub('names', 'name', 'description');
	$form->addSub('other', 'other stuff', 'description');
	
	$formpart = array('id' => 'name', 'type' => 'text', 'name' => 'your name', 'required' => true, 'disabled' => false, 'classname' => 'names', 'max' => 30, 'min' => 3);
	$form->addPart($formpart);
	
	$formpart = array('id' => 'age', 'type' => 'number', 'name' => 'your age', 'required' => false, 'disabled' => false, 'classname' => 'names');
	$form->addPart($formpart);
	
	$formpart = array('id' => 'iets', 'type' => 'number', 'name' => 'your age', 'required' => false, 'disabled' => false, 'classname' => 'names');
	$form->addPart($formpart);
	
	$formpart = array('id' => 'iets1', 'type' => 'number', 'name' => 'your age', 'required' => false, 'disabled' => false, 'classname' => 'names');
	$form->addPart($formpart);
	$formpart = array('id' => 'iets2', 'type' => 'number', 'name' => 'your age', 'required' => false, 'disabled' => false, 'classname' => 'names');
	$form->addPart($formpart);
	$formpart = array('id' => 'iets3', 'type' => 'number', 'name' => 'your age', 'required' => false, 'disabled' => false, 'classname' => 'names');
	$form->addPart($formpart);
	
	$form->reOrder('age', 3);
	
	echo '<pre>';
	print_r($form->formParts);
	print_r($form->formSubs);
	echo '</pre>';

?>