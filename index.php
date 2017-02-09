<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>apod test</title>
		<link rel="stylesheet" href="spectre.min.css">
	</head>
	<body>
		<div class="container" style="width: 128rem">
			<div class="columns">
				<div class="column col-2">
					<h4 class="text-bold">apod test</h4>
					<p><a href="https://github.com/andybe29/apodtest">GitHub</a></p>
				</div>
			</div>
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>id</th>
						<th colspan="2">actions</th>
						<th>title</th>
						<th>pubdate</th>
						<th>uploaded</th>
						<th>comments</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script type="text/javascript" src="helper.js"></script>
		<script>
			var settings = { loading : false };

			$(function() {
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
				// вывод записей из БД
				actionHandler({'data': {'action': 'units'}});
			});

			function actionHandler(e) {
				if (settings.loading) return;

				var post = {}, $out = $('tbody');
				$.each(e.data, function(key, val) { post[key] = val; });
				try { console.log(post) } catch(err) {};

				if (post.action == 'delete') {
					var $this = $out.find('tr[data-id="' + post.id + '"]');
					if (!confirm('Delete «' + $this.find('td').eq(3).text() + '»?')) return;

					$.ajax({
						data    : post,
						success : function(data) {
							if (data.ok) {
								$this.remove();
							} else {
								alert(data.err);
							}
						}
					});
				} else if (post.action == 'units') {
					$out.empty();

					$.ajax({
						data    : post,
						success : function(data) {
							if (data.ok) {
								$.each(data.units, function() {
									var h = [], uri = this.link.split('/').pop(); // smart URI
									h.push('<tr data-id="' + this.id + '">');
									h.push('<td>' + this.id + '</td>');
									h.push('<td class="col-1"><a href="' + uri + '/edit" class="btn btn-block btn-sm">edit</a></td>');
									h.push('<td class="col-1"><button class="btn btn-block btn-sm">delete</button></td>');
									h.push('<td><a href="' + uri + '">' + this.title + '</a></td>');
									h.push('<td><a href="' + this.link + '">' + this.pubDate.toDate().phpDate('d.m.Y H:i') + '</a></td>');
									h.push('<td>' + this.uploaded.toDate().phpDate('d.m.Y H:i') + '</td>');
									h.push('<td>' + this.comments + '</td>');
									h.push('</tr>');

									$out.append(h.join(''));
								});

								$out.find('a[href^="http"]').on('click', function() { window.open(this.href); return false });

								$.each($out.find('button'), function() {
									var params = {'action': 'delete', 'id': $(this).parents('tr').data('id')};
									$(this).on('click', params, actionHandler);
								});

								$out.append('<tr><td colspan="7"><div class="toast toast-primary">' + data.units.length + ' record(s) selected</div></td></tr>');
							} else {
								$out.html('<tr><td colspan="7"><div class="toast toast-danger">' + data.err + '</div></td></tr>');
							}
						}
					});
				}
			}
		</script>
	</body>
</html>