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

  me: () => request('/auth/me'),

  getBooks: (params = '') => request(`/books${params ? '?' + params : ''}`),
  getStockAlerts: () => request('/stock/alerts'),

  getOrders: (params = '') => request(`/orders${params ? '?' + params : ''}`),
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
};
