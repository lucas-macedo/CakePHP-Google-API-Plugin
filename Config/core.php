<?php

/**
 * Google Api Plugin
 *
 *
 * This is core configuration file.
 * Use it to configure core behavior of Google Api plugin.
 *
 *
 * @author Lucas Macedo
 * @package Google Api
 * @license http://opensource.org/licenses/GPL-3.0 GPL V3 License
 */


$config = array('GoogleApi'=>array(

	// Dados do Cliente Google Console
	'client' => array(
		'ApplicationName' => 'ApplicationName',
		'client_id' => 'xxxxxxxxxxxxxxxxxx.apps.googleusercontent.com',
		'email_address' => 'xxxxxxxxxxxxxxx@developer.gserviceaccount.com',
		'key_file_location' => APP . 'Plugin' . DS . 'GoogleApi' . DS . 'messes.p12',
	),
));