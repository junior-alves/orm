<?php
/**
 * Classe resposável por 
 * trabalhar com a parte 
 * de cache do banco.
 */
class Cache{
	
	/**
	 * Escreve dados no cache, retorna 
	 * falso em caso de falha.
	 * 
	 * @param string/id $id -> Identificador para o cache.
	 * @param any $data -> 
	 */
	public static function white($id, $data = null)
	{
		if($data === null)
		{
			return false;
		}
		elseif(!is_array($data) || is_object($data))
		{
			$data = array($data);
		}
		
		$path = Config::retrieve('SQL_CACHE_FOLDER');
		
		return (bool)file_put_contents($path.'/'.$id, serialize($data));
	}
	
	/**
	 * Faz a leitura de um arquivo no cache, 
	 * caso exista, retornará seu conteúdo, 
	 * caso contrário, retornará falso.
	 * 
	 * @param string/int $id -> Identificador do arquivo no cache.
	 * @param int $life -> Opcional, informa o tempo em segundos 
	 * ao qual o dado por ser considerado em cache, se a data de 
	 * modificação do arquivo for maior que a de segundos informados
	 * a função informará que o cache não existe.
	 */
	public static function read($id, $life = null)
	{
		$path = Config::retrieve('SQL_CACHE_FOLDER');
		
		if(!file_exists($path.'/'.$id))
			return false;
		
		if($life !== null && 
			((time() - $life)) > filemtime($path.'/'.$id))
				return false;
		
		return unserialize(trim(file_get_contents($path.'/'.$id)));
	}
}
