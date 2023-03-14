<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Model\User;
use App\Task\MongoTask;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Utils\ApplicationContext;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Query;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class MessageController extends AbstractController
{
    #[GetMapping(path: 'getConversationList')]
    public function getConversationList()
    {
        $userId = $this->request->getAttribute('userId');
        $page = $this->request->query('page', 1);
        $page = intval($page);
        $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
        $conversations = $mongoClient->getConversations('nursery', 'conversations', $userId, $page);
        $return_cons = [];
        foreach ($conversations as $conversation) {
            $contact = implode('', array_filter($conversation->document->participants, function ($val) use ($userId) {return $val != $userId; }));
            $contact = User::findFromCache($contact);
            $temp['c_id'] = $conversation->_id;
            $temp['contact'] = [
                'id' => $contact->id,
                'avatar' => $contact->full_avatar,
                'nickname' => $contact->name,
            ];
            $temp['last_message'] = $conversation->msgs ? $conversation->msgs[0] : [];
            $unreadCount = $mongoClient->getUnreadCount($conversation->_id, $userId);
            $temp['unread_count'] = $unreadCount ? $unreadCount[0]->n : 0;
            $return_cons[] = $temp;
        }
        return $this->success($return_cons);
    }

    #[PostMapping(path: 'readAll')]
    public function readAll()
    {
        $userId = $this->request->getAttribute('userId');
        $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
        $mongoClient->readAll($userId);
        return $this->success(true);
    }

    #[PostMapping(path: 'readConversation')]
    public function readConversation()
    {
        $userId = $this->request->getAttribute('userId');
        $conversationId = $this->request->post('c_id');
        if (! $conversationId) {
            $this->failed();
        }
        $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
        $mongoClient->readConversation($userId, $conversationId);
        return $this->success(true);
    }

    #[PostMapping(path: 'readOne')]
    public function readOne()
    {
        $userId = $this->request->getAttribute('userId');
        $messageId = $this->request->post('message_id');
        if (! $messageId) {
            $this->failed();
        }
        $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
        $mongoClient->readOne($userId, $messageId);
        return $this->success(true);
    }

    #[GetMapping(path: 'getChatList')]
    public function getChatList()
    {
        $userId = $this->request->getAttribute('userId');
        $conversationId = $this->request->query('c_id');
        $page = $this->request->query('page', 1);
        if (! $conversationId) {
            return $this->failed();
        }
        $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
        $list = $mongoClient->getChatList($page, $conversationId);
        foreach ($list as $k => $value){
            if ($value->msg_type == 'image' || $value->msg_type == 'video'){
                $list[$k]->content = config('file.storage.oss.prefix') . '/' . $value->content;
            }
        }
        // 获取聊天对象的头像
        // $conversation = $mongoClient->query('nursery.conversations', ['_id' => new ObjectId($conversationId)]);
        // $chatObjIds = $conversation[0]->participants;
        // $chatObjId = array_diff($chatObjIds, [$userId]);
        // $chatUser = User::first($chatObjId[0]);
        // $return = ['list' => $list, 'chatUser' => $chatUser->getAttributes()];
        return $this->success($list);
    }

    #[GetMapping(path: 'createConversation')]
    public function createConversation()
    {
        $userId = $this->request->getAttribute('userId');
        $conversationId = $this->request->query('c_id');
        $page = $this->request->query('page', 1);
        if (! $conversationId) {
            return $this->failed();
        }
        $mongoClient = ApplicationContext::getContainer()->get(MongoTask::class);
        $list = $mongoClient->getChatList($page, $conversationId);
        // 获取聊天对象的头像
        // $conversation = $mongoClient->query('nursery.conversations', ['_id' => new ObjectId($conversationId)]);
        // $chatObjIds = $conversation[0]->participants;
        // $chatObjId = array_diff($chatObjIds, [$userId]);
        // $chatUser = User::first($chatObjId[0]);
        // $return = ['list' => $list, 'chatUser' => $chatUser->getAttributes()];
        return $this->success($list);
    }
}
