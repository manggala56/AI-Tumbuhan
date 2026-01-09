import { useState, useRef, useCallback } from 'react';
import Webcam from 'react-webcam';
import { useLocation, useNavigate } from 'react-router-dom';
import { uploadScan } from '../services/api';
import { Camera, Upload, RefreshCw, ArrowLeft, X, Sparkles } from 'lucide-react';
export default function ScanPage() {
    const { state } = useLocation();
    const navigate = useNavigate();
    const webcamRef = useRef(null);
    const fileInputRef = useRef(null);
    const [imgSrc, setImgSrc] = useState(null);
    const [isUploading, setIsUploading] = useState(false);
    const [error, setError] = useState(null);
    const plant = state?.plant;
    if (!plant) {
        navigate('/');
        return null;
    }
    const capture = useCallback(() => {
        const imageSrc = webcamRef.current.getScreenshot();
        setImgSrc(imageSrc);
    }, [webcamRef]);
    const handleFileUpload = (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => setImgSrc(e.target.result);
            reader.readAsDataURL(file);
        }
    };
    const processScan = async () => {
        setIsUploading(true);
        setError(null);
        try {
            const res = await fetch(imgSrc);
            const blob = await res.blob();
            const file = new File([blob], "scan.jpg", { type: "image/jpeg" });
            const formData = new FormData();
            formData.append('plant_type_id', plant.id);
            formData.append('image', file);
            const response = await uploadScan(formData);
            navigate('/result', { state: { result: response, plant } });
        } catch (err) {
            console.error(err);
            const errorMessage = err.userMessage || "Failed to analyze image. Please try again.";
            setError(errorMessage);
            setIsUploading(false);
        }
    };
    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
            {}
            <div className="bg-gradient-to-r from-primary to-emerald-600 text-white px-6 py-4 flex items-center justify-between shadow-lg">
                <button onClick={() => navigate('/')} className="p-2 -ml-2 hover:bg-white/20 rounded-lg transition-colors">
                    <ArrowLeft size={24} />
                </button>
                <div className="flex-1 text-center">
                    <h2 className="font-bold text-lg">Scan {plant.name}</h2>
                    <p className="text-xs text-emerald-100">Position leaf clearly in frame</p>
                </div>
                <div className="w-10"></div>
            </div>
            {}
            <div className="p-6">
                <div className="relative bg-black rounded-3xl overflow-hidden shadow-2xl aspect-[3/4] max-h-[70vh]">
                    {imgSrc ? (
                        <img src={imgSrc} alt="Captured" className="w-full h-full object-contain" />
                    ) : (
                        <Webcam
                            audio={false}
                            ref={webcamRef}
                            screenshotFormat="image/jpeg"
                            videoConstraints={{ facingMode: "environment" }}
                            className="w-full h-full object-cover"
                        />
                    )}
                    {}
                    {!imgSrc && (
                        <div className="absolute inset-0 pointer-events-none">
                            <div className="absolute inset-0 border-2 border-white/30 m-8 rounded-2xl"></div>
                            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white/60 text-center">
                                <Sparkles size={32} className="mx-auto mb-2" />
                                <p className="text-sm">Center the leaf</p>
                            </div>
                        </div>
                    )}
                    {imgSrc && (
                        <button
                            onClick={() => setImgSrc(null)}
                            className="absolute top-4 right-4 p-3 bg-black/50 hover:bg-black/70 text-white rounded-full backdrop-blur-sm transition-colors"
                        >
                            <X size={20} />
                        </button>
                    )}
                </div>
                {}
                {error && (
                    <div className="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm text-center animate-shake">
                        {error}
                    </div>
                )}
                {}
                <div className="mt-8 flex justify-center items-center gap-6">
                    {imgSrc ? (
                        <>
                            <button
                                onClick={() => setImgSrc(null)}
                                className="flex flex-col items-center text-gray-600 hover:text-primary transition-colors"
                            >
                                <div className="p-4 rounded-2xl bg-gray-100 hover:bg-gray-200 mb-2 transition-colors">
                                    <RefreshCw size={24} />
                                </div>
                                <span className="text-xs font-medium">Retake</span>
                            </button>
                            <button
                                onClick={processScan}
                                disabled={isUploading}
                                className="flex flex-col items-center"
                            >
                                <div className={`p-6 rounded-2xl bg-gradient-to-r from-primary to-emerald-600 text-white shadow-xl hover:shadow-2xl mb-2 transition-all ${isUploading ? 'opacity-50 cursor-not-allowed' : 'hover:scale-105'}`}>
                                    {isUploading ? (
                                        <div className="animate-spin">
                                            <RefreshCw size={28} />
                                        </div>
                                    ) : (
                                        <Sparkles size={28} />
                                    )}
                                </div>
                                <span className="text-sm font-semibold text-primary">
                                    {isUploading ? 'Analyzing...' : 'Analyze'}
                                </span>
                            </button>
                        </>
                    ) : (
                        <>
                            <button
                                onClick={() => fileInputRef.current.click()}
                                className="flex flex-col items-center text-gray-600 hover:text-primary transition-colors"
                            >
                                <div className="p-4 rounded-2xl bg-gray-100 hover:bg-gray-200 mb-2 transition-colors">
                                    <Upload size={24} />
                                </div>
                                <span className="text-xs font-medium">Upload</span>
                            </button>
                            <input
                                type="file"
                                ref={fileInputRef}
                                className="hidden"
                                accept="image/*"
                                onChange={handleFileUpload}
                            />
                            <button
                                onClick={capture}
                                className="flex flex-col items-center"
                            >
                                <div className="p-6 rounded-2xl bg-gradient-to-r from-primary to-emerald-600 text-white shadow-xl hover:shadow-2xl hover:scale-105 mb-2 transition-all">
                                    <Camera size={32} />
                                </div>
                                <span className="text-sm font-semibold text-primary">Capture</span>
                            </button>
                            <div className="w-[72px]"></div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
