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

	createAutoload();

	while ($row = mysqli_fetch_object($result)) {

		$sql = "SHOW COLUMNS FROM " . $row->table;

		$resultColls = mysqli_query($con, $sql);

		if(!$resultColls) {
			echo "Erro " . mysqli_error($con);
		}

		echo "--------------------------------------------------------------------------<br>";
		echo "--------------------------------------------------------------------------<br>";
		echo " 	--------> Trabalhando nas classes de ".$row->table."...  <-----------   <br>";
		echo "--------------------------------------------------------------------------<br>";
		echo "--------------------------------------------------------------------------<br>";

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
		echo "--> Model de ".$row->table." criada com sucesso!  <br>";
		echo "--------------------------------------------------------------------------<br>";

		createDao ($row->table, $data);
		echo "--> DAO de ".$row->table." criada com sucesso!  <br>";
		echo "--------------------------------------------------------------------------<br>";

		createControl ($row->table, $data);
		echo "--> Control de ".$row->table." criada com sucesso!  <br>";
		echo "--------------------------------------------------------------------------<br>";

		createRest ($row->table, $data);
		echo "--> Rest de ".$row->table." criada com sucesso!  <br>";
		echo "--------------------------------------------------------------------------<br>";
		
		echo "<br><br>";
	}

	// model

	function createClass ($class, $data) {
		
		if(!file_exists('src')) mkdir('src');
		if(!file_exists('src/model')) mkdir('src/model');
		if(!file_exists('src/model/'.$class)) mkdir('src/model/'.$class);
		
		$fp = fopen('src/model/'.$class.'/'.ucfirst($class).".php", "a");
		
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
		if(!file_exists('src/model')) mkdir('src/model');
		if(!file_exists('src/model/'.$class)) mkdir('src/model/'.$class);
		
		$fp = fopen('src/model/'.$class.'/'.ucfirst($class)."DAO.php", "a");
		
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
		
		$escreve = fwrite($fp, "}\n\n?>");

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
		$text .= "	}\n\n";

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
		$text .= "	}\n\n";

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

	// control
	function createControl ($class, $data) {
		
		if(!file_exists('src')) mkdir('src');
		if(!file_exists('src/control')) mkdir('src/control');
		
		$fp = fopen('src/control/'.ucfirst($class)."Control.php", "a");
		
		$text = "<?php\n";
		$text .= "// control : ".$class."\n\n";

		$text .= "Class ".ucfirst($class)."Control {\n";
		$text .= "	//atributos\n";
		$text .= "	protected \$con;\n";
		$text .= "	protected \$obj;\n";
		$text .= "	protected \$objDAO;\n\n";

		$text .= "	//construtor\n";
		$text .= "	public function __construct(".ucfirst($class)." \$obj=NULL) {\n";
		$text .= "		\$this->con = Conexao::getInstance()->getConexao();\n";
		$text .= "		\$this->objDAO = new ".ucfirst($class)."DAO(\$this->con);\n";
		$text .= "		\$this->obj = \$obj;\n";
		$text .= "	}\n\n";

		$text .= "	//metodos\n";
		$text .= "	function cadastrar () {\n";
		$text .= "		return \$this->objDAO->cadastrar(\$this->obj);\n";
		$text .= "	}\n";

		$text .= "	function buscarPorId () {\n";
		$text .= "		return \$this->objDAO->buscarPorId(\$this->obj);\n";
		$text .= "	}\n";
		
		$text .= "	function listar () {\n";
		$text .= "		return \$this->objDAO->listar(\$this->obj);\n";
		$text .= "	}\n";

		$text .= "	function atualizar () {\n";
		$text .= "		return \$this->objDAO->atualizar(\$this->obj);\n";
		$text .= "	}\n";

		$text .= "	function deletar () {\n";
		$text .= "		return \$this->objDAO->deletar(\$this->obj);\n";
		$text .= "	}\n";

		$text .= "}\n";

		$text .= "?>";

		$escreve = fwrite($fp, $text, strlen($text));

		fclose($fp);
	}

	// rest

	function createRest ($class, $data) {
		
		if(!file_exists('src')) mkdir('src');
		if(!file_exists('src/rest')) mkdir('src/rest');
		
		$fp = fopen('src/rest/'.$class.".php", "a");
		
		$text = "<?php\n";
		$text .= "// rest : ".$class."\n\n";

		$text .= "//inclui autoload\n";
		$text .= "require_once 'autoload.php';\n\n";

		$text .= "//verifica requisição\n";
		$text .= "switch (\$_POST['metodo']) {\n";
		$text .= "	case: 'cadastrar':\n";
		$text .= "		cadastrar();\n";
		$text .= "		break;\n";
		$text .= "	case: 'buscarPorId':\n";
		$text .= "		buscarPorId();\n";
		$text .= "		break;\n";
		$text .= "	case: 'listar':\n";
		$text .= "		listar();\n";
		$text .= "		break;\n";
		$text .= "	case: 'atualizar':\n";
		$text .= "		atualizar();\n";
		$text .= "		break;\n";
		$text .= "	case: 'deletar':\n";
		$text .= "		deletar();\n";
		$text .= "		break;\n";
		$text .= "}\n\n";

		$text .= "function cadastrar () {\n";
		$text .= "	\$data = \$_POST['data'];\n";
		$attrs = "";
		foreach ($data as $key) {
			if($key->Field != "id") {
				if(!empty($key->fk)) {
					$attrs .= "		new ".ucfirst($key->fk)."(\$data['".$key->Field."']),\n";
				}else{
					$attrs .= "		\$data['".$key->Field."'],\n";
				}
			}
		}
		$attrs = substr($attrs, 0, -2);
		$text .= "	\$obj = new ".$class."(\n";
		$text .= "		NULL,\n";
		$text .= 		$attrs."\n";
		$text .= "	);\n";
		$text .= "	\$control = new ".ucfirst($class)."Control(\$obj);\n";
		$text .= "	\$id = \$control->cadastrar();\n";
		$text .= "	echo \$id;\n";
		$text .= "}\n";

		// buscar por id
		$text .= "function buscarPorId () {\n";
		$text .= "	\$data = \$_POST['data'];\n";
		$text .= "	\$control new ".ucfirst($class)."Control(new ".ucfirst($class)."(\$data['id']));\n";
		$text .= "	\$obj = \$control->buscarPorId();\n";
		$text .= "	if(!empty(\$obj)) {\n";
		$text .= "		echo json_encode(\$obj);\n";
		$text .= "	}\n";
		$text .= "}\n";

		// listar
		$text .= "function listar () {\n";
		$text .= "	\$control = new ".ucfirst($class)."Control(new ".ucfirst($class).");\n";
		$text .= "	\$lista = \$control->listar();\n";
		$text .= "	if(!empty(\$lista)) {\n";
		$text .= "		echo json_encode(\$lista);\n";
		$text .= "	}\n";
		$text .= "}\n";

		// atualizar
		$text .= "function atualizar () {\n";
		$text .= "	\$data = \$_POST['data'];\n";
		$attrs = "";
		foreach ($data as $key) {
			if(!empty($key->fk)) {
				$attrs .= "		new ".ucfirst($key->fk)."(\$data['".$key->Field."']),\n";
			}else{
				$attrs .= "		\$data['".$key->Field."'],\n";
			}
		}
		$attrs = substr($attrs, 0, -2);
		$text .= "	\$obj = new ".ucfirst($class)."(\n";
		$text .= 		$attrs."\n";
		$text .= "	);\n";
		$text .= "	\$control = new ".ucfirst($class)."Control(\$obj);\n";
		$text .= "	\$id = \$control->atualizar();\n";
		$text .= "	echo \$id;\n";
		$text .= "}\n";

		// deletar
		$text .= "function deletar () {\n";
		$text .= "	\$data = \$_POST['data'];\n";
		$text .= "	\$banco = new ".ucfirst($class)."();\n";
		$text .= "	\$banco->setId(\$data['id']);\n";
		$text .= "	\$control = new ".ucfirst($class)."Control(\$banco);\n";
		$text .= "	echo \$control->deletar();\n";
		$text .= "}\n\n";


		$text .= "?>";

		$escreve = fwrite($fp, $text, strlen($text));

		fclose($fp);
	}

	function createAutoload () {

		if(!file_exists('src')) mkdir('src');
		if(!file_exists('src/rest')) mkdir('src/rest');
		
		$fp = fopen("src/rest/autoload.php", "a");
		
		$text = "<?php\n";
		$text .= "// autoload \n\n";

		$text .= "//Trata requisição\n";
		$text .= "if(!\$_POST){\n";
		$text .= "	if(\$_GET) {\$_POST = \$_GET;}\n";
		$text .= "	else{\$_POST =  file_get_contents ( 'php://input' );}";
		$text .= "}\n\n";

		$text .= "// conexao\n";
		$text .= "require_once(\"../Conexao.php\");\n\n";

		$text .= "// carrega class";
		$text .= "function carregaClasses(\$class){\n";
		$text .= "	//Verifica se existe Control no nome da classe\n";
		$text .= "	if(strrpos(\$class, \"Control\")) {\n";
		$text .= "		require_once(\"../control/\".\$class.\".php\");\n";
		$text .= "	}\n\n";
		$text .= "	//Verifica se existe DAO no nome da classe";
		$text .= "	else if(strrpos(\$class, \"DAO\")) {\n";
		$text .= "		\$bean = strtolower(substr(\$class, 0, strrpos(\$class, \"DAO\")));\n";
		$text .= "		require_once \"../model/\".\$bean.\"/\".\$class.\".php\";{\n";
		$text .= " 	//se nao for control ou dao é model\n";
		$text .= " 	}else{\n";
		$text .= "		\$bean = strtolower(\$class);\n";
		$text .= "		require_once \"../model/\".\$bean.\"/\".\$class.\".php\";\n";
		$text .= "	}";
		$text .= "}\n\n";

		$text .= "//chama autoload\n";
		$text .= "spl_autoload_register(\"carregaClasses\");\n";

		$text .= "?>";

		$escreve = fwrite($fp, $text, strlen($text));

		fclose($fp);

	}
?>