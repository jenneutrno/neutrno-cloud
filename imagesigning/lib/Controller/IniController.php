<?php
namespace OCA\ImageSigning\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCA\ImageSigning\lib\php\settings;
use OCA\ImageSigning\lib\php\utils;


class IniController extends Controller {
	private $userId;
	protected $request;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->request = $request;
	}

    /**
     * Return context to client
     *
     * @NoAdminRequired
     *
     */
    public function getContext() {
        settings::init();
        return new DataResponse(settings::getAll());
    }

    /**
     * Return context to client
     *
     * @NoAdminRequired
     *
     */
    public function updateContext() {
        $params = $this->request->getParams();

        utils::refreshIniSigners ($params['signers']);

        utils::setActiveIniSigner ($params);

        return new DataResponse(["res" => "success",]);
    }

}
