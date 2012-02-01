<?php
/**
 * 
 */
class DB extends PDO{
	
	/**
	 * Driver para banco mysql.
	 */
	const MYSQL_DRIVER = 'Mysql';
	
	/**
	 * Armazena todas as conexoes feitas.
	 */
	private static $_connectionsMaps;
	
	/**
	 * Adiciona uma ou mais conexões para o model.
	 * 
	 * @param string $name -> Nome da conexão.
	 * @param DB Driver $driver -> Tipo de driver a ser usado na conexão.
	 * @param string $user -> Usuario da conexão.
	 * @param string $passwd -> Senha da conexão.
	 * @param string $host -> Endereço do host de conexão.
	 * @param string $base -> Nome do banco de dados.
	 * @param int $port -> Numero da porta da conexão.
	 */
	public static function connection($name, $driver, $user, $passwd, $host, $base, $port = null)
	{
		Config::add('connections', array($name => compact('driver', 'user', 'passwd', 'host','base', 'port')), true);
	}
	
	/**
	 * Cria uma conexão.
	 * 
	 * @param string $name -> Nome da conexão.
	 */
	public static function createConnection($name)
	{
		if(isset(self::$_connectionsMaps[$name]))
			return self::$_connectionsMaps[$name];
		
		$list = Config::retrieve('connections');
		
		if(!$list) die('Nenhuma conexao cadastrada.');
		
		$array = null;
		
		if(isset($list[$name])) $array = array($list[$name]);
		
		if($array == null)
		{
			foreach($list as $item)
			{
				if(isset($item[$name]))
					$array[] = $item[$name];
			}
		}
		
		$cont = count($array);
		$array = array_reverse($array);
		
		while($cont > 0)
		{
			try
			{
				extract($array[$cont-1]);
				$dir = realpath(dirname(__FILE__));
				include_once $dir.'/'.$driver.'.php';
				
				eval('$dns = '.$driver.'::DNS;');
				eval('$dns = "'.$dns.'";');
				if(is_null($port)) eval('$port = '.$driver.'::PORT;');
				
				$conn = new $driver($dns, $user, $passwd);
				$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
				
				self::$_connectionsMaps[$name] = $conn;
				$cont = 0;
				return $conn;
			}
			catch(PDOException $e)
			{
				
			}
			
			$cont--;
		}
		
		die('Banco de dados nao conectado.');
	}
	
	/**
	 * Recupera a estrutura da tabela.
	 * 
	 * @param string $tableName -> Nome da tabela que se deseja.
	 */
	public function getStructureTable($tableName)
	{
		return Cache::read($tableName.'_structure');
	}
	
	/**
	 * Direciona para o método apropriado 
	 * de renderização de acordo com o tipo.
	 * 
	 * @param array $data -> Array contendo os dados 
	 * para se montar a instrução.
	 * @param string $type -> String contendo o nome do tipo de ação.
	 */
	public function renderInstruction(&$data, $type)
	{
		$renderType = 'render'.ucfirst(strtolower($type));
		
		return $this->$renderType($data);
	}
	
	/**
	 * Faz a renderização(montagem) da instrução 
	 * sql para a ação create.
	 * 
	 * @param array $data -> Array com os dados para renderização.
	 */
	protected function renderCreate(&$data)
	{
		unset($data['data'][$data['table']['pk']]);
		
		$sql = 'INSERT INTO {$tableName} ';
		
		$sql .= '('.implode(',', array_keys($data['data'])).') VALUES(';
		
		$implodeValues = function($value)
		{
			if(is_array($value))
			{
				return '(' . implode(',', $value) . ')';
			}
		};
		
		$sql .= implode(',', array_map($implodeValues, array_values($data['data'])));
		
		$sql .= ')';
		
		echo $sql;
	}
}
