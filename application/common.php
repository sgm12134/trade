<?php

// 公共助手函数

use Symfony\Component\VarExporter\VarExporter;
use think\Config;
use think\Db;
use think\exception\HttpResponseException;
use think\Response;
use Utils\SpotApi;

if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int    $size      大小
     * @param string $delimiter 分隔符
     * @param int    $precision 小数位数
     * @return string
     */
    function format_bytes($size, $delimiter = '', $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . $delimiter . $units[$i];
    }
}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int    $time   时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }
}

if (!function_exists('human_date')) {

    /**
     * 获取语义化时间
     * @param int $time  时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }
}

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string  $url    资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $cdnurl = \think\Config::get('upload.cdnurl');
        $url = preg_match($regex, $url) || ($cdnurl && stripos($url, $cdnurl) === 0) ? $url : $cdnurl . $url;
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}


if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }
}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest   目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }
}

if (!function_exists('addtion')) {

    /**
     * 附加关联字段数据
     * @param array $items  数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields) {
            return $items;
        }
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model'];
            } else {
                $model = $v['name'] ? \think\Db::name($v['name']) : \think\Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = isset($ids[$v['field']]) ? $model->where($primary, 'in', $ids[$v['field']])->column($v['column'], $primary) : [];
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $linedata = array_intersect_key($result[$n], $curr);
                    $v[$fieldsArr[$n]['display']] = $fieldsArr[$n]['column'] == '*' ? $linedata : implode(',', $linedata);
                }
            }
        }
        return $items;
    }
}

if (!function_exists('var_export_short')) {

    /**
     * 使用短标签打印或返回数组结构
     * @param mixed   $data
     * @param boolean $return 是否返回数据
     * @return string
     */
    function var_export_short($data, $return = true)
    {
        return var_export($data, $return);
        $replaced = [];
        $count = 0;

        //判断是否是对象
        if (is_resource($data) || is_object($data)) {
            return var_export($data, $return);
        }

        //判断是否有特殊的键名
        $specialKey = false;
        array_walk_recursive($data, function (&$value, &$key) use (&$specialKey) {
            if (is_string($key) && (stripos($key, "\n") !== false || stripos($key, "array (") !== false)) {
                $specialKey = true;
            }
        });
        if ($specialKey) {
            return var_export($data, $return);
        }
        array_walk_recursive($data, function (&$value, &$key) use (&$replaced, &$count, &$stringcheck) {
            if (is_object($value) || is_resource($value)) {
                $replaced[$count] = var_export($value, true);
                $value = "##<{$count}>##";
            } else {
                if (is_string($value) && (stripos($value, "\n") !== false || stripos($value, "array (") !== false)) {
                    $index = array_search($value, $replaced);
                    if ($index === false) {
                        $replaced[$count] = var_export($value, true);
                        $value = "##<{$count}>##";
                    } else {
                        $value = "##<{$index}>##";
                    }
                }
            }
            $count++;
        });

        $dump = var_export($data, true);

        $dump = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $dump); // Starts
        $dump = preg_replace('#\n([ ]*)\),#', "\n$1],", $dump); // Ends
        $dump = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $dump); // Empties
        $dump = preg_replace('#\)$#', "]", $dump); //End

        if ($replaced) {
            $dump = preg_replace_callback("/'##<(\d+)>##'/", function ($matches) use ($replaced) {
                return isset($replaced[$matches[1]]) ? $replaced[$matches[1]] : "''";
            }, $dump);
        }

        if ($return === true) {
            return $dump;
        } else {
            echo $dump;
        }
    }
}

if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" dominant-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}

if (!function_exists('check_nav_active')) {
    /**
     * 检测会员中心导航是否高亮
     */
    function check_nav_active($url, $classname = 'active')
    {
        $auth = \app\common\library\Auth::instance();
        $requestUrl = $auth->getRequestUri();
        $url = ltrim($url, '/');
        return $requestUrl === str_replace(".", "/", $url) ? $classname : '';
    }
}

if (!function_exists('check_cors_request')) {
    /**
     * 跨域检测
     */
    function check_cors_request()
    {
        if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN']) {
            $info = parse_url($_SERVER['HTTP_ORIGIN']);
            $domainArr = explode(',', config('fastadmin.cors_request_domain'));
            $domainArr[] = request()->host(true);
            if (in_array("*", $domainArr) || in_array($_SERVER['HTTP_ORIGIN'], $domainArr) || (isset($info['host']) && in_array($info['host'], $domainArr))) {
                header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            } else {
                $response = Response::create('跨域检测无效', 'html', 403);
                throw new HttpResponseException($response);
            }

            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');

            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                }
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                }
                $response = Response::create('', 'html');
                throw new HttpResponseException($response);
            }
        }
    }
}

if (!function_exists('xss_clean')) {
    /**
     * 清理XSS
     */
    function xss_clean($content, $is_image = false)
    {
        return \app\common\library\Security::instance()->xss_clean($content, $is_image);
    }
}

if (!function_exists('check_ip_allowed')) {
    /**
     * 检测IP是否允许
     * @param string $ip IP地址
     */
    function check_ip_allowed($ip = null)
    {
        $ip = is_null($ip) ? request()->ip() : $ip;
        $forbiddenipArr = config('site.forbiddenip');
        $forbiddenipArr = !$forbiddenipArr ? [] : $forbiddenipArr;
        $forbiddenipArr = is_array($forbiddenipArr) ? $forbiddenipArr : array_filter(explode("\n", str_replace("\r\n", "\n", $forbiddenipArr)));
        if ($forbiddenipArr && \Symfony\Component\HttpFoundation\IpUtils::checkIp($ip, $forbiddenipArr)) {
            $response = Response::create('请求无权访问', 'html', 403);
            throw new HttpResponseException($response);
        }
    }
}

if (!function_exists('build_suffix_image')) {
    /**
     * 生成文件后缀图片
     * @param string $suffix 后缀
     * @param null   $background
     * @return string
     */
    function build_suffix_image($suffix, $background = null)
    {
        $suffix = mb_substr(strtoupper($suffix), 0, 4);
        $total = unpack('L', hash('adler32', $suffix, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $background = $background ? $background : "rgb({$r},{$g},{$b})";

        $icon = <<<EOT
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
            <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
            <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
            <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
            <path style="fill:{$background};" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"/>
            <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            <g><text><tspan x="220" y="380" font-size="124" font-family="Verdana, Helvetica, Arial, sans-serif" fill="white" text-anchor="middle">{$suffix}</tspan></text></g>
        </svg>
EOT;
        return $icon;
    }
}
function make_coupon_card() {

    $unique_no = substr(base_convert(md5(uniqid(md5(microtime(true)),true)), 16, 10), 0, 6);
    $dd=\think\Db::name('user')->where('invite_code',$unique_no)->find();
    if($dd){
        make_coupon_card();
    }
    return $unique_no;

}
function getip() {

    static $ip = '';

    $ip = $_SERVER['REMOTE_ADDR'];

    if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {

        $ip = $_SERVER['HTTP_CDN_SRC_IP'];

    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {

        $ip = $_SERVER['HTTP_CLIENT_IP'];

    } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {

        foreach ($matches[0] AS $xip) {

            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {

                $ip = $xip;

                break;

            }

        }

    }

    return $ip;

}

header("Content:Content-type:text/html;charset=utf-8");

//   // 作用取得客户端的ip、地理位置、浏览器、以及访问设备
    function GetBrowser()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $br = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i', $br)) {
                $br = 'MSIE';
            } elseif (preg_match('/Firefox/i', $br)) {
                $br = 'Firefox';
            } elseif (preg_match('/Chrome/i', $br)) {
                $br = 'Chrome';
            } elseif (preg_match('/Safari/i', $br)) {
                $br = 'Safari';
            } elseif (preg_match('/Opera/i', $br)) {
                $br = 'Opera';
            } else {
                $br = 'Other';
            }
            return $br;
        } else {
            return "获取浏览器信息失败！";
        }
    }

    ////获得访客浏览器语言
    function GetLang()
    {
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $lang = substr($lang, 0, 5);
            if (preg_match("/zh-cn/i", $lang)) {
                $lang = "简体中文";
            } elseif (preg_match("/zh/i", $lang)) {
                $lang = "繁体中文";
            } else {
                $lang = "English";
            }
            return json_encode("浏览器语言为" . $lang);
        } else {
            return "获取浏览器语言失败！";
        }
    }

    //获取客户端操作系统信息包括win10
    function GetOs()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $os = false;
        if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
            $os = 'Windows 95';
        } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
            $os = 'Windows ME';
        } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
            $os = 'Windows 98';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
            $os = 'Windows Vista';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
            $os = 'Windows 7';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
            $os = 'Windows 8';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
            $os = 'Windows 10';#添加win10判断
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
            $os = 'Windows XP';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
            $os = 'Windows 2000';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
            $os = 'Windows NT';
        } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
            $os = 'Windows 32';
        } else if (preg_match('/linux/i', $agent)) {
            $os = 'Linux';
        } else if (preg_match('/unix/i', $agent)) {
            $os = 'Unix';
        } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
            $os = 'SunOS';
        } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
            $os = 'IBM OS/2';
        } else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent)) {
            $os = 'Macintosh';
        } else if (preg_match('/PowerPC/i', $agent)) {
            $os = 'PowerPC';
        } else if (preg_match('/AIX/i', $agent)) {
            $os = 'AIX';
        } else if (preg_match('/HPUX/i', $agent)) {
            $os = 'HPUX';
        } else if (preg_match('/NetBSD/i', $agent)) {
            $os = 'NetBSD';
        } else if (preg_match('/BSD/i', $agent)) {
            $os = 'BSD';
        } else if (preg_match('/OSF1/i', $agent)) {
            $os = 'OSF1';
        } else if (preg_match('/IRIX/i', $agent)) {
            $os = 'IRIX';
        } else if (preg_match('/FreeBSD/i', $agent)) {
            $os = 'FreeBSD';
        } else if (preg_match('/teleport/i', $agent)) {
            $os = 'teleport';
        } else if (preg_match('/flashget/i', $agent)) {
            $os = 'flashget';
        } else if (preg_match('/webzip/i', $agent)) {
            $os = 'webzip';
        } else if (preg_match('/offline/i', $agent)) {
            $os = 'offline';
        } else {
            $os = '未知操作系统';
        }
        return $os;
    }



function generateOrderSn()
{
    $yearCode = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K"];
    //生成规则年（转换成一个大写字母来缩短长度）+月（转换为十六进制数）+日+当前时间戳的后
    //几位+当前的微秒数（小数点后面的5位）+一个两位的随机year的算法
    $year = $yearCode[date("Y") - 2018]; //用A表示2018年等，目的是缩短订单号
    $month = strtoupper(dechex(date("m")));//十六进制
    $day = date("d");
    $sec = substr(time(), -5); //取时间戳的后5位，因为前面的大至不会改变
    [$micr, $second] = explode("", microtime());
    $microsec = substr($micr, 2, 5);//取得微秒数小数点后面的5位数
    $rand = random_int(10, 99);
    $orderSn = $year . $month . $day . $sec . $microsec . $rand . uniqid('', true);
    return $orderSn;
}
function usdtprice(){

    return  Db::name('config')->where('name','usdtprice')->value('value');
    }
function is_valid_email($email)//判断是不是邮箱的函数
{
    return preg_match('/^[a-zA-Z0-9._%-]+@([a-zA-Z0-9.-]+.)+[a-zA-Z]{2,4}$/u', $email);
}


/**
 * 使用雪花算法生成订单ID
 * @return string
 * @throws \Exception
 */
function getNewOrderId(string $prefix = 'wx')
{
    #yCode 可以按照自己的实际情况调整
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J','I','K','L','Q','W','R','T','Y','U');
    $order_sn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    return $order_sn;
}

function http_get($url, $header = [], $proxy = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    if (!empty($proxy)) {
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_PROXY, "{$proxy['ip']}:{$proxy['port']}");
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, "{$proxy['username']}:{$proxy['password']}");
    }
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result);
}
