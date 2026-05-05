import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Clock, Globe } from 'lucide-react';

const MovieList = () => {
  const [movies, setMovies] = useState([]);
  const [filter, setFilter] = useState('ALL');
  const navigate = useNavigate();

  useEffect(() => {
    fetchMovies();
  }, [filter]);

  const fetchMovies = async () => {
    try {
      const url = filter === 'ALL' ? '/api/movies' : `/api/movies/language/${filter}`;
      const response = await fetch(url);
      if (response.ok) {
        const data = await response.json();
        setMovies(data);
      }
    } catch (err) {
      console.error('Failed to fetch movies', err);
    }
  };

  const handleBookClick = (movieId) => {
    const token = localStorage.getItem('token');
    if (!token) {
      navigate('/login');
    } else {
      navigate(`/shows/${movieId}`);
    }
  };

  const getLanguageColor = (lang) => {
    switch (lang) {
      case 'Telugu': return '#f59e0b';
      case 'Hindi': return '#ef4444';
      case 'English': return '#3b82f6';
      default: return '#8b5cf6';
    }
  };

  return (
    <div>
      <h2 className="text-center mb-4" style={{ fontSize: '2rem' }}>
        <span className="title-gradient">Now Showing</span>
      </h2>
      
      <div className="filters">
        {['ALL', 'English', 'Telugu', 'Hindi'].map((lang) => (
          <button
            key={lang}
            className={`filter-btn ${filter === lang ? 'active' : ''}`}
            onClick={() => setFilter(lang)}
          >
            {lang === 'ALL' && <Globe size={14} />}
            {lang}
          </button>
        ))}
      </div>

      <div className="movie-grid">
        {movies.map((movie, index) => (
          <div 
            key={movie.id} 
            className="movie-card"
            style={{ animationDelay: `${index * 0.05}s` }}
          >
            <span 
              className="language-badge"
              style={{ 
                background: `${getLanguageColor(movie.language)}22`,
                color: getLanguageColor(movie.language),
                borderColor: `${getLanguageColor(movie.language)}55`
              }}
            >
              {movie.language}
            </span>
            {movie.universe && (
              <span className="universe-badge">{movie.universe}</span>
            )}
            <div className="movie-poster-wrapper">
              <img
                src={movie.posterUrl}
                alt={movie.title}
                className="movie-poster"
                onError={(e) => { e.target.onerror = null; e.target.src = `https://placehold.co/300x450/1e293b/60a5fa?text=${encodeURIComponent(movie.title)}`; }}
              />
              <div className="movie-poster-overlay">
                <button 
                  className="btn btn-primary overlay-book-btn"
                  onClick={() => handleBookClick(movie.id)}
                >
                  Book Now
                </button>
              </div>
            </div>
            <div className="movie-info">
              <h3 className="movie-title">{movie.title}</h3>
              <div className="movie-meta mb-4">
                <span>{movie.genre}</span>
                <span style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                  <Clock size={14} /> {movie.duration} min
                </span>
              </div>
              <button 
                className="btn btn-primary" 
                style={{ width: '100%' }}
                onClick={() => handleBookClick(movie.id)}
              >
                Book Tickets
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default MovieList;
