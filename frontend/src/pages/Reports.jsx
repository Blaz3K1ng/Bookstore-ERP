import { useEffect, useState } from 'react';
import { api } from '../api/client';

function MetricCard({ label, value, sub }) {
  return (
    <div className="stat-card">
      <div className="stat-value">{value}</div>
      <div className="stat-label">{label}</div>
      {sub && <div className="stat-sub">{sub}</div>}
    </div>
  );
}

function RevenueChart({ data }) {
  if (!data || data.length === 0) return <p className="empty-text">No revenue data available.</p>;

  const max = Math.max(...data.map((d) => Number(d.revenue || d.total || 0)), 1);

  return (
    <div className="bar-chart">
      {data.slice(-12).map((row) => {
        const pct = Math.round((Number(row.revenue || row.total || 0) / max) * 100);
        return (
          <div key={row.month} className="bar-group">
            <div className="bar-track">
              <div className="bar-fill" style={{ height: `${pct}%` }} title={`₱${Number(row.revenue || row.total || 0).toLocaleString()}`} />
            </div>
            <div className="bar-label">{row.month?.slice(5)}</div>
          </div>
        );
      })}
    </div>
  );
}

export default function Reports() {
  const [dashboard, setDashboard] = useState(null);
  const [topBooks, setTopBooks] = useState([]);
  const [lowStock, setLowStock] = useState([]);
  const [monthlyRevenue, setMonthlyRevenue] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [tab, setTab] = useState('overview');

  useEffect(() => {
    async function loadAll() {
      setLoading(true);
      setError('');
      try {
        const [dash, top, low, monthly] = await Promise.allSettled([
          api.getReportDashboard(),
          api.getTopBooks(),
          api.getLowStock(),
          api.getMonthlyRevenue(),
        ]);

        if (dash.status === 'fulfilled') setDashboard(dash.value?.data || dash.value);
        if (top.status === 'fulfilled') setTopBooks(top.value?.data || top.value || []);
        if (low.status === 'fulfilled') setLowStock(low.value?.data || low.value || []);
        if (monthly.status === 'fulfilled') setMonthlyRevenue(monthly.value?.data || monthly.value || []);

        const firstError = [dash, top, low, monthly].find((r) => r.status === 'rejected');
        if (firstError) setError(`Some reports failed: ${firstError.reason?.message}`);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }
    loadAll();
  }, []);

  return (
    <div className="page">
      <div className="page-header">
        <div>
          <h1>📊 Reports</h1>
          <p className="page-subtitle">Business intelligence and analytics</p>
        </div>
      </div>

      {error && <p className="error-msg">{error}</p>}

      <div className="tabs">
        <button className={`tab ${tab === 'overview' ? 'tab-active' : ''}`} onClick={() => setTab('overview')}>Overview</button>
        <button className={`tab ${tab === 'revenue' ? 'tab-active' : ''}`} onClick={() => setTab('revenue')}>Revenue</button>
        <button className={`tab ${tab === 'books' ? 'tab-active' : ''}`} onClick={() => setTab('books')}>Books</button>
      </div>

      {loading ? (
        <p className="loading-text">Loading reports…</p>
      ) : tab === 'overview' ? (
        <>
          <div className="stats-grid">
            <MetricCard
              label="Total Revenue"
              value={`₱${Number(dashboard?.total_revenue || 0).toLocaleString()}`}
              sub="All-time paid invoices"
            />
            <MetricCard
              label="Total Orders"
              value={dashboard?.total_orders ?? '—'}
              sub="All-time orders placed"
            />
            <MetricCard
              label="Pending Orders"
              value={dashboard?.pending_orders ?? '—'}
              sub="Awaiting processing"
            />
            <MetricCard
              label="Low Stock Books"
              value={lowStock.length}
              sub="Below threshold"
            />
          </div>

          {lowStock.length > 0 && (
            <div className="card" style={{ marginTop: '1.5rem' }}>
              <h3>⚠️ Low Stock Alert</h3>
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Book</th>
                    <th>Author</th>
                    <th>Stock</th>
                    <th>Price</th>
                  </tr>
                </thead>
                <tbody>
                  {lowStock.slice(0, 10).map((b) => (
                    <tr key={b.id}>
                      <td><strong>{b.title}</strong></td>
                      <td>{b.author}</td>
                      <td><span className="badge badge-red">{b.stock}</span></td>
                      <td>₱{Number(b.price).toFixed(2)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </>
      ) : tab === 'revenue' ? (
        <div className="card">
          <h3>Monthly Revenue (last 12 months)</h3>
          <RevenueChart data={monthlyRevenue} />
          {monthlyRevenue.length > 0 && (
            <table className="data-table" style={{ marginTop: '1.5rem' }}>
              <thead>
                <tr><th>Month</th><th>Revenue</th></tr>
              </thead>
              <tbody>
                {[...monthlyRevenue].reverse().slice(0, 12).map((row) => (
                  <tr key={row.month}>
                    <td>{row.month}</td>
                    <td>₱{Number(row.revenue || row.total || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      ) : (
        <div className="card">
          <h3>Top Selling Books</h3>
          <table className="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Author</th>
                <th>Units Sold</th>
                <th>Revenue</th>
              </tr>
            </thead>
            <tbody>
              {topBooks.length === 0 && (
                <tr><td colSpan={5} className="empty-row">No sales data yet</td></tr>
              )}
              {topBooks.map((b, i) => (
                <tr key={b.book_id || i}>
                  <td>{i + 1}</td>
                  <td><strong>{b.title || `Book #${b.book_id}`}</strong></td>
                  <td>{b.author || '—'}</td>
                  <td>{b.total_sold}</td>
                  <td>₱{Number(b.total_revenue || 0).toFixed(2)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
