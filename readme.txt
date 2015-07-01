treereorder

tree.sql             - дамп базы
backend/config.php   - настройка соединения с базой (pdo)
.htaccess            - для работы маршрутов в Apache

Для nginx:

location /treereorder/ {
	try_files $uri $uri/ /treereorder/backend/index.php$is_args$args;
}

demo: http://wasnot.me/treereorder/
