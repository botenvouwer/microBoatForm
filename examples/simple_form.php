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
	
	$formpart = array(
		'id' => 'name',
		'type' => 'text',
		'name' => 'your name',
		'description' => '',
		'required' => true,
		'value' => '',
		'disabled' => false,
		'placeholder' => '',
		'min' => '',
		'max' => '',
		'param' => '',
		'options' => '',
		'classname' => 'names',
		'order' => 1
	);
	$form->addPart($formpart);
	
	if($form->isSend()){
		$gonogo = $form->validate();
		if($gonogo){
			
			echo '<pre>';
			print_r($_REQUEST[$form->id]);
			echo '</pre>';
			exit;
		}
	}
	
	$html = $form->getHtml();
	echo $html;