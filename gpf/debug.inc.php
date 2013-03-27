<?php
/**
 * GPF DEBUG 模式
 * 
 * @package default
 * @filesource
 */
$GLOBALS['gpf_debug_fp'] = NULL; //调试信息输出文件指针
$GLOBALS['gpf_debug_current_file'] = ''; //当前处理的PHP文件绝对路径
/**
 * PHP文件开启DEBUG模式入口
 * @param string $file 待调试的PHP文件绝对路径（传入__FILE__即可）
 */
function gpfd_file($file)
{//{{{
	$gk = 'gpf_debug_fp';
	$gk_file = 'gpf_debug_current_file';
	$GLOBALS[$gk_file] = addslashes($file);
	if (NULL === $GLOBALS[$gk])
		{
		//初始化
		$output = date("ymd") . $_SERVER['PHP_SELF'] . '_' . md5($_SERVER['REQUEST_URI']) . '.html';
		$output = str_replace('/', '_', $output);
		$GLOBALS[$gk] = fopen(GPF_DEBUG_OUTPUT . $output, 'wb');
		if (!$GLOBALS[$gk])
			{
			exit("无法创建DEBUG信息输出文件 {$output}");
			}
		unset($output);
		//写入初始信息
		$url = "http://{$_SERVER["SERVER_NAME"]}{$_SERVER["REQUEST_URI"]}";
		_gpfd_output("<a href=\"{$url}\" target=\"_blank\">{$url}</a><hr />");
		ob_start();
		}

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
