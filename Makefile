restart: stop start

icaptureUp:
	php ./service/run.php & > /dev/null 2>&1

workerUp:
	QUEUE=icapture APP_INCLUDE='./service/require.php' php './vendor/chrisboulton/php-resque/resque.php' & > /dev/null 2>&1

start: workerUp icaptureUp

stop:
	ps -ef |grep "php ./service/run.php" | grep -v grep | awk '{print $$2}'|xargs kill -9
	ps -ef |grep "php './vendor/chrisboulton/php-resque/resque.php'" | grep -v grep | awk '{print $$2}'|xargs kill -9
	ps -ef |grep "php ./vendor/chrisboulton/php-resque/resque.php" | grep -v grep | awk '{print $$2}'|xargs kill -9
