**NLOYT Backend**

Тестировался на Linux LAMP сервере
Для работы также необходимо установить php расширения pgsql, pthreads

Отправка сообщений в RabbitMQ находится в:
`app/AMQPReciever.php run`

Слушатель сообщений от RabbitMQ находится в:
`app/Console/Kernel.php schedule`
