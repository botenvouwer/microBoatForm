<?php

	//simple
	$array = array(
		'header' => array(
			'id' => 'myFrom',
			'name' => 'My form',
			'description' => 'This is an simple form',
			'submitType' => 'post',
			'action' => './index.php',
			'param' => '',
			'multiple' => false,
		),
		'form' => array(
			array(
				'id' => 'name',
				'type' => 'text',
				'name' => 'Name:',
				'description' => 'Your firstname',
				'required' => true,
				'value' => '',
				'disabled' => false,
				'placeholder' => 'karel',
				'min' => 3,
				'max' => 20,
				'param' => ''
			),
			array(
				'id' => 'sirname',
				'type' => 'text',
				'name' => 'Name:',
				'description' => 'Your sirname',
				'required' => true,
				'value' => '',
				'disabled' => false,
				'placeholder' => 'appel',
				'min' => 3,
				'max' => 20,
				'param' => ''
			),
			array(
				'id' => 'age',
				'type' => 'number',
				'name' => 'Age:',
				'description' => 'Your age in years',
				'required' => false,
				'value' => '',
				'disabled' => false,
				'placeholder' => 'karel',
				'min' => 3,
				'max' => 20,
				'param' => ''
			),
			array(
				'id' => 'mbtype',
				'type' => 'option',
				'name' => 'Member type:',
				'description' => 'Your age in years',
				//'required' => false, //can not have required
				'value' => '1', // Trooper
				'disabled' => false,
				//'placeholder' => '', // Can not have placeholder
				//'min' => 3, // Can not have min
				//'max' => 20, // Can not have max
				'param' => '',
				'options' => array('Darth lord','Stormtrooper','Imperial Pilot')
			)
		)
	);
	
	//multiplex
	$array = array(
		'header' => array(
			'id' => 'myFrom',
			'name' => 'My form',
			'description' => 'This is an simple form',
			'submitType' => 'post',
			'action' => './index.php',
			'param' => '',
			'multiple' => false,
		),
		'form' => array(
			array(
				'header' => array(
					'classname' => 'names',
					'name' => 'Fist part',
					'description' => 'Fill in your names'
				),
				'form' => array(
					array(
						'id' => 'name',
						'type' => 'text',
						'name' => 'Name:',
						'description' => 'Your firstname',
						'required' => true,
						'value' => '',
						'disabled' => false,
						'placeholder' => 'karel',
						'min' => 3,
						'max' => 20,
						'param' => ''
					),
					array(
						'id' => 'sirname',
						'type' => 'text',
						'name' => 'Name:',
						'description' => 'Your sirname',
						'required' => true,
						'value' => '',
						'disabled' => false,
						'placeholder' => 'appel',
						'min' => 3,
						'max' => 20,
						'param' => ''
					)
				)
			),
			array(
				'header' => array(
					'classname' => 'other',
					'name' => 'Second part',
					'description' => 'Fill in other stuf'
				),
				'form' => array(
					array(
						'id' => 'age',
						'type' => 'number',
						'name' => 'Age:',
						'description' => 'Your age in years',
						'required' => false,
						'value' => '',
						'disabled' => false,
						'placeholder' => 'karel',
						'min' => 3,
						'max' => 20,
						'param' => ''
					),
					array(
						'id' => 'mbtype',
						'type' => 'option',
						'name' => 'Member type:',
						'description' => 'Your age in years',
						//'required' => false, //can not have required
						'value' => '1', // Trooper
						'disabled' => true,
						//'placeholder' => '', // Can not have placeholder
						//'min' => 3, // Can not have min
						//'max' => 20, // Can not have max
						'param' => '',
						'options' => array('Darth lord','Stormtrooper','Imperial Pilot')
					)
				)
			)
		)
	);

?>