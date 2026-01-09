from fastapi import FastAPI, File, UploadFile, Form, BackgroundTasks
from fastapi.responses import JSONResponse
import shutil
from pathlib import Path
from app.model_logic import ModelManager
import uuid
import os
app = FastAPI(title="Plant Disease AI Service")
model_manager = ModelManager()
@app.on_event("startup")
async def startup_event():
    os.makedirs("app/models", exist_ok=True)
    model_manager.sync_disease_data()
    model_manager.load_production_model("app/models/plant_disease_recog_model_pwp.keras")
    model_manager.load_shadow_model("app/models/shadow_model.h5")
@app.get("/")
async def root():
    return {
        "message": "Plant Disease AI Service is Running",
        "docs": "/docs",
        "health": "/health"
    }
@app.post("/predict")
async def predict(
    file: UploadFile = File(...),
    plant_type: str = Form(None)
):
    temp_path = f"temp_{uuid.uuid4()}.jpg"
    with open(temp_path, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)
    try:
        result = model_manager.dual_predict(temp_path)
        return JSONResponse(content=result)
    finally:
        Path(temp_path).unlink(missing_ok=True)
@app.post("/deploy/shadow")
async def deploy_shadow(file: UploadFile = File(...)):
    shadow_path = "app/models/shadow_model.h5"
    with open(shadow_path, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)
    model_manager.load_shadow_model(shadow_path)
    return {"status": "shadow_model_deployed"}
@app.post("/deploy/promote")
async def promote_shadow():
    shadow_path = Path("app/models/shadow_model.h5")
    production_path = Path("app/models/production_model.h5")
    if shadow_path.exists():
        if production_path.exists():
            production_path.rename("app/models/production_model_backup.h5")
        shutil.copy(shadow_path, production_path)
        model_manager.load_production_model(str(production_path))
        return {"status": "shadow_promoted_to_production"}
    else:
        return JSONResponse(
            status_code=404,
            content={"error": "Shadow model not found"}
        )
@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "production_model_loaded": model_manager.production_model is not None,
        "shadow_model_loaded": model_manager.shadow_model is not None
    }