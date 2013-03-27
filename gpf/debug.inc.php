<?php
/**
 * GPF DEBUG 模式
 * 
 * @package default
 * @filesource
 */
$GLOBALS['gpf_debug_fp'] = NULL; //调试信息输出文件指针
$GLOBALS['gpf_debug_current_file'] = ''; //当前处理的PHP文件绝对路径
$GLOBALS['gpf_debug_current_file_urlencode'] = ''; //当前处理的PHP文件绝对路径(经URL编码)
/**
 * PHP文件开启DEBUG模式入口
 * @param string $file 待调试的PHP文件绝对路径（传入__FILE__即可）
 */
function gpfd_file($file)
{//{{{
	$gk = 'gpf_debug_fp';
	$gk_file = 'gpf_debug_current_file';
	$gk_urlencode = 'gpf_debug_current_file_urlencode';

	$GLOBALS[$gk_file] = addslashes($file);
	$GLOBALS[$gk_urlencode] = urlencode($file);
	if (NULL === $GLOBALS[$gk])
		{
		//初始化
		$output = date("ymd") . $_SERVER['PHP_SELF'] . '_' . md5($_SERVER['REQUEST_URI']) . '.html';
		$output = str_replace('/', '_', $output);
		gpf_log($output, GPF_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__);
		$GLOBALS[$gk] = fopen(GPF_DEBUG_OUTPUT . $output, 'wb');
		if (!$GLOBALS[$gk])
			{
			exit("无法创建DEBUG信息输出文件 {$output}");
			}
		unset($output);
		//写入初始信息
		$url = "http://{$_SERVER["SERVER_NAME"]}{$_SERVER["REQUEST_URI"]}";
		_gpfd_output("{$url}\r\n<a href=\"{$url}\" target=\"_blank\">[URL]</a><hr />");
		ob_start();
		}
	_gpfd_output("<h2 style=\"color:blue;font-size:18px;\">OPEN:{$file}</h2>\n");

	$php = file_get_contents($file);

	//================================ 字符串替换 ===============================
	$stri = $stro = array();
	$stri[] = 'return include gpf_debug(';
	$stro[] = '//gpf_debug(';

	$php = str_replace($stri, $stro, $php);

	//=============================== 正则替换 ===============================
	$pregi = $prego = array();
	//debug/php/php 直接转换为 php 中的 PHP 代码。eg. //debug/php/echo 1;
	$pregi[] = '#//debug/php/(.*)#';
	$prego[] = '\\1';
	//debug/disable{
	//调试模式时去除掉（不运行）的代码区,可以简单地把strip1改为strip0继续运行代码区
	//debug/disable}
	$pregi[] = '#//debug/disable{.*?//debug/disable}#se';
	$prego[] = "str_replace(array('//debug/disable{', '//debug/disable}'), array('if(0){', '}'), '\\0');";

	$php = preg_replace($pregi, $prego, $php);

	//================================ 其它 ===============================

	//debug/dump/$value, ... 用var_dump输出变量同时显示变量名
	$php = preg_replace_callback('#//debug/dump/(.*)#', '_gpfd_dump_callback', $php);

	$php = _gpfd_js_file($php);

	$debug_file = GPF_DEBUG_PHP . md5($file) . '.php';
	file_put_contents($debug_file, $php);
	return $debug_file;
}//}}}

/**
 * 输出调试信息
 */
function _gpfd_output($data)
{//{{{
	$gk = 'gpf_debug_fp';
	fwrite($GLOBALS[$gk], $data . "\n");
}//}}}

function _gpfd_dump_callback($match)
{//{{{
	$gk = 'gpf_debug_current_file';
	$arg = rtrim($match[1]);
	$arg_str = addslashes($arg);
	return "gpfdf_dump('{$GLOBALS[$gk]}', __LINE__, '{$arg_str}', {$arg});";
}//}}}

function _gpfd_ftitle($f, $l)
{//{{{
	return "<b>{$f}:{$l}</b><br />\n";
}//}}}

//输出变量
function gpfdf_dump($f, $l, $name)
{//{{{
	$arg = func_get_args();
	unset($arg[0], $arg[1], $arg[2]);
	$html = _gpfd_ftitle($f, $l);
	$html .= "<h2 style=\"color:red;font-size:18px;\">{$name}</h2>\n";
	$tmp = ob_get_clean();
	call_user_func_array('var_dump', $arg);
	$html .= ob_get_clean();
	echo $tmp;
	_gpfd_output($html);
}//}}}

//=============================== JS DEBUG ===============================
//js debug 后端处理函数
function gpfd_js()
{//{{{
	error_reporting(E_ALL & ~E_NOTICE);
	//计算调试信息输出文件保存路径
	$ref = $_SERVER['HTTP_REFERER'];
	if (!$ref)
		{
		//创建 HTTP_REFERER
		$url = "http://{$_SERVER["SERVER_NAME"]}{$_SERVER["REQUEST_URI"]}";
		?><a href="<?=$url?>">需跳转一次</a><?php
		exit;
		}
	$parse = parse_url($ref);
	$path = 'JS' . date("ymd") . $parse['path'] . '_' . md5($parse['query']) . '.html';
	$path = str_replace('/', '_', $path);
	$path = GPF_DEBUG_OUTPUT . $path;
	unset($parse);

	if ('init' === $_GET['jsdebug'])
		{
		//初始化调试信息文件
		//<!--//jsdebug/init--> 标签对应的后端函数
		file_put_contents($path, $ref . "<hr />\r\n");
		exit("初始化调试信息文件{$path}");
		}
	$fp = fopen($path, 'ab');
	if (!$fp)
		{
		exit("无法创建DEBUG信息输出文件 {$path}");
		}
	ob_start();
	ob_clean();
	$f = $_GET['jsdebug_file'];
	$l = $_GET['jsdebug_line'];
	$t = $_GET['jsdebug_title'];
	$output = "<b>{$f}:{$l}[{$t}]</b><br />\n";
	var_dump(_gpfd_js_json($_POST));
	$output .= ob_get_clean();
	fwrite($fp, $output . "<hr />\n");
	echo "GPF DEBUG JS 运行正常\n";
	print_r($_POST);
	exit;
}//}}}

//把数组中以“@”起头的键值（表示json数据）转为PHP数据
function _gpfd_js_json($data)
{//{{{
	foreach ($data as $k => $v)
		{
		if (is_array($v))
			{
			$data[$k] = _gpfd_js_json($v);
			continue;
			}
		if ('@' === $k[0] && is_string($v))
			{
			$k = '$' . substr($k, 1);
			$data[$k] = json_decode($v, true);
			}
		}
	return $data;
}//}}}

//编译PHP源代码中的"//jsdebug/"调试标签
function _gpfd_js_file($php)
{//{{{
	$gk_urlencode = 'gpf_debug_current_file_urlencode';
	//================================ 字符串替换 ===============================
	$stri = $stro = array();
	$stri[] = "<!--//jsdebug/init-->";
	$stro[] = '<script type="text/javascript">var GPF_DEBUG_JS_PHP = "' . GPF_DEBUG_JS_PHP . '";</script><script charset="UTF-8" src="' . GPF_DEBUG_JS_SCRIPT . '"></script>';
	//jsdebug/dump/{var} 记录一个JS变量数据（只支持一个）

	$php = str_replace($stri, $stro, $php);

	//=============================== 正则替换 ===============================
	$pregi = $prego = array();
	$pregi[] = '#//jsdebug/dump/(\w+)#';
	$prego[] = '$.post(GPF_DEBUG_JS_PHP + "&jsdebug_file=' . $GLOBALS[$gk_urlencode] . '&jsdebug_line=<?=__LINE__?>&jsdebug_title=\\1", gpf_debug_tojson(\\1), gpf_debug_callback);';

	$php = preg_replace($pregi, $prego, $php);

	return $php;
}//}}}
