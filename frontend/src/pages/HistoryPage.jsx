import { useState, useEffect } from 'react';
import { getHistory } from '../services/api';
import { Calendar, Leaf, TrendingUp } from 'lucide-react';
export default function HistoryPage() {
    const [history, setHistory] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    useEffect(() => {
        fetchHistory();
    }, []);
    const fetchHistory = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await getHistory();
            setHistory(response.data);
        } catch (error) {
            console.error("Failed to load history", error);
            const errorMessage = error.userMessage || "Failed to load history. Please try again.";
            setError(errorMessage);
        } finally {
            setLoading(false);
        }
    };
    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 pb-24">
            {}
            <div className="bg-gradient-to-r from-primary to-emerald-600 text-white px-6 py-8 rounded-b-[3rem] shadow-xl">
                <div className="flex items-center gap-3 mb-2">
                    <TrendingUp size={28} />
                    <h1 className="text-2xl font-bold">Scan History</h1>
                </div>
                <p className="text-emerald-100 text-sm">Track your plant health journey</p>
            </div>
            <div className="px-6 -mt-6">
                {loading ? (
                    <div className="bg-white rounded-2xl shadow-lg p-12 text-center">
                        <div className="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
                        <p className="mt-4 text-gray-500">Loading history...</p>
                    </div>
                ) : error ? (
                    <div className="bg-white rounded-2xl shadow-lg p-12 text-center">
                        <div className="text-6xl mb-4">⚠️</div>
                        <p className="text-red-600 font-semibold mb-2">Error Loading History</p>
                        <p className="text-sm text-gray-600 mb-6">{error}</p>
                        <button
                            onClick={fetchHistory}
                            className="px-6 py-3 bg-gradient-to-r from-primary to-emerald-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all"
                        >
                            Retry
                        </button>
                    </div>
                ) : history.length === 0 ? (
                    <div className="bg-white rounded-2xl shadow-lg p-12 text-center">
                        <div className="text-6xl mb-4">🌱</div>
                        <p className="text-gray-600 font-semibold">No scans yet</p>
                        <p className="text-sm text-gray-400 mt-2">Start scanning your plants to build your history!</p>
                    </div>
                ) : (
                    <div className="space-y-4 py-6">
                        {history.map((scan, index) => (
                            <div
                                key={scan.id}
                                style={{ animationDelay: `${index * 50}ms` }}
                                className="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden animate-[slideUp_0.4s_ease-out_forwards] opacity-0"
                            >
                                <div className="flex gap-4 p-4">
                                    <div className="relative flex-shrink-0">
                                        <img
                                            src={scan.scan_image_url || "https:
                                            alt="Scan"
                                            className="w-24 h-24 rounded-xl object-cover bg-gray-100 shadow-sm"
                                        />
                                        <div className="absolute -top-2 -right-2 bg-primary text-white p-1.5 rounded-full shadow-lg">
                                            <Leaf size={16} />
                                        </div>
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <div className="flex justify-between items-start mb-2">
                                            <span className="text-xs font-bold uppercase text-primary tracking-wide bg-primary/10 px-2 py-1 rounded-lg">
                                                {scan.plant_type}
                                            </span>
                                            <span className="text-xs text-gray-400 flex items-center gap-1">
                                                <Calendar size={12} />
                                                {new Date(scan.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
                                            </span>
                                        </div>
                                        <h3 className="font-bold text-gray-900 mb-2 line-clamp-1">{scan.disease_name}</h3>
                                        <div className="flex items-center gap-2">
                                            <div className={`text-xs px-3 py-1.5 rounded-full font-semibold ${scan.confidence_score > 80 ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'
                                                }`}>
                                                {scan.confidence_score?.toFixed(0)}% Confident
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
            <style jsx>{`
        @keyframes slideUp {
          from {
            opacity: 0;
            transform: translateY(10px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
      `}</style>
        </div>
    );
}
