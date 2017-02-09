<?php
	# редактирование записи
	if (isset($_GET['uri']) and $page['uri'] = $_GET['uri']) {
		require_once 'db.php';
		require_once 'simpleMySQLi.class.php';

		# создание объекта для работы с БД
		$sql = new simpleMySQLi($db, pathinfo(__FILE__, PATHINFO_DIRNAME));

		# поиск записи по URI
		$sql->str = 'select * from apodUnits where link like ' . $sql->varchar('%' . $page['uri']);
		$r = $sql->execute() ? $sql->assoc() : false;
		$sql->free();

		if (!$sql->rows) {
			$page['err'] = 'запись не найдена';
			goto foo;
		}

		$page = array_merge($page, $r);
	} else {
		$page['err'] = 'URI не задан или неверен';
	}

	foo:
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo isset($page['err']) ? 'apod test' : $page['title'] ?></title>
		<link rel="stylesheet" href="/apod/spectre.min.css">
	</head>
	<body>
<?php
	if (isset($page['err'])) {
?>
		<div class="container" style="width: 128rem">
			<ul class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="/apod/">Home</a>
			    </li>
			    <li class="breadcrumb-item">
			    	error
			    </li>
			</ul>
			<div class="columns">
				<div class="column col-12">
					<p class="toast toast-danger">Ошибка: <?php echo $page['err']; ?></p>
				</div>
			</div>
		</div>
<?php
	} else {
?>
		<input id="unit" type="hidden" data-id="<?php echo $page['id'] ?>">
		<div class="container" style="width: 128rem">
			<div class="columns">
				<ul class="breadcrumb">
					<li class="breadcrumb-item">
						<a href="/apod/">Home</a>
				    </li>
				    <li class="breadcrumb-item">
				    	<a href="/apod/<?php echo $page['uri']; ?>"><?php echo $page['title'] ?></a>
				    </li>
				    <li class="breadcrumb-item">
				    	редактирование записи
				    </li>
				</ul>
				<div class="column col-12">
					<div class="form-horizontal">
						<div class="form-group">
							<div class="col-2">
								<label class="form-label" for="title">заголовок:</label>
							</div>
							<div class="col-10">
								<input class="form-input" type="text" id="title" placeholder="обязательно введите текст заголовка!" value="<?php echo htmlspecialchars($page['title']) ?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-2">
								<label class="form-label" for="description">описание:</label>
							</div>
							<div class="col-10">
								<textarea class="form-input" id="description" placeholder="введите сюда текст описания, но можно оставить и пустым" rows="3"><?php echo htmlspecialchars($page['description']) ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<div class="col-2"></div>
							<div class="col-2">
								<button class="btn btn-block btn-primary" id="save">сохранить</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script>
			var settings = { loading : false };
			$(function() {
				settings.id = $('#unit').data('id');

				$('#save').on('click', {'action': 'save'}, actionHandler);

				$.ajaxSetup({
					type       : 'post',
					dataType   : 'json',
					url        : '/apod/ajax.php',
					cache      : false,
					timeout    : 300000,
					beforeSend : function() { settings.loading = true; },
					complete   : function() { settings.loading = false; },
					error      : function(xhr, status) { if (status == 'timeout') alert('Превышено время ожидания ответа'); }
				});
			});

			function actionHandler(e) {
				if (settings.loading || !settings.id) return;

				var post = {};
				$.each(e.data, function(key, val) { post[key] = val; });
				try { console.log(post) } catch(err) {};

				post.id = settings.id;

				if (post.action == 'save') {
					var $this = $('#title');
					post.title = $.trim($this.val());

					if (post.title.length == 0) {
						alert('обязательно введите текст заголовка!');
						$this.focus();
						return;
					}

					post.description = $.trim($('#description').val());

					$.ajax({
						data    : post,
						success : function(data) {
							if (data.ok) {
								location.href = location.href.replace('\/edit', '');
							} else {
								alert(data.err);
							}
						}
					});
				}
			}
		</script>
<?php
	}
?>
	</body>
</html>