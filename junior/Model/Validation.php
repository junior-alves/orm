<?php

/**
 * Classe responsável por 
 * validar dados do Model.
 */
class Validation{
	
	/**
	 * 
	 */
	public static function validate($value, $type, $exp = null)
	{
		switch($type)
		{
			case 'notNull':
				return !(bool)($value === null);
			break;
			
			case 'notEmpty':
				return (bool)self::_testExp('/.+/i', $value);
			break;
			
			case 'isNumeric':
				return (bool)self::_testExp('/^[0-9]+$/i', $value);
			break;
			
			case 'exp':
				if($exp === null)
					return false;
				
				return (bool)self::_testExp($exp, $value);
			break;
		}
	}
	
	/**
	 * Testa uma expressão regular 
	 * de validação.
	 */
	private static function _testExp($pattern, $string)
	{
		return preg_match($pattern, $string);
	}
}
