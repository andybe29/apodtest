<?php
	# вывод отдельной фотографии и комментариев
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

		$page = $r;
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
		<link rel="stylesheet" href="spectre.min.css">
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
				    	<?php echo $page['title'] ?>
				    </li>
				</ul>
				<div class="column col-12">
					<h4 class="text-bold"><?php echo $page['title'] ?></h4>
					<p><img src="<?php echo $page['url'] ?>" class="img-responsive"></p>
					<p class="text-justify"><?php echo $page['description'] ?></p>
					<p><a href="<?php echo $page['link'] ?>"><?php echo $page['link'] ?></a></p>
				</div>
				<div class="column col-12">
					<div class="form-horizontal">
						<div class="form-group">
							<div class="col-12">
								<input class="form-input" type="text" id="comment" placeholder="введите текст комментария и нажмите enter...">
							</div>
						</div>
					</div>
				</div>
				<div class="column col-12" id="comments">
				</div>
			</div>
		</div>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script type="text/javascript" src="helper.js"></script>
		<script>
			var settings = { loading : false };
			$(function() {
				settings.id = $('#unit').data('id');

				$('a[href^="http"]').on('click', function() { window.open(this.href); return false });

				$('#comment').on('keypress', function(e) {
					var code = (e.keyCode ? e.keyCode : e.which);
					if (code == 13) actionHandler({'data': {'action': 'post'}})
				});

				$.ajaxSetup({
					type       : 'post',
					dataType   : 'json',
					url        : 'ajax.php',
					cache      : false,
					timeout    : 300000,
					beforeSend : function() { settings.loading = true; },
					complete   : function() { settings.loading = false; },
					error      : function(xhr, status) { if (status == 'timeout') alert('Превышено время ожидания ответа'); }
				});
				// вывод комментариев
				actionHandler({'data': {'action': 'comments'}});
			});

			function actionHandler(e) {
				if (settings.loading || !settings.id) return;

				var post = {};
				$.each(e.data, function(key, val) { post[key] = val; });
				try { console.log(post) } catch(err) {};

				post.id = settings.id;

				if (post.action == 'comments') {
					var $out = $('#comments'); $out.empty();

					$.ajax({
						data    : post,
						success : function(data) {
							if (data.ok) {
								$.each(data.comments, function() {
									$out.append('<p><b>' + this.dtime.toDate().phpDate('d.m.Y H:i:') + '</b> ' + this.data + '</p>');
								});
							} else {
								$out.html('<p class="toast toast-danger">' + data.err + '</p>');
							}
						}
					});
				} else if (post.action == 'post') {
					var $this = $('#comment');
					post.data = $.trim($this.val());

					if (post.data.length == 0) {
						alert('напишите же что-нибудь!');
						$this.focus();
						return;
					}

					$.ajax({
						data    : post,
						success : function(data) {
							if (data.ok) {
								settings.loading = false;
								$this.val('');
								actionHandler({'data': {'action': 'comments'}});
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