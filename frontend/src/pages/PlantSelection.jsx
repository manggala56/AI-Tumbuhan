import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { getPlantTypes } from '../services/api';
import { ChevronRight, Leaf, Sparkles, AlertCircle } from 'lucide-react';
export default function PlantSelection() {
    const [plantTypes, setPlantTypes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();
    useEffect(() => {
        fetchPlants();
    }, []);
    const fetchPlants = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await getPlantTypes();
            setPlantTypes(response.data);
        } catch (error) {
            console.error("Failed to load plants", error);
            const errorMessage = error.userMessage || "Failed to connect to server. Please check your connection.";
            setError(errorMessage);
        } finally {
            setLoading(false);
        }
    };
    const handleSelect = (plant) => {
        navigate(`/scan/${plant.slug}`, { state: { plant } });
    };
    const plantIcons = {
        tomato: '🍅',
        chilli: '🌶️',
        corn: '🌽',
        potato: '🥔',
    };
    return (
        <div className="min-h-[calc(100vh-180px)] bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50">
            {}
            <div className="relative overflow-hidden bg-gradient-to-r from-primary to-emerald-600 text-white px-6 py-12 rounded-b-[3rem] shadow-xl">
                <div className="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
                <div className="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>
                <div className="relative z-10 text-center space-y-3">
                    <div className="flex justify-center mb-4">
                        <div className="bg-white/20 backdrop-blur-sm p-4 rounded-2xl">
                            <Sparkles size={40} className="text-yellow-300" />
                        </div>
                    </div>
                    <h1 className="text-3xl font-bold">Plant Disease Detector</h1>
                    <p className="text-emerald-100 text-sm max-w-sm mx-auto">
                        Select your plant type to get started with AI-powered disease detection
                    </p>
                </div>
            </div>
            {}
            <div className="px-6 -mt-8 pb-6">
                {loading ? (
                    <div className="bg-white rounded-2xl shadow-lg p-12 text-center">
                        <div className="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
                        <p className="mt-4 text-gray-500">Loading plants...</p>
                    </div>
                ) : error ? (
                    <div className="bg-white rounded-2xl shadow-lg p-8">
                        <div className="flex items-start gap-3 mb-4">
                            <AlertCircle className="text-red-500 flex-shrink-0 mt-1" size={24} />
                            <div className="flex-1">
                                <h3 className="font-semibold text-red-800">Connection Error</h3>
                                <p className="text-sm text-red-600 mt-1">{error}</p>
                            </div>
                        </div>
                        <button
                            onClick={fetchPlants}
                            className="w-full px-6 py-3 bg-gradient-to-r from-primary to-emerald-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all"
                        >
                            Retry
                        </button>
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {plantTypes.map((plant, index) => (
                            <button
                                key={plant.id}
                                onClick={() => handleSelect(plant)}
                                style={{ animationDelay: `${index * 100}ms` }}
                                className="group relative bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden animate-[slideUp_0.5s_ease-out_forwards] opacity-0"
                            >
                                <div className="absolute inset-0 bg-gradient-to-r from-primary/5 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div className="relative flex items-center p-5">
                                    <div className="h-16 w-16 rounded-2xl bg-gradient-to-br from-green-100 to-emerald-100 flex items-center justify-center text-4xl group-hover:scale-110 transition-transform duration-300 shadow-sm">
                                        {plantIcons[plant.slug] || <Leaf className="text-primary" size={32} />}
                                    </div>
                                    <div className="ml-4 flex-1 text-left">
                                        <h3 className="font-bold text-lg text-gray-900 group-hover:text-primary transition-colors">
                                            {plant.name}
                                        </h3>
                                        <p className="text-sm text-gray-500 mt-1 line-clamp-2">
                                            {plant.description || 'Detect diseases and get treatment advice'}
                                        </p>
                                    </div>
                                    <ChevronRight
                                        className="text-gray-300 group-hover:text-primary group-hover:translate-x-1 transition-all"
                                        size={24}
                                    />
                                </div>
                            </button>
                        ))}
                    </div>
                )}
                {}
                {!loading && !error && (
                    <div className="mt-6 bg-gradient-to-r from-blue-50 to-cyan-50 p-5 rounded-2xl border border-blue-100">
                        <div className="flex gap-3 items-start">
                            <div className="text-2xl">💡</div>
                            <div>
                                <h4 className="text-sm font-semibold text-blue-900">Pro Tip</h4>
                                <p className="text-xs text-blue-700 mt-1 leading-relaxed">
                                    For best results, take photos in good lighting with the affected leaf clearly visible.
                                    Avoid shadows and ensure the leaf fills most of the frame.
                                </p>
                            </div>
                        </div>
                    </div>
                )}
            </div>
            <style jsx>{`
        @keyframes slideUp {
          from {
            opacity: 0;
            transform: translateY(20px);
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
