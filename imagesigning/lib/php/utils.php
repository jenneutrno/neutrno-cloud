<?php

    namespace OCA\ImageSigning\lib\php;

    use OC_App;

    class utils {

        const iniFileName = 'app.ini';
        const sectionPrefix = 'signer_';

        public static function getAbsoluteDataPath ($dir, $filename, $server) {
            $relPath = str_replace ('//', '/', $dir . '/' .$filename);
            $dataPath = $server->getSystemConfig()->getValue("datadirectory");
            $userDataPath = $server->getUserFolder()->getFullPath($relPath);
            $fullPath = str_replace ('//', '/', $dataPath . '/' .$userDataPath);

            return $fullPath;
        }

        public static function getRelativeUserFolderPath ($dir, $filename, $server) {
            $relPath = str_replace ('//', '/', $dir . '/' .$filename);
            return $server->getUserFolder()->getFullPath($relPath);
        }

        public static function getUserName ($server) {
            $a = explode ("/", $server->getUserFolder()->getFullPath("/"));
            return $a[1];
        }

        public static function getAppFullPath () {
            return OC_App::getAppPath('imagesigning');
        }

        // -------------------------------------------------------------------------------------------------------------

        private static function getIniFileName () {
            return self::getAppFullPath() . '/' . self::iniFileName;
        }

        private static function addIniSectionWithContent ($section, $values) {
            $content = "[".$section."]\n";
            foreach($values as $key=>$value){
                $content .= $key."=".$value."\n";
            }
            return $content;
        }

        private static function updateIniFile ($content) {
            $f = fopen (self::getIniFileName(), 'w');
            fwrite ($f, $content);
            fclose ($f);
        }


        public static function parseIniFile () {
            $filePath = self::getIniFileName();
            return parse_ini_file($filePath, true);
        }

        public static function updateKeyIniFile ($section, $key, $value) {
            $iniFile = self::parseIniFile();
            $iniFile[$section][$key] = $value;

            $content = '';
            $first = true;
            foreach($iniFile as $section=>$values){
                if ($first) $first = false; else $content .= "\n";
                $content .= self::addIniSectionWithContent ($section, $values);
            }

            self::updateIniFile ($content);
        }

        public static function refreshIniSigners ($signers) {
            //// Remove all singers_ sections from ini
            $iniFile = self::parseIniFile();
            $content = '';
            $first = true;
            foreach($iniFile as $section=>$values){
                if (strpos($section, self::sectionPrefix) !== 0) {
                    if ($first) $first = false; else $content .= "\n";
                    $content .= self::addIniSectionWithContent ($section, $values);
                }
            }
            //// Add new signers
            foreach($signers as $k => $v) {
                if ($first) $first = false; else $content .= "\n";
                $section = $v['id'];
                unset ($v['id']);
                $content .= self::addIniSectionWithContent ($section, $v);
            }

            self::updateIniFile ($content);
        }

        public static function setActiveIniSigner ($params) {
            $activeSigner = '';
            $signers = $params['signers'];
            if (!is_null($signers)) {
                $aSigner = $params['activeSigner'];
                if (!is_null($aSigner)) {
                    //// Looking for active signer inside signers list
                    foreach ($signers as $k => $v) {
                        if ($v['id'] == $aSigner) { $activeSigner = $aSigner; break; }
                    }
                    //// If no signer match, then assign first signer
                    if ($activeSigner == '') $activeSigner = $signers[0]['id'];
                }
            }

            self::updateKeyIniFile ('general', 'activeSigner', $activeSigner);
        }
    }

