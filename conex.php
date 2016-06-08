<?php
	$con = mysqli_connect("localhost","root","", "sgaf");

	if (mysqli_connect_error()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$sql = 'SHOW TABLES FROM sgaf';

	$result = mysqli_query($con, $sql);

	if(!$result) {
		echo "Erro: " . mysqli_error($con);
	}

	while ($row = mysqli_fetch_object($result)) {

		
		$sql = "SHOW COLUMNS FROM " . $row->Tables_in_sgaf;

		$resultColls = mysqli_query($con, $sql);

		if(!$resultColls) {
			echo "Erro " . mysqli_error($con);
		}

		echo "---------------------------------<br>";
		echo "-- Tabela " .$row->Tables_in_sgaf." --<br>";
		echo "---------------------------------<br>"; 
		$data = '';
		while ($row2 = mysqli_fetch_object($resultColls)) {
			Echo "Nome: ". $row2->Field . " | Tipo: ".$row2->Type. " | Nulo: ". $row2->Null. " | Chave: ". $row2->Key ." | Default: ". $row2->Default ." | Extra: ". $row2->Extra ."</br>";
		}
		createClass($row->Tables_in_sgaf, $data);
		exit;
		echo "================================================================</br>";

	}

	function createClass ($class, $data) {
		echo count($data); exit;

		if(!file_exists('src')) mkdir('src');
		if(!file_exists('src/'.$class)) mkdir('src/'.$class);
		
		$fp = fopen('src/'.$class.'/'.$class.".php", "a");
		
		$text = "<?php\n";
		$text .= "// model : ".$class."\n\n";

		$text .= "Class ". ucfirst($class) ." implements JsonSerializable {\n";
		$text .= "//atributos\n";
		
		$escreve = fwrite($fp, $text, strlen($text));

		fclose($fp);
	}

	function createContentModel ($class, $data) {
		$fp = fopen('src/'.$class.'/'.$class.".php", "a");
		
		$text = "private ".$data->Field. ";\n";

		$escreve = fwrite($fp, $text, strlen($text));

		fclose($fp);
	}

?>