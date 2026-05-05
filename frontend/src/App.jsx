import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import Navbar from './components/Navbar';
import Login from './components/Login';
import Signup from './components/Signup';
import MovieList from './components/MovieList';
import ShowList from './components/ShowList';
import SeatSelection from './components/SeatSelection';
import BookingSummary from './components/BookingSummary';
import BookingHistory from './components/BookingHistory';
import WalletPage from './components/WalletPage';
import Dashboard from './components/Dashboard';

const PrivateRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  return token ? children : <Navigate to="/login" />;
};

function App() {
  return (
    <div className="app-container">
      <Navbar />
      <main className="main-content">
        <Routes>
          <Route path="/" element={<MovieList />} />
          <Route path="/login" element={<Login />} />
          <Route path="/signup" element={<Signup />} />
          <Route path="/shows/:movieId" element={<PrivateRoute><ShowList /></PrivateRoute>} />
          <Route path="/seats/:showId" element={<PrivateRoute><SeatSelection /></PrivateRoute>} />
          <Route path="/summary/:showId" element={<PrivateRoute><BookingSummary /></PrivateRoute>} />
          <Route path="/history" element={<PrivateRoute><BookingHistory /></PrivateRoute>} />
          <Route path="/wallet" element={<PrivateRoute><WalletPage /></PrivateRoute>} />
          <Route path="/dashboard" element={<PrivateRoute><Dashboard /></PrivateRoute>} />
        </Routes>
      </main>
    </div>
  );
}

export default App;
