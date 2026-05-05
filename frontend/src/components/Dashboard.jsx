import React, { useEffect, useState } from 'react';
import { Line, Pie } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import { Ticket, Film, MapPin, TrendingUp, Users, CheckCircle, Info, PieChart } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement);

const Dashboard = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const navigate = useNavigate();

    useEffect(() => {
        fetch('/api/analytics/dashboard', {
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
        })
            .then(res => {
                if (!res.ok) throw new Error('Failed to fetch analytics');
                return res.json();
            })
            .then(d => {
                setData(d);
                setLoading(false);
            })
            .catch(err => {
                setError(err.message);
                setLoading(false);
            });
    }, []);

    if (loading) return <div className="text-center mt-4"><div className="loading-spinner"></div></div>;
    if (error) return <div className="error-text text-center mt-4">{error}</div>;

    const chartData = {
        labels: data.trendDates && data.trendDates.length > 0 ? data.trendDates : ['No Data'],
        datasets: [
            {
                label: 'Bookings',
                data: data.trendCounts && data.trendCounts.length > 0 ? data.trendCounts : [0],
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                pointRadius: 4,
            } // Removed inner trailing comma
        ]
    }; // Removed trailing comma

    const chartOptions = {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.1)' }, ticks: { precision: 0, color: 'rgba(255,255,255,0.7)' } },
            x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.7)' } }
        }
    };

    const pieChartData = {
        labels: data.pieLabels && data.pieLabels.length > 0 ? data.pieLabels : ['No Data'],
        datasets: [{
            data: data.pieData && data.pieData.length > 0 ? data.pieData : [0],
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(239, 68, 68, 0.8)',
            ],
            borderColor: '#1e293b',
            borderWidth: 2
        }]
    };
    
    const pieOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right', labels: { color: 'rgba(255,255,255,0.8)' } }
        }
    };

    return (
        <div style={{ maxWidth: '1200px', margin: '0 auto', paddingBottom: '3rem' }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '2.5rem' }}>
                <h2 className="title-gradient" style={{ margin: 0, fontSize: '2.2rem' }}>Analytics Dashboard</h2>
                <button className="nav-back-btn" onClick={() => navigate('/')}>Back Home</button>
            </div>

            {/* Top Cards Row */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '1.5rem', marginBottom: '2rem' }}>
                <div className="netflix-card" style={{ display: 'flex', alignItems: 'center', gap: '1rem', padding: '1.5rem' }}>
                    <div style={{ background: 'rgba(59,130,246,0.15)', padding: '1rem', borderRadius: '12px', color: 'var(--primary-color)' }}>
                        <Ticket size={28} />
                    </div>
                    <div>
                        <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: '0.25rem' }}>Total Bookings</p>
                        <h3 style={{ fontSize: '1.8rem', margin: 0 }}>{data.totalBookings}</h3>
                    </div>
                </div>

                <div className="netflix-card" style={{ display: 'flex', alignItems: 'center', gap: '1rem', padding: '1.5rem' }}>
                    <div style={{ background: 'rgba(16,185,129,0.15)', padding: '1rem', borderRadius: '12px', color: 'var(--success)' }}>
                        <MapPin size={28} />
                    </div>
                    <div>
                        <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: '0.25rem' }}>Top City</p>
                        <h3 style={{ fontSize: '1.2rem', margin: 0, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis', maxWidth: '150px' }}>{data.mostPopularCity}</h3>
                        <p style={{ color: 'var(--success)', fontSize: '0.8rem', margin: 0 }}>{data.mostPopularCityCount} bookings</p>
                    </div>
                </div>

                <div className="netflix-card" style={{ display: 'flex', alignItems: 'center', gap: '1rem', padding: '1.5rem' }}>
                    <div style={{ background: 'rgba(245,158,11,0.15)', padding: '1rem', borderRadius: '12px', color: 'var(--warning, #f59e0b)' }}>
                        <CheckCircle size={28} />
                    </div>
                    <div>
                        <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: '0.25rem' }}>Seats Booked</p>
                        <h3 style={{ fontSize: '1.8rem', margin: 0 }}>{data.totalBookedSeats}</h3>
                    </div>
                </div>

                <div className="netflix-card" style={{ display: 'flex', alignItems: 'center', gap: '1rem', padding: '1.5rem' }}>
                    <div style={{ background: 'rgba(139,92,246,0.15)', padding: '1rem', borderRadius: '12px', color: 'var(--accent)' }}>
                        <Info size={28} />
                    </div>
                    <div>
                        <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: '0.25rem' }}>Seats Available</p>
                        <h3 style={{ fontSize: '1.8rem', margin: 0 }}>{data.totalAvailableSeats}</h3>
                    </div>
                </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(500px, 1fr))', gap: '2rem' }}>
                
                {/* Charts Column */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
                    {/* Bookings Trend Chart */}
                    <div className="netflix-card" style={{ padding: '2rem' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '1.5rem' }}>
                            <TrendingUp size={24} style={{ color: 'var(--primary-color)' }} />
                            <h3 style={{ margin: 0 }}>Booking Trends</h3>
                        </div>
                        <div style={{ height: '300px' }}>
                            <Line data={chartData} options={chartOptions} />
                        </div>
                    </div>

                    {/* Movie Distribution Pie Chart */}
                    <div className="netflix-card" style={{ padding: '2rem' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '1.5rem' }}>
                            <PieChart size={24} style={{ color: 'var(--success)' }} />
                            <h3 style={{ margin: 0 }}>Movie Distribution</h3>
                        </div>
                        <div style={{ height: '300px', display: 'flex', justifyContent: 'center' }}>
                            <Pie data={pieChartData} options={pieOptions} />
                        </div>
                    </div>
                </div>

                {/* Split Column for Top Movie & Occupancy */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
                    
                    {/* Overall Top Movie */}
                    <div className="netflix-card" style={{ padding: '2rem' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '1.5rem' }}>
                            <Film size={24} style={{ color: 'var(--success)' }} />
                            <h3 style={{ margin: 0 }}>Current Top Movie</h3>
                        </div>
                        {data.topMovieOverall ? (
                            <div style={{ display: 'flex', gap: '1.5rem', alignItems: 'center' }}>
                                <img src={data.topMovieOverall.posterUrl} alt="Top Movie" style={{ width: '90px', borderRadius: '8px', boxShadow: '0 4px 15px rgba(0,0,0,0.3)' }} />
                                <div>
                                    <h2 style={{ margin: 0, marginBottom: '0.5rem' }}>{data.topMovieOverall.title}</h2>
                                    <div style={{ display: 'flex', gap: '0.5rem', marginBottom: '0.8rem' }}>
                                        <span className="movie-tag">{data.topMovieOverall.language}</span>
                                        <span className="movie-tag">{data.topMovieOverall.genre}</span>
                                    </div>
                                    <p style={{ color: 'var(--success)', margin: 0, fontWeight: '600' }}>{data.mostBookedMovieCount} Total Bookings</p>
                                </div>
                            </div>
                        ) : <p style={{ color: 'var(--text-muted)' }}>No bookings yet.</p>}
                    </div>

                    {/* Occupancy Progress Bar */}
                    <div className="netflix-card" style={{ padding: '2rem' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '1.5rem' }}>
                            <Users size={24} style={{ color: 'var(--accent)' }} />
                            <h3 style={{ margin: 0 }}>Platform Seat Occupancy</h3>
                        </div>
                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem' }}>
                            <span style={{ color: 'var(--text-muted)' }}>Booked: {data.totalBookedSeats}</span>
                            <span style={{ fontWeight: 'bold' }}>{data.occupancyPercentage}</span>
                        </div>
                        <div style={{ width: '100%', height: '14px', background: 'rgba(255,255,255,0.1)', borderRadius: '10px', overflow: 'hidden' }}>
                            <div style={{ width: data.occupancyPercentage, height: '100%', background: 'linear-gradient(90deg, var(--primary-color), var(--success))', transition: 'width 1s ease-in-out' }}></div>
                        </div>
                        <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem', marginTop: '1rem', textAlign: 'right' }}>
                            Total Base Seats: {data.totalAvailableSeats + data.totalBookedSeats}
                        </p>
                    </div>

                    {/* Recommendation Engine Snippet */}
                    {data.recommendedMovie && (
                        <div className="netflix-card" style={{ padding: '2rem', border: '1px solid rgba(139, 92, 246, 0.3)' }}>
                            <h3 style={{ color: 'var(--accent)', marginBottom: '1rem', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>✨ Recommended For You</h3>
                            <div style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
                                <img src={data.recommendedMovie.posterUrl} alt="Recommended" style={{ width: '60px', borderRadius: '6px' }} />
                                <div>
                                    <h4 style={{ margin: 0, fontSize: '1.2rem' }}>{data.recommendedMovie.title}</h4>
                                    <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', margin: '0.2rem 0' }}>{data.recommendedReason}</p>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
