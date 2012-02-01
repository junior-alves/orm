<?php
/**
 * Classe responsavel por toda 
 * a parte de configuracao 
 * referente ao banco de dados.
 */
class Config{
	/**
	 * Guarda a instancia da classe config.
	 */
	private static $_instance;
	
	/**
	 * Array da lista de configurações.
	 */
	private $list = array();
	
	/**
	 * Construtor da classe Config.
	 */
	private function __construct()
	{
		$dir = realpath(dirname(__FILE__));
		
		$this->list['SQL_CACHE_FOLDER'] = realpath($dir.'/../../../cache/sql');
	}
	
	/**
	 * Adiciona uma configuração a lista.
	 * 
	 * @param string $id -> Id da configuração.
	 * @param any $value -> Valor a ser armazenado.
	 * @param bool $append -> Caso setado como true, 
	 * adiciona o valor ao final do array, caso contrario, 
	 * apaga os dados já existentes nesse id e insere os 
	 * novos.
	 */
	public static function add($id, $value, $append = false)
	{
		$_this = &self::init();
		
		if($append === false)
		{
			$_this->list[$id] = $value;
			return true;
		}
		
		if(isset($_this->list[$id]))
		{
			//if(!is_array($_this->list[$id])) 
				$_this->list[$id] = array($_this->list[$id]);
			
			$_this->list[$id][] = $value;
			return true;
		}
		
		$_this->list[$id] = $value;
	}
	
	/**
	 * Recupera uma configuração da lista.
	 * 
	 * @param string $id -> Id da configuração.
	 */
	public static function retrieve($id)
	{
		$_this = self::init();
		
		if(isset($_this->list[$id]))
			return $_this->list[$id];
		
		return false;
	}
	
	/**
	 * Inicializa a instancia da configuração
	 */
	private static function init()
	{
		if(is_null(self::$_instance))
			self::$_instance = new Config;
		
		return self::$_instance;
	}
}