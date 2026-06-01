'use strict';

const amqp      = require('amqplib');
const nodemailer = require('nodemailer');

// ── Configuration ─────────────────────────────────────────────────────────────

const RABBITMQ_URL = process.env.RABBITMQ_URL
  || `amqp://${process.env.RABBITMQ_USER || 'guest'}:${process.env.RABBITMQ_PASSWORD || 'guest'}@${process.env.RABBITMQ_HOST || 'rabbitmq'}:${process.env.RABBITMQ_PORT || 5672}`;

const QUEUE = process.env.NOTIFICATION_QUEUE || 'order.notifications';

const mailer = nodemailer.createTransport({
  host:   process.env.SMTP_HOST   || '',
  port:   parseInt(process.env.SMTP_PORT || '587', 10),
  secure: false,
  auth: process.env.SMTP_USER ? {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS || '',
  } : undefined,
});

const MAIL_FROM = process.env.MAIL_FROM || 'notifications@pagecraft.ph';
const EMAIL_ENABLED = !!(process.env.SMTP_HOST && process.env.SMTP_USER);

// ── Structured logger ─────────────────────────────────────────────────────────

function log(level, message, data = {}) {
  console.log(JSON.stringify({
    level,
    message,
    service: 'notification-service',
    timestamp: new Date().toISOString(),
    ...data,
  }));
}

// ── Email helper ──────────────────────────────────────────────────────────────

async function sendOrderConfirmation(order) {
  if (!EMAIL_ENABLED) {
    log('info', 'Email not configured — skipping email send', { order_id: order.id });
    return;
  }

  try {
    await mailer.sendMail({
      from:    MAIL_FROM,
      to:      `customer-${order.customer_id}@example.com`,
      subject: `Order #${order.id} Confirmed — PageCraft Bookstore`,
      text: [
        `Hello ${order.customer_name},`,
        '',
        `Your order #${order.id} has been received!`,
        `Total: ₱${Number(order.total_amount).toLocaleString()}`,
        `Payment: ${order.payment_method}`,
        '',
        'Thank you for shopping at PageCraft Bookstore.',
      ].join('\n'),
    });
    log('info', 'Order confirmation email sent', { order_id: order.id });
  } catch (err) {
    log('error', 'Failed to send email', { order_id: order.id, error: err.message });
  }
}

// ── Message handler ───────────────────────────────────────────────────────────

async function handleMessage(msg, channel) {
  let payload;

  try {
    payload = JSON.parse(msg.content.toString());
  } catch {
    log('warn', 'Invalid JSON payload — discarding message');
    channel.nack(msg, false, false);
    return;
  }

  const event = payload.event || 'unknown';
  const order = payload.data  || {};

  log('info', `Event received: ${event}`, {
    event,
    order_id:      order.id,
    customer_name: order.customer_name,
    total_amount:  order.total_amount,
  });

  if (event === 'order.created') {
    await sendOrderConfirmation(order);
  }

  channel.ack(msg);
}

// ── Connection with retry ─────────────────────────────────────────────────────

async function connectWithRetry(retries = 10, delayMs = 5000) {
  for (let attempt = 1; attempt <= retries; attempt++) {
    try {
      log('info', `Connecting to RabbitMQ (attempt ${attempt}/${retries})...`);
      const conn    = await amqp.connect(RABBITMQ_URL);
      const channel = await conn.createChannel();

      await channel.assertQueue(QUEUE, { durable: true });
      channel.prefetch(1);

      log('info', `Listening on queue: ${QUEUE}`);

      channel.consume(QUEUE, (msg) => {
        if (msg) handleMessage(msg, channel).catch((err) => {
          log('error', 'Unhandled error in message handler', { error: err.message });
          channel.nack(msg, false, true);
        });
      });

      conn.on('error',  (err) => log('error', 'RabbitMQ connection error', { error: err.message }));
      conn.on('close',  ()    => {
        log('warn', 'RabbitMQ connection closed — reconnecting in 10s...');
        setTimeout(() => connectWithRetry(), 10_000);
      });

      return; // success
    } catch (err) {
      log('warn', `RabbitMQ not ready — retrying in ${delayMs / 1000}s`, { error: err.message });
      if (attempt < retries) {
        await new Promise((r) => setTimeout(r, delayMs));
      } else {
        log('error', 'Could not connect to RabbitMQ after max retries — exiting');
        process.exit(1);
      }
    }
  }
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────

log('info', `Starting notification-service (EMAIL_ENABLED=${EMAIL_ENABLED})`);
connectWithRetry();
