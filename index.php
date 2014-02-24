<?php

	include('./microBoatForm.class.php');
	
	$form = new microBoatForm();
	
	$form->loadFromFile('./forms/myform.json');
	
	if($form->isSend()){
		$test = print_r($form->getData(), true);
		echo "Verzonden <br><pre>$test</pre><br>";
	}
	
	echo $form->saveFile('./forms/myform.mbf.json');
	echo $form->getHTML();
	
	/*
	echo '<pre>';
	print_r($form->formParts);
	print_r($form->formSubs);
	echo '</pre>';
	*/
?>