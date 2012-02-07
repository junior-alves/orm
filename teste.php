<?php
// $pdo = new PDO('mysql:host=localhost;dbname=mydb', 'root', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')); 
// 
// $sql = "update tabela1 set texto = ? where id >= ? and id <= 5 and ativo = ?";
// 
// $stm = $pdo->prepare($sql);
// 
// //$stm->bindValue(1, 'meu outro texto.', PDO::PARAM_STR);
// 
// //$stm->bindValue(2, 1, PDO::PARAM_INT);
// 
// //$stm->bindValue(3, 10, PDO::PARAM_INT);
// 
// var_dump($stm->execute(array('meu primeiro texto', 1, false)));

$t = microtime(true);

include_once './junior/Model/Model.php';

DB::connection('default', DB::MYSQL_DRIVER, 'root', '1', 'localhost', 'mydb');
DB::connection('default', DB::MYSQL_DRIVER, 'root', '2', 'localhost', 'test');
DB::connection('default', DB::MYSQL_DRIVER, 'root', '', 'localhost', 'mydb');
//Config::connection('default', DB::MYSQL_DRIVER, 'root', '', 'localhost');
//Config::connection('default2', DB::MYSQL_DRIVER, 'root2', '', 'localhost2');

class Produto extends  Model
{
	
}

class Cliente extends Model
{
	protected $dependents = array('Produto');
}

$model = new Cliente();

$texto = 'zla bla bla';
$nome[] = 'joao1';
$nome[] = 'joao2';
$nome[] = 'joao3';
$create = $model->create(array(
					'texto' => $texto, 
					'nome' => $nome, 
					'pipoca' => 1,
					'email' => 'jr290488@gmail.com'));
										
var_dump($model->rowCount());

//print_r($model);

print_r(round((microtime(true) - $t) * 1000, 2).' ms');
