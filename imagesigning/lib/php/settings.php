<?php

    namespace OCA\ImageSigning\lib\php;

    use OCA\ImageSigning\lib\php\utils;

    class settings {

        private static $context = [
              'signers' => []
            , 'activeSigner' => ''
            , 'signedFilePrefix' => ''
            , 'allowMime' => ['application/pdf',]
        ];

        public static function init () {
            try {
                $parsed_ini = utils::parseIniFile();

                $signServers = [];
                // Get sign servers information
                foreach ($parsed_ini as $section => $v) {
                    if (strpos ($section, utils::sectionPrefix) === 0) {
                        $s = [    'id' => $section
                                , 'name' => $v['name']
                                , 'serverURL' => $v['serverURL']
                                , 'workerId' => $v['workerId']
                        ];
                        array_push ($signServers, $s);
                    }
                }
                self::$context['signers'] = $signServers;

                // Get information from general section
                $gen = $parsed_ini['general'];
                self::$context['activeSigner'] = (isset ($gen['activeSigner'])) ? $gen['activeSigner'] : self::setDefaultActiveSigner();
                self::$context['signedFilePrefix'] = $gen['signedFilePrefix'];
            }
            catch (\Exception $ex) {
                error_log (print_r($ex,true),4);
            }
        }

        public static function get ($label) {
            return self::$context[$label];
        }

        public static function getAll () {
            return self::$context;
        }

        public static function getActiveSigner () {
            foreach (self::$context['signers'] as $a) {
                if ($a['id'] == self::$context['activeSigner'])
                    return $a;
            }
            return null;
        }


        // -------------------------------------------------------------------------------------------------------------

        private static function setDefaultActiveSigner () {
            $id = self::$context['signers'][0]['id'];
            utils::updateKeyIniFile('general', 'activeSigner', $id);
            return $id;
        }
    }

