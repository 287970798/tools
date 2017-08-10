<?php
class Captcha {
	//字体文件
	private $_fontFile='';
	//字体大小
	private $_size=20;
	//画布宽度
	private $_width=120;
	//画面高度
	private $_height=40;
	//验证码长度
	private $_length=4;
	//画布资源
	private $_image=null;
	//雪花个数
	private $_snow=0;
	//像素个数
	private $_pixel=0;
	//线段个数
	private $_line=0;
	//弧线个数
	private $_arc=0;
	//初始化数据
	public function __construct($config=array()){
		if(is_array($config)&&count($config)>0){
			//检测字体文件	
			if(isset($config['fontFile'])&&is_file($config['fontFile'])&&is_readable($config['fontFile'])){
				$this->_fontFile = $config['fontFile'];
			}else{
				return false;
			}
			//检测是否设置字体大小
			if(isset($config['size'])&&$config['size']>0){
				$this->_size = (int)$config['size'];
			}
			//检测是否设置画布宽和高
			if(isset($config['width'])&&$config['width']>0){
				$this->_width = (int)$config['width'];
			}
			if(isset($config['height'])&&$config['height']>0){
				$this->_height = (int)$config['height'];
			}
			//检测是否设置验证码长度
			if(isset($config['length'])&&$config['length']>0){
				$this->_length = (int)$config['length'];
			}
			//配置干扰元素
			if(isset($config['snow'])&&$config['snow']>0){
				$this->_snow = $config['snow'];
			}
			if(isset($config['pixel'])&&$config['pixel']>0){
				$this->_pixel = $config['pixel'];
			}
			if(isset($config['line'])&&$config['line']>0){
				$this->_line = $config['line'];
			}
			if(isset($config['arc'])&&$config['arc']>0){
				$this->_arc = $config['arc'];
			}
			//创建画面
			$this->_image=imagecreatetruecolor($this->_width,$this->_height);
			return $this->_image;
		}else{
			return false;
		}
	}
	//生成验证码
	public function getCaptcha(){
		$white = imagecolorallocate($this->_image,255,255,255);
		//填充矩形
		imagefilledrectangle($this->_image,0,0,$this->_width,$this->_height,$white);
		//生成验证码
		$str = $this->_generateStr($this->_length);
		if(false===$str){
			return false;
		}
		//绘制验证码
		$fontFile = $this->_fontFile;
		for($i=0;$i<$this->_length;$i++){
			$size = $this->_size;
			$angle = mt_rand(-30,30);
			$x = ($this->_width/$this->_length)*$i+mt_rand(5,10);
			$y = ($this->_height/1.5);
			$color = $this->_getRandColor();
			//$text = mb_substr($str,$i,1,'utf-8');
			$text = $str{$i};
			imagettftext($this->_image,$size,$angle,$x,$y,$color,$fontFile,$text);
		}
		//绘制干扰元素
		if($this->_snow){
			$this->_getSnow();
		}
		if($this->_pixel){
			$this->_getPixel();
		}
		if($this->_line){
			$this->_getLine();
		}
		if($this->_arc){
			$this->_getArc();
		}
		//输出图像
		header('content-type:image/png');
		imagepng($this->_image);
		imagedestroy($this->_image);
		return strtolower($str);
	}
	//产生雪花
	private function _getSnow(){
		for($i=0;$i<$this->_snow;$i++){
			imagestring($this->_image,mt_rand(1,5),mt_rand(0,$this->_width),mt_rand(0,$this->_height),'*',$this->_getRandColor());
		}
	}
	//产生像素
	private function _getPixel(){
		for($i=0;$i<$this->_pixel;$i++){
			imagesetpixel($this->_image,mt_rand(0,$this->_width),mt_rand(0,$this->_height),$this->_getRandColor());
		}
	}
	//产生线段
	private function _getLine(){
		for($i=0;$i<$this->_line;$i++){
			imageline($this->_image,mt_rand(0,$this->_width),mt_rand(0,$this->_height),mt_rand(0,$this->_width),mt_rand(0,$this->_height),$this->_getRandColor());
		}
	}
	//产生弧线
	private function _getArc(){
		for($i=0;$i<$this->_arc;$i++){
			imagearc($this->_image,mt_rand(0,$this->_width),mt_rand(0,$this->_height),mt_rand(0,$this->_width/2),mt_rand(0,$this->_height),mt_rand(0,360),mt_rand(0,360),$this->_getRandColor());
		}
	}
	//产生验证码字符
	private function _generateStr($length=4){
		if($length<1||$length>30){
			return false;
		}
		$chars = array(
			'a','b','c','d','e','f','g','h','k','m','n','p','x','y','z',
			'A','B','C','D','E','F','G','H','K','M','N','P','X','Y','Z',
			1,2,3,4,5,6,7,8,9
		);
		$str = join('',array_rand(array_flip($chars),4));
		return $str;
	}
	//随机颜色
	private function _getRandColor(){
		return imagecolorallocate($this->_image,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
	}
}
