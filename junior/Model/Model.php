<?php
/**
 * Incluindo arquivos necessarios.
 */
$dir = realpath(dirname(__FILE__));

include_once($dir.'/Config/Config.php');
include_once($dir.'/Cache/Cache.php');
include_once($dir.'/DB/DB.php');
include_once($dir.'/Validation.php');

/**
 * Classe resposavel por realizar todas 
 * as operacoes de CRUD em uma tabela 
 * do banco.
 * 
 * Baseada na classe Model do cakePHP 
 * http://book.cakephp.org/2.0/en/models.html.
 */
class Model{
	
	/**
	 * Opcional, define o nome pelo qual esse model 
	 * representará publicamente, caso não definido 
	 * será o nome da classe.
	 */
	public $name = false;
	
	/**
	 * Opcional, define o nome da tabela no banco, 
	 * caso não definido será o nome da classe.
	 */
	public $tableName = false;
	
	/**
	 * Define o nome da conexão a 
	 * ser usada pelo model.
	 */
	protected $_connectionName = 'default';
	
	/**
	 * Guarda os dados referentes ao 
	 * model em geral.
	 */
	protected $modelData = array();
	
	/**
	 * Associa o model atual com um 
	 * ou mais models.
	 * 
	 * Associação simples, assumirá que 
	 * o model associado ao model atual 
	 * contém um campo com a seguinte estrutura:
	 * 
	 * nomeTabelaModelAtual_fk.
	 * 
	 * protected $bindModels = array('Perfil', 'Comentarios');
	 * 
	 * Associação detalhada:
	 * 
	 * protected $bindModels = array(
	 * 			'Cliente',
	 * 			'Usuario' => array(
	 * 				'fk' => 'Perfil_fk',
	 * 				'dependent' => true
	 * 			)
	 * 		);
	 * 
	 * 'fk' => (Opcional) Nome da chave extrangeira no model 
	 * Usuario, se não informado usará a estrutura "nomeTabelaModelAtual_fk".
	 * 'dependent' => Se definido como true, quando um Perfil 
	 * for criado ou modificado, também modificará ou criará 
	 * a relação no model Usuario, válido apenas a um registro
	 * por vez.
	 */
	protected $dependents = array();
	
	/**
	 * 
	 */
	protected $validate = array();
	
	/**
	 * Armazena o nome de campos 
	 * que devem ter seu valor 
	 * original preservado.
	 * 
	 * Obs: Nome da propriedade está ruim.
	 */
	private $noParse = array();
	
	/**
	* Armazena a classe do banco a ser usado.
	* 
	* @var DB
	*/
	private $_db = false;
	
	/**
	 * Metodo construtor da classe.
	 */
	public function __construct($data = null)
	{
		$className = get_class($this);
		
		if(!$this->name)
			$this->name = $className;
		
		if(!$this->tableName) 
			$this->tableName = $className;
		
		if(!$this->_db) 
			$this->_db = DB::createConnection($this->_connectionName);
		
		//parent::__construct($data);
		$this->modelData['data'] = array();
		
		$this->modelData['table'] = $this->_db->getStructureTable($this->tableName);
	}
	
	/**
	 * Metodo magico call.
	 */
	public function __call($name, $arguments)
	{
		if($this->_db !== null)
			return $this->_db->$name($arguments);
	}
	
	/**
	 * Persiste um ou mais registros do model 
	 * na tabela do banco.
	 * 
	 * @return boolean -> TRUE / FALSE
	 */
	public function create($dataParam, $cascade = true)
	{
		if(!is_array($dataParam)) 
			return false;
		
		$dataDiff = $dataParam;
		
		$this->_filter($dataParam);
		
		if(count($dataParam) <= 0)
			return false;
		
		$dataDiff = (array_diff_key($dataDiff, $dataParam));
		
		$this->_normalizeData($dataParam, 'create');
		
		$this->setDataModel($dataParam);
		
		extract($this->modelData['table']);
		
		unset($columns[$pk]);
		
		$columns = array_keys($columns);
		
		$data = array_combine(array_keys($dataParam), array_values($this->modelData['data'][$this->tableName]));
		
		$renderParams = array(
							'pk' => $pk,
							'noParse' => $this->noParse,
							'tableName' => $this->tableName,
							'columns' => $columns,
							'data' => $data);
		
		$executeData = $this->_db->renderInstruction($renderParams, 'create');
		
		$this->inTransaction = $this->_db->inTransaction();
		
		if(!$this->inTransaction)
			$this->_db->beginTransaction();

		if($this->_db->executeRenderData($executeData))
		{
			$data = & $this->modelData['data'][$this->name];
			
			if(count($dataDiff) > 0 && $cascade)
			{
				foreach($this->dependents as $key => $value)
				{
					if(is_array($value))
					{
						$m = new $key();
						$fk = !isset($value['fk'])? $this->tableName.'_fk' : $value['fk'];
					}
					else
					{
						$m = new $value();
						$fk = $this->tableName.'_fk';
					}
					
					$dataDiff[$fk] = $this->_db->lastInsertId();
					
					if(!$m->create($dataDiff, $cascade))
					{
						if($this->inTransaction)
							$this->_db->rollBack();
						return false;
					}
				}
			}
			
			if($this->inTransaction)
				$this->_db->commit();
			
			return $data = true;
		}
		
		if($this->inTransaction)
			$this->_db->rollBack();
		
		return $data = false;
	}
	
	/**
	 * Atualiza os campos de um model no banco
	 * de acordo com as opções definidas no parametro.
	 * 
	 * @param Array $fields -> Array contendo o nome de todos 
	 * os campos a qual se deseja atualizar.
	 * @param String/Array $optionsQuery -> String/Array contendo 
	 * todas a opçoes e condiçoes da atualização.
	 */
	public function update($fields = array(), $optionsQuery = null)
	{
		
	}
	
	/**
	 * Seleciona uma ou varias linhas de 
	 * uma ou mais tabelas de acordo com 
	 * as opções definidas no parametro.
	 * 
	 * @param string/array $optionsQuery -> String/Array contendo 
	 * todas as opções referentes a seleção.
	 */
	public function select($optionsQuery = null)
	{
		
	}
	
	/**
	 * Deleta uma ou mais linha do model no banco
	 * baseado no valor da chave primaria.
	 * 
	 * @param int/array $id -> String ou array de com 
	 * o valor das chaves primarias das linhas que se 
	 * deseja deletar.
	 */
	public function delete($id = null)
	{
		
	}
	
	/**
	 * Salva os dados de um model no banco 
	 * baseado no valor da chave primaria.
	 */
	public function save()
	{
		
	}
	
	/**
	 * Le uma ou mais linhas de uma tabela. 
	 * 
	 * @param string/array $fields -> Campos aos quais se deseja recuperar.
	 * @param int/array $id -> Valor da chave primaria da linha que se deseja.
	 */
	public function read($fields = null, $id = null)
	{
		
	}
	
	/**
	 * Executa tipos específicos de seleção como first / count / all 
	 * de acordo com as opções e condições do segundo parametro.
	 * 
	 * @param string $type -> Informa o tipo de seleção no qual se deseja.
	 * @param String/Array -> String/Array contendo as opções e codições para 
	 * a seleção.
	 */
	public function find($type = 'first', $optionsQuery = array())
	{
		
	}
	
	/**
	 * Seta os dados ao model.
	 * 
	 * @param array $data -> Array contendo os dados a serem setados.
	 * da tabela no model.
	 */
	public function setDataModel(&$data = array(), $value = null)
	{
		if(!is_array($data) && $value !== null)
			$data = array($data => $value);
		
		$columns = & $this->modelData['table']['columns'];
		
		$tmpData = & $this->modelData['data'][$this->name];

		foreach($data as $fieldName => $fieldValue)
		{
			if(isset($columns[$fieldName]))
			{
				$tmpData[$fieldName] = $data[$fieldName];
			}
				
		}
	}
	
	public function getDataModel()
	{
		if(!isset($this->modelData['data'][$this->name]))
			return false;
		
		return $this->modelData['data'][$this->name];
	}
	
	/**
	 * Normaliza os dados que foram setados 
	 * no model.
	 * 
	 * @param array $columns -> Ponteiro para o array contendo 
	 * as colunas da tabela.
	 */
	private function _normalizeData(&$data = array(), $type = null)
	{
		switch($type)
		{
			case 'create':
				
				$tmp_data = array();
				
				array_multisort($data);
				
				$size  = count(end($data));

				$tmp_data = array_map(array(&$this, '_transform'), $data, array_pad(array(), count($data), $size));
				
				$data = (array_combine(array_keys($data), $tmp_data));
				
			break;		
		}
	}
	
	private function _transform($data, $size)
	{	
		if(!is_array($data))
			return array_pad(array(), $size, $data);
		
		if(count($data) < $size)
			return array_pad($data, $size, end($data));
		
		return $data;
	}
	
	/**
	 * Retorna a colunas que tem valor default definidos.
	 */
	private function _getDefault()
	{
		$columns = $this->modelData['table']['columns'];
		
		$data = array();
		
		foreach($columns as $field => $value)
		{
			if(!empty($value['default']) && $value['default'] !== null)
				$data[$field] = $value['default'];
		}
		
		return $data;
	}
	
	/**
	 * Retorna todos os campos aos quais 
	 * não se tem um valor default definido.
	 */
	private function _getEmptyFields()
	{
		$columns = $this->modelData['table']['columns'];
		
		$data = array();
		
		foreach($columns as $field => $value)
		{
			if(empty($value['default']))
				$data[$field] = $value['default'];
		}
		
		return $data;
	}
	
	/**
	 * 
	 */
	private function _filter(&$data)
	{
		$columns = $this->modelData['table']['columns'];
		
		foreach($data as $key => $value)
		{
			if(!isset($columns[$key]))
			{
				unset($data[$key]);
				continue;
			}
			
			if(is_array($value) && isset($value['value']))
				$data[$key] = $value['value'];
			
			if(is_array($value) 
			&& isset($value['parse']) 
			&& $value['parse'] === false)
				$this->noParse[] = $key;
		}
	}
}
