<?php
/*
2009-11-13
数组处理模块

前序：array_

//update
2010-1-9
去除 arrReg,arrSet,arrFil 函数——基本上被数据库辅函数替代
函数添加 array_ 前序
新增array_int
2009-11-13：
模块名由 form 改为 arr 。原意是表单处理模块，但表单就是post及get，而这两者于php中也是一个数组
而本模块的函数则可以用于数组。
*/
//==============================

//ggzhu 2010-6-13
//取得数组的维数(深度)
//数组需要是规则的，如矩阵
function array_depth($data)
{
	assert(is_array($data));
	
	$ret = 0;
	//用$tmp记录当前数组
	$tmp = $data;
	$i = 0;
	while(1)
		{
		//是数组则维数加1
		if (is_array($tmp))
			{
			$ret++;
			$tmp = current($tmp);
			}
		//非数组则退出循环
		else
			{
			break;
			}
		}
	return $ret;
}

//ggzhu 2010-6-13
//用递归求出数组的最大维数(深度)
function array_depth_max($data)
{
	//参数非数组则已到数组最未，维数为0
	if (!is_array($data))
		{
		return 0;
		}
	$ret = 1;
	$max = 0;
	$array_depth_max = __FUNCTION__;
	foreach ($data as $v)
		{
		$depth = $array_depth_max($v);
		if ($max < $depth)
			{
			$max = $depth;
			}
		}
	return $max + 1;
}

function array_one($arr)
{
//2010-1-9
//把数组强制转换为一维数组，如果数组元素为数组(多维)，则unset掉

//args
//$arr(array)

	if(is_array($arr))
	{
		foreach($arr as $key => $value)
		{
			if(is_array($value))
			{
				unset($arr[$key]);
			}
		}
		return $arr;
	}
	return false;
}

function array_csv2arr($fileName, $row = array(), $sum=0)
{
//2009-12-30
//从文件中读入并解析CSV字段，以数组的形式返回，返回数组每个元素为一行，每一行中有其对应的CSV值
//
	$check=is_file($fileName);
	if($check)
	{
		$fp=fopen($fileName,'rd');
		$buildArr=array();
		if($sum>0){
			$check=true;
			$temArr=fgetcsv($fp);
			if($temArr==false)
			{
				$check=false;
			}
			if($check&&count($temArr)!=$sum)
			{
				$check=false;
			}
			if(!$check)
			{
				fclose($fp);
				return false;
			}
			fseek($fp,0);
		}
		while($csv = fgetcsv($fp))
		{
			$tmp = array();
			foreach($csv as $key => $value)
			{
				if(isset($row[$key]))
				{
					$tmp[$row[$key]] = $value;
				}
				else
				{
					$tmp[$key] = $value;
				}
			}
			$buildArr[] = $tmp;
		}
		fclose($fp);
	}else{
		$buildArr=false;
	}
	return $buildArr;
}

//==============================
//==============================
//==============================

/*
	数据库矩阵函数集
	什么是数据库矩阵：
	定义语句：array(array('内容'))
			第一列	第二列	...
	第一行:	[0][0]	[0][1]	...
	第二行:	[1][0]	[1][0]	...
	...
	数组维数必须是以0开始的顺序数字
*/

function array_matrixData_csv($fp)
{
/*
	把CSV文件文件流中的内容以数据库矩阵的格式返回
	fp=CSV文件文件流
	调用者要保证文件流的正确性
*/
//2010-1-2：准备废弃
	$matrixData=array();
	$seek=0;
	while($row=fgetcsv($fp))
	{
		$matrixData[$seek]=$row;
		++$seek;
	}
	return $matrixData;
}
function array_matrixData_once($matrixData,$index=0)
{
/*
	去除数据库矩阵中index列的值重复的行，保留重复值第一次出现的行	
	调用者保证数据库矩阵格式正确且index不越界
*/
	$length=count($matrixData);
	$exist=array();
	for($i=0;$i<$length;++$i)
	{
		if(isset($exist[$matrixData[$i][$index]]))
		{
			unset($matrixData[$i]);
		}
		else
		{
			$exist[$matrixData[$i][$index]]=true;
		}
	}
	if($length!=count($matrixData))
	{
		$matrixData=array_merge($matrixData);
	}
	return $matrixData;
}
function array_matrixData_list($matrixData,$index=0,$linkStr=',')
{
/*
	把数据库矩阵index列的值合拼成字符串列表，各值之间以linkStr连接且列表中的值是唯一的
	返回一串字符串
	调用者保证数据库矩阵格式正确且index不越界
*/
	$length=count($matrixData);
	$list='';
	for($i=0;$i<$length-1;++$i)
	{
		$list.=$matrixData[$i][$index].$linkStr;
	}
	$list.=$matrixData[$i][$index];
	return $list;
}
function array_matrixData_index($matrixData,$index)
{
/*
	建立数据库矩阵index列的索引
	注意：如列值重复，索引指向最后出现的行
	调用者保证数据库矩阵格式正确且index不越界
	返回格式：一维数组
	[index列值]=>值所在行(数据库矩阵一维)
*/
	$length=count($matrixData);
	$rowIndex=array();
	for($i=0;$i<$length;++$i)
	{
		$rowIndex[$matrixData[$i][$index]]=$i;
	}
	return $rowIndex;
}

//==============================
//==============================
//==============================

//ggzhu 2010-6-7
//提取数组数据
//$field 为 需要的键列表字符串，多个键之间以","分隔,支持sql中select语句中字段的as语法。$data(array[])为数据数组
//如$field='a,b as theb,c',而$data中存在a b c d 4个键值,则会提取a b c三个键值的值，而其中b键值会被替换为"theb"
//注意，“,”左右不能有多余的空格，因为数组键值支持空格，写入多余的空格可能会使结果出错。
function array_sub($data, $field)
{
	$ret = array();
	$fields = explode(',', $field);
	foreach ($fields as $value)
		{
		//搜索字符串中是否含有“as”字符
		$seek = stripos($value, ' as ');
		if (false !== $seek)
			{
			$key = substr($value, 0, $seek);
			$map = substr($value, $seek + 4);
			}
		else
			{
			$key = $map = $value;
			}
		//进行数据的提取
		if (isset($data[$key]))
			{
			$ret[$map] = $data[$key];
			}
		}
	return $ret;
}
?>