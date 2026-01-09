#!/bin/bash

echo "🚀 Setting up Production Environment..."
echo ""

# Check if we're in the production directory
if [ ! -f "README.md" ]; then
    echo "❌ Please run this script from the production directory"
    exit 1
fi

# Backend Setup
echo "📦 Setting up Backend..."
cd backend
if [ -f "composer.json" ]; then
    composer install
    if [ ! -f ".env" ]; then
        cp .env.example .env
        php artisan key:generate
    fi
    php artisan migrate --seed
    echo "✅ Backend setup complete"
else
    echo "⚠️  Backend composer.json not found"
fi
cd ..

# AI Service Setup
echo ""
echo "🤖 Setting up AI Service..."
cd ai
if [ -f "requirements.txt" ]; then
    python3 -m venv venv
    source venv/bin/activate
    pip install -r requirements.txt
    echo "✅ AI Service setup complete"
else
    echo "⚠️  AI requirements.txt not found"
fi
cd ..

# Frontend Setup
echo ""
echo "🎨 Setting up Frontend..."
cd frontend
if [ -f "package.json" ]; then
    npm install
    echo "✅ Frontend setup complete"
else
    echo "⚠️  Frontend package.json not found"
fi
cd ..

echo ""
echo "✅ All components setup complete!"
echo ""
echo "📝 Next steps:"
echo "   1. Configure .env files"
echo "   2. Run: ./start.sh"
