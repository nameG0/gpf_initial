<?php
/**
 * 附件模块公共函数（相当于 API）
 * 2011-10-16
 * 
 * @package default
 * @filesource
 */

//生成一个当前日期及随机数组合的数字形文件名
function atta_make_name($fileext)
{//{{{
	$fileext = $fileext ? '.' . $fileext : '';
	return date('Ymdhis') . rand(100, 999) . $fileext;
}//}}}

/**
 * 格式化 $_FILES 数据，改为一维数组
 * 键 _is_error 是原 error 的简化，只有 true/false 两种值,true表示上传成功
 * 键 _fileext 后缀
 */
function atta_upload_init($field)
{//{{{
	$upload = array();
	$files = $_FILES[$field];
	if (!$files)
		{
		return $upload;
		}
	if (!is_array($files['error']))
		{
		$tmp = array();
		foreach ($files as $k => $v)
			{
			$tmp[$k][0] = $v;
			}
		$files = $tmp;
		unset($tmp);
		}
	foreach ($files['error'] as $key => $error)
		{
		$fileext = trim(substr(strrchr($files['name'][$key], '.'), 1, 10));
		$dir = date("Y/md/");
		$name = atta_make_name($fileext);
		$upload[$key] = array(
			"error" => $error,
			'tmp_name' => $files['tmp_name'][$key],
			'name' => $files['name'][$key],
			'type' => $files['type'][$key],
			'size' => $files['size'][$key],

			"_is_error" => $error !== UPLOAD_ERR_OK,
			//是否应发出警告，除上上传成功或没文件上传外，都应发出警告
			"_is_waring" => ($error !== UPLOAD_ERR_OK && $error !== UPLOAD_ERR_NO_FILE),
			//错误信息
			"_error" => '',
			"_name" => $name,
			"_fileext" => $fileext,
			"_dir" => $dir,
			"_fulldir" => UPLOAD_ROOT . $dir,
			"_path" => $dir . $name,
			"_fullpath" => UPLOAD_ROOT . $dir . $name,
			"_url" => UPLOAD_URL . $dir . $name,
			);
		if ($upload[$key]['_is_error'])
			{
			$msg = '';
			switch ($error)
				{
				case UPLOAD_ERR_INI_SIZE:
					$msg = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。';
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$msg = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。';
					break;
				case UPLOAD_ERR_PARTIAL:
					$msg = '文件只有部分被上传。';
					break;
				case UPLOAD_ERR_NO_FILE:
					$msg = '没有文件被上传。';
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$msg = '找不到临时文件夹。';
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$msg = '文件写入失败。';
					break;
				}
			$upload[$key]['_error'] = $msg;
			unset($msg);
			}
		}
	return $upload;
}//}}}

/**
 * 过滤上传文件，参数分别为：允许的后缀，禁止的后缀，最小大小，最大大小。后缀以","分隔多个值，如"jpge,jpg,png"
 * @return void
 */
function atta_upload_filter(& $upload, $allow_ext = UPLOAD_ALLOWEXT, $deny_ext = UPLOAD_DENYEXT, $minsize = UPLOAD_MINSIZE, $maxsize = UPLOAD_MAXSIZE)
{//{{{
	if ($allow_ext)
		{
		$tmp = explode(",", $allow_ext);
		foreach ($upload as $k => $v)
			{
			if ($v['_is_error'])
				{
				continue;
				}
			if (!in_array($v['_fileext'], $tmp))
				{
				$upload[$k]['_is_error'] = true;
				$upload[$k]['_is_waring'] = true;
				$upload[$k]['_error'] = "fileext not allow";
				}
			}
		}
	if ($deny_ext)
		{
		$tmp = explode(",", $deny_ext);
		foreach ($upload as $k => $v)
			{
			if ($v['_is_error'])
				{
				continue;
				}
			if (in_array($v['_fileext'], $tmp))
				{
				$upload[$k]['_is_error'] = true;
				$upload[$k]['_is_waring'] = true;
				$upload[$k]['_error'] = 'fileext deny';
				}
			}
		}
	if ($minsize)
		{
		foreach ($upload as $k => $v)
			{
			if ($v['_is_error'])
				{
				continue;
				}
			if ($v['size'] < $minsize)
				{
				$upload[$k]['_is_error'] = true;
				$upload[$k]['_is_waring'] = true;
				$upload[$k]['_error'] = 'file size too small';
				}
			}
		}
	if ($maxsize)
		{
		foreach ($upload as $k => $v)
			{
			if ($v['_is_error'])
				{
				continue;
				}
			if ($v['size'] > $maxsize)
				{
				$upload[$k]['_is_error'] = true;
				$upload[$k]['_is_waring'] = true;
				$upload[$k]['_error'] = 'file size too big';
				}
			}
		}
}//}}}

//快速上传已格式化的 $upload 数据，文件保存到原 _fullpath 指向的路径。
//$upload	已 atta_upload_format 化的数据
//return $upload _is_upload 标记是否上传成功
function atta_upload_move(& $upload)
{//{{{
	foreach ($upload as $k => $v)
		{
		if ($v['_is_error'])
			{
			continue;
			}
		mkdiri($v['_fulldir']);
		$upload[$k]['_is_upload'] = move_uploaded_file($v['tmp_name'], $v['_fullpath']);
		}
}//}}}

function atta_upload_close(& $upload)
{//{{{
	$upload = NULL;
}//}}}
