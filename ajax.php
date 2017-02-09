<?php
	# бэкенд
	if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	} else die();

	if (ob_get_length()) ob_clean();

	$ret = ['ok' => false, 'err' => 'system error'];

	$func = function($val) {
		return (is_scalar($val)) ? trim(strip_tags($val)) : $val;
	};
	$post = array_map(function($val) { return trim(strip_tags($val)); }, $_POST);

	# возможные команды
	$actions = ['comments', 'delete', 'post', 'units'];
	if (!isset($post['action']) or !in_array($post['action'], $actions)) goto foo; # https://xkcd.com/292/

	if ($post['action'] != 'units') {
		# во всех действиях, окромя вывода списка записей, требуется наличие id записи
		# проверка на наличие и валидность $_POST['id']
		if (!isset($post['id']) or ($post['id'] = (int)$post['id']) <= 0) goto foo;
	}

	require_once 'db.php';
	require_once 'simpleMySQLi.class.php';

	# создание объекта для работы с БД
	$sql = new simpleMySQLi($db, pathinfo(__FILE__, PATHINFO_DIRNAME));

	if ($post['action'] == 'comments') {
		# вывод комментариев к записи
		$sql->str = 'select dtime, data from apodComments where uid=' . $post['id'] . ' order by id desc';
		$sql->execute();

		$u = $sql->execute() ? $sql->all() : false;
		$sql->free();

		if ($u === false) goto foo; # ошибка выполнения запроса

		# массив для передачи данных во фронт-энд
		$ret['comments'] = [];

		# обработка выбранных данных
		foreach ($u as $r) {
			$r['dtime'] = strtotime($r['dtime']);
			$ret['comments'][] = $r;
		}

		$ret['ok'] = true;
	} else if ($post['action'] == 'delete') {
		# удаление записи

		# удаление из apodUnits
		$sql->str = 'delete from apodUnits where id=' . $post['id'];
		$sql->execute();

		if (!$sql->rows) {
			$ret['err'] = 'ошибка удаления записи либо запись уже удалена';
			goto foo;
		}

		# удаление из apodComments
		$sql->str = 'delete from apodComments where uid=' . $post['id'];
		$sql->execute();

		$ret['ok'] = true;
	} else if ($post['action'] == 'post') {
		# постинг комментария к записи
		# проверка наличия непустого коммента
		if (!isset($post['data']) or !mb_strlen($post['data'])) goto foo;

		# запись в apodComments
		# подготовка данных
		$data = [];
		$data['uid']   = $post['id'];
		$data['dtime'] = $sql->varchar($sql->now());
		$data['data']  = $sql->varchar($post['data']);

		if (false === ($ret['ok'] = $sql->insert('apodComments', $data) ? true : false)) {
			$ret['err'] = 'ошибка добавления комментария';
		}
	} else if ($post['action'] == 'units') {
		# вывод записей из БД
		$sql->str   = [];
		$sql->str[] = 'select u.*, count(c.id) as comments from apodUnits u';
		$sql->str[] = 'left join apodComments c on c.uid=u.id';
		$sql->str[] = 'group by u.id order by u.pubDate desc';
		# выборка результатов
		$u = $sql->execute() ? $sql->all() : false;
		$sql->free();

		if ($u === false) goto foo; # ошибка выполнения запроса

		# массив для передачи данных во фронт-энд
		$ret['units'] = [];
		# обработка выбранных данных
		foreach ($u as $r) {
			$data = [];
			$data['id']       = (int)$r['id'];
			$data['title']    = $r['title'];
			$data['link']     = $r['link'];
			$data['pubDate']  = strtotime($r['pubDate']);
			$data['uploaded'] = strtotime($r['uploaded']);
			$data['comments'] = (int)$r['comments'];

			$ret['units'][] = $data;
		}

		$ret['ok'] = true; # ajax-запрос выполнен успешно
	}

	foo:
	if ($ret['ok']) unset($ret['err']);

	header('Content-Type: application/json');
	echo json_encode($ret);