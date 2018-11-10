<?php
namespace App\Models;

use App\Config\DB;
use App\Models\User;
use App\Models\ChatSQL;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $sql_connect;
    private $users;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
//        var_dump($this->users);

        $db = new DB;
        $this->sql_connect = $db->connect();

    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later

        $params = $conn->httpRequest->getUri()->getQuery();
        $draft_array = explode('&', $params);

        foreach ($draft_array as $result) {
            $res = explode('=', $result);
            $res_array[$res[0]] = $res[1];
        }

        $this->clients->attach($conn);
        $this->users[] = [
            "conn" => $conn,
            "conn_id" => $conn->resourceId,
            "who" => $res_array['who'],
            "whom" => $res_array["whom"]
        ];

        ChatSQL::updateThisUserOnline($res_array['who'], time(), 1, $this->sql_connect);

        foreach ($this->users as $user) {
            echo($user['conn_id'] . " - who: " . $user['who'] . " to: " . $user['whom'] . PHP_EOL);
        }
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {

        $res_array = json_decode($msg, true);
        $res_array['msg'] = isset($res_array['msg']) ? htmlspecialchars($res_array['msg'], ENT_QUOTES) : '';
        $res_array['date'] = date("h:i:s - j M Y");
//        $msg = json_encode($res_array);

        // проверяем не поддельное ли это сообщение
        $check = 0;
        foreach ($this->users as $user) {                   // проверяем всех юзеров, которые у нас есть
            if ($user['conn_id'] == $from->resourceId) {    // если мы нашли у себя в списке юзера, который только что отправил сообщение
                if ($user['who'] == $res_array['who']) {    // если этот юзер - это реально тот, кто отправил сообщение
                    $check = 1;
                }
            }
        }

        // если проверка на подлинность не пройдена (то-есть юзер коннекшна не соответствует юзеру-отправителю сообщения)
        if ($check == 0) {
            $from->send("Error!!! Things that you are trying to do are forbidden");
            return;
        }

        $sender = ChatSQL::getUserName($res_array['who'], $this->sql_connect)[0];
        $receiver = ChatSQL::getUserName($res_array['whom'], $this->sql_connect)[0];
        $res_array['sender'] = $sender['firstname'] . " " . $sender['lastname'];
        $res_array['receiver'] = $receiver['firstname'] . " " . $receiver['lastname'];
        $msg = json_encode($res_array);

//        $numRecv = count($this->clients) - 1;

        // если тип - message - находим коннекшн того, кто отправил, смотрим кому отправил, проверяем можуг ли общаться и только после - отправляем сообщение
        if ($res_array['type'] == 'msg') {
            // проверяем могут ли юзеры переписываться впринципе
            if (!(ChatSQL::checkConnection($res_array['who'], $res_array['whom'], $this->sql_connect))) {
                $from->send("Error!!! You can't communicate with this user");
                return;
            }

            // если сообщение пустое - отправляем клиенту сообщение об ошибке, что сообщение пустое
            if (!strlen($res_array['msg'])) {
                $from->send("Error!!! You are trying to send empty message");
                return;
            }

            // сохраняем сообщение в базу
            ChatSQL::sendMessageToDB($res_array['who'], $res_array['whom'], $res_array['msg'], time(), $this->sql_connect);
            ChatSQL::sendNotification($res_array['whom'], $res_array['sender'] . " sent you new message", 0, time(), $res_array['who'], $this->sql_connect);

            // отправляем ответ отправителю сообщения
            foreach ($this->users as $user) {
                // находим конекшны того, кто отправил сообщение
                if ($user['who'] == $res_array['who']) {
                    $user['conn']->send($msg);
                }
            }

            // отправляем ответ получателю сообщения
            foreach ($this->users as $user) {
                if ($user['who'] == $res_array['whom']) {
                    $user['conn']->send($msg);
                }
            }
        }

        // если клиент сделал поставил лайк или дизлайк. Отработать только нотификейшны. В базу будет слать аякс через HTTP
        else if ($res_array['type'] == 'like' || $res_array['type'] == 'dislike') {

            if ($res_array['type'] == 'like') {
                ChatSQL::sendNotification($res_array['whom'], $res_array['sender'] . " like you", 0, time(), $res_array['who'], $this->sql_connect);
            } else {
                ChatSQL::sendNotification($res_array['whom'], $res_array['sender'] . " dislike you", 0, time(), $res_array['who'], $this->sql_connect);
            }

            foreach ($this->users as $user) {
                if ($user['who'] == $res_array['whom']) {
                    $user['conn']->send($msg);
                }
            }
        }
        else if ($res_array['type'] == 'connect' || $res_array['type'] == 'disconnect') {

            if ($res_array['type'] == 'connect') {
                ChatSQL::sendNotification($res_array['whom'], "You have a new connection with " . $res_array['sender'], 0, time(), $res_array['who'], $this->sql_connect);
            } else {
                ChatSQL::sendNotification($res_array['whom'], "You were disconnected from " . $res_array['sender'], 0, time(), $res_array['who'], $this->sql_connect);
            }

            foreach ($this->users as $user) {
                if ($user['who'] == $res_array['whom'] || $user['who'] == $res_array['who']) {
                    $user['conn']->send($msg);
                }
            }
        }
        // если клиент просмотрел страницу юзера
        else if ($res_array['type'] == 'view') {
            $res = json_encode(ChatSQL::updateViews($res_array['who'], $res_array['whom'], time(), $this->sql_connect));
            if ($res) {

                ChatSQL::sendNotification($res_array['whom'], "Your page was viewed by " . $res_array['sender'], 0, time(), $res_array['who'], $this->sql_connect);

                foreach ($this->users as $user) {
                    if ($user['who'] == $res_array['whom']) {
                        $user['conn']->send($msg);
                    }
                }
            }
        } else if ($res_array['type'] == 'block') {

            ChatSQL::sendNotification($res_array['whom'], "You has been blocked by " . $res_array['sender'], 0, time(), $res_array['who'], $this->sql_connect);

            foreach ($this->users as $user) {
                if ($user['who'] == $res_array['whom']) {
                    $user['conn']->send($msg);
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {

        $who = null;
        $isOnline = false;

        // The connection is closed, remove it, as we can no longer send it messages
        foreach ($this->users as $index => $user) {
            if ($user['conn_id'] == $conn->resourceId) {
                $who = $user['who'];
                ChatSQL::updateThisUserOnline($user['who'], time(), 1, $this->sql_connect);
                unset($this->users[$index]);
            }
        }

        foreach ($this->users as $user) {
            if ($user['who'] == $who) {
                $isOnline = true;
                break;
            }
        }

        if (!$isOnline)
            ChatSQL::updateThisUserOnline($who, time(), 0, $this->sql_connect);

        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
