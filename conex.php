<?php
	$host = "localhost";
	$banco = "sgaf";
	$user = "root";
	$senha = "";

	$con = mysqli_connect($host, $user, $senha, $banco);

	if (mysqli_connect_error()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	// $sql = 'SHOW TABLES FROM '.$banco;
	$sql = sprintf("SELECT TABLE_NAME as 'table' FROM information_schema.TABLES t where t.TABLE_SCHEMA = '%s'", $banco);

	$result = mysqli_query($con, $sql);

	if(!$result) {
		echo "Erro: " . mysqli_error($con);
	}

	while ($row = mysqli_fetch_object($result)) {

		$sql = "SHOW COLUMNS FROM " . $row->table;

		$resultColls = mysqli_query($con, $sql);

		if(!$resultColls) {
			echo "Erro " . mysqli_error($con);
		}

		$data = array();
		while ($row2 = mysqli_fetch_object($resultColls)) {
			$sql = sprintf("SELECT COLUMN_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = '%s' AND REFERENCED_TABLE_NAME IS NOT NULL", $banco, $row->table);
			$resultKeys = mysqli_query($con, $sql);
			if(!$resultKeys) {
				echo "Erro " . mysqli_error($con);
			}
			
			while($row3 = mysqli_fetch_object($resultKeys)) {
				if($row3->COLUMN_NAME == $row2->Field) {
					$row2->fk = $row3->REFERENCED_TABLE_NAME;
				}
			}
			array_push($data, $row2);
		}

		createClass($row->table, $data);
		createDao ($row->table, $data);
		
	}

	// model

	function createClass ($class, $data) {
		
		if(!file_exists('src')) mkdir('src');
		if(!file_exists('src/'.$class)) mkdir('src/'.$class);
		
		$fp = fopen('src/'.$class.'/'.ucfirst($class).".php", "a");
		
		$text = "<?php\n";
		$text .= "// model : ".$class."\n\n";

		$text .= "Class ". ucfirst($class) ." implements JsonSerializable {\n";
		
		$escreve = fwrite($fp, $text, strlen($text));

		writeAttrs ($fp, $class, $data);
		writeConstruct ($fp, $class, $data);
		writeGetSet ($fp, $class, $data);
		writeJsonSerialize ($fp, $class, $data);

		$escreve = fwrite($fp, '}');

		fclose($fp);

	}

	function writeAttrs ($fp, $class, $data) {
		$text = "	//atributos\n";
		foreach ($data as $key) {
			$text .= "	private $".$key->Field. ";\n";
		}
		$text .= "\n";
		$escreve = fwrite($fp, $text, strlen($text));
	}

	function writeConstruct ($fp, $class, $data) {
		$text = "	//constutor\n";
		$text .= "	public function __construct\n";
		$text .= "	(\n";
		foreach ($data as $key) {
			if(!empty($key->fk)) {
				$text .= "		".ucfirst($key->fk)." $".$key->Field. " = NULL,\n";
			}else{
				$text .= "		$".$key->Field. " = NULL,\n";
			}
		}
		$text = substr($text, 0, -2). "\n";
		
		$text .= "	)\n";
		$text .= "	{\n";
		foreach ($data as $key) {
			$text .= "		\$this->".$key->Field."	= $".$key->Field. ";\n";
		}
		$text .= "	}\n\n";

		$escreve = fwrite($fp, $text, strlen($text));
	}

	function writeGetSet ($fp, $class, $data) {
		$text = "	//Getters e Setters\n";
		foreach ($data as $key) {
			$text .= "	public function get".ucfirst($key->Field)."() {\n";
			$text .= "		return \$this->".$key->Field.";\n";
			$text .= "	}\n";
			$text .= "	public function set".ucfirst($key->Field)."($".$key->Field.") {\n";
			$text .= "		\$this->".$key->Field." = $".$key->Field.";\n";
			$text .= "		return \$this;\n";
			$text .= "	}\n";
		}
		$text .= "\n";
		$escreve = fwrite($fp, $text, strlen($text));
	}

	function writeJsonSerialize ($fp, $class, $data) {
		$text = "	//Json Serializable\n";
		$text .= "	public function JsonSerialize () {\n";
		$text .= "		return [\n";
		foreach ($data as $key) {
			$text .= "			\"".$key->Field."\"	=> \$this->".$key->Field. ",\n";	
		}
		$text = substr($text, 0, -2). "\n";
		$text .= "		];\n";
		$text .= "	}\n";

		$escreve = fwrite($fp, $text, strlen($text));
	}

	// dao

	function createDao ($class, $data) {
		
		if(!file_exists('src')) mkdir('src');
		if(!file_exists('src/'.$class)) mkdir('src/'.$class);
		
		$fp = fopen('src/'.$class.'/'.ucfirst($class)."DAO.php", "a");
		
		$text = "<?php\n";
		$text .= "// dao : ".$class."\n\n";

		$text .= "Class ".ucfirst($class)."DAO {\n";
		$text .= "	//atributos\n";
		$text .= "	private \$con;\n";
		$text .= "	private \$sql;\n";
		$text .= "	private \$obj;\n";
		$text .= "	private \$lista = array();\n\n";

		$text .= "	//construtor\n";
		$text .= "	public function __construct(\$con) {\n";
		$text .= "		\$this->con = \$con;\n";
		$text .= "	}\n\n";
		
		$escreve = fwrite($fp, $text, strlen($text));

		writeCadastrar ($fp, $class, $data);
		writeBuscarPorId ($fp, $class, $data);
		writeListar ($fp, $class, $data);
		writeAtualizar ($fp, $class, $data);
		writeDeletar ($fp, $class, $data);
		
		$escreve = fwrite($fp, '}');

		fclose($fp);
	}

	function writeCadastrar ($fp, $class, $data) {
		$text = "	//cadastrar\n";
		$text .= "	function cadastrar (".ucfirst($class)." \$obj) {\n";
		$text .= "		\$this->sql = sprintf(\"INSERT INTO ".$class."(";
		$values = "		VALUES(";
		$objs = "";
		foreach($data as $key) {
			if($key->Field == 'dataedicao' || $key->Field == 'datacadastro' || $key->Field == "id") {
				//
			}else{
				$text .= $key->Field. ", ";
				$values .= _getType ($key->Type) . ", ";
				$objs .= "			mysqli_real_escape_string(\$this->con, \$obj->get".ucfirst($key->Field)."()),\n";
			}
		}
		$text = substr($text, 0 , -2) . ")\n";
		$values = substr($values, 0 , -2) . ")\",\n";
		$objs = substr($objs, 0 , -2) . ");\n";
			
		$text .= $values . $objs;
		
		$text .= "		if(!mysqli_query(\$this->con, \$this->sql)) {\n";
		$text .= "			die('[ERRO]: Class('.get_class(\$obj).') | Metodo(Cadastrar) | Erro('.mysqli_error(\$this->con).')');\n";
		$text .= "		}\n";
		$text .= "		return mysqli_insert_id(\$this->con);\n";
		$text .= "	};\n\n";

		$escreve = fwrite($fp, $text, strlen($text));
	}

	function writeAtualizar ($fp, $class, $data) {
		$text = "	//atualizar\n";
		$text .= "	function atualizar (".ucfirst($class)." \$obj) {\n";
		$text .= "		\$this->sql = sprintf(\"UPDATE ".$class." SET ";
		$objs = "";
		foreach($data as $key) {
			if($key->Field != "id") {
				$text .=  $key->Field. " = ". _getType ($key->Type). ", ";
				if($key->Field == "dataedicao") {
					$objs .= "			mysqli_real_escape_string(\$this->con, date('Y-m-d')),\n";
				}else{
					$objs .= "			mysqli_real_escape_string(\$this->con, \$obj->get".ucfirst($key->Field)."()),\n";
				}
			}
		}
		$text = substr($text, 0 , -2);
		$text .=  " WHERE id = %d \",\n";
		$objs .= "			mysqli_real_escape_string(\$this->con, \$obj->getId()));\n";
		
		$text .= $objs;

		$text .= "		if(!mysqli_query(\$this->con, \$this->sql)) {\n";
		$text .= "			die('[ERRO]: Class('.get_class(\$obj).') | Metodo(Atualizar) | Erro('.mysqli_error(\$this->con).')');\n";
		$text .= "		}\n";
		$text .= "		return mysqli_insert_id(\$this->con);\n";
		$text .= "	};\n\n";

		$escreve = fwrite($fp, $text, strlen($text));
	}

	function writeBuscarPorId ($fp, $class, $data) {
		$text = "	//buscarPorId\n";
		$text .= "	function buscarPorId (".ucfirst($class)." \$obj) {\n";
		$text .= "		\$this->sql = sprintf(\"SELECT * FROM ".$class." WHERE id = %d\",\n";
		$text .= "			mysqli_real_escape_string(\$this->con, \$obj->getId()));\n";
		$text .= "		\$resultSet = mysqli_query(\$this->con, \$this->sql);\n";
		$text .= "		if(!\$resultSet) {\n";
		$text .= "			die('[ERRO]: Class('.get_class(\$obj).') | Metodo(BuscarPorId) | Erro('.mysqli_error(\$this->con).')');\n";
		$text .= "		}\n";
		$text .= "		while(\$row = mysqli_fetch_object(\$resultSet)) {\n";
		$objs = "";
		foreach ($data as $key) {
			if(!empty($key->fk)) {
				$text .= "			//classe ".$key->fk."\n";
				$text .= "			\$control".ucfirst($key->fk)." = new ".ucfirst($key->fk)."Control(new ".ucfirst($key->fk)."(\$row->".$key->Field."));\n";
				$text .= "			\$obj".ucfirst($key->fk)." = \$control".ucfirst($key->fk)."->buscarPorId();\n";
				$objs .= "\$obj".ucfirst($key->fk). ", ";
			}else{
				$objs .= "\$row->".$key->Field. ", ";
			}
		}
		$objs = substr($objs, 0 , -2) . "";
		$text .= "			\$this->obj = new ".ucfirst($class)."(".$objs.");\n";
		
		$text .= "			array_push(\$this->lista, \$this->obj)\n";
		$text .= "		}\n";
		$text .= "		return \$this->lista\n";
		$text .= "	}\n\n";
	
		$escreve = fwrite($fp, $text, strlen($text));
	}

	function writeListar ($fp, $class, $data) {
		$text = "	//listar\n";
		$text .= "	function listar (".ucfirst($class)." \$obj) {\n";
		$text .= "		\$this->sql = \"SELECT * FROM ".$class."\"\n";
		$text .= "		\$resultSet = mysqli_query(\$this->con, \$this->sql)\n";
		$text .= "		if(!\$resultSet) {\n";
		$text .= "			die('[ERRO]: Class(Banco) | Metodo(Listar) | Erro('.mysqli_error(\$this->con).')');\n";
		$text .= "		}\n";
		$text .= "		while(\$row = mysqli_fetch_object(\$resultSet)) {\n";
		$objs = "";
		foreach ($data as $key) {
			if(!empty($key->fk)) {
				$text .= "			//classe ".$key->fk."\n";
				$text .= "			\$control".ucfirst($key->fk)." = new ".ucfirst($key->fk)."Control(new ".ucfirst($key->fk)."(\$row->".$key->Field."));\n";
				$text .= "			\$obj".ucfirst($key->fk)." = \$control".ucfirst($key->fk)."->buscarPorId();\n";
				$objs .= "\$obj".ucfirst($key->fk). ", ";
			}else{
				$objs .= "\$row->".$key->Field. ", ";
			}
		}
		$objs = substr($objs, 0 , -2) . "";
		$text .= "			\$this->obj = new ".ucfirst($class)."(".$objs.");\n";
		
		$text .= "			array_push(\$this->lista, \$this->obj)\n";
		$text .= "		}\n";
		$text .= "		return \$this->lista\n";
		$text .= "	}\n\n";
	
		$escreve = fwrite($fp, $text, strlen($text));
	}

	function writeDeletar ($fp, $class, $data) {
		$text = "	//deletar\n";
		$text .= "	function deletar (".ucfirst($class)." \$obj) {\n";
		$text .= "		\$this->sql = sprintf(\"DELETE FROM ".$class." WHERE id = %d\",\n";
		$text .= "			mysqli_real_escape_string(\$this->con, \$obj->getId()));\n";
		$text .= "		\$resultSet = mysqli_query(\$this->con, \$this->sql);\n";
		$text .= "		if(!\$resultSet) {\n";
		$text .= "			die('[ERRO]: Class('.get_class(\$obj).') | Metodo(Deletar) | Erro('.mysqli_error(\$this->con).')');\n";
		$text .= "		}\n";
		$text .= "		return true;\n";
		$text .= "	}\n\n";

		$escreve = fwrite($fp, $text, strlen($text));
	}

	function _getType ($type) {
		if(strripos($type, "(")) $type = substr($type, 0, strripos($type, "("));
		
		$t = '';
		if(
			$type == "int" ||
			$type == "tinyint" ||
			$type == "bigint" ||
			$type == "smallint" ||
			$type == "bit" ||
			$type == "real"
		) {$t = "%d";}
		else if(
			$type == "double" ||
			$type == "float" ||
			$type == "decimal" ||
			$type == "numeric"
		) {$t = "%f";}
		else {$t = "'%s'";}

		return $t;
	}
?>