import { useEffect, useState } from 'react';
import { api } from '../api/client';

const STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
const PAYMENTS = ['gcash', 'paymaya', 'cash', 'credit_card', 'cod'];

export default function Orders() {
  const [orders, setOrders] = useState([]);
  const [books, setBooks] = useState([]);
  const [customers, setCustomers] = useState([]);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({
    customer_id: '', customer_name: '', shipping_address: '',
    payment_method: 'gcash', book_id: '', quantity: 1,
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);

  const load = () => {
    setLoading(true);
    Promise.all([
      api.getOrders(),
      api.getBooks(),
      api.getCustomers(),
    ]).then(([o, b, c]) => {
      setOrders(o.data?.data || []);
      setBooks(b.data?.data || []);
      setCustomers(c.data?.data || []);
    }).finally(() => setLoading(false));
  };

  useEffect(load, []);

  const handleCustomerChange = (id) => {
    const customer = customers.find((c) => c.id === Number(id));
    setForm({
      ...form,
      customer_id: id,
      customer_name: customer?.name || '',
      shipping_address: customer?.address ? `${customer.address}, ${customer.city}` : '',
    });
  };

  const handleCreate = async (e) => {
    e.preventDefault();
    setError('');
    try {
      await api.createOrder({
        customer_id: Number(form.customer_id),
        customer_name: form.customer_name,
        shipping_address: form.shipping_address,
        payment_method: form.payment_method,
        items: [{ book_id: Number(form.book_id), quantity: Number(form.quantity) }],
      });
      setShowForm(false);
      load();
    } catch (err) {
      setError(err.message);
    }
  };

  const updateStatus = async (id, status) => {
    await api.updateOrderStatus(id, status);
    load();
  };

  return (
    <div>
      <header className="page-header row">
        <div>
          <h1>Orders</h1>
          <p>Sales order management</p>
        </div>
        <button className="btn-primary" onClick={() => setShowForm(!showForm)}>
          {showForm ? 'Cancel' : '+ New Order'}
        </button>
      </header>

      {showForm && (
        <form className="card form-card" onSubmit={handleCreate}>
          <h2>Place Order</h2>
          {error && <div className="alert alert-error">{error}</div>}
          <div className="form-grid">
            <label>
              Customer
              <select value={form.customer_id} onChange={(e) => handleCustomerChange(e.target.value)} required>
                <option value="">Select customer</option>
                {customers.map((c) => (
                  <option key={c.id} value={c.id}>{c.name}</option>
                ))}
              </select>
            </label>
            <label>
              Book
              <select value={form.book_id} onChange={(e) => setForm({ ...form, book_id: e.target.value })} required>
                <option value="">Select book</option>
                {books.map((b) => (
                  <option key={b.id} value={b.id}>{b.title} (stock: {b.stock_qty})</option>
                ))}
              </select>
            </label>
            <label>
              Quantity
              <input type="number" min="1" value={form.quantity}
                onChange={(e) => setForm({ ...form, quantity: e.target.value })} required />
            </label>
            <label>
              Payment
              <select value={form.payment_method} onChange={(e) => setForm({ ...form, payment_method: e.target.value })}>
                {PAYMENTS.map((p) => <option key={p} value={p}>{p}</option>)}
              </select>
            </label>
            <label className="span-2">
              Shipping Address
              <input value={form.shipping_address} onChange={(e) => setForm({ ...form, shipping_address: e.target.value })} required />
            </label>
          </div>
          <button type="submit" className="btn-primary">Place Order</button>
        </form>
      )}

      {loading ? (
        <p className="empty">Loading...</p>
      ) : (
        <div className="card">
          <table className="table">
            <thead>
              <tr>
                <th>ID</th><th>Customer</th><th>Total</th>
                <th>Payment</th><th>Status</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {orders.map((o) => (
                <tr key={o.id}>
                  <td>#{o.id}</td>
                  <td>{o.customer_name}</td>
                  <td>₱{Number(o.total_amount).toLocaleString()}</td>
                  <td>{o.payment_method}</td>
                  <td><span className={`badge badge-${o.status}`}>{o.status}</span></td>
                  <td>
                    {o.status !== 'cancelled' && o.status !== 'delivered' && (
                      <select
                        value={o.status}
                        onChange={(e) => updateStatus(o.id, e.target.value)}
                        className="status-select"
                      >
                        {STATUSES.filter((s) => s !== 'cancelled').map((s) => (
                          <option key={s} value={s}>{s}</option>
                        ))}
                      </select>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
