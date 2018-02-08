<?php

namespace OCA\ImageSigning\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	public function getForm() {
		return new TemplateResponse('imagesigning', 'admin');
	}

	public function getSection() {
		return 'security';
	}

	public function getPriority() {
		return 22;
	}
}
