<?php
class Image {
	public $error='';

	//获取图像信息
	public function getImageInfo($filename){
		$imageInfo=getImagesize($filename);
		if(false===$imageInfo){
			return false;
		}
		$info=array(
			'width'=>$imageInfo[0],
			'height'=>$imageInfo[1],
			'mime'=>$imageInfo['mime'],
			'ext'=>strtolower(image_type_to_extension($imageInfo[2],false)),
			'createFun'=>str_replace('/','createfrom',$imageInfo['mime']),
			'outFun'=>str_replace('/',null,$imageInfo['mime'])
		);
		return $info;
	}
	//获取图片缩略图
	public function thumb($filename,$thumbPath='thumb',$prefix='thumb',$maxW=200,$maxH=200,$delSource=false){
		$imgInfo=$this->getImageInfo($filename);
		if(false===$imgInfo){
			$this->error=$filename.'不是真实图片';
			return false;
		}
		//得到原始图像的宽和高
		$srcW=$imgInfo['width'];
		$srcH=$imgInfo['height'];
		//得到缩放比例
		$ratio_orig=$srcW/$srcH;
		if($maxW/$maxH>$ratio_orig){
			$maxW=$maxH*$ratio_orig;
		}else{
			$maxH=$maxW/$ratio_orig;
		}
		//创建原图画布资源
		$createFun=$imgInfo['createFun'];
		if(!function_exists($createFun)){
			$this->error=$createFun.'函数不支持，请先开启相关扩展';
			return false;
		}
		$srcImg=$createFun($filename);
		//创建缩略图
		if(function_exists('imagecreatetruecolor')){
			$dstImg=imagecreatetruecolor($maxW,$maxH);
		}else{
			$dstImg=imagecreate($maxW,$maxH);
		}
		if(function_exists('imagecopyresampled')){
			imagecopyresampled($dstImg,$srcImg,0,0,0,0,$maxW,$maxH,$srcW,$srcH);
		}else{
			imagecopyresized($dstImg,$srcImg,0,0,0,0,$maxW,$maxH,$srcW,$srcH);
		}
		$outFun=$imgInfo['outFun'];
		$this->makeDir($thumbPath);
		$basename=basename($filename);
		$rand=mt_rand(100000,999999);
		$dstName="{$prefix}_{$rand}_{$basename}";
		$dst=$thumbPath.'/'.$dstName;
		if(file_exists($dst)){
			$this->error='目标目录存在同名文件，请重新命名后进行操作';
			return false;
		}
		$outFun($dstImg,$dst);
		imagedestroy($dstImg);
		imagedestroy($srcImg);
		if($delSource){
			@unlink($filename);
		}
		return $dst;
	}
	//文字水印
	public function waterText($filename,$fontFile,$text='KVS',$pos=0,$color=array(255,0,0),$waterPath='waterText',$prefix='waterText',$fontSize=24,$fontAngle=0,$alpha=50,$delSource=false){
		if(!file_exists($filename)){
			$this->error = $filename.'文件不存在';
			return false;
		}
		$imgInfo=$this->getImageInfo($filename);
		$image=$imgInfo['createFun']($filename);
		$rect=imagettfbbox($fontSize,$fontAngle,$fontFile,$text);
		$minX=min(array($rect[0],$rect[2],$rect[4],$rect[6]));
		$maxX=max(array($rect[0],$rect[2],$rect[4],$rect[6]));
		$minY=min(array($rect[1],$rect[3],$rect[5],$rect[7]));
		$maxY=max(array($rect[1],$rect[3],$rect[5],$rect[7]));
		$width=$maxX-$minX;
		$height=$maxY-$minY;
		//计算书写坐标位置
		list($x,$y)=$this->getWaterPos($pos,$imgInfo['width'],$imgInfo['height'],$width,$height);
		$y=$y?:$fontSize*2;
		$color=imagecolorallocatealpha($image,$color[0],$color[1],$color[2],$alpha);
		imagettftext($image,$fontSize,$fontAngle,$x,$y,$color,$fontFile,$text);
		$this->makeDir($waterPath);
		$basename=basename($filename);
		$rand=mt_rand(100000,999999);
		$dstName="{$prefix}_{$rand}_{$basename}";
		$dst=$waterPath.'/'.$dstName;
		if(file_exists($dst)){
			$this->error='目标目录存在同名文件，请重新命名后操作';
			return false;
		}
		$imgInfo['outFun']($image,$dst);
		if($delSource){
			@unlink($filename);
		}
		return $dst;
	}
	//图片水印
	public function waterPic($filename,$waterPic,$pos=0,$pct=50,$waterPicPath='waterPic',$delSource=false,$prefix='waterPic'){
		if(!file_exists($filename)||!file_exists($waterPic)){
			$this->error=$filename.'源文件或水印图片不存在';
			return false;
		}
		$fileInfo=$this->getImageInfo($filename);
		$waterInfo=$this->getImageInfo($waterPic);
		$file=$fileInfo['createFun']($filename);
		$water=$waterInfo['createFun']($waterPic);
		list($x,$y)=$this->getWaterPos($pos,$fileInfo['width'],$fileInfo['height'],$waterInfo['width'],$waterInfo['height']);
		imagecopymerge($file,$water,$x,$y,0,0,$waterInfo['width'],$waterInfo['height'],$pct);
		$this->makeDir($waterPicPath);
		$rand=mt_rand(100000,999999);
		$basename=basename($filename);
		$dstName="{$prefix}_{$rand}_{$basename}";
		$dst=$waterPicPath.'/'.$dstName;
		if(file_exists($dst)){
			$this->error='目标目录存在同名文件，请重新命名后操作';
			return false;
		}
		$fileInfo['outFun']($file,$dst);
		imagedestroy($file);
		imagedestroy($water);
		if($delSource){
			@unlink($filename);
		}
		return $dst;

	}
	//生成目录
	public function makeDir($path){
		if(!file_exists($path)){
			mkdir($path,0777,true);
		}
	}
	//获取水印坐标
	public function getWaterPos($pos,$dstW,$dstH,$srcW,$srcH){
		switch($pos){
			case 0:
				$x=0;
				$y=0;
				break;
			case 1:
				$x=($dstW-$srcW)/2;
				$y=0;
				break;
			case 2:
				$x=$dstW-$srcW;
				$y=0;
				break;
			case 3:
				$x=0;
				$y=($dstH-$srcH)/2;
				break;
			case 4:
				$x=($dstW-$srcW)/2;
				$y=($dstH-$srcH)/2;
				break;
			case 5:
				$x=$dstW-$srcW;
				$y=($dstH-$srcH)/2;
				break;
			case 6:
				$x=0;
				$y=$dstH-$srcH;
				break;
			case 7:
				$x=($dstW-$srcW)/2;
				$y=$dstH-$srcH;
				break;
			case 8:
				$x=$dstW-$srcW;
				$y=$dstH-$srcH;
				break;
			default:
				$x=0;
				$y=0;
		}
		return array($x,$y);
	}
}
