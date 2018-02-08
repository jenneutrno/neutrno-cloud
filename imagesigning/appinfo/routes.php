<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\ImageSigning\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
	        ['name' => 'ini#getContext', 'url' => '/getcontext', 'verb' => 'GET'],
        	['name' => 'ini#updateContext', 'url' => '/updatecontext', 'verb' => 'POST'],
        	['name' => 'crypt#cryptFile', 'url' => '/crypt/cryptfile', 'verb' => 'POST'],
        	['name' => 'crypt#cryptAbort', 'url' => '/crypt/cryptabort', 'verb' => 'POST'],
    ]
];
