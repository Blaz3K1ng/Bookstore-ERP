import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const nav = [
  { to: '/', label: 'Dashboard', icon: '◫' },
  { to: '/books', label: 'Inventory', icon: '📚' },
  { to: '/orders', label: 'Orders', icon: '🛒' },
  { to: '/customers', label: 'Customers', icon: '👥' },
  { to: '/invoices', label: 'Finance', icon: '💰' },
];

export default function Layout() {
  const { user, logout } = useAuth();

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <span className="brand-icon">📖</span>
          <div>
            <strong>PageCraft</strong>
            <small>Bookstore ERP</small>
          </div>
        </div>
        <nav>
          {nav.map((item) => (
            <NavLink key={item.to} to={item.to} end={item.to === '/'} className="nav-link">
              <span>{item.icon}</span> {item.label}
            </NavLink>
          ))}
        </nav>
        <div className="sidebar-footer">
          <div className="user-chip">
            <span className="avatar">{user?.name?.[0]}</span>
            <div>
              <strong>{user?.name}</strong>
              <small>{user?.role?.replace('_', ' ')}</small>
            </div>
          </div>
          <button className="btn-ghost" onClick={logout}>Sign out</button>
        </div>
      </aside>
      <main className="main-content">
        <Outlet />
      </main>
    </div>
  );
}
