import { useEffect, useState } from 'react';
import { api } from '../api/client';

export default function Invoices() {
  const [invoices, setInvoices] = useState([]);
  const [revenue, setRevenue] = useState(null);
  const [loading, setLoading] = useState(true);

  const load = () => {
    setLoading(true);
    Promise.all([api.getInvoices(), api.getRevenue()])
      .then(([inv, rev]) => {
        setInvoices(inv.data?.data || []);
        setRevenue(rev.data);
      })
      .finally(() => setLoading(false));
  };

  useEffect(load, []);

  const markPaid = async (id) => {
    await api.updateInvoiceStatus(id, 'paid');
    load();
  };

  return (
    <div>
      <header className="page-header">
        <h1>Finance</h1>
        <p>Invoices and revenue (auto-created via RabbitMQ on order placement)</p>
      </header>

      <div className="stat-grid">
        <div className="stat-card">
          <span className="stat-label">Paid Revenue</span>
          <span className="stat-value">₱{Number(revenue?.total_revenue || 0).toLocaleString()}</span>
        </div>
        <div className="stat-card">
          <span className="stat-label">Paid</span>
          <span className="stat-value">{revenue?.paid_invoices ?? 0}</span>
        </div>
        <div className="stat-card">
          <span className="stat-label">Pending</span>
          <span className="stat-value stat-warn">{revenue?.pending_invoices ?? 0}</span>
        </div>
      </div>

      {loading ? (
        <p className="empty">Loading...</p>
      ) : invoices.length === 0 ? (
        <div className="card empty-state">
          <p>No invoices yet. Place an order to trigger invoice creation via RabbitMQ.</p>
        </div>
      ) : (
        <div className="card">
          <table className="table">
            <thead>
              <tr>
                <th>Invoice</th><th>Order</th><th>Customer</th>
                <th>Amount</th><th>Payment</th><th>Status</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {invoices.map((inv) => (
                <tr key={inv.id}>
                  <td>#{inv.id}</td>
                  <td>Order #{inv.order_id}</td>
                  <td>{inv.customer_name}</td>
                  <td>₱{Number(inv.amount).toLocaleString()}</td>
                  <td>{inv.payment_method}</td>
                  <td><span className={`badge badge-${inv.status === 'paid' ? 'delivered' : inv.status === 'voided' ? 'cancelled' : 'pending'}`}>{inv.status}</span></td>
                  <td>
                    {inv.status === 'pending' && (
                      <button className="btn-sm btn-primary" onClick={() => markPaid(inv.id)}>Mark Paid</button>
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
