<?php

include './vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Текущий скрипт является основой для эмулятора устройства.
 * Основная его задача быть постоянно рабочим и всегда в состоянии ожидания команды от сервера.
 * Запускается единажды администратором сервера и является эмуляцией созданного устройства.
 */

/**
 * Не ограничивается время работы устройства.
 */
set_time_limit(0);

/**
 * Аргументом во время запуска является его уникальный пин.
 * Сейчас пины выдаются устройствам от 123450 и на повышение для пользовательских устройств и от 223450 для устройств сервисного центра.
 */
$pin = $argv[1];

/**
 * Устанавливается соединение с RabbitMQ сервером.
 * Адресом локально указывается имя docker контейнера, для глобального использования указываеся адрес сервер.
 * Текущее значение 194.182.85.89
 */
$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
$channel = $connection->channel();

/**
 * Очередь для отправки сообщения, однако данный скрипт отправку сообщений не осуществляет, но указание этого параметра неообходимо для работы программы.
 */
$channel->queue_declare('pin_'.$pin, false, false, false, false);

/**
 * @param AMQPMessage $message
 *
 * Объявляется обработчик получения команд.
 * Он анализирует тело сообщения и выполняет содержащуюся в нем команду, если она известна эмулятору устройства.
 */
$callback = function(AMQPMessage $message) use ($channel, $pin) {
    $command = json_decode($message->body, true);
    if ($command['action'] == 'include') {
        shell_exec('php device_template_cron.php '.$pin.' >> /dev/null &');
    } elseif ($command['action'] == 'bridge') {
        shell_exec('php device_socket_connection.php '.$command['url'].' >> /dev/null &');
    }
};

/**
 * Указывается очередь для прослушивания и параметры соединения, а также обработчик получения сообщений.
 * Имя очереди формируется как "pin_" + уникальный выданный устройству пин.
 */
$channel->basic_consume('pin_'.$pin, '', false, true, false, false, $callback);

/**
 * Устанавливается постоянная прослушка новых сообщений и обработка их получения.
 */
while(count($channel->callbacks)) {
    try {
        $channel->wait();
    } catch (ErrorException $e) {}
}

/**
 * Закрывается соединение в случае закрытия очереди или возникшей проблемы.
 * На практике никогда не происходило.
 */
$channel->close();
try {
    $connection->close();
} catch (Exception $e) {}
