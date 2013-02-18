<?php 
require 'bmp.func.php';
class image
{
	var $w_pct = 100;
	var $interlace = 0;

	function image()
	{
	}

	function info($img) 
	{
		$imageinfo = getimagesize($img);
		if($imageinfo === false) return false;
		$imagetype = strtolower(substr(image_type_to_extension($imageinfo[2]),1));
		$imagesize = filesize($img);
		$info = array(
			'width'=>$imageinfo[0],
			'height'=>$imageinfo[1],
			'type'=>$imagetype,
			'size'=>$imagesize,
			'mime'=>$imageinfo['mime']
		);
		return $info;
	}

	function thumb($image, $filename = '', $maxwidth = 200, $maxheight = 50, $suffix='_thumb') 
	{
		ini_set('gd.jpeg_ignore_warning', 1);
		if(!$this->check($image)) return false;
		$info  = image::info($image);
		if($info === false) return false;
		$srcwidth  = $info['width'];
		$srcheight = $info['height'];
		if (!$srcwidth || !$srcheight)
			{
			log::add("图片长宽非法 w[{$srcwidth}] h[{$srcheight}]", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return false;
			}
		$type = strtolower($info['type']);
		unset($info);
		$scale_max = max($srcwidth/$maxwidth, $srcheight/$maxheight);
		if($scale_max<1){
			$width  = $srcwidth;
			$height = $srcheight;		
		}else{
			$scale = min($maxwidth/$srcwidth, $maxheight/$srcheight);
			$width  = ceil($srcwidth*$scale);
			$height = ceil($srcheight*$scale);
		}
		$createfun = 'imagecreatefrom'.($type=='jpg' ? 'jpeg' : $type);
		$srcimg = @$createfun($image);
		if (!$srcimg)
			{
			return false;
			}
		if($type != 'gif' && function_exists('imagecreatetruecolor'))
			{
			$thumbimg = imagecreatetruecolor($width, $height); 
			}
		else
			{
			$thumbimg = imagecreate($width, $height); 
			}
		if (!$thumbimg)
			{
			log::add("无法新建图象 w[{$width}] h[{$height}]", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return false;
			}
		if(function_exists('imagecopyresampled'))
			imagecopyresampled($thumbimg, $srcimg, 0, 0, 0, 0, $width, $height, $srcwidth, $srcheight); 
		else
			imagecopyresized($thumbimg, $srcimg, 0, 0, 0, 0, $width, $height,  $srcwidth, $srcheight); 
		if($type=='gif' || $type=='png')
			{
			$background_color  =  imagecolorallocate($thumbimg,  0, 255, 0);  //  指派一个绿色  
			imagecolortransparent($thumbimg, $background_color);  //  设置为透明色，若注释掉该行则输出绿色的图 
			}
		if($type=='jpg' || $type=='jpeg') imageinterlace($thumbimg, $this->interlace);
		$imagefun = 'image'.($type=='jpg' ? 'jpeg' : $type);
		if(empty($filename)) $filename  = substr($image, 0, strrpos($image, '.')).$suffix.'.'.$type;
		$imagefun($thumbimg, $filename);
		imagedestroy($thumbimg);
		imagedestroy($srcimg);
		return $filename;
	}

	function watermark($source, $target = '', $w_pos = 0, $w_img = '', $w_text = '', $w_font = 5, $w_color = '#333333', $w_quality = 100)
	{
		if(!$this->check($source)) return false;
		if(!$target) $target = $source;
		if(preg_match("/\.gif/", $source) && $w_img) $w_img = preg_replace("/\.png/",".gif",$w_img);
		$source_info = getimagesize($source);
		$source_w    = $source_info[0];
		$source_h    = $source_info[1];
		//eto:2009-03-19  检测图片大小，太小不加水印
		if($source_w<200 || $source_h<200)
			{
			$w_img = '';
			$w_text = $w_text?$w_text:'yj518.com';
			}
		switch($source_info[2])
			{
			case 1 :
				$source_img = imagecreatefromgif($source);
				break;
			case 2 :
				$source_img = imagecreatefromjpeg($source);
				break;
			case 3 :
				$source_img = imagecreatefrompng($source);
				break;
			case 6 :
				$source_img = imagecreatefrombmp($source);
				break;
			default :
				return false;
			}
		if(!empty($w_img) && !preg_match("/".str_replace("/","\/",PHPCMS_ROOT)."/",$w_img)) $w_img = PHPCMS_ROOT.$w_img;
		if(!empty($w_img) && file_exists($w_img))
			{
			$ifwaterimage = 1;
			$water_info   = getimagesize($w_img);
			$width        = $water_info[0];
			$height       = $water_info[1];
			switch($water_info[2])
				{
				case 1 :
					$water_img = imagecreatefromgif($w_img);
					break;
				case 2 :
					$water_img = imagecreatefromjpeg($w_img);
					break;
				case 3 :
					$water_img = imagecreatefrompng($w_img);
					break;
				case 6 :
					$water_img = imagecreatefrombmp($w_img);
					break;
				default :
					return;
				}
			}
		else
			{
			$ifwaterimage = 0;
			$temp = @imagettfbbox(ceil($w_font*2.5), 0, 'include/fonts/cour.ttf', $w_text);//取得使用 truetype 字体的文本的范围
			$width = $temp[2] - $temp[6];
			$height = $temp[3] - $temp[7];
			unset($temp);
			}
		switch($w_pos)
			{
			case 0:
				$wx = rand(0,($source_w - $width));
				$wy = rand(0,($source_h - $height));
				break;
			case 1:
				$wx = 5;
				$wy = 5;
				break;
			case 2:
				$wx = ($source_w - $width) / 2;
				$wy = 5;
				break;
			case 3:
				$wx = $source_w - $width;
				$wy = 5;
				break;
			case 4:
				$wx = 5;
				$wy = ($source_h - $height) / 2;
				break;
			case 5:
				$wx = ($source_w - $width) / 2;
				$wy = ($source_h - $height) / 2;
				break;
			case 6:
				$wx = $source_w - $width;
				$wy = ($source_h - $height) / 2;
				break;
			case 7:
				$wx = 5;
				$wy = $source_h - $height - 5;
				break;
			case 8:
				$wx = ($source_w - $width) / 2;
				$wy = $source_h - $height - 5;
				break;
			case 9:
				$wx = $source_w - $width - 5;
				$wy = $source_h - $height - 5;
				break;
			default:
				$wx = rand(0,($source_w - $width));
				$wy = rand(0,($source_h - $height));
				break;
			}
		if($ifwaterimage)
			{
			//imagecopymerge($source_img, $water_img, $wx, $wy, 0, 0, $width,$height,$this->w_pct);
			imagecopy($source_img, $water_img, $wx, $wy, 0, 0, $width,$height);
			}
		else
			{
			if(!empty($w_color) && (strlen($w_color)==7))
				{
				$r = hexdec(substr($w_color,1,2));
				$g = hexdec(substr($w_color,3,2));
				$b = hexdec(substr($w_color,5));
				}
			else
				{
				return;
				}
			imagestring($source_img,$w_font,$wx,$wy,$w_text,imagecolorallocate($source_img,$r,$g,$b));
			}
		switch($source_info[2])
			{
			case 1 :
				imagegif($source_img, $target);
				break;
			case 2 :
				imagejpeg($source_img, $target, $w_quality);
				break;
			case 3 :
				imagepng($source_img, $target);
				break;
			case 6 :
				imagebmp($source_img, $target);
				break;
			default :
				return;
			}
		if(isset($water_info))
			{
			unset($water_info);
			}
		if(isset($water_img))
			{
			imagedestroy($water_img);
			}
		unset($source_info);
		imagedestroy($source_img);
	}

	function check($image)
	{
		return extension_loaded('gd') && preg_match("/\.(jpg|jpeg|gif|png|bmp)/", $image, $m) && file_exists($image) && function_exists('imagecreatefrom'.($m[1] == 'jpg' ? 'jpeg' : $m[1]));
	}
}
?>
