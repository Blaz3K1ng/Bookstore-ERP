<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * FinancePublisher
 *
 * Publishes order.created events to RabbitMQ so the Finance Service
 * can asynchronously create invoices.
 *
 * Communication method: Message Broker (RabbitMQ)  (satisfies inter-service requirement #2)
 */
class FinancePublisher
{
    private string $queue;

    public function __construct()
    {
        $this->queue = env('RABBITMQ_QUEUE', 'order.created');
    }

    /**
     * Publish an order.created event.
     * Finance Service consumer will pick this up and create the invoice.
     *
     * @param array $orderData  Full order payload
     */
    public function publishOrderCreated(array $orderData): void
    {
        try {
            $connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST',     'rabbitmq'),
                env('RABBITMQ_PORT',     5672),
                env('RABBITMQ_USER',     'guest'),
                env('RABBITMQ_PASSWORD', 'guest'),
            );

            $channel = $connection->channel();

            // Durable queue — survives broker restart
            $channel->queue_declare(
                $this->queue,
                passive:   false,
                durable:   true,
                exclusive: false,
                auto_delete: false,
            );

            $payload = json_encode([
                'event'      => 'order.created',
                'occurred_at'=> now()->toISOString(),
                'data'       => $orderData,
            ]);

            $message = new AMQPMessage($payload, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type'  => 'application/json',
            ]);

            $channel->basic_publish($message, '', $this->queue);

            $channel->close();
            $connection->close();

        } catch (\Exception $e) {
            // Log and continue — invoice will be created manually or retried
            \Log::error('FinancePublisher: failed to publish order.created', [
                'order_id' => $orderData['id'] ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
