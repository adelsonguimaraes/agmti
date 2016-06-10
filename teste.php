<html>
<head>
	<title>Teste</title>

	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
</head>
<body >
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    	<div class="container">
    		<div class="navbar-header">
	            <a class="navbar-brand" href="../index.php">
	                ../Seleção de Testes
	            </a> 
        	</div>
    	</div>
	</nav>
	<div class="jumbotron">
		<div class="container-fluid">
			<h3>Teste da Classe</h3>
			<div class="row">
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-heading">
							<h3>Listar</h3> 
						</div>
						<div class="panel-body">
							<div id="lista"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="panel">
						<div class="panel-heading">
							<h3>Cadastro</h3> 
						</div>
						<div class="panel-body">
							<form>
								<div class="form-group">
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
								</div>
								<button type="button" class="btn btn-success">Cadastrar</button>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3>Atualização</h3> 
						</div>
						<div class="panel-body">
							<form>
								<div class="form-group">
									Selecione: 
									<select class="form-control">
										<option value=""></option>
									</select>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
								</div>
								<div><input type="submit" id="cadastrar" name="cadastrar" value="Cadastrar"/></div>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="panel">
						<div class="panel-heading">
							<h3>Buscar por ID</h3> 
						</div>
						<div class="panel-body">
							<form>
								<div class="form-group">
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
								</div>
								<div><input type="submit" id="cadastrar" name="cadastrar" value="Cadastrar"/></div>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="panel">
						<div class="panel-heading">
							<h3>Deletar</h3> 
						</div>
						<div class="panel-body">
							<form>
								<div class="form-group">
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
									Teste: <input class="form-control" type="text" id="teste1" name="teste1" size="50"/>
								</div>
								<div><input type="submit" id="cadastrar" name="cadastrar" value="Cadastrar"/></div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<nav class="navbar navbar-default navbar-fixed-bottom" role="navigation">
    	<footer>
	        <div class="container" style="padding-top:15px; text-align:center;">
	            <p>Gerador de Classes 1.0</p>
	        </div>
	    </footer>
	</nav>
</body>

<script src="https://code.jquery.com/jquery-2.2.4.min.js"   integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="   crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

<script>
	$(function () {

		function listar () {
			$.ajax({
				url: '../rest/banco.php',
				type: 'POST',
				data: '{metodo:listar}',
				success: function (data) {
					var data = $.parseJSON(data);
					$('#lista').html(data);
				}
			});
		}

		listar();

		$('#cadastrar').click(function (){
		});
	
		$('#atualizar').click(function (){
			
		});
	
		$('#deletar').click(function (){
			
		});
	});
</script>

</html>
