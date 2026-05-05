import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { ArrowLeft, Ticket, XCircle, CheckCircle, Calendar, MapPin, Clock, ChevronLeft, ChevronRight, Film } from 'lucide-react';

const BookingHistory = () => {
  const [bookings, setBookings] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(0);
  const [cancelModal, setCancelModal] = useState(null); // stores booking id
  const [successMsg, setSuccessMsg] = useState('');
  const navigate = useNavigate();
  const ITEMS_PER_PAGE = 4;

  useEffect(() => {
    fetchBookings();
  }, []);

  const fetchBookings = async () => {
    setLoading(true);
    try {
      const token = localStorage.getItem('token');
      const userStr = localStorage.getItem('user');
      if (!token || !userStr) return;
      
      const user = JSON.parse(userStr);
      const response = await fetch(`/api/bookings/user/${user.email}`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      
      if (response.ok) {
        const text = await response.text();
        try {
          const data = JSON.parse(text);
          data.sort((a, b) => new Date(b.bookingTime) - new Date(a.bookingTime));
          setBookings(data);
        } catch { console.error('Parse error:', text.substring(0, 200)); }
      }
    } catch (err) {
      console.error('Failed to fetch bookings', err);
    } finally {
      setLoading(false);
    }
  };

  const initiateCancel = (bookingId) => {
    setCancelModal(bookingId);
  };

  const handleCancel = async () => {
    if (!cancelModal) return;
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`/api/cancel/${cancelModal}`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` }
      });

      const text = await response.text();
      if (response.ok) {
        setSuccessMsg(text);
        fetchBookings();
      } else {
        setError(text || 'Cancellation failed');
      }
    } catch {
      setError('An error occurred during cancellation.');
    } finally {
      setCancelModal(null);
      setTimeout(() => setSuccessMsg(''), 5000);
    }
  };

  const formatDateTime = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) +
      ' at ' + date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
  };

  const formatShowTime = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
  };

  const formatShowDate = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleDateString('en-IN', { weekday: 'short', day: '2-digit', month: 'short' });
  };

  // Pagination
  const totalPages = Math.ceil(bookings.length / ITEMS_PER_PAGE);
  const paginatedBookings = bookings.slice(page * ITEMS_PER_PAGE, (page + 1) * ITEMS_PER_PAGE);

  const bookedCount = bookings.filter(b => b.status === 'BOOKED').length;
  const cancelledCount = bookings.filter(b => b.status === 'CANCELLED').length;

  if (loading) {
    return (
      <div style={{ textAlign: 'center', padding: '4rem' }}>
        <div className="loading-spinner"></div>
        <p style={{ color: 'var(--text-muted)', marginTop: '1rem' }}>Loading your bookings...</p>
      </div>
    );
  }

  return (
    <div style={{ maxWidth: '950px', margin: '0 auto' }}>

      {/* Header with back button */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <button className="nav-back-btn" onClick={() => navigate('/')}>
            <ArrowLeft size={20} />
          </button>
          <div>
            <h2 style={{ margin: 0 }}>My Bookings</h2>
            <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem', margin: 0, marginTop: '0.25rem' }}>
              {bookings.length} total • {bookedCount} active • {cancelledCount} cancelled
            </p>
          </div>
        </div>
        <button className="btn btn-primary" onClick={() => navigate('/')} style={{ padding: '0.5rem 1.25rem' }}>
          <Film size={16} /> Book More
        </button>
      </div>

      {error && <p className="error-text mb-4" style={{ textAlign: 'center' }}>{error}</p>}
      {successMsg && <div className="glass-card mb-4" style={{ borderColor: 'var(--success)', color: 'var(--success)', textAlign: 'center', fontWeight: 'bold' }}>{successMsg}</div>}

      {/* Custom Confirmation Modal */}
      {cancelModal && (
        <div className="modal-overlay">
          <div className="glass-card modal-content" style={{ maxWidth: '400px', textAlign: 'center' }}>
            <XCircle size={40} style={{ color: 'var(--danger)', marginBottom: '1rem', opacity: 0.8 }} />
            <h3 className="mb-2">Cancel Booking?</h3>
            <p style={{ color: 'var(--text-muted)', marginBottom: '1.5rem', fontSize: '0.95rem' }}>
              Are you sure you want to cancel this booking? Cancellation charges may apply depending on the show time.
            </p>
            <div style={{ display: 'flex', gap: '1rem', justifyContent: 'center' }}>
              <button className="btn btn-outline" onClick={() => setCancelModal(null)}>No, Keep It</button>
              <button className="btn" style={{ background: 'var(--danger)', color: 'white' }} onClick={handleCancel}>Yes, Cancel</button>
            </div>
          </div>
        </div>
      )}

      {/* Booking Cards */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
        {paginatedBookings.map((booking) => (
          <div key={booking.id} style={{ display: 'grid', gridTemplateColumns: 'minmax(0, 1fr) 220px', gap: '1.5rem', marginBottom: '1.5rem' }}>
            
            {/* Box A: Main Booking Info */}
            <div className={`booking-card ${booking.status === 'CANCELLED' ? 'cancelled' : ''}`} style={{ margin: 0, height: '100%' }}>
              <div style={{ display: 'flex', gap: '1.5rem' }}>
              {/* Movie Poster */}
              <div className="booking-poster">
                <img
                  src={booking.show?.movie?.posterUrl || 'https://placehold.co/120x180/1e293b/60a5fa?text=Movie'}
                  alt={booking.show?.movie?.title || 'Movie'}
                  onError={(e) => { e.target.onerror = null; e.target.src = `https://placehold.co/120x180/1e293b/60a5fa?text=${encodeURIComponent(booking.show?.movie?.title || 'Movie')}`; }}
                />
              </div>

              {/* Info */}
              <div style={{ flex: 1, minWidth: 0, position: 'relative' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                  <h3 style={{ marginBottom: '0.5rem', fontSize: '1.25rem' }}>
                    {booking.show?.movie?.title || 'Unknown Movie'}
                  </h3>
                  <div className={`booking-status-badge ${booking.status === 'BOOKED' ? 'booked' : 'cancelled'}`} style={{ position: 'relative', top: '0', right: '0', margin: '0' }}>
                    {booking.status === 'BOOKED'
                      ? <><CheckCircle size={14} /> Confirmed</>
                      : <><XCircle size={14} /> Cancelled</>}
                  </div>
                </div>

                <div className="booking-meta">
                  <span><Calendar size={14} /> {formatShowDate(booking.show?.showTime)}</span>
                  <span><Clock size={14} /> {formatShowTime(booking.show?.showTime)}</span>
                  <span><MapPin size={14} /> {booking.show?.screen?.theatre?.name || 'Theatre'}</span>
                </div>

                <div style={{ marginTop: '0.75rem', display: 'flex', gap: '0.4rem', flexWrap: 'wrap' }}>
                  {(booking.bookedSeats || []).map(bs => (
                    <span key={bs.id} className="booking-seat-chip">
                      {bs.seat?.seatNumber || 'N/A'}
                    </span>
                  ))}
                </div>

                <div className="booking-footer">
                  <div>
                    <span className="booking-id">Booking #{booking.id}</span>
                    <span className="booking-date">{formatDateTime(booking.bookingTime)}</span>
                  </div>
                </div>
              </div>

              </div>
            </div>

            {/* Box B: Payment Detail Isolated */}
            <div className={`booking-card ${booking.status === 'CANCELLED' ? 'cancelled' : ''}`} style={{ margin: 0, display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'center', textAlign: 'center', height: '100%', background: 'rgba(255,255,255,0.02)' }}>
                <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem', marginBottom: '0.5rem' }}>Total Amount Paid</p>
                <h3 style={{ color: 'var(--success)', margin: '0 0 0.5rem 0', fontSize: '1.8rem' }}>
                  ₹{booking.totalAmount ? booking.totalAmount.toFixed(2) : '0.00'}
                </h3>
                {booking.paymentMethod && (
                  <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', background: 'rgba(255,255,255,0.05)', padding: '0.2rem 0.6rem', borderRadius: '4px', marginBottom: '1.5rem' }}>
                    Standard {booking.paymentMethod}
                  </span>
                )}
                {booking.status === 'BOOKED' && (
                  <button
                    className="cancel-booking-btn"
                    onClick={() => initiateCancel(booking.id)}
                    style={{ width: '100%' }}
                  >
                    <XCircle size={14} /> Cancel Ticket
                  </button>
                )}
            </div>
          </div>
        ))}

        {bookings.length === 0 && (
          <div className="glass-card text-center" style={{ padding: '4rem 2rem' }}>
            <Ticket size={48} style={{ color: 'var(--text-muted)', opacity: 0.3, marginBottom: '1rem' }} />
            <h3 style={{ color: 'var(--text-muted)', marginBottom: '0.5rem' }}>No Bookings Yet</h3>
            <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: '1.5rem' }}>
              Start by browsing movies and booking your first ticket!
            </p>
            <button className="btn btn-primary" onClick={() => navigate('/')}>
              <Film size={16} /> Browse Movies
            </button>
          </div>
        )}
      </div>

      {/* Pagination */}
      {totalPages > 1 && (
        <div className="pagination-bar">
          <button
            className="pagination-btn"
            disabled={page === 0}
            onClick={() => setPage(p => p - 1)}
          >
            <ChevronLeft size={18} /> Previous
          </button>
          <div className="pagination-info">
            Page {page + 1} of {totalPages}
          </div>
          <button
            className="pagination-btn"
            disabled={page >= totalPages - 1}
            onClick={() => setPage(p => p + 1)}
          >
            Next <ChevronRight size={18} />
          </button>
        </div>
      )}
    </div>
  );
};

export default BookingHistory;
