<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * ConsumeOrderEvents
 *
 * Listens on the 'order.created' RabbitMQ queue.
 * For every message received, creates an Invoice record in finance_db.
 *
 * Run via: php artisan consume:orders
 * In Docker this is the 'finance-consumer' service.
 *
 * Communication method: Message Broker / RabbitMQ  (inter-service requirement #2)
 */
class ConsumeOrderEvents extends Command
{
    protected $signature   = 'consume:orders';
    protected $description = 'Listen for order.created events from RabbitMQ and create invoices';

    public function handle(): void
    {
        $queue = env('RABBITMQ_QUEUE', 'order.created');

        $this->info("Connecting to RabbitMQ [{$queue}]...");

        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST',     'rabbitmq'),
            env('RABBITMQ_PORT',     5672),
            env('RABBITMQ_USER',     'guest'),
            env('RABBITMQ_PASSWORD', 'guest'),
        );

        $channel = $connection->channel();

        $channel->queue_declare($queue, false, true, false, false);

        $this->info('Waiting for order events. CTRL+C to stop.');

        $callback = function (AMQPMessage $msg) {
            try {
                $payload = json_decode($msg->body, true);
                $order   = $payload['data'] ?? [];

                if (empty($order)) {
                    $this->warn('Received empty order payload — skipping.');
                    $msg->nack();
                    return;
                }

                // Idempotency: skip if invoice already exists for this order
                if (Invoice::where('order_id', $order['id'])->exists()) {
                    $this->line("Invoice for order #{$order['id']} already exists — skipping.");
                    $msg->ack();
                    return;
                }

                Invoice::create([
                    'order_id'       => $order['id'],
                    'customer_id'    => $order['customer_id'],
                    'customer_name'  => $order['customer_name'],
                    'amount'         => $order['total_amount'],
                    'payment_method' => $order['payment_method'],
                    'status'         => 'pending',
                ]);

                $this->info("✓ Invoice created for order #{$order['id']} ({$order['customer_name']})");

                $msg->ack();  // Acknowledge message — remove from queue

            } catch (\Exception $e) {
                $this->error('Failed to create invoice: ' . $e->getMessage());
                $msg->nack(false, true);  // Requeue on failure
            }
        };

        // prefetch 1 = process one message at a time (fair dispatch)
        $channel->basic_qos(0, 1, false);
        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
