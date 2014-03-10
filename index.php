<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<?php
	
	echo '<link rel="stylesheet" href="./styles/basic.css" type="text/css">';
	
	include('./microBoatForm.class.php');
	
	$form = new microBoatForm();
	
	$form->loadFromFile('./forms/myform.json');
	
	if($form->isSend()){
		if($form->validate()){
			$test = print_r($form->getData(), true);
			echo "Verzonden en gevalideert <br><pre>$test</pre><br>";
		}
	}
	
	echo $form->getHTML();
	
	/*
	echo '<pre>';
	print_r($form->formParts);
	print_r($form->formSubs);
	echo '</pre>';
	*/
?>