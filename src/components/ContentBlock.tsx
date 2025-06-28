'use client';
import { useDrag } from 'react-dnd';
import { useRef } from 'react';
import Image from 'next/image';

export default function ContentBlock({ block, index, moveBlock, onEdit }) {
  const ref = useRef(null);

  const [{ isDragging }, drag] = useDrag({
    type: 'BLOCK',
    item: { index },
    collect: (monitor) => ({
      isDragging: !!monitor.isDragging(),
    }),
  });

  const handleContentChange = (e) => {
    onEdit(block.id, e.target.value);
  };

  return (
    <div
      ref={drag}
      className={`p-4 rounded-lg border-2 transition-all mb-4 ${
        isDragging ? 'opacity-50 border-blue-500' : 'border-dashed border-gray-300'
      }`}
    >
      {block.type === 'text' ? (
        <textarea
          defaultValue={block.content}
          onChange={handleContentChange}
          className="w-full min-h-[100px] p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Enter your text here..."
        />
      ) : block.type === 'image' ? (
        <div className="aspect-video bg-gray-100 rounded flex items-center justify-center overflow-hidden">
          <Image
            src={block.content || 'https://via.placeholder.com/400x300.png?text=Placeholder'}
            alt="Content image"
            width={400}
            height={300}
            className="object-cover w-full h-full"
          />
        </div>
      ) : null}

      <div className="mt-2 flex justify-end">
        <button
          onClick={() => onEdit(block.id, '')}
          className="text-sm text-red-500 hover:text-red-700"
        >
          Clear
        </button>
      </div>
    </div>
  );
}
