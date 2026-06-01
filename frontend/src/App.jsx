import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import { CartProvider } from './context/CartContext';
import Layout from './components/Layout';
import Login from './pages/Login';
import Register from './pages/Register';
import CustomerProfile from './pages/CustomerProfile';
import LandingPage from './pages/LandingPage';
import Dashboard from './pages/Dashboard';
import Books from './pages/Books';
import Orders from './pages/Orders';
import Customers from './pages/Customers';
import Invoices from './pages/Invoices';
import Suppliers from './pages/Suppliers';
import Reports from './pages/Reports';

function PrivateRoute({ children, allowedRoles }) {
  const { user, loading } = useAuth();
  if (loading) return <div className="loading-screen">Loading...</div>;
  if (!user) return <Navigate to="/login" replace />;
  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return <Navigate to="/dashboard" replace />;
  }
  return children;
}

export default function App() {
  return (
    <AuthProvider>
      <CartProvider>
        <BrowserRouter>
          <Routes>
            {/* Public Storefront */}
            <Route path="/" element={<LandingPage />} />
            
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/profile" element={<CustomerProfile />} />
            
            {/* Private ERP Application */}
            <Route
              path="/dashboard"
              element={
                <PrivateRoute>
                  <Layout />
                </PrivateRoute>
              }
            >
              <Route index element={<Dashboard />} />
              <Route path="books" element={
                <PrivateRoute allowedRoles={['super_admin', 'inventory_admin', 'catalog_admin']}><Books /></PrivateRoute>
              } />
              <Route path="orders" element={
                <PrivateRoute allowedRoles={['super_admin', 'orders_admin']}><Orders /></PrivateRoute>
              } />
              <Route path="customers" element={
                <PrivateRoute allowedRoles={['super_admin', 'orders_admin']}><Customers /></PrivateRoute>
              } />
              <Route path="invoices" element={
                <PrivateRoute allowedRoles={['super_admin', 'finance_admin']}><Invoices /></PrivateRoute>
              } />
              <Route path="suppliers" element={
                <PrivateRoute allowedRoles={['super_admin', 'inventory_admin']}><Suppliers /></PrivateRoute>
              } />
              <Route path="reports" element={
                <PrivateRoute allowedRoles={['super_admin', 'finance_admin']}><Reports /></PrivateRoute>
              } />
            </Route>
            
            {/* Fallback redirect */}
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </BrowserRouter>
      </CartProvider>
    </AuthProvider>
  );
}
