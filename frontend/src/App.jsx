import { BrowserRouter as Router, Routes, Route, Link, useLocation } from 'react-router-dom';
import PlantSelection from './pages/PlantSelection';
import ScanPage from './pages/ScanPage';
import ResultPage from './pages/ResultPage';
import HistoryPage from './pages/HistoryPage';
import { Camera, History, Home } from 'lucide-react';
function BottomNav() {
  const location = useLocation();
  const isActive = (path) => location.pathname === path;
  return (
    <nav className="fixed bottom-0 w-full bg-white/80 backdrop-blur-lg border-t border-gray-200 z-10 shadow-lg">
      <div className="max-w-md mx-auto flex justify-around py-2">
        <Link
          to="/"
          className={`flex flex-col items-center py-2 px-6 rounded-xl transition-all ${isActive('/') ? 'text-primary bg-primary/10' : 'text-gray-500 hover:text-primary'
            }`}
        >
          <Home size={24} />
          <span className="text-xs mt-1 font-medium">Home</span>
        </Link>
        <Link
          to="/"
          className="flex flex-col items-center -mt-6 bg-gradient-to-r from-primary to-emerald-600 text-white p-4 rounded-2xl shadow-xl hover:shadow-2xl transition-all hover:scale-105"
        >
          <Camera size={28} />
          <span className="text-xs mt-1 font-medium">Scan</span>
        </Link>
        <Link
          to="/history"
          className={`flex flex-col items-center py-2 px-6 rounded-xl transition-all ${isActive('/history') ? 'text-primary bg-primary/10' : 'text-gray-500 hover:text-primary'
            }`}
        >
          <History size={24} />
          <span className="text-xs mt-1 font-medium">History</span>
        </Link>
      </div>
    </nav>
  );
}
function App() {
  return (
    <Router>
      <div className="min-h-screen bg-gray-50 pb-24">
        {}
        <main className="max-w-md mx-auto">
          <Routes>
            <Route path="/" element={<PlantSelection />} />
            <Route path="/scan/:plantSlug" element={<ScanPage />} />
            <Route path="/result" element={<ResultPage />} />
            <Route path="/history" element={<HistoryPage />} />
          </Routes>
        </main>
        {}
        <BottomNav />
      </div>
    </Router>
  );
}
export default App;
