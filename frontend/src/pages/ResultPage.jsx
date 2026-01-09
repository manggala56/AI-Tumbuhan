import { useLocation, useNavigate } from 'react-router-dom';
import { useState } from 'react';
import { submitFeedback } from '../services/api';
import { ArrowLeft, CheckCircle, AlertTriangle, Star, Heart } from 'lucide-react';
export default function ResultPage() {
    const { state } = useLocation();
    const navigate = useNavigate();
    const result = state?.result;
    const plant = state?.plant;
    if (!result) {
        navigate('/');
        return null;
    }
    const [rating, setRating] = useState(0);
    const [comment, setComment] = useState("");
    const [feedbackSent, setFeedbackSent] = useState(false);
    const isHealthy = result.disease_name?.toLowerCase().includes('healthy');
    const handleFeedback = async () => {
        if (rating === 0) return;
        try {
            await submitFeedback(result.id, { rating, comment });
            setFeedbackSent(true);
        } catch (error) {
            console.error("Feedback failed", error);
        }
    };
    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 pb-24">
            {}
            <div className="bg-gradient-to-r from-primary to-emerald-600 text-white px-6 py-4 flex items-center justify-between shadow-lg">
                <button onClick={() => navigate('/')} className="p-2 -ml-2 hover:bg-white/20 rounded-lg transition-colors">
                    <ArrowLeft size={24} />
                </button>
                <h2 className="font-bold text-lg">Diagnosis Result</h2>
                <div className="w-10"></div>
            </div>
            <div className="px-6 py-6 space-y-6">
                {}
                <div className="bg-white rounded-3xl shadow-xl overflow-hidden">
                    <div className="h-56 w-full bg-gradient-to-br from-gray-100 to-gray-200 relative">
                        <img
                            src={result.scan_image_url || "https:
                            alt="Scan"
                            className="w-full h-full object-cover"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                        <div className="absolute bottom-0 left-0 right-0 p-5 text-white">
                            <p className="text-sm font-medium opacity-90">{plant.name}</p>
                            <p className="text-xs opacity-70">{new Date(result.created_at).toLocaleDateString('id-ID', { dateStyle: 'long' })}</p>
                        </div>
                    </div>
                    <div className="p-6">
                        <div className="flex items-start gap-4 mb-4">
                            <div className={`p-3 rounded-2xl ${isHealthy ? 'bg-green-100' : 'bg-red-100'}`}>
                                {isHealthy ? (
                                    <CheckCircle className="text-green-600" size={32} />
                                ) : (
                                    <AlertTriangle className="text-red-600" size={32} />
                                )}
                            </div>
                            <div className="flex-1">
                                <h3 className="text-2xl font-bold text-gray-900 leading-tight mb-2">
                                    {result.disease_name}
                                </h3>
                                <div className="flex items-center gap-2">
                                    <div className={`px-3 py-1 rounded-full text-sm font-semibold ${result.confidence_score > 80 ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'
                                        }`}>
                                        {result.confidence_score?.toFixed(1)}% Confidence
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {}
                {!isHealthy && result.treatment_advice && (
                    <div className="bg-gradient-to-br from-blue-50 to-cyan-50 p-6 rounded-3xl border border-blue-100 shadow-md">
                        <div className="flex items-center gap-2 mb-3">
                            <div className="text-2xl">💊</div>
                            <h3 className="font-bold text-blue-900">Treatment Recommendation</h3>
                        </div>
                        <div className="text-sm text-blue-800 whitespace-pre-line leading-relaxed pl-8">
                            {result.treatment_advice}
                        </div>
                    </div>
                )}
                {}
                <div className="bg-white p-6 rounded-3xl shadow-lg">
                    <div className="flex items-center gap-2 mb-4">
                        <Heart className="text-pink-500" size={20} />
                        <h3 className="font-bold text-gray-800">Help Us Improve</h3>
                    </div>
                    {!feedbackSent ? (
                        <div className="space-y-4">
                            <div className="flex justify-center gap-2">
                                {[1, 2, 3, 4, 5].map((star) => (
                                    <button
                                        key={star}
                                        onClick={() => setRating(star)}
                                        className={`transition-all ${star <= rating ? 'text-yellow-400 scale-110' : 'text-gray-300'}`}
                                    >
                                        <Star fill={star <= rating ? "currentColor" : "none"} size={36} />
                                    </button>
                                ))}
                            </div>
                            <textarea
                                className="w-full border-2 border-gray-200 rounded-2xl p-4 text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all"
                                placeholder="Share your experience (optional)"
                                rows={3}
                                value={comment}
                                onChange={(e) => setComment(e.target.value)}
                            />
                            <button
                                onClick={handleFeedback}
                                disabled={rating === 0}
                                className="w-full bg-gradient-to-r from-primary to-emerald-600 text-white py-4 rounded-2xl font-bold disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-xl transition-all"
                            >
                                Submit Feedback
                            </button>
                        </div>
                    ) : (
                        <div className="text-center py-6">
                            <div className="text-5xl mb-3">🎉</div>
                            <p className="text-green-600 font-semibold text-lg">Thank you for your feedback!</p>
                            <p className="text-gray-500 text-sm mt-1">Your input helps us improve our AI</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
