<html>
<head>
	<title>Teste</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">";
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

<script src="https://code.jquery.com/jquery-2.2.4.min.js"   integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="   crossorigin="anonymous"></script>";
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>";

<script>
	$(function () {

		function listar () {
			$.ajax({
				url: '../rest/banco.php',
				type: 'POST',
				data: '{metodo:listar}',
				success: function (data) {
					
				}
			});
		}

		$('#cadastrar').click(function (){

		});
	
		$('#atualizar').click(function (){
			
		});
	
		$('#deletar').click(function (){
			
		});
	});
</script>

</html>
