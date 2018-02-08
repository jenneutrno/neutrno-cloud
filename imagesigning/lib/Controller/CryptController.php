<?php
namespace OCA\ImageSigning\Controller;

use OCP\IRequest;
use OCP\IServerContainer;
use OCP\ILogger;
use OCP\Files\IRootFolder;
use OCP\Lock\ILockingProvider;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCA\ImageSigning\lib\php\crypt;
use OCA\ImageSigning\lib\php\utils;
use OCA\ImageSigning\lib\php\settings;

class CryptController extends Controller
{

    protected $appName;
    protected $request;
    protected $server;
    protected $node;
    protected $logger;
    protected $user;

    public function __construct($AppName, IRequest $request, IServerContainer $server, IRootFolder $node, ILogger $logger)
    {
        parent::__construct($AppName, $request);
        $this->appName = $AppName;
        $this->request = $request;
        $this->server = $server;
        $this->node = $node;
        $this->user = utils::getUserName ($server);
        $this->logger = $logger;
        settings::init();
    }

    /**
     * Abort existing encrypt action
     *
     * @NoAdminRequired
     *
     */
    public function cryptAbort() {
        $fileSource = $this->request->getParams()['fname'];
        $targetDir = $this->request->getParams()['targetdir'];
        $prefix = settings::get('signedFilePrefix');
        $filePath = utils::getRelativeUserFolderPath ($targetDir, $prefix . $fileSource, $this->server);

        $this->node->get($filePath)->delete();

        $this->logme ('info', "User abort action");
        return new DataResponse(["res" => 'success', ]);
    }

    /**
     * Encrypt existing file
     *
     * @NoAdminRequired
     *
     */
    public function cryptFile() {
        $baseDir = $this->request->getParams()['dir'];
        $fileSource = $this->request->getParams()['fname'];
        $targetDir = $this->request->getParams()['targetdir'];
        $prefix = settings::get('signedFilePrefix');

        $this->logme ('info', "Sign request - baseDir: " . $baseDir . " - source file: " . $fileSource . " - target dir: " . $targetDir . " - prefix: " . $prefix);

        $source = utils::getAbsoluteDataPath($baseDir, $fileSource, $this->server);
        $target = utils::getAbsoluteDataPath($targetDir, $prefix . $fileSource, $this->server);

        $this->logme ('info', "SignServer - Start");
        $crypt = new crypt();
        $res = $crypt->cryptFile($source);
        $this->logme ('info', "SignServer - End with result " . strval($res));

        $errorMsg = "";
        switch ($res) {
            case $crypt::SUCCESS :
                $crypt->writeFile ($source, $target);

                $userPath = utils::getRelativeUserFolderPath ($targetDir, $prefix . $fileSource, $this->server);
                $this->node->get($userPath);
                $this->logme ('info', "Target file has been wrote. Success.");
                break;
            case $crypt::GENERIC_EXCEPTION : $errorMsg = "Generic error"; break;
            case $crypt::REQFAIL_EXCEPTION : $errorMsg = "Request failed error"; break;
            case $crypt::INTERNAL_EXCEPTION : $errorMsg = "Internal server error"; break;
        }
        return new DataResponse(["res" => $res, "errormsg" => $errorMsg, "prefix" => $prefix, ]);
    }


    private function logme ($type, $message) {
        $message = $this->user . " - " .$message;
        switch ($type) {
            case 'info' : $this->logger->info($message, ['app' => $this->appName, ]); break;
        }
    }
}

