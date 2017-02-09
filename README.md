# apodtest

Написать систему обработки и комментирования RSS-ленты NASA, используя PHP [версии >= 5.3] и любую БД на выбор (предпочтительны MySQL, Firebird/Interbase или PostgreSQL). [Тестовое задание](http://txti.es/test-case)

[Демо](http://andy.bezbozhny.com/apod/)

## скрипты

* feed.php - импорт данных из rss
* ajax.php - скрипт выполнения ajax-запросов
* index.php - глагне, список записей из БД
* unit.php - страница вывода отдельной записи с фото и комментированием
* edit.php - страница редактирования заголовка и описания записи

## база данных

* apod.sql - дамп таблиц
* db.php - массив с доступом к БД
* simpleMySQLi.class.php - класс-обёртка mysqli. [GitHub](https://github.com/andybe29/misc)

## прочее

* .htaccess - правила mod_rewrite
* spectre.min.css - a lightweight, responsive and modern CSS framework. [GitHub](https://github.com/picturepan2/spectre)
* helper.js - хелпер-функции для вывода даты/времени