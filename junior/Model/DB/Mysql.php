<?php
/**
 * 
 */
class Mysql extends DB{
	
	/**
	 * DNS de conexão PDO.
	 */
	const DNS = 'mysql:host=$host;dbname=$base';
	
	/**
	 * Numero padrao da porta para esse banco.
	 */
	const PORT = 3306;
	
	/**
	 * Metodo sobreescrito.
	 * 
	 * Retorna a estrutura de uma 
	 * tabela no banco mysql.
	 * 
	 * @param string $tableName -> Nome da tabela.
	 */
	public function getStructureTable($tableName)
	{
		if(($data = parent::getStructureTable($tableName)) !== false)
			return $data;
		
		$stm = $this->query('SHOW FULL COLUMNS FROM '.$tableName);
		
		if(!$stm) die('Erro ao acessar a tabela '.$tableName);
		
		$result = $stm->fetchAll(PDO::FETCH_OBJ);
		
		$stm->closeCursor();
		
		$return = array();
		
		foreach($result as $column)
		{
			$return['columns'][$column->Field] = array(
												'type' => $this->getPDOColumnType($column->Type),
												'default' => $column->Default,
												'null' => ($column->Null === 'YES' ? true : false));
															
			if($column->Key == 'PRI') 
				$return['pk'] = $column->Field;
		}
		
		Cache::white($tableName.'_structure', $return);
		
		return $return;
	}
	
	/**
	 * Metodo sobreescrito.
	 * 
	 * Seta os dados ao model.
	 * 
	 * @param array $data -> Array contendo os dados a serem setados.
	 * @param array $tableStructure -> Ponteiro para a o array estrutural
	 * da tabela no model.
	 */
	public function setDataModel($data = array(), &$tableStructure = array())
	{
		parent::setDataModel($data, $tableStructure);
	}
	
	/**
	 * Metodo sobreescrito.
	 * 
	 * Retorna o tipo de uma coluna.
	 * 
	 * @param string $col -> Tipo primario da coluna.
	 */
	public function getPDOColumnType($col)
	{
		if(strpos($col, 'tinyint') !== false)
		{
			return PDO::PARAM_BOOL;
		}
		elseif(strpos($col, 'int') !== false)
		{
			return PDO::PARAM_INT;
		}
		elseif((strpos($col, 'char') || strpos($col, 'text')) !== false)
		{
			return PDO::PARAM_STR;
		}
		else
		{
			return PDO::PARAM_STR;
		}
		// if (strpos($col, 'text') !== false) {
			// return PDO::PARAM_STR;
		// }
		// if (strpos($col, 'blob') !== false || $col === 'binary') {
			// return 'binary';
		// }
		// if (strpos($col, 'float') !== false || strpos($col, 'double') !== false || strpos($col, 'decimal') !== false) {
			// return 'float';
		// }
		// if (strpos($col, 'enum') !== false) {
			// return "enum($vals)";
		// }
	}
}
