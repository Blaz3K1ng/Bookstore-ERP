import { useEffect, useState } from 'react';
import { api } from '../api/client';

function SupplierModal({ onClose, onSaved }) {
  const [form, setForm] = useState({ name: '', contact_name: '', email: '', phone: '', address: '' });
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  async function handleSubmit(e) {
    e.preventDefault();
    setSaving(true);
    try {
      await api.createSupplier(form);
      onSaved();
    } catch (err) {
      setError(err.message);
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <h3>New Supplier</h3>
        {error && <p className="error-msg">{error}</p>}
        <form onSubmit={handleSubmit}>
          <label>Company Name *</label>
          <input required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
          <label>Contact Person</label>
          <input value={form.contact_name} onChange={(e) => setForm({ ...form, contact_name: e.target.value })} />
          <label>Email</label>
          <input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
          <label>Phone</label>
          <input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
          <label>Address</label>
          <textarea value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} rows={2} />
          <div className="modal-actions">
            <button type="button" className="btn-ghost" onClick={onClose}>Cancel</button>
            <button type="submit" className="btn-primary" disabled={saving}>
              {saving ? 'Saving…' : 'Create Supplier'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function PurchaseOrderModal({ suppliers, onClose, onSaved }) {
  const [form, setForm] = useState({ supplier_id: '', book_id: '', quantity: 1, unit_cost: '', notes: '' });
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  async function handleSubmit(e) {
    e.preventDefault();
    setSaving(true);
    try {
      await api.createPurchaseOrder(form);
      onSaved();
    } catch (err) {
      setError(err.message);
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <h3>New Purchase Order</h3>
        {error && <p className="error-msg">{error}</p>}
        <form onSubmit={handleSubmit}>
          <label>Supplier *</label>
          <select required value={form.supplier_id} onChange={(e) => setForm({ ...form, supplier_id: e.target.value })}>
            <option value="">Select supplier…</option>
            {suppliers.map((s) => (
              <option key={s.id} value={s.id}>{s.name}</option>
            ))}
          </select>
          <label>Book ID *</label>
          <input required type="number" min="1" value={form.book_id} onChange={(e) => setForm({ ...form, book_id: e.target.value })} />
          <label>Quantity *</label>
          <input required type="number" min="1" value={form.quantity} onChange={(e) => setForm({ ...form, quantity: e.target.value })} />
          <label>Unit Cost (₱) *</label>
          <input required type="number" step="0.01" min="0" value={form.unit_cost} onChange={(e) => setForm({ ...form, unit_cost: e.target.value })} />
          <label>Notes</label>
          <textarea value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} rows={2} />
          <div className="modal-actions">
            <button type="button" className="btn-ghost" onClick={onClose}>Cancel</button>
            <button type="submit" className="btn-primary" disabled={saving}>
              {saving ? 'Saving…' : 'Create PO'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

const STATUS_BADGE = {
  draft: 'badge-grey',
  ordered: 'badge-blue',
  received: 'badge-green',
  cancelled: 'badge-red',
};

export default function Suppliers() {
  const [tab, setTab] = useState('suppliers');
  const [suppliers, setSuppliers] = useState([]);
  const [purchaseOrders, setPurchaseOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showSupplierModal, setShowSupplierModal] = useState(false);
  const [showPOModal, setShowPOModal] = useState(false);
  const [actionLoading, setActionLoading] = useState(null);

  async function loadData() {
    setLoading(true);
    setError('');
    try {
      const [sRes, poRes] = await Promise.all([api.getSuppliers(), api.getPurchaseOrders()]);
      setSuppliers(sRes.data?.data || sRes.data || sRes || []);
      setPurchaseOrders(poRes.data?.data || poRes.data || poRes || []);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => { loadData(); }, []);

  async function handleReceivePO(id) {
    setActionLoading(id);
    try {
      await api.receivePurchaseOrder(id);
      await loadData();
    } catch (err) {
      setError(err.message);
    } finally {
      setActionLoading(null);
    }
  }

  async function handleCancelPO(id) {
    if (!confirm('Cancel this purchase order?')) return;
    setActionLoading(id);
    try {
      await api.cancelPurchaseOrder(id);
      await loadData();
    } catch (err) {
      setError(err.message);
    } finally {
      setActionLoading(null);
    }
  }

  return (
    <div className="page">
      <div className="page-header">
        <div>
          <h1>🏭 Suppliers</h1>
          <p className="page-subtitle">Manage suppliers and purchase orders</p>
        </div>
        <div style={{ display: 'flex', gap: '0.5rem' }}>
          <button className="btn-ghost" onClick={() => setShowPOModal(true)}>+ Purchase Order</button>
          <button className="btn-primary" onClick={() => setShowSupplierModal(true)}>+ Supplier</button>
        </div>
      </div>

      {error && <p className="error-msg">{error}</p>}

      <div className="tabs">
        <button className={`tab ${tab === 'suppliers' ? 'tab-active' : ''}`} onClick={() => setTab('suppliers')}>
          Suppliers ({suppliers.length})
        </button>
        <button className={`tab ${tab === 'pos' ? 'tab-active' : ''}`} onClick={() => setTab('pos')}>
          Purchase Orders ({purchaseOrders.length})
        </button>
      </div>

      {loading ? (
        <p className="loading-text">Loading…</p>
      ) : tab === 'suppliers' ? (
        <table className="data-table">
          <thead>
            <tr>
              <th>Company</th>
              <th>Contact</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {suppliers.length === 0 && (
              <tr><td colSpan={5} className="empty-row">No suppliers yet</td></tr>
            )}
            {suppliers.map((s) => (
              <tr key={s.id}>
                <td><strong>{s.name}</strong></td>
                <td>{s.contact_name || '—'}</td>
                <td>{s.contact_email || s.email || '—'}</td>
                <td>{s.phone || '—'}</td>
                <td><span className={`badge ${(s.is_active || s.status === 'active') ? 'badge-green' : 'badge-grey'}`}>
                  {(s.is_active || s.status === 'active') ? 'Active' : 'Inactive'}
                </span></td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : (
        <table className="data-table">
          <thead>
            <tr>
              <th>PO #</th>
              <th>Supplier</th>
              <th>Book ID</th>
              <th>Qty</th>
              <th>Total Cost</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {purchaseOrders.length === 0 && (
              <tr><td colSpan={7} className="empty-row">No purchase orders yet</td></tr>
            )}
            {purchaseOrders.map((po) => (
              <tr key={po.id}>
                <td>#{po.id}</td>
                <td>{po.supplier?.name || po.supplier_id}</td>
                <td>{po.book_id}</td>
                <td>{po.quantity}</td>
                <td>₱{Number(po.total_cost).toFixed(2)}</td>
                <td><span className={`badge ${STATUS_BADGE[po.status] || 'badge-grey'}`}>{po.status}</span></td>
                <td>
                  {po.status === 'ordered' && (
                    <button
                      className="btn-sm btn-primary"
                      disabled={actionLoading === po.id}
                      onClick={() => handleReceivePO(po.id)}
                    >
                      {actionLoading === po.id ? '…' : 'Receive'}
                    </button>
                  )}
                  {(po.status === 'draft' || po.status === 'ordered') && (
                    <button
                      className="btn-sm btn-ghost"
                      disabled={actionLoading === po.id}
                      onClick={() => handleCancelPO(po.id)}
                      style={{ marginLeft: '0.25rem' }}
                    >
                      Cancel
                    </button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {showSupplierModal && (
        <SupplierModal
          onClose={() => setShowSupplierModal(false)}
          onSaved={() => { setShowSupplierModal(false); loadData(); }}
        />
      )}
      {showPOModal && (
        <PurchaseOrderModal
          suppliers={suppliers}
          onClose={() => setShowPOModal(false)}
          onSaved={() => { setShowPOModal(false); loadData(); }}
        />
      )}
    </div>
  );
}
