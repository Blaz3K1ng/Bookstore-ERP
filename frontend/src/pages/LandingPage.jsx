import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../api/client';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';

export default function LandingPage() {
  const navigate = useNavigate();
  const { user } = useAuth();
  const [books, setBooks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [checkoutLoading, setCheckoutLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const { addToCart, isCartOpen, setIsCartOpen, cart, updateQuantity, removeFromCart, clearCart, cartTotal, cartCount } = useCart();

  useEffect(() => {
    loadBooks();
  }, []);

  const loadBooks = (search = '') => {
    setLoading(true);
    const params = search ? `search=${encodeURIComponent(search)}` : '';
    api.getBooks(params)
      .then((res) => setBooks(res.data?.data || []))
      .catch((err) => console.error("Failed to load books:", err))
      .finally(() => setLoading(false));
  };

  const handleSearch = (e) => {
    e.preventDefault();
    loadBooks(searchTerm);
  };

  const handleCheckout = async () => {
    if (!user) {
      navigate('/register');
      return;
    }
    setCheckoutLoading(true);
    try {
      const items = cart.map(item => ({
        book_id: item.id,
        quantity: item.quantity
      }));
      await api.createOrder({
        customer_id: user.id,
        items: items
      });
      alert('Order placed successfully! Check your dashboard.');
      clearCart();
      setIsCartOpen(false);
    } catch (err) {
      alert(err.message || 'Checkout failed');
    } finally {
      setCheckoutLoading(false);
    }
  };

  // Group books by genre for "Featured" and "New Arrivals" illusion
  const featuredBooks = books.slice(0, 4);
  const otherBooks = books.slice(4);

  return (
    <div className="storefront">
      {/* Storefront Navbar */}
      <nav className="store-nav">
        <div className="store-nav-container">
          <Link to="/" className="store-brand">
            <span className="icon">📚</span> PageCraft
          </Link>
          <div className="store-nav-links">
            <form onSubmit={handleSearch} className="store-search">
              <input 
                type="text" 
                placeholder="Search books..." 
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
              <button type="submit">Search</button>
            </form>
            <button className="cart-btn" onClick={() => setIsCartOpen(true)}>
              🛒 Cart
              {cartCount > 0 && <span className="cart-badge">{cartCount}</span>}
            </button>
            {user ? (
              user.role === 'customer' 
                ? <Link to="/profile" className="btn-ghost" style={{width: 'auto'}}>My Profile</Link>
                : <Link to="/dashboard" className="btn-ghost" style={{width: 'auto'}}>ERP Dashboard</Link>
            ) : (
              <Link to="/login" className="btn-ghost" style={{width: 'auto'}}>Login</Link>
            )}
          </div>
        </div>
      </nav>

      {/* Hero Banner */}
      <section className="hero-banner">
        <div className="hero-content">
          <h1>Discover Your Next Great Read</h1>
          <p>Explore thousands of books ranging from fiction to technical guides. Curated just for you.</p>
          <button className="btn-hero" onClick={() => window.scrollTo({ top: 600, behavior: 'smooth' })}>
            Shop Now
          </button>
        </div>
      </section>

      {/* Main Content */}
      <main className="store-main">
        {loading ? (
          <div className="loading-screen">Loading books...</div>
        ) : (
          <>
            {/* Featured Books Section */}
            {featuredBooks.length > 0 && (
              <section className="book-section">
                <h2 className="section-title">Featured Books</h2>
                <div className="book-grid">
                  {featuredBooks.map(book => (
                    <div key={book.id} className="book-card">
                      <div className="book-cover-placeholder">
                        <span className="book-title-overlay">{book.title}</span>
                      </div>
                      <div className="book-info">
                        <h3>{book.title}</h3>
                        <p className="book-author">{book.author}</p>
                        <p className="book-genre">{book.genre}</p>
                        <div className="book-bottom">
                          <span className="book-price">₱{Number(book.price).toLocaleString()}</span>
                          <button 
                            className="btn-add-cart" 
                            onClick={() => addToCart(book)}
                            disabled={book.stock_qty <= 0}
                          >
                            {book.stock_qty > 0 ? 'Add to Cart' : 'Out of Stock'}
                          </button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </section>
            )}

            {/* All Books Section */}
            {otherBooks.length > 0 && (
              <section className="book-section">
                <h2 className="section-title">More Arrivals</h2>
                <div className="book-grid">
                  {otherBooks.map(book => (
                    <div key={book.id} className="book-card">
                      <div className="book-cover-placeholder">
                        <span className="book-title-overlay">{book.title}</span>
                      </div>
                      <div className="book-info">
                        <h3>{book.title}</h3>
                        <p className="book-author">{book.author}</p>
                        <div className="book-bottom">
                          <span className="book-price">₱{Number(book.price).toLocaleString()}</span>
                          <button 
                            className="btn-add-cart" 
                            onClick={() => addToCart(book)}
                            disabled={book.stock_qty <= 0}
                          >
                            {book.stock_qty > 0 ? 'Add' : 'Out'}
                          </button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </section>
            )}
            
            {books.length === 0 && (
              <div className="empty-state">No books found.</div>
            )}
          </>
        )}
      </main>

      {/* Shopping Cart Drawer */}
      <div className={`cart-drawer-overlay ${isCartOpen ? 'open' : ''}`} onClick={() => setIsCartOpen(false)}>
        <div className={`cart-drawer ${isCartOpen ? 'open' : ''}`} onClick={e => e.stopPropagation()}>
          <div className="cart-header">
            <h2>Your Cart</h2>
            <button className="close-btn" onClick={() => setIsCartOpen(false)}>✕</button>
          </div>
          
          <div className="cart-items">
            {cart.length === 0 ? (
              <p className="empty-cart">Your cart is empty.</p>
            ) : (
              cart.map(item => (
                <div key={item.id} className="cart-item">
                  <div className="cart-item-info">
                    <h4>{item.title}</h4>
                    <p>₱{Number(item.price).toLocaleString()}</p>
                  </div>
                  <div className="cart-item-actions">
                    <button onClick={() => updateQuantity(item.id, item.quantity - 1)}>-</button>
                    <span>{item.quantity}</span>
                    <button onClick={() => updateQuantity(item.id, item.quantity + 1)}>+</button>
                    <button className="remove-btn" onClick={() => removeFromCart(item.id)}>🗑️</button>
                  </div>
                </div>
              ))
            )}
          </div>

          {cart.length > 0 && (
            <div className="cart-footer">
              <div className="cart-total">
                <span>Total:</span>
                <span>₱{cartTotal.toLocaleString()}</span>
              </div>
              <button 
                className="btn-checkout" 
                onClick={handleCheckout}
                disabled={checkoutLoading}
              >
                {checkoutLoading ? 'Processing...' : 'Proceed to Checkout'}
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
