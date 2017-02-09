<?php
	# импорт фида
	$url = 'https://www.nasa.gov/rss/dyn/lg_image_of_the_day.rss';

	# попытка получения контента
	$obj = simplexml_load_file($url) or die('failed');

	require 'db.php';
	require 'simpleMySQLi.class.php';

	# создание объекта для записи в БД
	$sql = new simpleMySQLi($db, pathinfo(__FILE__, PATHINFO_DIRNAME));

	# кол-во добавленных записей
	$added = 0;

	# парсинг
	foreach ($obj->channel->item as $node) {
		$attr = (array)$node->enclosure;

		# принимаются только записи с mime-типом "image/jpeg"
		# поскольку из текущей версии канала неясно, какие ещё могут быть и как их выводить
		if ($attr['@attributes']['type'] !== 'image/jpeg') continue;

		$data = [];
		$data['title']       = $sql->varchar($node->title);
		$data['link']        = $sql->varchar($node->link);
		$data['description'] = $sql->varchar($node->description);
		$data['url']         = $sql->varchar($attr['@attributes']['url']);
		$data['pubDate']     = $sql->varchar(date('Y-m-d H:i:s', strtotime($node->pubDate)));

		# проверка записи на существование
		# replace не подходит, ибо будет изменяться значение apodUnits.id
		# а оно нужно для связи с apodComments
		$sql->str = 'select * from apodUnits where link=' . $data['link'];
		$sql->execute();
		$sql->free();

		if ($sql->rows) continue;

		if ($sql->insert('apodUnits', $data) === false) {
			die('mysql error');
		} else if ($sql->rows) {
			$added ++;
		}
	}

	echo $added . ' new records added';
