<?php

final class Tool {
	
	//显示过滤
	static function htmlString($data=''){
		$string = '';
		if(is_array($data)){
			foreach($data as $key=>$value){
				$string[$key]=Tool::htmlString($value); 
			}	
		}elseif(is_object($data)){
			$string = new stdClass;
			foreach($data as $key=>$value){
				$string->$key=Tool::htmlString($value);	
			}	
		}else{
			$string=htmlspecialchars($data);	
		}
		return $string;
	}
}
