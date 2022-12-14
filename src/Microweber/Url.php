<?php
namespace Microweber;

class Url
{
    public $site_url_var;
    public $current_url_var;



    public function site($add_string = false)
    {
        return $this->site_url($add_string);
    }

    public function hostname()
    {
        static $u1;
        if ($u1 == false) {
            $valid_domain = parse_url($this->site_url());
            if (isset($valid_domain['host'])) {
                $host = str_ireplace('www.', null, $valid_domain['host']);
                $u1 = $host;
            }
        }
        return $u1;
    }

    function link_to_file($path)
    {
        $path = str_ireplace(MW_ROOTPATH, '', $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        $path = str_ireplace(MW_ROOTPATH, '', $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        $path = str_ireplace(MW_ROOTPATH, '', $path);
        $this_file = @dirname(dirname(dirname(__FILE__)));
        $path = str_ireplace($this_file, '', $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        $path = ltrim($path, '/');
        $path = ltrim($path, '\\');
        return $this->site_url($path);
    }
    public function set($url = false)
    {
        return $this->site_url_var = ($url);
    }

    public function set_current($url = false)
    {
        return $this->current_url_var = ($url);
    }
    function to_path($path)
    {
        if (trim($path) == '') {
            return false;
        }
        $path = str_ireplace($this->site_url(), MW_ROOTPATH, $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        return normalize_path($path, false);
    }

    function redirect($url)
    {
        if (trim($url) == '') {
            return false;
        }
        $url = str_ireplace('Location:', '', $url);
        $url = trim($url);
        if (headers_sent()) {
            print '<meta http-equiv="refresh" content="0;url=' . $url . '">';
        } else {
            header('Location: ' . $url);
        }
        exit();
    }

    public function params($skip_ajax = false)
    {
        return $this->param($param = "__MW_GET_ALL_PARAMS__", $skip_ajax);
    }

    public function param($param, $skip_ajax = false, $force_url = false)
    {
        if ($_POST) {
            if (isset($_POST['search_by_keyword'])) {
                if ($param == 'keyword') {
                    return $_POST['search_by_keyword'];
                }
            }
        }
        $url = $this->current($skip_ajax);
        if ($force_url != false) {
            $url = $force_url;
        }
        $rem = $this->site_url();
        $url = str_ireplace($rem, '', $url);
        $url = str_ireplace('?', '/', $url);
        $url = str_ireplace('=', ':', $url);
        $url = str_ireplace('&', '/', $url);
        $all_params = array();
        $segs = explode('/', $url);
        foreach ($segs as $segment) {
            $seg1 = explode(':', $segment);
            if ($param == '__MW_GET_ALL_PARAMS__') {
                if (isset($seg1[0]) and isset($seg1[1])) {
                    $all_params[$seg1[0]] = $seg1[1];
                }
            } else {
                $param_sub_position = false;
                if (trim($seg1[0]) == trim($param)) {
                    if ($param_sub_position == false) {
                        $the_param = str_ireplace($param . ':', '', $segment);
                        if ($param == 'custom_fields_criteria') {
                            $the_param1 = $this->app->format->base64_to_array($the_param);
                            return $the_param1;
                        }
                        return $the_param;
                    } else {

                        $the_param = str_ireplace($param . ':', '', $segment);
                        $params_list = explode(',', $the_param);
                        if ($param == 'custom_fields_criteria') {
                            $the_param1 = base64_decode($the_param);
                            $the_param1 = unserialize($the_param1);
                            return $the_param1;
                        }
                        return $the_param;
                    }
                }
            }
        }

        if (empty($all_params)) {
            return false;
        }
        return $all_params;
    }
    function param_set($param,$value=false, $url = false)
    {
        if ($url == false) {
            $url = $this->string();
        }
        $site = $this->site_url();
        $url = str_ireplace($site, '', $url);
        $segs = explode('/', $url);
        $segs_clean = array();
        $found = false;
        foreach ($segs as $segment) {
            $origsegment = ($segment);
            $segment = explode(':', $segment);
            if ($segment[0] == $param) {
                $segment[1] = $value;
                $origsegment = impode(':', $segment);
                $found = true;
                $segs_clean[] = $origsegment;

            } else {
                $segs_clean[] = $origsegment;
            }
        }

        if($found == false){
            $segment = array();
            $segment[] = $param;
            $segment[] = $value;
            $origsegment = impode(':', $segment);
            $segs_clean[] = $origsegment;

        }


        $segs_clean = implode('/', $segs_clean);
        $site = ($segs_clean);
        return $site;
    }
    function param_unset($param, $url = false)
    {
        if ($url == false) {
            $url = $this->string();
        }
        $site = $this->site_url();
        $url = str_ireplace($site, '', $url);
        $segs = explode('/', $url);
        $segs_clean = array();
        foreach ($segs as $segment) {
            $origsegment = ($segment);
            $segment = explode(':', $segment);
            if ($segment[0] == $param) {
            } else {
                $segs_clean[] = $origsegment;
            }
        }
        $segs_clean = implode('/', $segs_clean);
        $site = ($segs_clean);
        return $site;
    }

    /**
     * Returns the current url path, does not include the domain name
     *
     * @param bool $skip_ajax If true it will try to get the referring url from ajax request
     * @return string the url string
     */
    function string($skip_ajax = false)
    {
        if ($skip_ajax == true) {
            $url = $this->current($skip_ajax);
        } else {
            $url = false;
        }

        $u1 = implode('/', $this->segment(-1, $url));

        return $u1;
    }

    /**
     * Returns the current url as a string
     *
     * @param bool $skip_ajax If true it will try to get the referring url from ajax request
     * @param bool $no_get If true it will remove the params after '?'
     * @return string the url string
     */
    public function current($skip_ajax = false, $no_get = false)
    {
        $u = false;
        if ($skip_ajax == true) {
            $is_ajax = $this->is_ajax();
            if ($is_ajax == true) {
                if ($_SERVER['HTTP_REFERER'] != false) {
                    $u = $_SERVER['HTTP_REFERER'];
                }
            }
        }

        if ($u == false and $this->current_url_var != false) {
            $u = $this->current_url_var;
        }
        if ($u == false) {

            if (!isset($_SERVER['REQUEST_URI'])) {
                $serverrequri = $_SERVER['PHP_SELF'];
            } else {
                $serverrequri = $_SERVER['REQUEST_URI'];
            }
            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
            $protocol = 'http';
            $port = 80;
            if (isset($_SERVER["SERVER_PROTOCOL"])) {
                $protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
            }
            if (isset($_SERVER["SERVER_PORT"])) {
                $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
            }
            if (isset($_SERVER["SERVER_PORT"])) {
                $u = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $serverrequri;
            } elseif (isset($_SERVER["HOSTNAME"])) {
                $u = $protocol . "://" . $_SERVER['HOSTNAME'] . $port . $serverrequri;
            }
        }

        if ($no_get == true) {
            $u = strtok($u, '?');
        }
        if (is_string($u)) {
            $u = str_replace(' ', '%20', $u);
        }
        return $u;
    }

    /**
     * Return true if the current request is via ajax
     *
     * @return true|false
     */
    public function is_ajax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
    }

    public function  strleft($s1, $s2)
    {
        return substr($s1, 0, strpos($s1, $s2));
    }

    /**
     * Returns single URL segment
     *
     * @param $num The segment number
     * @param bool $page_url If false it will use the current URL
     * @return string|false the url segment or false
     */
    public function segment($num = -1, $page_url = false)
    {
        $u = false;
        if ($page_url == false or $page_url == '') {
            $u1 = $this->current();
        } else {
            $u1 = $page_url;
        }
        $u2 = $this->site_url();
        $u1 = rtrim($u1, '\\');
        $u1 = rtrim($u1, '/');
        $u2 = rtrim($u2, '\\');
        $u2 = rtrim($u2, '/');
        $u2 = reduce_double_slashes($u2);
        $u1 = reduce_double_slashes($u1);
        $u2 = rawurldecode($u2);
        $u1 = rawurldecode($u1);
        $u1 = str_replace($u2, '', $u1);
        $u1 = str_replace(' ', '%20', $u1);
        if (!isset($u) or $u == false) {
            $u = explode('/', trim(preg_replace('/([^\w\:\-\.\%\/])/i', '', current(explode('?', $u1, 2))), '/'));
        }
        if ($num != -1) {
            if (isset($u[$num])) {
                return $u[$num];
            } else {
                return null;
            }
        } else {
            return $u;
        }


    }

    public function site_url($add_string = false)
    {


        $site_url = $this->site_url_var;

        if ($site_url == false) {
            return site_url($add_string);
        }

//to be deleted
        if ($site_url == false) {



            $pageURL = 'http';
            if (isset($_SERVER["HTTPS"]) and ($_SERVER["HTTPS"] == "on")) {
                $pageURL .= "s";
            }

            $subdir_append = false;
            if (isset($_SERVER['REDIRECT_URL'])) {
                $subdir_append = $_SERVER['REDIRECT_URL'];
            }

            $pageURL .= "://";
            if (isset($_SERVER["SERVER_NAME"]) and isset($_SERVER["SERVER_PORT"]) and $_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
            } elseif (isset($_SERVER["SERVER_NAME"])) {
                $pageURL .= $_SERVER["SERVER_NAME"];
            } else if (isset($_SERVER["HOSTNAME"])) {
                $pageURL .= $_SERVER["HOSTNAME"];
            }
            $pageURL_host = $pageURL;
            $pageURL .= $subdir_append;

            $d = '';
            if (isset($_SERVER['SCRIPT_NAME'])) {
                $d = dirname($_SERVER['SCRIPT_NAME']);
                $d = trim($d, DIRECTORY_SEPARATOR);
            }

            if ($d == '') {
                $pageURL = $pageURL_host;
            } else {

                $pageURL_host = rtrim($pageURL_host, '/') . '/';
                $d = ltrim($d, '/');
                $d = ltrim($d, DIRECTORY_SEPARATOR);

                $pageURL = $pageURL_host . $d;

            }
            //
            if (isset($_SERVER['QUERY_STRING'])) {
                $pageURL = str_replace($_SERVER['QUERY_STRING'], '', $pageURL);
            }


            $uz = parse_url($pageURL);
            if (isset($uz['query'])) {
                $pageURL = str_replace($uz['query'], '', $pageURL);
                $pageURL = rtrim($pageURL, '?');
            }

            $url_segs = explode('/', $pageURL);

            $i = 0;
            $unset = false;
            foreach ($url_segs as $v) {
                if ($unset == true and $d != '') {

                    unset($url_segs[$i]);
                }
                if ($v == $d and $d != '') {

                    $unset = true;
                }

                $i++;
            }
            $url_segs[] = '';
            $this->site_url_var = $site_url = implode('/', $url_segs);

        }


        return $site_url . $add_string;
    }

    /**
     * Returns ALL URL segments as array
     *
     * @param bool $page_url If false it will use the current URL
     * @return array|false the url segments or false
     */
    public function segments($page_url = false)
    {
        return $this->segment($k = -1, $page_url);
    }

    function slug($text)
    {
        // Swap out Non "Letters" with a -
        $text = preg_replace('/[^\\pL\d]+/u', '-', $text);
        // Trim out extra -'s
        $text = trim($text, '-');
        $text = URLify::filter($text);
        // Strip out anything we haven't been able to convert
        $text = preg_replace('/[^-\w]+/', '', $text);
        $text = str_replace(':', '-', $text);
        return $text;
    }

    public function download($requestUrl, $post_params = false, $save_to_file = false)
    {

        if ($post_params != false and is_array($post_params)) {
            $postdata = http_build_query($post_params);
        } else {
            $postdata = false;

        }
        $ref = site_url();

        $opts = array('http' => array('method' => 'POST', 'header' => "User-Agent: Microweber/" . MW_VERSION . "\r\n" . 'Content-type: application/x-www-form-urlencoded' . "\r\n" . 'Referer: ' . $ref . "\r\n", 'content' => $postdata));
        $requestUrl = str_replace(' ', '%20', $requestUrl);

        if (function_exists('curl_init')) {
            $ch = curl_init($requestUrl);
            curl_setopt($ch, CURLOPT_COOKIEJAR, MW_CACHE_DIR . "global/cookie.txt");
            curl_setopt($ch, CURLOPT_COOKIEFILE, MW_CACHE_DIR . "global/cookie.txt");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Microweber " . MW_VERSION . ";)");
            if ($post_params != false) {
                curl_setopt($ch, CURLOPT_POST, count($post_params));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
            }
            //	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            //curl_setopt($ch, CURLOPT_TIMEOUT, 400);
            $result = curl_exec($ch);

            curl_close($ch);
        } else {
            $context = stream_context_create($opts);
            $result = file_get_contents($requestUrl, false, $context);
        }

        if ($save_to_file == true) {
            file_put_contents($save_to_file, $result);
        } else {
            return $result;
        }
        return false;
    }

    public function replace_site_url($arr)
    {
        $site = $this->site_url();
        if (is_string($arr)) {
            $ret = str_ireplace($site, '{SITE_URL}', $arr);
            return $ret;
        }
        if (is_array($arr) and !empty($arr)) {
            $ret = array();
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $v = $this->replace_site_url($v);
                } else {
                    $v = str_ireplace($site, '{SITE_URL}', $v);
                }
                $ret[$k] = ($v);
            }
            return $ret;
        }
    }

    var $repaced_urls = array();
    public function replace_site_url_back($arr)
    {
        if($arr == false){
            return;
        }

        if (is_string($arr)) {
            $parser_mem_crc = 'replace_site_vars_back_' . crc32($arr);
            if (isset($this->repaced_urls[$parser_mem_crc])) {
                $ret = $this->repaced_urls[$parser_mem_crc];
            } else {
                $site = $this->site_url();
                $ret = str_replace('{SITE_URL}', $site, $arr);
                $this->repaced_urls[$parser_mem_crc] = $ret;
             }
            return $ret;
        }

        if (is_array($arr) and !empty($arr)) {
            $ret = array();
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $v = $this->replace_site_url_back($v);
                } else if (is_string($v) and $v !== '0') {
                    $v = $this->replace_site_url_back($v);
                }
                $ret[$k] = ($v);
            }
            return $ret;
        }
    }

    public function api_link($str = '')
    {
        $str = ltrim($str, '/');
        return $this->site_url('api/' . $str);
    }

}

/**
 * A PHP port of URLify.js from the Django project
 * (https://github.com/django/django/blob/master/django/contrib/admin/static/admin/js/urlify.js).
 * Handles symbols from Latin languages, Greek, Turkish, Russian, Ukrainian,
 * Czech, Polish, and Latvian. Symbols it cannot transliterate
 * it will simply omit.
 *
 * Usage:
 *
 * echo URLify::filter (' J\'??tudie le fran??ais ');
 * // "jetudie-le-francais"
 *
 * echo URLify::filter ('Lo siento, no hablo espa??ol.');
 * // "lo-siento-no-hablo-espanol"
 */
class URLify
{

    public static $maps = array('latin_map' => array('??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'AE', '??' => 'C', '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'D', '??' => 'N', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'Y', '??' => 'TH', '??' => 'ss', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'ae', '??' => 'c', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'd', '??' => 'n', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'y', '??' => 'th', '??' => 'y'), 'latin_symbols_map' => array('??' => '(c)'), 'greek_map' => array('??' => 'a', '??' => 'b', '??' => 'g', '??' => 'd', '??' => 'e', '??' => 'z', '??' => 'h', '??' => '8', '??' => 'i', '??' => 'k', '??' => 'l', '??' => 'm', '??' => 'n', '??' => '3', '??' => 'o', '??' => 'p', '??' => 'r', '??' => 's', '??' => 't', '??' => 'y', '??' => 'f', '??' => 'x', '??' => 'ps', '??' => 'w', '??' => 'a', '??' => 'e', '??' => 'i', '??' => 'o', '??' => 'y', '??' => 'h', '??' => 'w', '??' => 's', '??' => 'i', '??' => 'y', '??' => 'y', '??' => 'i', '??' => 'A', '??' => 'B', '??' => 'G', '??' => 'D', '??' => 'E', '??' => 'Z', '??' => 'H', '??' => '8', '??' => 'I', '??' => 'K', '??' => 'L', '??' => 'M', '??' => 'N', '??' => '3', '??' => 'O', '??' => 'P', '??' => 'R', '??' => 'S', '??' => 'T', '??' => 'Y', '??' => 'F', '??' => 'X', '??' => 'PS', '??' => 'W', '??' => 'A', '??' => 'E', '??' => 'I', '??' => 'O', '??' => 'Y', '??' => 'H', '??' => 'W', '??' => 'I', '??' => 'Y'), 'turkish_map' => array('??' => 's', '??' => 'S', '??' => 'i', '??' => 'I', '??' => 'c', '??' => 'C', '??' => 'u', '??' => 'U', '??' => 'o', '??' => 'O', '??' => 'g', '??' => 'G'), 'russian_map' => array('??' => 'a', '??' => 'b', '??' => 'v', '??' => 'g', '??' => 'd', '??' => 'e', '??' => 'yo', '??' => 'zh', '??' => 'z', '??' => 'i', '??' => 'j', '??' => 'k', '??' => 'l', '??' => 'm', '??' => 'n', '??' => 'o', '??' => 'p', '??' => 'r', '??' => 's', '??' => 't', '??' => 'u', '??' => 'f', '??' => 'h', '??' => 'c', '??' => 'ch', '??' => 'sh', '??' => 'sh', '??' => '', '??' => 'y', '??' => '', '??' => 'e', '??' => 'yu', '??' => 'ya', '??' => 'A', '??' => 'B', '??' => 'V', '??' => 'G', '??' => 'D', '??' => 'E', '??' => 'Yo', '??' => 'Zh', '??' => 'Z', '??' => 'I', '??' => 'J', '??' => 'K', '??' => 'L', '??' => 'M', '??' => 'N', '??' => 'O', '??' => 'P', '??' => 'R', '??' => 'S', '??' => 'T', '??' => 'U', '??' => 'F', '??' => 'H', '??' => 'C', '??' => 'Ch', '??' => 'Sh', '??' => 'Sh', '??' => '', '??' => 'Y', '??' => '', '??' => 'E', '??' => 'Yu', '??' => 'Ya'), 'ukrainian_map' => array('??' => 'Ye', '??' => 'I', '??' => 'Yi', '??' => 'G', '??' => 'ye', '??' => 'i', '??' => 'yi', '??' => 'g'), 'czech_map' => array('??' => 'c', '??' => 'd', '??' => 'e', '??' => 'n', '??' => 'r', '??' => 's', '??' => 't', '??' => 'u', '??' => 'z', '??' => 'C', '??' => 'D', '??' => 'E', '??' => 'N', '??' => 'R', '??' => 'S', '??' => 'T', '??' => 'U', '??' => 'Z'), 'polish_map' => array('??' => 'a', '??' => 'c', '??' => 'e', '??' => 'l', '??' => 'n', '??' => 'o', '??' => 's', '??' => 'z', '??' => 'z', '??' => 'A', '??' => 'C', '??' => 'e', '??' => 'L', '??' => 'N', '??' => 'O', '??' => 'S', '??' => 'Z', '??' => 'Z'), 'latvian_map' => array('??' => 'a', '??' => 'c', '??' => 'e', '??' => 'g', '??' => 'i', '??' => 'k', '??' => 'l', '??' => 'n', '??' => 's', '??' => 'u', '??' => 'z', '??' => 'A', '??' => 'C', '??' => 'E', '??' => 'G', '??' => 'i', '??' => 'k', '??' => 'L', '??' => 'N', '??' => 'S', '??' => 'u', '??' => 'Z'));
    /**
     * List of words to remove from URLs.
     */
    public static $remove_list = array();
    /**
     * The character map.
     */
    private static $map = array();
    /**
     * The character list as a string.
     */
    private static $chars = '';
    /**
     * The character list as a regular expression.
     */
    private static $regex = '';

    /**
     * Add new characters to the list.
     * `$map` should be a hash.
     */
    public static function add_chars($map)
    {
        if (!is_array($map)) {
            throw new LogicException('$map must be an associative array.');
        }
        self::$maps[] = $map;
        self::$map = array();
        self::$chars = '';
    }

    /**
     * Append words to the remove list.
     * Accepts either single words
     * or an array of words.
     */
    public static function remove_words($words)
    {
        $words = is_array($words) ? $words : array($words);
        self::$remove_list = array_merge(self::$remove_list, $words);
    }

    /**
     * Filters a string, e.g., "Petty theft" to "petty-theft"
     */
    public static function filter($text, $length = 60)
    {
        $text = self::downcode($text);

        // remove all these words from the string before urlifying
        $text = preg_replace('/\b(' . join('|', self::$remove_list) . ')\b/i', '', $text);

        // if downcode doesn't hit, the char will be stripped here
        $text = preg_replace('/[^-\w\s]/', '', $text);
        // remove unneeded chars
        $text = preg_replace('/^\s+|\s+$/', '', $text);
        // trim
        // leading/trailing
        // spaces
        $text = preg_replace('/[-\s]+/', '-', $text);
        // convert spaces to
        // hyphens
        $text = strtolower($text);
        // convert to lowercase
        return trim(substr($text, 0, $length), '-');
        // trim to first
        // $length
        // chars
    }

    /**
     * Alias of `URLify::downcode()`.
     */
    public static function transliterate($text)
    {
        return self::downcode($text);
    }

    /**
     * Transliterates characters to their ASCII equivalents.
     */
    public static function downcode($text)
    {
        self::init();

        if (preg_match_all(self::$regex, $text, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $char = $matches[0][$i];
                if (isset(self::$map[$char])) {
                    $text = str_replace($char, self::$map[$char], $text);
                }
            }
        }
        return $text;
    }

    /**
     * Initializes the character map.
     */
    private static function init()
    {
        if (count(self::$map) > 0) {
            return;
        }

        foreach (self::$maps as $map) {
            foreach ($map as $orig => $conv) {
                self::$map[$orig] = $conv;
                self::$chars .= $orig;
            }
        }

        self::$regex = '/[' . self::$chars . ']/u';
    }

}