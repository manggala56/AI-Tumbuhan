import tensorflow as tf
from tensorflow.keras.preprocessing import image
import numpy as np
from pathlib import Path
import json
import os
import requests
class ModelManager:
    def __init__(self):
        self.production_model = None
        self.shadow_model = None
        self.class_labels = []
        self.plant_disease_data = []
        self.load_class_labels()
        self.load_plant_disease_data()
    def sync_disease_data(self, api_url="http://localhost:8000/api/v1/diseases"):
        try:
            response = requests.get(api_url, timeout=5)
            if response.status_code == 200:
                data = response.json()
                if isinstance(data, list) and len(data) > 0:
                    json_path = Path("app/models/plant_disease.json")
                    with open(json_path, 'w') as f:
                        json.dump(data, f, indent=4)
                    print(f"✓ Synced {len(data)} disease definitions from Backend.")
                    self.plant_disease_data = data
                else:
                    print("⚠ Backend returned empty or invalid disease data.")
            else:
                 print(f"⚠ Failed to sync disease data. Status: {response.status_code}")
        except Exception as e:
            print(f"⚠ Could not sync disease data from Backend: {e}. Using local cache.")
    def load_class_labels(self):
        labels_path = Path("app/models/class_labels.txt")
        if labels_path.exists():
            with open(labels_path, 'r') as f:
                self.class_labels = [line.strip() for line in f.readlines()]
            print(f"✓ Loaded {len(self.class_labels)} class labels")
        else:
            print(f"⚠ Class labels file not found at {labels_path}")
    def load_plant_disease_data(self):
        json_path = Path("app/models/plant_disease.json")
        if json_path.exists():
            try:
                with open(json_path, 'r') as f:
                    self.plant_disease_data = json.load(f)
                print(f"✓ Loaded detailed disease data for {len(self.plant_disease_data)} classes")
            except Exception as e:
                 print(f"⚠ Failed to load plant disease JSON: {e}")
        else:
             print(f"⚠ Plant disease JSON not found at {json_path}")
    def load_production_model(self, model_path: str):
        if Path(model_path).exists():
            try:
                self.production_model = tf.keras.models.load_model(model_path)
                print(f"✓ Production model loaded from {model_path}")
            except Exception as e:
                print(f"⚠ Failed to load production model: {e}")
        else:
            print(f"⚠ Production model not found at {model_path}")
    def load_shadow_model(self, model_path: str):
        if Path(model_path).exists():
            try:
                self.shadow_model = tf.keras.models.load_model(model_path)
                print(f"✓ Shadow model loaded from {model_path}")
            except Exception as e:
                print(f"⚠ Failed to load shadow model: {e}")
        else:
            print(f"⚠ Shadow model not found at {model_path}")
    def preprocess_image(self, img_path: str):
        img = image.load_img(img_path, target_size=(160, 160))
        img_array = image.img_to_array(img)
        img_array = np.expand_dims(img_array, axis=0)
        return img_array
    def predict(self, img_array, model):
        predictions = model.predict(img_array)
        class_idx = np.argmax(predictions[0])
        confidence = float(predictions[0][class_idx])
        label = self.class_labels[class_idx] if (self.class_labels and class_idx < len(self.class_labels)) else f"class_{class_idx}"
        details = {}
        if self.plant_disease_data and class_idx < len(self.plant_disease_data):
            details = self.plant_disease_data[class_idx] 
            if 'name' in details:
                label = details['name']
        return label, confidence, details
    def dual_predict(self, img_path: str):
        try:
            img_array = self.preprocess_image(img_path)
        except Exception as e:
            return {'error': str(e)}
        result = {}
        if self.production_model:
            label, confidence, details = self.predict(img_array, self.production_model)
            treatment_advice = "No specific advice available."
            if details:
                cause = details.get('cause', 'Unknown cause.')
                cure = details.get('cure', 'No cure info.')
                treatment_advice = f"**Cause:** {cause}\n\n**Cure:** {cure}"
            result['main_prediction'] = {
                'label': label,
                'confidence': confidence,
                'model_version': 'v1.0-prod',
                'treatment_advice': treatment_advice
            }
        else:
            result['main_prediction'] = {
                'label': 'Model Not Loaded',
                'confidence': 0.0,
                'model_version': 'none',
                'treatment_advice': 'System error: Model not loaded.'
            }
        if self.shadow_model:
            label, confidence, _ = self.predict(img_array, self.shadow_model)
            result['shadow_prediction'] = {
                'label': label,
                'confidence': confidence,
                'model_version': 'v2.0-candidate'
            }
        else:
            result['shadow_prediction'] = None
        return result