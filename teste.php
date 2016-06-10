<html>
<head>
	<title>Teste</title>
</head>
<body bgcolor="#666">
	<div style="background:#0099e6; margin:200px 500px; text-align:center;">
		<h1>Teste da Class </h1>
		<form method="post" action="" style="background:#b3e6ff; padding:30px;">
			<div>Teste: <input type="text" id="teste1" name="teste1" size="50"/></div>
			<div>Teste: <input type="text" size="50"/></div>
			<div>Teste: <input type="text" size="50"/></div>
			<div>Teste: <input type="text" size="50"/></div>
			<div><input type="submit" id="cadastrar" name="cadastrar" value="Cadastrar"/></div>
		</form>
	</div>
</body>
</html>

<?php
	if(isset($_POST)) {
		var_dump($_POST);
	}
?>