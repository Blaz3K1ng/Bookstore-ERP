import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../api/client';

export default function Dashboard() {
  const [stats, setStats] = useState(null);
  const [alerts, setAlerts] = useState([]);
  const [recentOrders, setRecentOrders] = useState([]);

  useEffect(() => {
    Promise.all([
      api.getRevenue().catch(() => ({ data: {} })),
      api.getStockAlerts().catch(() => ({ data: [] })),
      api.getOrders('per_page=5').catch(() => ({ data: { data: [] } })),
    ]).then(([revenue, stockAlerts, orders]) => {
      setStats(revenue.data);
      setAlerts(stockAlerts.data || []);
      setRecentOrders(orders.data?.data || []);
    });
  }, []);

  return (
    <div>
      <header className="page-header">
        <h1>Dashboard</h1>
        <p>Overview of your bookstore operations</p>
      </header>

      <div className="stat-grid">
        <div className="stat-card">
          <span className="stat-label">Total Revenue</span>
          <span className="stat-value">₱{Number(stats?.total_revenue || 0).toLocaleString()}</span>
        </div>
        <div className="stat-card">
          <span className="stat-label">Invoices</span>
          <span className="stat-value">{stats?.total_invoices ?? '—'}</span>
        </div>
        <div className="stat-card">
          <span className="stat-label">Pending Invoices</span>
          <span className="stat-value stat-warn">{stats?.pending_invoices ?? '—'}</span>
        </div>
        <div className="stat-card">
          <span className="stat-label">Low Stock Items</span>
          <span className="stat-value stat-danger">{alerts.length}</span>
        </div>
      </div>

      <div className="grid-2">
        <section className="card">
          <div className="card-header">
            <h2>Recent Orders</h2>
            <Link to="/orders" className="link">View all</Link>
          </div>
          {recentOrders.length === 0 ? (
            <p className="empty">No orders yet. Place one from the Orders page.</p>
          ) : (
            <table className="table">
              <thead>
                <tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th></tr>
              </thead>
              <tbody>
                {recentOrders.map((o) => (
                  <tr key={o.id}>
                    <td>#{o.id}</td>
                    <td>{o.customer_name}</td>
                    <td>₱{Number(o.total_amount).toLocaleString()}</td>
                    <td><span className={`badge badge-${o.status}`}>{o.status}</span></td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </section>

        <section className="card">
          <div className="card-header">
            <h2>Low Stock Alerts</h2>
            <Link to="/books" className="link">Inventory</Link>
          </div>
          {alerts.length === 0 ? (
            <p className="empty">All stock levels are healthy.</p>
          ) : (
            <ul className="alert-list">
              {alerts.slice(0, 6).map((b) => (
                <li key={b.id}>
                  <strong>{b.title}</strong>
                  <span>{b.stock_qty} left (reorder at {b.reorder_level})</span>
                </li>
              ))}
            </ul>
          )}
        </section>
      </div>
    </div>
  );
}
