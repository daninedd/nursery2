<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Task;

use Hyperf\Task\Annotation\Task;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

class MongoTask
{
    /* @var Manager */
    public $manager;

    #[Task]
    public function insert(string $namespace, array $document)
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
        $bulk = new BulkWrite();
        $r = $bulk->insert($document);
        $result = $this->manager()->executeBulkWrite($namespace, $bulk, $writeConcern);
        return $result->getInsertedCount() ? $r->__toString() : null;
    }

    #[Task]
    public function query(string $namespace, array $filter = [], array $options = [])
    {
        $query = new Query($filter, $options);
        $cursor = $this->manager()->executeQuery($namespace, $query);
        return $cursor->toArray();
    }

    #[Task]
    public function count(string $namespace, string $collection, $filters = [], $options = [])
    {
        $command = new Command(['count' => $collection, 'query' => $filters]);
        return $this->manager()->executeCommand($namespace, $command)->toArray();
    }

    #[Task]
    public function getUnreadCount($conversationId, $receiver)
    {
        $command = new Command(['count' => 'messages', 'query' => ['coversation_id' => $conversationId, 'receiver' => $receiver, 'read' => false]]);
        return $this->manager()->executeCommand('nursery', $command)->toArray();
    }

    #[Task]
    public function getConversations(string $namespace, string $collection, $userId, $page)
    {
        $pageSize = 50;
//        $skip = [
//            '$skip' => ($page - 1) * $pageSize,
//        ];
        $limit = [
            '$limit' => $pageSize,
        ];
        $document = [
            'aggregate' => $collection,
            'pipeline' => [
                [
                    '$match' => [
                        'deleted_at' => null,
                        'participants' => ['$in' => [$userId]],
                    ],
                ],
                [
                    '$project' => [
                        '_id' => ['$toString' => '$_id'],
                        'document' => '$$ROOT',
                    ],
                ],
                [
                    '$lookup' => [
                        'from' => 'messages',
                        'localField' => '_id',
                        'foreignField' => 'coversation_id',
                        'as' => 'msgs',
                        'pipeline' => [
                            [
                                '$project' => [
                                    'sender' => 1,
                                    'receiver' => 1,
                                    'content' => 1,
                                    'msg_type' => 1,
                                    'read' => 1,
                                    'created_at' => 1,
                                ],
                            ],
                            ['$sort' => ['created_at' => -1]],
                            ['$limit' => 1],
                        ],
                    ],
                ],
                [
                    '$sort' => [
                        'msgs.created_at' => -1,
                    ],
                ],
                // $skip,
                $limit,
            ],
            'cursor' => new \stdClass(),
            'allowDiskUse' => false,
        ];
        // todo 会话记录分页
        $command = new Command($document);
        $cursor = $this->manager()->executeCommand($namespace, $command);
        $re = [];
        foreach ($cursor as $arr) {
            $re[] = $arr;
        }
        return $re;
    }

    #[Task]
    public function readAll($userId)
    {
        $bulk = new BulkWrite();
        $bulk->update(
            ['receiver' => $userId, 'read' => false],
            ['$set' => ['read' => true]],
            ['multi' => true]
        );
        $result = $this->manager()->executeBulkWrite('nursery.messages', $bulk);
        return $result->getModifiedCount();
    }

    #[Task]
    public function readConversation($userId, $conversationId)
    {
        $bulk = new BulkWrite();
        $bulk->update(
            ['receiver' => $userId, 'read' => false, 'coversation_id' => $conversationId],
            ['$set' => ['read' => true]],
            ['multi' => true]
        );
        $result = $this->manager()->executeBulkWrite('nursery.messages', $bulk);
        return $result->getModifiedCount();
    }

    #[Task]
    public function readOne($userId, $messageId)
    {
        $bulk = new BulkWrite();
        $bulk->update(
            ['receiver' => $userId, 'read' => false, '_id' => new ObjectId($messageId)],
            ['$set' => ['read' => true]],
            ['multi' => false]
        );
        $result = $this->manager()->executeBulkWrite('nursery.messages', $bulk);
        return $result->getModifiedCount();
    }

    #[Task]
    public function getChatList($page, $conversationId)
    {
        $pageSize = 20;
        $skip = ($page - 1) * $pageSize;
        $query = new Query(['coversation_id' => $conversationId], [
            'sort' => ['created_at' => -1],
            'limit' => $pageSize,
            'skip' => $skip,
        ]);
        $cursor = $this->manager()->executeQuery('nursery.messages', $query);
        return $cursor->toArray();
    }

    protected function manager()
    {
        if ($this->manager instanceof Manager) {
            return $this->manager;
        }

        $uri = env('MONGO_URI', 'mongodb://127.0.0.1');
        return $this->manager = new Manager($uri, ['username' => env('MONGO_USER'), 'password' => env('MONGO_PASSWORD')]);
    }
}
