<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FinancePublisher
{
    private string $queue;
    private string $notificationQueue;

    public function __construct()
    {
        $this->queue             = env('RABBITMQ_QUEUE', 'order.created');
        $this->notificationQueue = env('NOTIFICATION_QUEUE', 'order.notifications');
    }

    public function publishOrderCreated(array $orderData): void
    {
        try {
            $connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST', 'rabbitmq'),
                (int) env('RABBITMQ_PORT', 5672),
                env('RABBITMQ_USER', 'guest'),
                env('RABBITMQ_PASSWORD', 'guest'),
            );

            $channel = $connection->channel();
            $channel->queue_declare($this->queue,             false, true, false, false);
            $channel->queue_declare($this->notificationQueue, false, true, false, false);

            $payload = json_encode([
                'event'       => 'order.created',
                'occurred_at' => now()->toISOString(),
                'data'        => $orderData,
            ]);

            $message = new AMQPMessage($payload, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type'  => 'application/json',
            ]);

            // Publish to finance queue (invoice creation) and notification queue (email/log)
            $channel->basic_publish($message, '', $this->queue);
            $channel->basic_publish($message, '', $this->notificationQueue);

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            \Log::error('FinancePublisher: failed to publish order.created', [
                'order_id' => $orderData['id'] ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
