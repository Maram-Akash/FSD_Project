import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { MapPin, Calendar, ArrowLeft } from 'lucide-react';

const ShowList = () => {
  const { movieId } = useParams();
  const [shows, setShows] = useState([]);
  const [selectedCity, setSelectedCity] = useState(null);
  const [selectedTheatre, setSelectedTheatre] = useState(null);
  const [showCityModal, setShowCityModal] = useState(true);
  const navigate = useNavigate();

  const cities = [
    'Chennai', 'Bangalore', 'Mumbai', 'Delhi',
    'Hyderabad', 'Kolkata', 'Pune', 'Jaipur',
    'Kochi', 'Vizag', 'Lucknow', 'Ahmedabad'
  ];

  useEffect(() => {
    if (selectedCity) {
      fetchShows();
      setSelectedTheatre(null);
    }
  }, [movieId, selectedCity]);

  const fetchShows = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`/api/shows/${movieId}`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      if (response.ok) {
        const data = await response.json();
        const filtered = data.filter(s => s.screen.theatre.location.toLowerCase().includes(selectedCity.toLowerCase()));
        setShows(filtered);
      }
    } catch (err) {
      console.error('Failed to fetch shows', err);
    }
  };

  const handleCitySelect = (city) => {
    setSelectedCity(city);
    setShowCityModal(false);
  };

  const handleShowSelect = (showId) => {
    navigate(`/seats/${showId}`);
  };

  const formatTime = (isoString) => {
    const date = new Date(isoString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  // Group shows by Theatre
  const theatres = shows.reduce((acc, show) => {
    const theatreId = show.screen.theatre.id;
    if (!acc[theatreId]) {
      acc[theatreId] = {
        info: show.screen.theatre,
        slots: []
      };
    }
    acc[theatreId].slots.push(show);
    return acc;
  }, {});

  return (
    <div className="show-selection-container">
      {/* CITY SELECTION MODAL */}
      {showCityModal && (
        <div className="modal-overlay">
          <div className="glass-card modal-content" style={{ maxWidth: '600px' }}>
            <h2 className="text-center mb-4">Select Your City</h2>
            <div className="city-grid" style={{ gridTemplateColumns: 'repeat(3, 1fr)' }}>
              {cities.map((city, index) => (
                <button
                  key={city}
                  className={`city-btn ${selectedCity === city ? 'active' : ''}`}
                  onClick={() => handleCitySelect(city)}
                  style={{ animationDelay: `${index * 0.04}s` }}
                >
                  <MapPin size={16} style={{ marginRight: '0.4rem', opacity: 0.6 }} />
                  {city}
                </button>
              ))}
            </div>
          </div>
        </div>
      )}

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <button className="nav-back-btn" onClick={() => navigate('/')}>
            <ArrowLeft size={20} />
          </button>
          <h2 style={{ margin: 0 }}>Select {selectedTheatre ? 'Time Slot' : 'Theatre'} in {selectedCity}</h2>
        </div>
        {selectedCity && (
          <button className="btn btn-outline" style={{ fontSize: '0.8rem' }} onClick={() => setShowCityModal(true)}>
            Change City
          </button>
        )}
      </div>

      <div className="selection-layout">
        {/* LEVEL 2: THEATRE SELECTION */}
        {!selectedTheatre && (
          <div className="theatre-grid">
            {Object.values(theatres).map((t) => (
              <div key={t.info.id} className="glass-card theatre-card" onClick={() => setSelectedTheatre(t)}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                  <div className="theatre-icon">
                    <MapPin size={24} />
                  </div>
                  <div>
                    <h3 style={{ margin: 0 }}>{t.info.name}</h3>
                    <p style={{ color: 'var(--text-muted)', fontSize: '0.8rem' }}>{t.info.location}</p>
                  </div>
                </div>
                <div style={{ marginTop: '1rem', color: 'var(--primary-color)', fontSize: '0.9rem', fontWeight: '600' }}>
                  {t.slots.length} Shows Available
                </div>
              </div>
            ))}
            {selectedCity && shows.length === 0 && <p className="text-center">No theatres found for this movie in {selectedCity}.</p>}
          </div>
        )}

        {/* LEVEL 3: TIME SLOT SELECTION */}
        {selectedTheatre && (
          <div className="time-slot-view">
            <button className="btn btn-outline mb-4" onClick={() => setSelectedTheatre(null)} style={{ padding: '0.5rem 1rem' }}>
              ← Back to Theatres
            </button>
            <div className="glass-card">
              <div style={{ marginBottom: '2rem', borderBottom: '1px solid rgba(255,255,255,0.1)', paddingBottom: '1rem' }}>
                <h3>{selectedTheatre.info.name}</h3>
                <p style={{ color: 'var(--text-muted)' }}>{selectedTheatre.info.location}</p>
              </div>

              <p className="mb-4" style={{ fontWeight: '500' }}>Available Time Slots:</p>
              <div className="time-grid">
                {selectedTheatre.slots.map(show => (
                  <button key={show.id} className="time-btn" onClick={() => handleShowSelect(show.id)}>
                    <div className="time-val">{formatTime(show.showTime)}</div>
                    <div className="screen-val">{show.screen.screenName}</div>
                    <div className="price-val">₹{show.basePrice}</div>
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default ShowList;
