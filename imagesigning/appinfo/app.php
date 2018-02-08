<?php
	\OCP\Util::addStyle('imagesigning', 'imagesigning');
    	$eventDispatcher = \OC::$server->getEventDispatcher();
    	$eventDispatcher->addListener(
        'OCA\Files::loadAdditionalScripts',
        function() { \OCP\Util::addScript('imagesigning', 'additionalScripts'); }
    );
?>

