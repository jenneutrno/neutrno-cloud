<?php

    namespace OCA\ImageSigning\lib\php;

    use OCA\ImageSigning\lib\php\settings;

    require_once __DIR__ . "/nusoap/class.nusoap_base.php";
    require_once __DIR__ . "/nusoap/class.soap_parser.php";
    require_once __DIR__ . "/nusoap/class.xmlschema.php";
    require_once __DIR__ . "/nusoap/class.soap_transport_http.php";
    require_once __DIR__ . "/nusoap/class.wsdl.php";
    require_once __DIR__ . "/nusoap/class.soapclient.php";


    class crypt {
        const SUCCESS = 0;
        const GENERIC_EXCEPTION = 1;
        const REQFAIL_EXCEPTION = 2;
        const INTERNAL_EXCEPTION = 3;

        private $signServerParams = null;
        private $decodedFileContents = null;



        public function __construct() {
            $this->signServerParams = settings::getActiveSigner();
        }


        public function cryptFile ($sourceFile) {
            $this->decodedFileContents = null;
            $res = self::GENERIC_EXCEPTION;

            try {
                $client = new \nusoap_client ($this->signServerParams['serverURL'], true);

                $fileHandle = fopen($sourceFile, "r");
                $fileContents = fread($fileHandle, filesize($sourceFile));
                fclose($fileHandle);

                $encodeFileContents = base64_encode($fileContents);
                $data = ['worker' => $this->signServerParams['workerId'], 'data' => $encodeFileContents,];
                $signResult = $client->call('processData', $data);


                // Success keys: return => archiveId, data, requestId, SignerCertificate
                // Error keys: faultcode, faultstring, detail [ RequestFailedException, InternalServerException ]
                if ((array_key_exists ('faultcode', $signResult)) || (array_key_exists ('faultstring', $signResult))) {
                    if (array_key_exists ('detail', $signResult)) {
                        if (array_key_exists ('RequestFailedException', $signResult ['detail']))  { $res = self::REQFAIL_EXCEPTION; }
                        elseif (array_key_exists ('RequestFailedException', $signResult ['detail']))  { $res = self::INTERNAL_EXCEPTION; }
                    }
                }
                else {
                    if (array_key_exists ('return', $signResult)) {
                        $this->decodedFileContents = base64_decode($signResult['return']['data']);
                        $res = self::SUCCESS;
                    }
                }
            }
            catch (\Exception $ex) {
                $res = self::GENERIC_EXCEPTION;
            }

            return $res;
        }


        public function writeFile ($sourceFile, $targetFile) {
            if ($this->decodedFileContents != null) {
                file_put_contents($targetFile, $this->decodedFileContents, LOCK_EX);

                $fileOwn = fileowner($sourceFile);
                chown ($targetFile, $fileOwn); chgrp ($targetFile, $fileOwn);
                $fileAttrs = fileperms($sourceFile);
                chmod ($targetFile, $fileAttrs);
            }
        }
    }





