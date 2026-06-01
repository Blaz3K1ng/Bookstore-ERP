const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

function getToken() {
  return localStorage.getItem('token');
}

async function request(path, options = {}) {
  const headers = {
    Accept: 'application/json',
    ...(options.body ? { 'Content-Type': 'application/json' } : {}),
    ...options.headers,
  };

  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;

  const res = await fetch(`${API_BASE}${path}`, { ...options, headers });
  const data = await res.json().catch(() => ({}));

  if (!res.ok) {
    throw new Error(data.message || data.errors?.[0] || `Request failed (${res.status})`);
  }

  return data;
}

export const api = {
  login: (email, password) =>
    request('/auth/login', { method: 'POST', body: JSON.stringify({ email, password }) }),
  register: (name, email, password) =>
    request('/auth/register', { method: 'POST', body: JSON.stringify({ name, email, password, password_confirmation: password, role: 'customer' }) }),

  me: () => request('/auth/me'),

  getBooks: (params = '') => request(`/books${params ? '?' + params : ''}`),
  getStockAlerts: () => request('/stock/alerts'),

  getOrders: (params = '') => request(`/orders${params ? '?' + params : ''}`),
  getCustomerOrders: (customerId) => request(`/orders/customer/${customerId}`),
  createOrder: (payload) =>
    request('/orders', { method: 'POST', body: JSON.stringify(payload) }),
  updateOrderStatus: (id, status) =>
    request(`/orders/${id}/status`, { method: 'PATCH', body: JSON.stringify({ status }) }),
  cancelOrder: (id) => request(`/orders/${id}`, { method: 'DELETE' }),

  getCustomers: (params = '') => request(`/customers${params ? '?' + params : ''}`),
  createCustomer: (payload) =>
    request('/customers', { method: 'POST', body: JSON.stringify(payload) }),

  getInvoices: (params = '') => request(`/invoices${params ? '?' + params : ''}`),
  updateInvoiceStatus: (id, status) =>
    request(`/invoices/${id}/status`, { method: 'PATCH', body: JSON.stringify({ status }) }),
  getRevenue: () => request('/reports/revenue'),
  getMonthlyRevenue: () => request('/reports/revenue/monthly'),

  // Supplier Service
  getSuppliers: () => request('/suppliers'),
  createSupplier: (payload) =>
    request('/suppliers', { method: 'POST', body: JSON.stringify(payload) }),
  updateSupplier: (id, payload) =>
    request(`/suppliers/${id}`, { method: 'PATCH', body: JSON.stringify(payload) }),
  deleteSupplier: (id) => request(`/suppliers/${id}`, { method: 'DELETE' }),

  getPurchaseOrders: (params = '') => request(`/purchase-orders${params ? '?' + params : ''}`),
  createPurchaseOrder: (payload) =>
    request('/purchase-orders', { method: 'POST', body: JSON.stringify(payload) }),
  receivePurchaseOrder: (id) =>
    request(`/purchase-orders/${id}/status`, { method: 'PATCH', body: JSON.stringify({ status: 'received' }) }),
  cancelPurchaseOrder: (id) =>
    request(`/purchase-orders/${id}/status`, { method: 'PATCH', body: JSON.stringify({ status: 'cancelled' }) }),

  // Reporting Service
  getReportDashboard: () => request('/reports/dashboard'),
  getTopBooks: () => request('/reports/top-books'),
  getLowStock: () => request('/reports/low-stock'),
};
