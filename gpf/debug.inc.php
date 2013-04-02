<?php
/**
 * GPF DEBUG 模式
 * 
 * @package default
 * @filesource
 */
//环境检查
$_gpf_check = array(
	'GPF_DEBUG_PHP', 'GPF_DEBUG_OUTPUT', 'GPF_DEBUG_JS_SCRIPT', 'GPF_DEBUG_JS_SCRIPT', 'GPF_DEBUG_JS_PHP',
	);
foreach ($_gpf_check as $_v)
	{
	if (!defined($_v))
		{
		exit("未定义常量 {$_v}");
		}
	}
//可通过定义GPF_DEBUG_LOAD常量指向自定义debug函数定义文件实现自动加载
if (defined('GPF_DEBUG_LOAD'))
	{
	if (function_exists('gpf_inc'))
		{
		exit("未加载 gpf.inc.php 时不能定义 GPF_DEBUG_LOAD 常量");
		}
	gpf_inc(GPF_DEBUG_LOAD);
	}

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

	$_file = realpath($file);
	if (!$_file)
		{
		exit("待DEBUG文件不存在:{$file}");
		}
	$file = $_file;
	$GLOBALS[$gk_file] = $filestr = addslashes($file);
	$GLOBALS[$gk_urlencode] = urlencode($file);
	if (NULL === $GLOBALS[$gk])
		{
		//初始化
		$output = date("ymd") . $_SERVER['PHP_SELF'] . '_' . md5($_SERVER['REQUEST_URI']) . '.html';
		$output = str_replace('/', '_', $output);
		if (function_exists('gpf_log'))
			{
			$h = "<a href=\"file:///" . GPF_DEBUG_OUTPUT . "{$output}\" target=\"_blank\">{$output}</a>";
			gpf_log($h, GPF_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__);
			unset($h);
			}
		$GLOBALS[$gk] = fopen(GPF_DEBUG_OUTPUT . $output, 'wb');
		if (!$GLOBALS[$gk])
			{
			exit("无法创建DEBUG信息输出文件 {$output}");
			}
		unset($output);
		//写入初始信息
		$url = "http://{$_SERVER["SERVER_NAME"]}{$_SERVER["REQUEST_URI"]}";
		gpfd_output("{$url}\r\n<a href=\"{$url}\" target=\"_blank\">[URL]</a><br />" . date("Y-m-d H:i:s") . "<hr />");
		ob_start();
		}
	gpfd_output("<h2 style=\"color:blue;font-size:18px;\">DEBUG:{$file}</h2>\n");

	$tmp = basename($file);
	$debug_file = GPF_DEBUG_PHP . $tmp[0] . '/' . md5($file) . '.php';
	unset($tmp);
	if (is_file($debug_file) && filemtime($debug_file) > filemtime($file))
		{
		return $debug_file;
		}
	gpfd_output("<p>debug:replace</p>\n");

	//开始进行替换
	$php = file_get_contents($file);

	$stri = $stro = array(); //字符串替换
	$pregi = $prego = array(); //正则替换

	//避免死循环
	$stri[] = 'return include gpf_debug(';
	$stro[] = "//GPF DEBUG: {$file} === ";

	$stri[] = '__FILE__';
	$stro[] = "'{$filestr}'";

	//debug/php/php 直接转换为 php 中的 PHP 代码。eg. //debug/php/echo 1;
	$pregi[] = '#//debug/php/(.*)#';
	$prego[] = '\\1';
	//debug/disable{
	//调试模式时去除掉（不运行）的代码区,可以简单地把strip1改为strip0继续运行代码区
	//debug/disable}
	$pregi[] = '#//debug/disable{.*?//debug/disable}#se';
	$prego[] = "str_replace(array('//debug/disable{', '//debug/disable}'), array('if(0){', '}'), '\\0');";
	//debug/off{
	//"disable"的别名
	//debug/off}
	$pregi[] = '#//debug/off{.*?//debug/off}#se';
	$prego[] = "str_replace(array('//debug/off{', '//debug/off}'), array('if(0){', '}'), '\\0');";
	/* //debug/run
	 PHP_CODE 表示这部份代码是debug模式下才运行的代码段
	 */
	$pregi[] = '#/\* //debug/run.*?\*/#se';
	$prego[] = "str_replace(array('/*', '*/'), '//', '\\0');";
	//debug/if/[条件]{
	//PHP_CODE debug模式时会变成实际的if语句，实现[条件]成立时才执行。
	//debug/if}
	$pregi[] = '#//debug/if/(.*?){(.*?)//debug/if}#s';
	$prego[] = "if (\\1) {\\2}";
	//debug/offif/[条件]{
	//PHP_CODE debug模式时变成实际的if语句，实现[条件]成立时不执行。
	//debug/offif}
	$pregi[] = '#//debug/offif/(.*?){(.*?)//debug/offif}#s';
	$prego[] = "if (!(\\1)) {\\2}";
	//debug/if:1:/PHP_CODE{
	//... 当 if:1: 时，PHP_CODE生效（if:0:时则无效），用于按条件添加像for,foreach等这类带大括号的控制结构
	//debug/endif/}
	$pregi[] = '#//debug/if:1:/(.*?)//debug/endif/(.*)#se';
	$prego[] = "str_replace(array('//debug/if:1:/', '//debug/endif/'), '', '\\0')";

	//=============================== 提供简单的测试断言功能 ===============================
	//debug/test= 测试状态开关,设为1(true)为开，设为0(false)为关
	//debug/test=1
	$pregi[] = '#//debug/test=(.*?)/(.*)#';
	$prego[] = "if(defined('GPF_TEST')){ \$gpf_debug_test = \\1; gpfd_test_name('{$filestr}', __LINE__, \$gpf_debug_test, '\\2'); }//";
	//debug/test/[断言] 测试断言，会向浏览器直接输出断言结果
	//debug/test/1 === 1
	$pregi[] = '#//debug/test/([^\r\n]*)#';
	$prego[] = 'if ($gpf_debug_test) { gpfd_test(\\1, \'' . $GLOBALS[$gk_file] . '\', __LINE__); }';
	//debug/testif/[断言条件]/[断言] 按条件进行测试断言
	//debug/testif/$open == 1/$close == 1
	$pregi[] = '#//debug/testif/([^/]*)/([^\r\n]*)#';
	$prego[] = 'if ($gpf_debug_test && \\1) { gpfd_test(\\2, \'' . $GLOBALS[$gk_file] . '\', __LINE__); }';
	//debug/testoff{
	//PHP_CODE 测试开启时不运行的PHP代码
	//debug/testoff}
	$pregi[] = '#//debug/testoff{(.*?)//debug/testoff}#se';
	$prego[] = "str_replace(array('//debug/testoff{', '//debug/testoff}'), array('if(!\$gpf_debug_test){', '}'), '\\0');";
	//debug/testphp/PHP_CODE 测试开启时运行的PHP代码（单行）
	//debug/testphp/echo 1;
	$pregi[] = '#//debug/testphp/#';
	$prego[] = 'if($gpf_debug_test) ';
	/* //debug/testphp 测试开启时运行的PHP代码段（可多行）
	 PHP_CODE
	 */
	$pregi[] = '#/\* //debug/testphp(.*?)\*/#se';
	$prego[] = "str_replace(array('/* //debug/testphp', '*/'), array('if(\$gpf_debug_test){', '}'), '\\0')";

	$php = str_replace($stri, $stro, $php);
	$php = preg_replace($pregi, $prego, $php);

	//debug/dump/$value, ... 用var_dump输出变量同时显示变量名
	$php = preg_replace_callback('#//debug/dump/(.*)#', '_gpfd_dump_callback', $php);

	//=============================== //debug/f/ 系列扩展函数 ===============================
	//debug/f/NAME/ARG
	//debug/f/result/$result
	$php = preg_replace_callback('#//debug/f/([^/]*)/([^\r\n]*)#', '_gpfdf_callback', $php);

	$php = _gpfd_js_file($php);

	//调用扩展替换函数
	$func_name = 'gpfd_my';
	if (function_exists($func_name))
		{
		$php = $func_name($php);
		}

	$dir = dirname($debug_file);
	if (!is_dir($dir))
		{
		mkdir($dir);
		}
	file_put_contents($debug_file, $php);
	return $debug_file;
}//}}}

/**
 * 输出调试信息
 */
function gpfd_output($data)
{//{{{
	$gk = 'gpf_debug_fp';
	fwrite($GLOBALS[$gk], $data . "\n");
}//}}}

function _gpfd_dump_callback($match)
{//{{{
	$gk = 'gpf_debug_current_file';
	$arg = rtrim($match[1]);
	$arg_str = addslashes($arg);
	$f = $GLOBALS[$gk];
	return "gpfd_dump(\"<b>{$f}:\".__LINE__.\"</b><br />\", '{$arg_str}', {$arg});";
}//}}}
//输出变量
function gpfd_dump($html, $name)
{//{{{
	$arg = func_get_args();
	unset($arg[0], $arg[1]);
	$html .= "<h2 style=\"color:red;font-size:18px;\">{$name}</h2>\n";
	$tmp = ob_get_contents();
	ob_clean();
	call_user_func_array('var_dump', $arg);
	$html .= ob_get_contents();
	ob_clean();
	echo $tmp;
	gpfd_output($html);
}//}}}

//测试断言
function gpfd_test($test, $f, $l)
{//{{{
	$color = $test ? 'green' : 'red';
	?><p style="color:<?=$color?>"><?=$test ? 'TRUE' : 'FALSE'?> <?=$f?>:<?=$l?></p>
<?php
}//}}}
//显示测试项名称及开启状态
function gpfd_test_name($f, $l, $is_test, $name)
{//{{{
	?>
	<div style="color:<?=$is_test ? 'green' : 'blue'?>">
		[<?=$is_test ? '开启' : '关闭'?>] <?=$name?> <?=$f?>:<?=$l?>
	</div>
	<?php
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

//=============================== //debug/f/ 系列 ===============================
//允许自定义函数扩展 //debug/f/ 标签
function _gpfdf_callback($match)
{//{{{
	$gk = 'gpf_debug_current_file';
	$name = $match[1];
	$arg = $match[2];
	$func_name = "gpfdf_{$name}_callback";
	if (function_exists($func_name))
		{
		return $func_name($arg);
		}
	$func_name = "gpfdf_{$name}";
	if (!function_exists($func_name))
		{
		return $match[0];
		}
	//为方便调试信息的查看，首个参数总是文件路径和行号说明：
	$f = $GLOBALS[$gk];
	return "{$func_name}(\"<b>{$f}:\".__LINE__.\"</b><br />\", {$arg});";
}//}}}

//输出二维数组（一般用于输出数据库查询记录集）
function gpfdf_res($output, $res)
{//{{{
	$key = array_keys(reset($res));
	$output .= '<table class="" cellpadding="0" cellspacing="0" border="1" align="" style="font-size:12px;" ><tr><th>|';
	$output .= join("|</th><th>|", $key) . '|</th></tr>';
	foreach ($res as $r)
		{
		$output .= '<tr>';
		foreach ($r as $v)
			{
			$output .= '<td>' . var_export($v, 1) . '</td>';
			}
		$output .= '</tr>';
		}
	$output .= '</table>';
	gpfd_output($output);
}//}}}
