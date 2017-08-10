<?php
class DB {
	static function getDB(){
		$mysqli = new mysqli(DB_HOST,DB_USER,DB_PSW,DB_NAME,DB_PORT);
		if(mysqli_connect_errno()){
			exit('数据库连接错误。'.mysqli_connect_errno());
		}
		$mysqli->set_charset('utf8');
		return $mysqli;
	}

	static function unDB(&$mysqli,&$result=null){
		if(is_object($result)){
			$result->free();
			$result = null;
		}
		if(is_object($mysqli)){
			if($mysqli->errno){
				exit('数据库操作出错！');
			}
			$mysqli->close();
			$mysqli = null;
		}
	}
}
