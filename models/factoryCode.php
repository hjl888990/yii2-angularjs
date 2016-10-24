<?php
namespace app\models;

use app\models\validateCode\validateCode1;

class factoryCode{

	/**
	 * 生成验证码
	 * @param string $int
	 * @return object
	 */
	public static function createObj($type = null){
		return new validateCode1();
	}
	
	/**
	 * 校验验证码
	 * @param string $input
	 * @param bool $flag
	 */
	public static function validate($input, $flag = false){
		$result = false;
		$objCode = new validateCode1();
		if(!empty($input)){
			$result = $objCode->validate($input, $flag);
		}
		return $result;
	}
}
