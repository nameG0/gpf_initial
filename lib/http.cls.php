<?php
/*
edit:
2011-07-06

http ��
*/
//���� gzip ѹ��������
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

//��ԭ Transfer-Encoding: chunked ������
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

//�½�
//$method post/get
function head_init($url, $method = 'GET')
{
	$method = strtoupper($method) == 'POST' ? 'POST' : 'GET';
	return array(
		"is_head" => true,
		"url" => $url,
		"method" => $method,
		"Accept" => "*/*",
		"Referer" => "",	//��Դҳ
		"Accept-Language" => "zh-cn",
		"Accept-Encoding" => "identity",	//���� "gzip, deflate" ���������ܻ᷵�� gzip ѹ��������
		"User-Agent" => $_SERVER['HTTP_USER_AGENT'],
		"Cache-Control" => "no-cache",
		"cookie" => array(),
		"upload" => array(),
		"post" => array(),
		);
}

//����ͷ��
//$value ��Ϊ����ɾ��ָ������Ϣ
//����cookie��������ʽ
	//(array(name=value,...))
	//(string=cookie_str)	cookieͷ��Ϣ,�� name=haha;
//post ����
	//$value(array)
//upload �ϴ��ļ�
	//$value(string)(array)	һ��(string)����(array)�ļ�·��
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

//����ͷ��Ϣ
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
	//������ get �ύ post ��ʽ������
	$method = ($head['post'] || $head['upload']) ? 'POST' : $head['method'];
	$out = "{$method} $path HTTP/1.1\r\n";
	//post,upload,cookie ��δ��ʽ��������ͷ������
	$post = $head['post'];
	$upload = $head['upload'];
	$cookie = $head['cookie'];
	unset($head['post'], $head["upload"], $head["cookie"], $head["is_head"], $head["url"], $head["method"]);
	//��ɴ��ı�ͷ��
	foreach ($head as $k => $v)
		{
		$out .= "{$k}: {$v}\r\n";
		}
	//��ʽ������ͷ��
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
	var $header;		//���յ�ͷ��Ϣ
	public $out = '';	//������ͷ��Ϣ
	var $ContentType;
	var $errno;
	var $errstr;

	function __construct()
	{
		$this->init();
	}

	//��ʼ���������������ӣ���ÿ�����������ִ��
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
		//����ͷ��Ϣ
		$matches = parse_url($url);
		$host = $matches['host'];
		$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
		$port = $matches['port'] ? $matches['port'] : 80;
		if($referer == '') $referer = URL;
		$out = "$this->method $path HTTP/1.1\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Referer: $referer\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		//$out .= "Accept-Encoding: gzip, deflate\r\n";	//��������������ܻ᷵�� gzip ѹ��������
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

		//����
		//if($timeout > ini_get('max_execution_time')) @set_time_limit($timeout);	//ggzhu 2010-10-20 �������� Fatal error[����] ����
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
			$this->errstr = '�������ݳ�ʱ';
			fclose($fp);
			return false;
			}
		$maxsize = min($limit, 1024000);
		if($maxsize == 0) $maxsize = 1024000;
		//$start = false;
		//����ͷ��Ϣ
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
				$this->errstr = "����ͷ��Ϣ��ʱ";
				fclose($fp);
				return false;
				}
			}
		//��������
		while(!feof($fp))
			{
			$line = fread($fp, $maxsize);
			if(strlen($this->data) > $maxsize) break;
			$this->data .= $line;
			$status = stream_get_meta_data($fp);
			if ($status["timed_out"])
				{
				$this->errstr = "�������ĳ�ʱ";
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

	//����cookie��������ʽ
		//(array(name=value,...))
		//(string=name, string=value)
		//(string=cookie_str)	cookieͷ��Ϣ
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

	//ȡ���������ص� cookie ��Ϣ
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

	//���ؽ��յ�ҳ������
	//$auto	�Ƿ��Զ����� chunked �� gzip �����
	function get_data($auto = true)
	{
		//������ص����������룬�����Ƿ��������ӷ��͵� header.Accept-Encoding ��Ϣ������ֱ���� gzip ����
		//֮ǰ���������ʹ�� MSXML2.XMLHTTP ���أ������Զ����н��룬�� MSXML2.XMLHTTP ��ͨ���Բ��ã��϶���վ����һ�¾ͱ�Ϊ�����ˣ����Ҳ��ÿ��ơ�
		//���ԣ�����Ӧ���жϷ���ͷ��Ϣ�� Content-Encoding �Ƿ�Ϊ gzip����������ݽ��н��롣
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

	//���� ���յ�ͷ��Ϣ
	//$name	��ָ������ĳ��ͷ��Ϣֵ,�緵�ص�ͷ��Ϣ�� Transfer-Encoding: chunked\r\n ������ Transfer-Encoding ���� chunked
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
