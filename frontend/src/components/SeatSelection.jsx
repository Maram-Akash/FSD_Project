import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';

const SeatSelection = () => {
  const { showId } = useParams();
  const [seats, setSeats] = useState([]);
  const [selectedSeats, setSelectedSeats] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    fetchSeats();
  }, [showId]);

  const fetchSeats = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem('token');
      const response = await fetch(`/api/seats/${showId}`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      if (response.ok) {
        const data = await response.json();
        setSeats(data);
      }
    } catch (err) {
      console.error('Failed to fetch seats', err);
    } finally {
      setLoading(false);
    }
  };

  // Heatmap pricing based on row
  const getSeatPrice = (seatNumber) => {
    const row = seatNumber.charAt(0);
    if (['A', 'B'].includes(row)) return 100;   // Front
    if (['C', 'D', 'E'].includes(row)) return 150; // Middle
    return 200;                                   // Back
  };

  // Heatmap color based on row zone
  const getSeatZone = (seatNumber) => {
    const row = seatNumber.charAt(0);
    if (['A', 'B'].includes(row)) return 'front';
    if (['C', 'D', 'E'].includes(row)) return 'middle';
    return 'back';
  };

  const totalPrice = selectedSeats.reduce((sum, seat) => sum + getSeatPrice(seat.seatNumber), 0);

  const handleSeatClick = (seat) => {
    if (seat.booked) return;
    if (selectedSeats.find(s => s.id === seat.id)) {
      setSelectedSeats(selectedSeats.filter(s => s.id !== seat.id));
    } else {
      setSelectedSeats([...selectedSeats, seat]);
    }
  };

  const handleContinue = () => {
    if (selectedSeats.length === 0) return;
    navigate(`/summary/${showId}`, { state: { selectedSeats, totalPrice } });
  };

  // Group seats by row
  const rows = seats.reduce((acc, seat) => {
    const rowId = seat.seatNumber.charAt(0);
    if (!acc[rowId]) acc[rowId] = [];
    acc[rowId].push(seat);
    return acc;
  }, {});

  if (loading) {
    return (
      <div style={{ textAlign: 'center', padding: '4rem' }}>
        <div className="loading-spinner"></div>
        <p style={{ color: 'var(--text-muted)', marginTop: '1rem' }}>Loading seats...</p>
      </div>
    );
  }

  return (
    <div>
      <div style={{ display: 'flex', alignItems: 'center', gap: '1rem', marginBottom: '1.5rem', justifyContent: 'center' }}>
        <button className="nav-back-btn" onClick={() => navigate(-1)} style={{ position: 'absolute', left: '2rem' }}>
          <ArrowLeft size={20} />
        </button>
        <h2 className="text-center" style={{ margin: 0 }}>Select Your Seats</h2>
      </div>

      <div className="glass-card" style={{ maxWidth: '900px', margin: '0 auto' }}>

        {/* SCREEN */}
        <div className="screen-container">
          <div className="screen"></div>
        </div>

        {/* ZONE LEGEND */}
        <div className="zone-legend">
          <div className="zone-item">
            <div className="zone-dot front-dot"></div>
            <span>Front</span>
          </div>
          <div className="zone-item">
            <div className="zone-dot middle-dot"></div>
            <span>Middle</span>
          </div>
          <div className="zone-item">
            <div className="zone-dot back-dot"></div>
            <span>Back</span>
          </div>
        </div>

        {/* HEATMAP SEAT GRID */}
        <div className="seat-grid">
          {Object.keys(rows).sort().map(rowId => {
            const zone = getSeatZone(rowId + '1');
            return (
              <div key={rowId} className="seat-row">
                <div className={`row-label zone-label-${zone}`}>{rowId}</div>
                {rows[rowId]
                  .sort((a, b) => parseInt(a.seatNumber.substring(1)) - parseInt(b.seatNumber.substring(1)))
                  .map(seat => {
                    const isSelected = !!selectedSeats.find(s => s.id === seat.id);
                    const zone = getSeatZone(seat.seatNumber);
                    let seatClass = 'seat';
                    if (seat.booked) seatClass += ' booked';
                    else if (isSelected) seatClass += ' selected';
                    else seatClass += ` available heatmap-${zone}`;
                    return (
                      <div
                        key={seat.id}
                        className={seatClass}
                        onClick={() => handleSeatClick(seat)}
                        title={`Seat ${seat.seatNumber}`}
                      >
                        {seat.seatNumber.substring(1)}
                      </div>
                    );
                  })}
              </div>
            );
          })}
        </div>

        {/* LEGEND */}
        <div className="seat-legend">
          <div className="legend-item">
            <div className="legend-box available heatmap-front"></div>
            <span>Available</span>
          </div>
          <div className="legend-item">
            <div className="legend-box selected"></div>
            <span>Selected</span>
          </div>
          <div className="legend-item">
            <div className="legend-box booked"></div>
            <span>Booked</span>
          </div>
        </div>

        {/* SELECTED SEATS SUMMARY */}
        {selectedSeats.length > 0 && (
          <div className="seat-summary">
            <div>
              <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem', marginBottom: '0.5rem' }}>Selected Seats</p>
              <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap' }}>
                {selectedSeats.map(s => (
                  <span key={s.id} className={`seat-tag zone-tag-${getSeatZone(s.seatNumber)}`}>
                    {s.seatNumber} — ₹{getSeatPrice(s.seatNumber)}
                  </span>
                ))}
              </div>
            </div>
            <div style={{ textAlign: 'right', minWidth: '140px' }}>
              <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>Total</p>
              <h2 style={{ color: 'var(--success)', fontSize: '2.2rem', fontWeight: '800' }}>₹{totalPrice}</h2>
            </div>
          </div>
        )}

        <button
          className="btn btn-primary"
          onClick={handleContinue}
          disabled={selectedSeats.length === 0}
          style={{ width: '100%', marginTop: '2rem', opacity: selectedSeats.length === 0 ? 0.4 : 1, fontSize: '1.1rem', padding: '1rem' }}
        >
          {selectedSeats.length > 0 ? `Confirm ${selectedSeats.length} Ticket${selectedSeats.length > 1 ? 's' : ''} — ₹${totalPrice}` : 'Select Seats to Continue'}
        </button>
      </div>
    </div>
  );
};

export default SeatSelection;
