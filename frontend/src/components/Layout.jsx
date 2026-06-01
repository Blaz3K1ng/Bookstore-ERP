import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const nav = [
  { to: '/dashboard', label: 'Dashboard', icon: '◫', roles: ['admin', 'super_admin', 'finance_admin', 'inventory_admin', 'catalog_admin', 'orders_admin', 'staff'] },
  { to: '/dashboard/books', label: 'Inventory', icon: '📚', roles: ['admin', 'super_admin', 'inventory_admin', 'catalog_admin'] },
  { to: '/dashboard/orders', label: 'Orders', icon: '🛒', roles: ['admin', 'super_admin', 'orders_admin'] },
  { to: '/dashboard/customers', label: 'Customers', icon: '👥', roles: ['admin', 'super_admin', 'orders_admin'] },
  { to: '/dashboard/invoices', label: 'Finance', icon: '💰', roles: ['admin', 'super_admin', 'finance_admin'] },
  { to: '/dashboard/suppliers', label: 'Suppliers', icon: '🏭', roles: ['admin', 'super_admin', 'inventory_admin'] },
  { to: '/dashboard/reports', label: 'Reports', icon: '📊', roles: ['admin', 'super_admin', 'finance_admin'] },
];

export default function Layout() {
  const { user, logout } = useAuth();

  const filteredNav = nav.filter(item => 
    !item.roles || item.roles.includes(user?.role)
  );

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
          {filteredNav.map((item) => (
            <NavLink key={item.to} to={item.to} end={item.to === '/dashboard'} className="nav-link">
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
