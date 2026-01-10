'use client';
import { useState } from 'react';

export default function MediaUploader() {
  const [isDragging, setIsDragging] = useState(false);

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    const files = Array.from(e.dataTransfer.files);
    // Handle file upload logic here
  };

  return (
    <div
      onDragOver={(e) => e.preventDefault()}
      onDragEnter={() => setIsDragging(true)}
      onDragLeave={() => setIsDragging(false)}
      onDrop={handleDrop}
      className={`border-2 rounded-xl p-4 text-center transition-colors ${
        isDragging ? 'border-blue-500 bg-blue-50' : 'border-dashed'
      }`}
    >
      <div className="text-gray-500">
        <p className="font-medium">Drag & drop media here</p>
        <p className="text-sm">or</p>
        <button className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
          Browse Files
        </button>
      </div>
    </div>
  );
}
