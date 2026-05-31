import { useEffect, useState } from 'react';
import { api } from '../api/client';

export default function Books() {
  const [books, setBooks] = useState([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);

  const load = () => {
    setLoading(true);
    const params = search ? `search=${encodeURIComponent(search)}` : '';
    api.getBooks(params)
      .then((res) => setBooks(res.data?.data || []))
      .finally(() => setLoading(false));
  };

  useEffect(load, []);

  return (
    <div>
      <header className="page-header">
        <h1>Inventory</h1>
        <p>Books catalog and stock levels</p>
      </header>

      <div className="toolbar">
        <input
          placeholder="Search by title, author, ISBN..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          onKeyDown={(e) => e.key === 'Enter' && load()}
        />
        <button className="btn-primary" onClick={load}>Search</button>
      </div>

      {loading ? (
        <p className="empty">Loading...</p>
      ) : (
        <div className="card">
          <table className="table">
            <thead>
              <tr>
                <th>Title</th><th>Author</th><th>Genre</th>
                <th>Price</th><th>Stock</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              {books.map((b) => (
                <tr key={b.id}>
                  <td><strong>{b.title}</strong><br /><small>{b.isbn}</small></td>
                  <td>{b.author}</td>
                  <td>{b.genre}</td>
                  <td>₱{Number(b.price).toLocaleString()}</td>
                  <td>{b.stock_qty}</td>
                  <td>
                    {b.stock_qty <= b.reorder_level ? (
                      <span className="badge badge-cancelled">Low stock</span>
                    ) : (
                      <span className="badge badge-delivered">OK</span>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
