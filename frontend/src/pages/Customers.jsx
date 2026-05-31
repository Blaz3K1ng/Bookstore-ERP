import { useEffect, useState } from 'react';
import { api } from '../api/client';

export default function Customers() {
  const [customers, setCustomers] = useState([]);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ name: '', email: '', phone: '', city: '', address: '' });
  const [loading, setLoading] = useState(true);

  const load = () => {
    setLoading(true);
    api.getCustomers().then((res) => setCustomers(res.data?.data || [])).finally(() => setLoading(false));
  };

  useEffect(load, []);

  const handleCreate = async (e) => {
    e.preventDefault();
    await api.createCustomer(form);
    setShowForm(false);
    setForm({ name: '', email: '', phone: '', city: '', address: '' });
    load();
  };

  return (
    <div>
      <header className="page-header row">
        <div>
          <h1>Customers</h1>
          <p>CRM — customer profiles and lifetime value</p>
        </div>
        <button className="btn-primary" onClick={() => setShowForm(!showForm)}>
          {showForm ? 'Cancel' : '+ Add Customer'}
        </button>
      </header>

      {showForm && (
        <form className="card form-card" onSubmit={handleCreate}>
          <h2>New Customer</h2>
          <div className="form-grid">
            <label>Name<input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required /></label>
            <label>Email<input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required /></label>
            <label>Phone<input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} required /></label>
            <label>City<input value={form.city} onChange={(e) => setForm({ ...form, city: e.target.value })} required /></label>
            <label className="span-2">Address<input value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} /></label>
          </div>
          <button type="submit" className="btn-primary">Save Customer</button>
        </form>
      )}

      {loading ? (
        <p className="empty">Loading...</p>
      ) : (
        <div className="card">
          <table className="table">
            <thead>
              <tr>
                <th>Name</th><th>Email</th><th>City</th>
                <th>Orders</th><th>Lifetime Value</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              {customers.map((c) => (
                <tr key={c.id}>
                  <td><strong>{c.name}</strong></td>
                  <td>{c.email}</td>
                  <td>{c.city}</td>
                  <td>{c.total_orders}</td>
                  <td>₱{Number(c.lifetime_value).toLocaleString()}</td>
                  <td><span className={`badge badge-${c.status === 'vip' ? 'processing' : c.status === 'inactive' ? 'cancelled' : 'pending'}`}>{c.status}</span></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
