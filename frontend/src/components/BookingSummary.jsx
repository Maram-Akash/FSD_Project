import React, { useState, useEffect } from 'react';
import { useLocation, useParams, useNavigate } from 'react-router-dom';
import { Wallet, CheckCircle, AlertCircle } from 'lucide-react';

const BookingSummary = () => {
  const { showId } = useParams();
  const location = useLocation();
  const navigate = useNavigate();
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [walletBalance, setWalletBalance] = useState(null);
  const [loading, setLoading] = useState(false);

  const selectedSeats = location.state?.selectedSeats || [];
  const totalPrice = location.state?.totalPrice || 0;

  useEffect(() => {
    if (selectedSeats.length === 0) { navigate('/'); return; }
    fetchWalletBalance();
  }, []);

  const authHeader = () => ({
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
    'Content-Type': 'application/json'
  });

  const fetchWalletBalance = async () => {
    try {
      const res = await fetch('/api/wallet', { headers: authHeader() });
      if (res.ok) {
        const data = await res.json();
        setWalletBalance(data.balance ?? data);  // handle both {balance:...} and legacy formats
      }
    } catch { }
  };

  const getSeatZone = (seatNumber) => {
    const row = seatNumber.charAt(0);
    if (['A', 'B'].includes(row)) return 'front';
    if (['C', 'D', 'E'].includes(row)) return 'middle';
    return 'back';
  };

  const handleConfirmBooking = async () => {
    if (walletBalance !== null && walletBalance < totalPrice) {
      setError(`Insufficient wallet balance. Available: ₹${walletBalance.toFixed(2)}, Required: ₹${totalPrice}`);
      return;
    }
    setLoading(true);
    try {
      const response = await fetch('/api/book', {
        method: 'POST',
        headers: authHeader(),
        body: JSON.stringify({
          showId: parseInt(showId),
          seatIds: selectedSeats.map(s => s.id),
          paymentMethod: 'WALLET'
        })
      });

      if (response.ok) {
        // Update local wallet balance
        fetchWalletBalance();
        // Dispatch event to update Navbar wallet balance
        window.dispatchEvent(new Event('walletUpdated'));
        setSuccess(true);
        setTimeout(() => navigate('/history'), 3000);
      } else {
        const text = await response.text();
        setError(text || 'Booking failed');
      }
    } catch {
      setError('An error occurred. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const insufficientBalance = walletBalance !== null && walletBalance < totalPrice;

  return (
    <div>
      <h2 className="text-center mb-4">Booking Summary</h2>
      <div className="glass-card" style={{ maxWidth: '640px', margin: '0 auto' }}>
        {success ? (
          <div className="text-center" style={{ padding: '2rem 0' }}>
            <CheckCircle size={64} color="var(--success)" style={{ margin: '0 auto 1.5rem', display: 'block' }} />
            <h3 style={{ marginBottom: '0.5rem' }}>Booking Confirmed!</h3>
            <p style={{ color: 'var(--text-muted)' }}>Your tickets have been booked successfully.</p>
            <p className="mt-4" style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>Redirecting to your bookings...</p>
          </div>
        ) : (
          <>
            {/* Seats Section */}
            <div style={{ marginBottom: '1.5rem' }}>
              <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem', marginBottom: '0.75rem' }}>Selected Seats</p>
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.5rem' }}>
                {selectedSeats.map(seat => (
                  <span key={seat.id} className={`seat-tag zone-tag-${getSeatZone(seat.seatNumber)}`}>
                    {seat.seatNumber}
                  </span>
                ))}
              </div>
            </div>

            {/* Price Breakdown */}
            <div style={{ background: 'rgba(255,255,255,0.03)', borderRadius: '12px', padding: '1.25rem', marginBottom: '1.5rem' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem', fontSize: '0.9rem' }}>
                <span style={{ color: 'var(--text-muted)' }}>{selectedSeats.length} Ticket(s)</span>
                <span>₹{totalPrice}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem', fontSize: '0.9rem' }}>
                <span style={{ color: 'var(--text-muted)' }}>Convenience Fee</span>
                <span style={{ color: 'var(--success)' }}>FREE</span>
              </div>
              <div style={{ borderTop: '1px solid rgba(255,255,255,0.1)', paddingTop: '0.75rem', marginTop: '0.75rem', display: 'flex', justifyContent: 'space-between' }}>
                <span style={{ fontWeight: '700', fontSize: '1.1rem' }}>Total</span>
                <span style={{ fontWeight: '700', fontSize: '1.4rem', color: 'var(--success)' }}>₹{totalPrice}</span>
              </div>
            </div>

            {/* Wallet Payment */}
            <div className={`wallet-pay-box ${insufficientBalance ? 'insufficient' : ''}`}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '0.75rem' }}>
                <div className="wallet-icon-badge">
                  <Wallet size={20} />
                </div>
                <div>
                  <p style={{ fontWeight: '600', marginBottom: '0.15rem' }}>Pay with Wallet</p>
                  <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>
                    Available: {walletBalance !== null ? `₹${walletBalance.toFixed(2)}` : 'Loading...'}
                  </p>
                </div>
              </div>

              {insufficientBalance && (
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.3)', borderRadius: '8px', padding: '0.75rem', marginBottom: '0.75rem' }}>
                  <AlertCircle size={16} color="var(--danger)" />
                  <p style={{ fontSize: '0.85rem', color: 'var(--danger)' }}>
                    Insufficient balance. Need ₹{(totalPrice - walletBalance).toFixed(2)} more.{' '}
                    <span style={{ textDecoration: 'underline', cursor: 'pointer' }} onClick={() => navigate('/wallet')}>
                      Add money →
                    </span>
                  </p>
                </div>
              )}
            </div>

            {error && <p className="error-text text-center" style={{ marginBottom: '1rem' }}>{error}</p>}

            <div style={{ display: 'flex', gap: '1rem', marginTop: '1.5rem' }}>
              <button className="btn btn-outline" style={{ flex: 1 }} onClick={() => navigate(-1)}>
                Back
              </button>
              <button
                className="btn btn-primary"
                style={{ flex: 2, opacity: (insufficientBalance || loading) ? 0.5 : 1 }}
                onClick={handleConfirmBooking}
                disabled={insufficientBalance || loading}
              >
                <Wallet size={16} />
                {loading ? 'Processing...' : `Pay ₹${totalPrice} from Wallet`}
              </button>
            </div>
          </>
        )}
      </div>
    </div>
  );
};

export default BookingSummary;
