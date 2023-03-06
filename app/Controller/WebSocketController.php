<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Model\User;
use App\Task\MongoTask;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\Redis;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Phper666\JWTAuth\Util\JWTUtil;

#[SocketIONamespace('/')]
class WebSocketController extends BaseNamespace
{
    #[Inject]
    protected Redis $redis;

    #[Event('event')]
    public function onEvent(Socket $socket, $data)
    {
        return 'Event Data' . $socket->getSid();
    }

//    #[Event('online')]
//    public function onOnline(Socket $socket, $data)
//    {
//        $jwtData = JWTUtil::getParser()->parse($data)->claims()->all();
//        $this->redis->hset('socketio', $jwtData['user_id'], $socket->getSid());
//        $socket->emit('event', '注册成功!');
//    }

    #[Event('say')]
    public function onSay(Socket $socket, $data)
    {
        $sid = $data;

        $this->to($sid)->emit('event', ['a' => 'b', 'c' => 'd']);
        // $data = Json::decode($data);
        // $this->to($data['room']);
        // $socket->to($socket->getSid())->emit('event', $socket->getSid() . " say: {$data['message']}");
//        $this->emit('event', $socket->getSid());
        // return 'hello' . $socket->getSid();
    }

    #[Event('disconnect')]
    public function onDisconnect(Socket $socket)
    {
        $query = $socket->getRequest()->getQueryParams();
        $token = $query['token'] ?? null;
        if ($token) {
            $this->redis->hDel('ws:socketio', $this->getUserId($token));
        }
    }

    #[Event('connect')]
    public function onConnect(Socket $socket)
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        $token = $request->query('token');
        if (! $token) {
            $socket->disconnect();
        } else {
            $user_id = $this->getUserId($token);
            $this->redis->hset('ws:socketio', $user_id, $socket->getSid());
            $results = $this->getUnreadCount($user_id);
            $socket->emit('getMessageCount', $results);
        }
    }

    #[Event('sendMessage')]
    public function onSendMessage(Socket $socket, $token, $message)
    {
        $me = $this->getUserId($token);
        if (! isset($message['content']) || ! $message['content']) {
            return false;
        }
        if (! isset($message['msg_type']) || ! in_array($message['msg_type'], ['text', 'image', 'video', 'card'])) {
            return false;
        }
        $sid = $this->redis->hGet('ws:socketio', $message['user_id']);
        $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
        $results = $mongoClient->query('nursery.conversations', ['participants' => ['$all' => [$message['user_id'], $me]]]);
        if (! $results) {
            $mongoClient->insert('nursery.conversations', [
                'created_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
                'participants' => [$message['user_id'], $me],
            ]);
            $conversationId = $mongoClient->query('nursery.conversations', ['participants' => ['$all' => [$message['user_id'], $me]]])[0]->_id->__toString();
        } else {
            $conversationId = $results[0]->_id->__toString();
        }
        $msg = [
            'content' => $message['content'],
            'created_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
            'msg_type' => $message['msg_type'],
            'read' => false,
            'receiver' => $message['user_id'],
            'sender' => $me,
            'coversation_id' => $conversationId,
        ];
        $messageId = $mongoClient->insert('nursery.messages', $msg);
        if ($sid) {
            $socket->to($sid)->emit('getMessageCount', $this->getUnreadCount($message['user_id']));
            $contact = User::findFromCache($message['user_id']);
            $mySelf = User::findFromCache($me);
            $socket->to($sid)->emit('receive_conversation', [
                'c_id' => strval($conversationId),
                'contact' => [
                    'id' => $me,
                    'avatar' => $mySelf->full_avatar,
                    'nickname' => $mySelf->name,
                ],
                'last_message' => $mongoClient->query(
                    'nursery.messages',
                    ['receiver' => $message['user_id'], 'coversation_id' => $conversationId],
                    ['sort' => ['created_at' => -1], 'limit' => 1]
                )[0],
                'unread_count' => $this->getConversationUnreadCount($message['user_id'], $conversationId),
            ]);
            // 推送消息
            $socket->to($sid)->emit('receiveMessage', array_merge(['message_id' => $messageId], $msg));
        }
        return array_merge(['_id' => ['$oid' => $messageId]], ['message_id' => $messageId], array_merge($msg, ['contact_avatar' => User::findFromCache($message['user_id'])->full_avatar]));
    }

    /**
     *获取未读数量.
     */
    #[Event('getUnreadCount')]
    public function onGetUnreadCount(Socket $socket, $token)
    {
        $userId = $this->getUserId($token);
        return $this->getUnreadCount($userId);
    }

    // 获取未读消息总数
    protected function getUnreadCount($user_id)
    {
        $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
        $results = $mongoClient->count('nursery', 'messages', ['receiver' => $user_id, 'read' => false]);
        return $results ? $results[0]->n : 0;
    }

    // 获取某个会话的未读消息数量
    protected function getConversationUnreadCount($user_id, $conversationId)
    {
        // 获取未读消息总数
        $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
        $results = $mongoClient->count('nursery', 'messages', ['receiver' => $user_id, 'read' => false, 'coversation_id' => $conversationId]);
        return $results ? $results[0]->n : 0;
    }

//    #[Event('getMessageCount')]
//    public function onGetMessageCount(Socket $socket){
//        $query = $socket->getRequest()->getQueryParams();
//        $token = $query['token'] ?? null;
//        if ($token) {
//            $user_id = $this->getUserId($token);
//            $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
//            $results = $mongoClient->query('nursery.messages', ['user_id' => $user_id], []);
//            var_dump($results);
//            $socket->emit('getMessageCount', 0);
//        }
//    }

    protected function getUserId(string $token)
    {
        $jwtData = JWTUtil::getParser()->parse($token)->claims()->all();
        return $jwtData['user_id'];
    }
}
