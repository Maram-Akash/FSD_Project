import React, { useState, useEffect, useCallback } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { Film, User, LogOut, Wallet } from 'lucide-react';

const Navbar = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const token = localStorage.getItem('token');
  const userStr = localStorage.getItem('user');
  const user = userStr ? JSON.parse(userStr) : null;
  const [walletBalance, setWalletBalance] = useState(null);
  const [balanceUpdated, setBalanceUpdated] = useState(false);

  const fetchWalletBalance = useCallback(async () => {
    try {
      const currentToken = localStorage.getItem('token');
      if (!currentToken) return;
      const res = await fetch('/api/wallet', {
        headers: { 'Authorization': `Bearer ${currentToken}` }
      });
      if (res.ok) {
        const data = await res.json();
        setWalletBalance(data.balance ?? 0);
        // Trigger pulse animation
        setBalanceUpdated(true);
        setTimeout(() => setBalanceUpdated(false), 1000);
      }
    } catch { }
  }, []);

  useEffect(() => {
    if (token) fetchWalletBalance();
  }, [token, fetchWalletBalance]);

  // Listen for wallet update events from WalletPage or BookingSummary
  useEffect(() => {
    const handleWalletUpdate = () => {
      fetchWalletBalance();
    };
    window.addEventListener('walletUpdated', handleWalletUpdate);
    return () => window.removeEventListener('walletUpdated', handleWalletUpdate);
  }, [fetchWalletBalance]);

  // Also refresh wallet on route changes
  useEffect(() => {
    if (token) fetchWalletBalance();
  }, [location.pathname, token, fetchWalletBalance]);

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  return (
    <nav className="navbar">
      <Link to="/" className="navbar-brand" style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
        <Film size={28} color="#3b82f6" />
        CineSmart
      </Link>

      <div className="navbar-links">
        {token ? (
          <>
            <span style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>Hi, {user?.name}</span>

            {/* Wallet Balance Chip */}
            <Link to="/wallet" className={`wallet-chip ${balanceUpdated ? 'pulse' : ''}`}>
              <Wallet size={14} />
              ₹{walletBalance !== null ? walletBalance.toFixed(2) : '—'}
            </Link>

            <Link to="/history" className="btn btn-outline nav-btn" style={{ padding: '0.4rem 1rem' }}>
              My Bookings
            </Link>
            <Link to="/dashboard" className="btn btn-outline nav-btn" style={{ padding: '0.4rem 1rem' }}>
              Dashboard
            </Link>
            <button onClick={handleLogout} className="btn btn-primary nav-btn" style={{ padding: '0.4rem 1rem' }}>
              <LogOut size={16} /> Logout
            </button>
          </>
        ) : (
          <>
            <Link to="/login" className="btn btn-outline nav-btn" style={{ padding: '0.4rem 1rem' }}>
              <User size={16} /> Login
            </Link>
            <Link to="/signup" className="btn btn-primary nav-btn" style={{ padding: '0.4rem 1rem' }}>
              Sign Up
            </Link>
          </>
        )}
      </div>
    </nav>
  );
};

export default Navbar;
