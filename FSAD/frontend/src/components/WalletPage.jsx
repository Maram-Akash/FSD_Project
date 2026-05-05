import React, { useState, useEffect } from 'react';
import { Wallet, Plus, ArrowUpCircle, ArrowDownCircle, CreditCard, RefreshCw } from 'lucide-react';

const QUICK_AMOUNTS = [100, 200, 500, 1000, 2000, 5000];

const WalletPage = () => {
  const [wallet, setWallet] = useState(null);
  const [transactions, setTransactions] = useState([]);
  const [amount, setAmount] = useState('');
  const [loading, setLoading] = useState(false);
  const [fetchLoading, setFetchLoading] = useState(true);
  const [message, setMessage] = useState({ text: '', type: '' });

  useEffect(() => {
    init();
  }, []);

  const authHeader = () => ({
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
    'Content-Type': 'application/json'
  });

  const init = async () => {
    setFetchLoading(true);
    await Promise.all([fetchWallet(), fetchTransactions()]);
    setFetchLoading(false);
  };

  const fetchWallet = async () => {
    try {
      const res = await fetch('/api/wallet', { headers: authHeader() });
      const text = await res.text();
      try {
        const data = JSON.parse(text);
        if (res.ok) setWallet(data);
      } catch { console.error('Wallet parse error:', text.substring(0, 200)); }
    } catch (err) { console.error('Wallet fetch error:', err); }
  };

  const fetchTransactions = async () => {
    try {
      const res = await fetch('/api/wallet/transactions', { headers: authHeader() });
      const text = await res.text();
      try {
        const data = JSON.parse(text);
        if (res.ok) setTransactions(Array.isArray(data) ? data : []);
      } catch { console.error('Txn parse error:', text.substring(0, 200)); }
    } catch (err) { console.error('Txn fetch error:', err); }
  };

  const handleAddMoney = async (e) => {
    e.preventDefault();
    const amt = parseFloat(amount);
    if (!amt || amt <= 0) {
      setMessage({ text: 'Please enter a valid amount', type: 'error' });
      return;
    }
    setLoading(true);
    setMessage({ text: '', type: '' });

    try {
      const res = await fetch('/api/wallet/add', {
        method: 'POST',
        headers: authHeader(),
        body: JSON.stringify({ amount: amt })
      });

      const rawText = await res.text();
      let data = {};
      try {
        data = JSON.parse(rawText);
      } catch {
        console.error('Non-JSON response from server:', rawText.substring(0, 300));
        setMessage({ text: `Server error (${res.status}). Please restart the Spring Boot server.`, type: 'error' });
        setLoading(false);
        return;
      }

      if (res.ok) {
        setWallet(data);
        setAmount('');
        setMessage({ text: `✓ ₹${amt.toFixed(2)} added to your wallet!`, type: 'success' });
        fetchTransactions();
        // Dispatch custom event to update Navbar wallet balance
        window.dispatchEvent(new Event('walletUpdated'));
      } else {
        setMessage({ text: data.error || data.message || `Error ${res.status}`, type: 'error' });
      }
    } catch (err) {
      console.error('Fetch error:', err);
      setMessage({ text: 'Cannot reach server. Is Spring Boot running on port 8080?', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleQuickSelect = (q) => {
    setAmount(String(q));
    setMessage({ text: '', type: '' });
  };

  const formatDate = (iso) => {
    if (!iso) return '';
    return new Date(iso).toLocaleString('en-IN', {
      day: '2-digit', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });
  };

  if (fetchLoading) {
    return (
      <div style={{ textAlign: 'center', padding: '4rem' }}>
        <div className="loading-spinner"></div>
        <p style={{ color: 'var(--text-muted)', marginTop: '1rem' }}>Loading wallet...</p>
      </div>
    );
  }

  return (
    <div style={{ maxWidth: '900px', margin: '0 auto' }}>
      <h2 style={{ marginBottom: '2rem', display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
        <Wallet size={28} color="#3b82f6" /> My Wallet
      </h2>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '2rem' }}>

        {/* LEFT: Balance + Add Money */}
        <div>
          {/* Balance Card */}
          <div className="wallet-balance-card">
            <div style={{ fontSize: '0.9rem', color: 'rgba(255,255,255,0.7)', marginBottom: '0.5rem' }}>
              Available Balance
            </div>
            <div className="wallet-balance-amount">
              ₹{wallet ? Number(wallet.balance).toFixed(2) : '0.00'}
            </div>
            <div className="wallet-balance-glow"></div>
          </div>

          {/* Add Money Form */}
          <div className="glass-card" style={{ marginTop: '1.5rem' }}>
            <h3 style={{ marginBottom: '1.5rem', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
              <Plus size={18} color="#3b82f6" /> Add Money
            </h3>

            {/* Quick Amounts */}
            <div className="quick-amounts">
              {QUICK_AMOUNTS.map(q => (
                <button
                  key={q}
                  type="button"
                  className={`quick-btn ${amount == q ? 'active' : ''}`}
                  onClick={() => handleQuickSelect(q)}
                >
                  ₹{q}
                </button>
              ))}
            </div>

            <form onSubmit={handleAddMoney}>
              <div className="form-group">
                <label className="form-label">Or enter custom amount (₹)</label>
                <input
                  type="number"
                  className="form-control"
                  placeholder="e.g. 750"
                  value={amount}
                  onChange={e => {
                    setAmount(e.target.value);
                    setMessage({ text: '', type: '' });
                  }}
                  min="1"
                  max="50000"
                  step="1"
                />
              </div>

              {message.text && (
                <div
                  className={message.type === 'success' ? 'success-text' : 'error-text'}
                  style={{
                    marginBottom: '1rem', fontSize: '0.9rem', padding: '0.5rem 0.75rem', borderRadius: '6px',
                    background: message.type === 'success' ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)'
                  }}
                >
                  {message.text}
                </div>
              )}

              <button
                type="submit"
                className="btn btn-primary"
                style={{ width: '100%', fontSize: '1rem' }}
                disabled={loading || !amount}
              >
                {loading ? (
                  <><RefreshCw size={16} style={{ animation: 'spin 0.8s linear infinite' }} /> Processing...</>
                ) : (
                  <><CreditCard size={16} /> Add ₹{amount || '0'} to Wallet</>
                )}
              </button>
            </form>
          </div>
        </div>

        {/* RIGHT: Transaction History */}
        <div>
          <div className="glass-card">
            <h3 style={{ marginBottom: '1.5rem' }}>Transaction History</h3>
            <div style={{
              display: 'flex', flexDirection: 'column', gap: '0.75rem',
              maxHeight: '500px', overflowY: 'auto', paddingRight: '0.25rem'
            }}>
              {transactions.length === 0 ? (
                <div style={{ textAlign: 'center', padding: '3rem 0', color: 'var(--text-muted)' }}>
                  <Wallet size={36} style={{ marginBottom: '0.75rem', opacity: 0.3 }} />
                  <p>No transactions yet</p>
                  <p style={{ fontSize: '0.8rem', marginTop: '0.25rem' }}>Add money to get started</p>
                </div>
              ) : (
                transactions.map(txn => (
                  <div key={txn.id} className="txn-row">
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                      <div className={`txn-icon ${txn.type === 'CREDIT' ? 'credit' : 'debit'}`}>
                        {txn.type === 'CREDIT'
                          ? <ArrowUpCircle size={18} />
                          : <ArrowDownCircle size={18} />}
                      </div>
                      <div>
                        <p style={{ fontSize: '0.85rem', fontWeight: '500', marginBottom: '0.15rem' }}>
                          {txn.description}
                        </p>
                        <p style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
                          {formatDate(txn.createdAt)}
                        </p>
                      </div>
                    </div>
                    <div className={`txn-amount ${txn.type === 'CREDIT' ? 'credit' : 'debit'}`}>
                      {txn.type === 'CREDIT' ? '+' : '-'}₹{Number(txn.amount).toFixed(2)}
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default WalletPage;
