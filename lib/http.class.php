<?php
/*
edit:
2011-07-06

http 类
*/
//解码 gzip 压缩的数据
function gzdecode($data)
{
	$flags = ord(substr($data, 3, 1));
	$headerlen = 10;
	$extralen = 0;
	//$filenamelen = 0;
	if ($flags & 4)
		{
		$extralen = unpack('v' ,substr($data, 10, 2));
		$extralen = $extralen[1];
		$headerlen += 2 + $extralen;
		}
	if ($flags & 8) // Filename
		$headerlen = strpos($data, chr(0), $headerlen) + 1;
	if ($flags & 16) // Comment
		$headerlen = strpos($data, chr(0), $headerlen) + 1;
	if ($flags & 2) // CRC at end of file
		$headerlen += 2;
	$unpacked = gzinflate(substr($data, $headerlen));
	$unpacked = $unpacked === FALSE ? $data : $unpacked;
	return $unpacked;
}

//还原 Transfer-Encoding: chunked 的数据
function unchunk($data)
{
	$fp = 0;
	$outData = "";
	while ($fp < strlen($data))
		{
		$rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
		$num = hexdec(trim($rawnum));
		$fp += strlen($rawnum);
		$chunk = substr($data, $fp, $num);
		$outData .= $chunk;
		$fp += strlen($chunk);
		}
	return $outData;
}

//新建
//$method post/get
function head_init($url, $method = 'GET')
{
	$method = strtoupper($method) == 'POST' ? 'POST' : 'GET';
	return array(
		"is_head" => true,
		"url" => $url,
		"method" => $method,
		"Accept" => "*/*",
		"Referer" => "",	//来源页
		"Accept-Language" => "zh-cn",
		"Accept-Encoding" => "identity",	//如用 "gzip, deflate" 服务器可能会返回 gzip 压缩的数据
		"User-Agent" => $_SERVER['HTTP_USER_AGENT'],
		"Cache-Control" => "no-cache",
		"cookie" => array(),
		"upload" => array(),
		"post" => array(),
		);
}

//设置头标
//$value 如为空则删除指定的信息
//设置cookie，三种形式
	//(array(name=value,...))
	//(string=cookie_str)	cookie头信息,如 name=haha;
//post 数据
	//$value(array)
//upload 上传文件
	//$value(string)(array)	一个(string)或多个(array)文件路径
function head_set(&$head, $name, $value = '')
{
	if (!$value)
		{
		if (in_array($name, array('post', 'upload', 'cookie')))
			{
			$head[$name] = array();
			}
		else
			{
			unset($head[$name]);
			}
		return true;
		}
	if (!is_array($head[$name]))
		{
		$head[$name] = $value;
		return true;
		}
	$value = (array)$value;
	$head[$name] = array_merge($head[$name], $value);
	return true;
}

//生成头信息
function head_make($head)
{
	if (!$head['is_head'])
		{
		return false;
		}
	$url = $head["url"];
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = $matches['port'] ? $matches['port'] : 80;
	$head['Host'] = $host;
	//避免以 get 提交 post 格式的数据
	$method = ($head['post'] || $head['upload']) ? 'POST' : $head['method'];
	$out = "{$method} $path HTTP/1.1\r\n";
	//post,upload,cookie 是未格式化的数组头标数据
	$post = $head['post'];
	$upload = $head['upload'];
	$cookie = $head['cookie'];
	unset($head['post'], $head["upload"], $head["cookie"], $head["is_head"], $head["url"], $head["method"]);
	//组成纯文本头标
	foreach ($head as $k => $v)
		{
		$out .= "{$k}: {$v}\r\n";
		}
	//格式化数组头标
	if ($cookie) $out .= "Cookie: "._h_make_cookie($cookie)."\r\n";
	if ($post || $upload)
		{
		$out .= $upload ? _h_make_upload($post, $upload) : _h_make_post($post);
		}
	else
		{
		$out .= "Connection: Close\r\n\r\n";
		}
	return $out;
}

function _h_make_cookie($cookie)
{
	$cookie = (array)$cookie;
	$ret = '';
	foreach ($cookie as $k => $v)
		{
		if (is_int($k))
			{
			$ret .= $v;
			}
		else
			{
			$ret .= "{$k}={$v};";
			}
		}
	return $ret;
}
//only post
function _h_make_post($post)
{
	$ret = "Content-Type: application/x-www-form-urlencoded\r\n";
	$out = '';
	$middle = "";
	foreach ($post as $k => $v)
		{
		$out .= $middle . $k.'='.rawurlencode($v);
		$middle = "&";
		}
	$ret .= "Content-Length: ".strlen($out)."\r\n";
	$ret .= "Connection: Close\r\n\r\n";
	$ret .= $out;
	return $ret;
}
//post and upload
function _h_make_upload($post, $upload)
{
	$boundary = "AaB03x";
	$ret = "Content-Type: multipart/form-data; boundary=$boundary\r\n";
	$out = '';
	foreach ($post as $k => $v)
		{
		$out .= "--$boundary\r\n";
		$out .= "Content-Disposition: form-data; name=\"".$k."\"\r\n";
		$out .= "\r\n".$v."\r\n";
		$out .= "--$boundary\r\n";
		}
	foreach ($upload as $k => $v)
		{
		$out .= "--$boundary\r\n";
		$out .= "Content-Disposition: file; name=\"$k\"; filename=\"".basename($v)."\"\r\n";
		$out .= "Content-Type: ".get_mime($v)."\r\n";
		$out .= "\r\n".file_get_contents($v)."\r\n";
		$out .= "--$boundary\r\n";
		}
	$out .= "--$boundary--\r\n";
	$ret .= "Content-Length: ".strlen($out)."\r\n";
	$ret .= "Connection: Close\r\n\r\n";
	$ret .= $out;
	return $ret;
}
class http
{
	var $method;
	var $cookie;
	var $post;
	var $header;		//接收的头信息
	public $out = '';	//发出的头信息
	var $ContentType;
	var $errno;
	var $errstr;

	function __construct()
	{
		$this->init();
	}

	//初始化，如请求多个链接，在每次请求结束后执行
	function init()
	{
		$this->method = 'GET';
		$this->cookie = '';
		$this->post = '';
		$this->header = '';
		$this->errno = 0;
		$this->errstr = '';
	}

	function post($url, $data = array(), $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
	{
		$this->method = 'POST';
		$this->ContentType = "Content-Type: application/x-www-form-urlencoded\r\n";
		if($data)
		{
			$post = '';
			foreach($data as $k=>$v)
			{
				$post .= $k.'='.rawurlencode($v).'&';
			}
			$this->post .= substr($post, 0, -1);
		}
		return $this->request($url, $referer, $limit, $timeout, $block);
	}

	function get($url, $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
	{
		$this->method = 'GET';
		return $this->request($url, $referer, $limit, $timeout, $block, $is_header);
	}

	function upload($url, $data = array(), $files = array(), $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
	{
		$this->method = 'POST';
		$boundary = "AaB03x";
		$this->ContentType = "Content-Type: multipart/form-data; boundary=$boundary\r\n";
		if($data)
		{
			foreach($data as $k => $v)
			{ 
				$this->post .= "--$boundary\r\n"; 
				$this->post .= "Content-Disposition: form-data; name=\"".$k."\"\r\n"; 
				$this->post .= "\r\n".$v."\r\n"; 
				$this->post .= "--$boundary\r\n";
			}
		}
		foreach($files as $k=>$v)
		{
			$this->post .= "--$boundary\r\n"; 
			$this->post .= "Content-Disposition: file; name=\"$k\"; filename=\"".basename($v)."\"\r\n"; 
			$this->post .= "Content-Type: ".$this->get_mime($v)."\r\n"; 
			$this->post .= "\r\n".file_get_contents($v)."\r\n"; 
			$this->post .= "--$boundary\r\n"; 
		}
		$this->post .= "--$boundary--\r\n";
		return $this->request($url, $referer, $limit, $timeout, $block);
	}

	function request($url, $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
	{
		//生成头信息
		$matches = parse_url($url);
		$host = $matches['host'];
		$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
		$port = $matches['port'] ? $matches['port'] : 80;
		if($referer == '') $referer = URL;
		$out = "$this->method $path HTTP/1.1\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Referer: $referer\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		//$out .= "Accept-Encoding: gzip, deflate\r\n";	//用这个服务器可能会返回 gzip 压缩的数据
		$out .= "Accept-Encoding: identity\r\n";
		$out .= "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
		$out .= "Host: $host\r\n";
		if($this->cookie) $out .= "Cookie: $this->cookie\r\n";
		if($this->method == 'POST')
		{
			$out .= $this->ContentType;
			$out .= "Content-Length: ".strlen($this->post)."\r\n";
			$out .= "Cache-Control: no-cache\r\n";
			$out .= "Connection: Close\r\n\r\n";
			$out .= $this->post;
		}
		else
		{
			$out .= "Connection: Close\r\n\r\n";
		}
		$this->out = $out;

		//链接
		//if($timeout > ini_get('max_execution_time')) @set_time_limit($timeout);	//ggzhu 2010-10-20 这句会引发 Fatal error[致命] 错误
		$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$fp)
			{
			$this->errno = $errno;
			$this->errstr = $errstr;
			return false;
			}
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		fwrite($fp, $out);
		$this->data = '';
		$status = stream_get_meta_data($fp);

		if ($status['timed_out'])
			{
			$this->errstr = '发送数据超时';
			fclose($fp);
			return false;
			}
		$maxsize = min($limit, 1024000);
		if($maxsize == 0) $maxsize = 1024000;
		//$start = false;
		//接收头信息
		while(!feof($fp))
			{
			$line = fgets($fp);
			$this->header .= $line;
			if ($line == "\r\n" || $line == "\n")
				{
				break;
				}
			$status = stream_get_meta_data($fp);
			if ($status["timed_out"])
				{
				$this->errstr = "接收头信息超时";
				fclose($fp);
				return false;
				}
			}
		//接收正文
		while(!feof($fp))
			{
			$line = fread($fp, $maxsize);
			if(strlen($this->data) > $maxsize) break;
			$this->data .= $line;
			$status = stream_get_meta_data($fp);
			if ($status["timed_out"])
				{
				$this->errstr = "接收正文超时";
				fclose($fp);
				return false;
				}
			}
		fclose($fp);
		return $this->is_ok();
	}

	/*
	function save($file)
	{
		dir_create(dirname($file));
		return file_put_contents($file, $this->data);
	}
	*/

	//设置cookie，三种形式
		//(array(name=value,...))
		//(string=name, string=value)
		//(string=cookie_str)	cookie头信息
	function set_cookie($name, $value = '')
	{
		if (is_string($name) && !$value)
			{
			$this->cookie .= $name;
			return true;
			}
		$cookie = is_array($name) ? $name : array($name => $value);
		foreach ($cookie as $k => $v)
			{
			$this->cookie .= "$k=$v; ";
			}
		$this->cookie = substr($this->cookie, 0, -2);
		return true;
	}

	//取服务器返回的 cookie 信息
	function get_cookie()
	{
		$cookies = array();
		if(preg_match_all("/Set-Cookie: (.*)/", $this->header, $m))
		{
			foreach ($m[1] as $v)
				{
				$v = str_replace(array("\r", "\n"), '', $v);
				foreach(explode("; ", $v) as $c)
					{
					list($k, $v) = explode('=', $c);
					$cookies[$k] = $v;
					}
				}
		}
		return $cookies;
	}

	//返回接收的页面内容
	//$auto	是否自动处理 chunked 及 gzip 的情况
	function get_data($auto = true)
	{
		//如果返回的内容是乱码，可能是服务器无视发送的 header.Accept-Encoding 信息，内容直接用 gzip 编码
		//之前此种情况是使用 MSXML2.XMLHTTP 下载，它能自动进行解码，但 MSXML2.XMLHTTP 的通用性不好，较多网站让它一下就变为乱码了，而且不好控制。
		//所以，正解应是判断返回头信息中 Content-Encoding 是否为 gzip，是则对内容进行解码。
		$data = $this->data;
		if ($auto)
			{
			if ('chunked' == $this->get_header('Transfer-Encoding'))
				{
				$data = unchunk($data);
				}
			if ('gzip' == $this->get_header("Content-Encoding"))
				{
				$data = gzdecode($data);
				}
			}
		return $data;
	}

	//返回 接收的头信息
	//$name	可指定返回某个头信息值,如返回的头信息有 Transfer-Encoding: chunked\r\n 则输入 Transfer-Encoding 返回 chunked
	function get_header($name = '')
	{
		if (!$name)
			{
			return $this->header;
			}
		preg_match("/{$name}: (.*)\r\n/i", $this->header, $match);
		return $match[1];
	}

	function get_status()
	{
		preg_match("|^HTTP/1.1 ([0-9]{3}) (.*)|", $this->header, $m);
		return array($m[1], $m[2]);
	}

	function get_mime($file)
	{
		$ext = fileext($file);
		if($ext == '') return '';
		$mime_types = cache_read('mime.inc.php', PHPCMS_ROOT.'include/');
		return isset($mime_types[$ext]) ? $mime_types[$ext] : '';
	}

	function is_ok()
	{
		$status = $this->get_status();
		if(intval($status[0]) != 200)
		{
			$this->errno = $status[0];
			$this->errstr = $status[1];
			return false;
		}
		return true;
	}

	function errno()
	{
		return $this->errno;
	}

	function errmsg()
	{
		return $this->errstr;
	}
}
?>
