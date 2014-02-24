<?php

	include('./microBoatForm.class.php');
	
	$form = new microBoatForm();
	
	$form->setID('myFrom'); // The form id wil be the html id like id='myForm'
	$form->name = 'My form';
	$form->description = 'This is an single form';
	$form->submitType = 'post'; // post default
	$form->action = './index.php'; //The action url or in case of submitmode ajax you requirce the microBoatWebapp.js
	$form->param = ''; // a parameter for the form handler. You can also just add a incisable form element
	$form->multiple = true; // turn on multi form
	
	$form->addSub('test', 'test', 'blabla');
	
	$formpart = array('id' => 'iets1', 'type' => 'number', 'name' => 'your age', 'required' => false, 'disabled' => false, 'classname' => 'test');
	$form->addPart($formpart);
	$formpart = array('id' => 'iets2', 'type' => 'number', 'name' => 'your age', 'required' => false, 'disabled' => false, 'classname' => 'test');
	$form->addPart($formpart);
	$formpart = array('id' => 'iets3', 'type' => 'radiobox', 'options' => array('hallo', 'test', 'yes'), 'name' => 'your age', 'required' => false, 'disabled' => false, 'classname' => 'test');
	$form->addPart($formpart);
	
	$form->addSub('names', 'test', 'blabla');
	$formpart = array('id' => 'name', 'type' => 'text', 'name' => 'your name', 'required' => true, 'disabled' => false, 'classname' => 'names', 'max' => 30, 'min' => 3);
	$form->addPart($formpart);
	$formpart = array('id' => 'age', 'type' => 'number', 'name' => 'deze!', 'required' => false, 'disabled' => false, 'classname' => 'names');
	$form->addPart($formpart);
	$formpart = array('id' => 'iets', 'type' => 'number', 'name' => 'your age', 'required' => true, 'disabled' => false, 'classname' => 'names');
	$form->addPart($formpart);
	
	if($form->isSend()){
		echo "yesyesyes <br>";
	}
	
	echo $form->getHTML();
	
	/*
	echo '<pre>';
	print_r($form->formParts);
	print_r($form->formSubs);
	echo '</pre>';
	*/
?>