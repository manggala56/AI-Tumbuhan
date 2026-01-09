#!/bin/bash

echo "🚀 Starting Plant Disease AI System..."
echo ""

# Start Backend
echo "📦 Starting Backend (Laravel)..."
cd backend
php artisan serve --port=8000 &
BACKEND_PID=$!
echo "✅ Backend running on http://localhost:8000 (PID: $BACKEND_PID)"
cd ..

# Start AI Service
echo ""
echo "🤖 Starting AI Service (FastAPI)..."
cd ai
source venv/bin/activate
uvicorn app.main:app --port 8001 &
AI_PID=$!
echo "✅ AI Service running on http://localhost:8001 (PID: $AI_PID)"
cd ..

# Start Frontend
echo ""
echo "🎨 Starting Frontend (React)..."
cd frontend
npm run dev &
FRONTEND_PID=$!
echo "✅ Frontend running on http://localhost:5173 (PID: $FRONTEND_PID)"
cd ..

echo ""
echo "✅ All services started!"
echo ""
echo "📝 Access points:"
echo "   Frontend:  http://localhost:5173"
echo "   Backend:   http://localhost:8000"
echo "   AI API:    http://localhost:8001"
echo "   Admin:     http://localhost:8000/admin"
echo ""
echo "Press Ctrl+C to stop all services"

# Wait for Ctrl+C
trap "kill $BACKEND_PID $AI_PID $FRONTEND_PID; exit" INT
wait
