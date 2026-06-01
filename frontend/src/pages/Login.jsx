import { useState } from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Login() {
  const { user, login } = useAuth();
  const [email, setEmail] = useState('admin@pageturn.com');
  const [password, setPassword] = useState('password123');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  if (user) {
    if (user.role === 'customer') return <Navigate to="/profile" replace />;
    return <Navigate to="/dashboard" replace />;
  }

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await login(email, password);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-page">
      <div className="login-card">
        <div className="login-header">
          <span className="login-logo">📖</span>
          <h1>PageCraft ERP</h1>
          <p>Bookstore microservices dashboard</p>
        </div>
        <form onSubmit={handleSubmit}>
          {error && <div className="alert alert-error">{error}</div>}
          <label>
            Email
            <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
          </label>
          <label>
            Password
            <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
          </label>
          <button type="submit" className="btn-primary" disabled={loading}>
            {loading ? 'Signing in...' : 'Sign in'}
          </button>
        </form>
        <p className="login-hint" style={{ fontSize: '0.8rem', marginTop: '1rem', color: '#64748b' }}>
          <b>Demo Accounts (password123):</b><br/>
          admin@pageturn.com (Super Admin)<br/>
          finance@pageturn.com (Finance Admin)<br/>
          inventory@pageturn.com (Inventory Admin)<br/>
          customer@example.com (Customer)
        </p>
      </div>
    </div>
  );
}
