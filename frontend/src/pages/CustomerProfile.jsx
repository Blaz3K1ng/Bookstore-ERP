import { useEffect, useState } from 'react';
import { Navigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { api } from '../api/client';

export default function CustomerProfile() {
  const { user, logout } = useAuth();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (user && user.role === 'customer') {
      api.getCustomerOrders(user.id)
        .then(res => setOrders(res.data || []))
        .catch(err => console.error(err))
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, [user]);

  if (!user) return <Navigate to="/login" replace />;

  return (
    <div className="storefront">
      <nav className="store-nav">
        <div className="store-nav-container">
          <Link to="/" className="store-brand">
            <span className="icon">ðŸ“š</span> PageCraft
          </Link>
          <div className="store-nav-links">
            <Link to="/" className="btn-ghost" style={{width: 'auto'}}>Back to Store</Link>
            <button className="btn-ghost" onClick={logout} style={{width: 'auto'}}>Log out</button>
          </div>
        </div>
      </nav>

      <main className="store-main" style={{ maxWidth: '800px', margin: '0 auto', paddingTop: '4rem' }}>
        <h1 style={{ marginBottom: '0.5rem', color: 'white' }}>My Profile</h1>
        <p style={{ color: 'var(--text-muted)', marginBottom: '2rem' }}>
          Welcome back, {user.name} ({user.email})
        </p>

        <section className="card">
          <div className="card-header">
            <h2>Order History</h2>
          </div>
          {loading ? (
            <p className="loading-text">Loading orders...</p>
          ) : orders.length === 0 ? (
            <p className="empty">You haven't placed any orders yet. <Link to="/" style={{color: 'var(--primary)'}}>Start shopping!</Link></p>
          ) : (
            <table className="table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Total Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {orders.map(order => (
                  <tr key={order.id}>
                    <td>#{order.id}</td>
                    <td>{new Date(order.created_at).toLocaleDateString()}</td>
                    <td>â‚±{Number(order.total_amount).toLocaleString()}</td>
                    <td><span className={`badge badge-${order.status.toLowerCase()}`}>{order.status}</span></td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </section>
      </main>
    </div>
  );
}
