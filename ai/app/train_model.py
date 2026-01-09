import tensorflow as tf
from tensorflow.keras.applications import EfficientNetB0
from tensorflow.keras import layers, models
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.callbacks import ModelCheckpoint, EarlyStopping, ReduceLROnPlateau
import argparse
import os
from pathlib import Path
def create_model(num_classes, input_shape=(224, 224, 3)):
    base_model = EfficientNetB0(
        include_top=False,
        weights='imagenet',
        input_shape=input_shape
    )
    base_model.trainable = False
    model = models.Sequential([
        base_model,
        layers.GlobalAveragePooling2D(),
        layers.BatchNormalization(),
        layers.Dropout(0.3),
        layers.Dense(256, activation='relu'),
        layers.BatchNormalization(),
        layers.Dropout(0.3),
        layers.Dense(num_classes, activation='softmax')
    ])
    return model, base_model
def get_data_generators(train_dir, val_dir, batch_size=32, img_size=(224, 224)):
    train_datagen = ImageDataGenerator(
        rescale=1./255,
        rotation_range=40,
        width_shift_range=0.2,
        height_shift_range=0.2,
        shear_range=0.2,
        zoom_range=0.2,
        horizontal_flip=True,
        fill_mode='nearest'
    )
    val_datagen = ImageDataGenerator(rescale=1./255)
    train_generator = train_datagen.flow_from_directory(
        train_dir,
        target_size=img_size,
        batch_size=batch_size,
        class_mode='categorical'
    )
    val_generator = val_datagen.flow_from_directory(
        val_dir,
        target_size=img_size,
        batch_size=batch_size,
        class_mode='categorical'
    )
    return train_generator, val_generator
def train_model(
    train_dir,
    val_dir,
    epochs=50,
    batch_size=32,
    learning_rate=0.001,
    output_path="app/models/trained_model.h5"
):
    print("🚀 Starting model training...")
    print("📁 Loading dataset...")
    train_gen, val_gen = get_data_generators(train_dir, val_dir, batch_size)
    num_classes = len(train_gen.class_indices)
    print(f"📊 Found {num_classes} classes: {list(train_gen.class_indices.keys())}")
    print("🏗️ Building model...")
    model, base_model = create_model(num_classes)
    model.compile(
        optimizer=tf.keras.optimizers.Adam(learning_rate=learning_rate),
        loss='categorical_crossentropy',
        metrics=['accuracy', tf.keras.metrics.TopKCategoricalAccuracy(k=3, name='top_3_accuracy')]
    )
    callbacks = [
        ModelCheckpoint(
            output_path,
            monitor='val_accuracy',
            save_best_only=True,
            verbose=1
        ),
        EarlyStopping(
            monitor='val_loss',
            patience=10,
            restore_best_weights=True,
            verbose=1
        ),
        ReduceLROnPlateau(
            monitor='val_loss',
            factor=0.5,
            patience=5,
            min_lr=1e-7,
            verbose=1
        )
    ]
    print("\\n🏋️ Phase 1: Training classification head...")
    history1 = model.fit(
        train_gen,
        validation_data=val_gen,
        epochs=epochs // 2,
        callbacks=callbacks,
        verbose=1
    )
    print("\\n🔧 Phase 2: Fine-tuning entire model...")
    base_model.trainable = True
    model.compile(
        optimizer=tf.keras.optimizers.Adam(learning_rate=learning_rate / 10),
        loss='categorical_crossentropy',
        metrics=['accuracy', tf.keras.metrics.TopKCategoricalAccuracy(k=3, name='top_3_accuracy')]
    )
    history2 = model.fit(
        train_gen,
        validation_data=val_gen,
        epochs=epochs // 2,
        callbacks=callbacks,
        verbose=1
    )
    model.save(output_path)
    print(f"\\n✅ Model saved to: {output_path}")
    class_labels_path = Path(output_path).parent / "class_labels.txt"
    with open(class_labels_path, "w") as f:
        for label in train_gen.class_indices.keys():
            f.write(label + "\\n")
    print(f"✅ Class labels saved to: {class_labels_path}")
    print("\\n📊 Final evaluation:")
    results = model.evaluate(val_gen)
    print(f"Validation Loss: {results[0]:.4f}")
    print(f"Validation Accuracy: {results[1]:.4f}")
    print(f"Top-3 Accuracy: {results[2]:.4f}")
    return model, history1, history2
def main():
    parser = argparse.ArgumentParser(description='Train plant disease detection model')
    parser.add_argument('--train-dir', type=str, default='dataset/train',
                        help='Path to training data directory')
    parser.add_argument('--val-dir', type=str, default='dataset/val',
                        help='Path to validation data directory')
    parser.add_argument('--epochs', type=int, default=50,
                        help='Number of training epochs')
    parser.add_argument('--batch-size', type=int, default=32,
                        help='Batch size for training')
    parser.add_argument('--learning-rate', type=float, default=0.001,
                        help='Initial learning rate')
    parser.add_argument('--output', type=str, default='app/models/production_model.h5',
                        help='Output path for trained model')
    args = parser.parse_args()
    if not os.path.exists(args.train_dir):
        print(f"❌ Training directory not found: {args.train_dir}")
        print("\\n📁 Expected directory structure:")
        print("dataset/")
        print("├── train/")
        print("│   ├── Class1/")
        print("│   ├── Class2/")
        print("│   └── ...")
        print("└── val/")
        print("    ├── Class1/")
        print("    ├── Class2/")
        print("    └── ...")
        return
    os.makedirs(os.path.dirname(args.output), exist_ok=True)
    train_model(
        args.train_dir,
        args.val_dir,
        epochs=args.epochs,
        batch_size=args.batch_size,
        learning_rate=args.learning_rate,
        output_path=args.output
    )
if __name__ == "__main__":
    main()