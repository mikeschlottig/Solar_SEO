'use client';
import { useState } from 'react';
import ContentBlock from './ContentBlock';

// Define Block type
interface Block {
  id: number;
  type: 'text' | 'image';
  content: string;
}

export default function BlockEditor({ initialBlocks = [] }: { initialBlocks?: Block[] }) {
  const [blocks, setBlocks] = useState<Block[]>(initialBlocks.length > 0 ? initialBlocks : [
    { id: Date.now(), type: 'text', content: 'Welcome to your CMS!' }
  ]);

  const moveBlock = (fromIndex: number, toIndex: number) => {
    const newBlocks = [...blocks];
    const [moved] = newBlocks.splice(fromIndex, 1);
    newBlocks.splice(toIndex, 0, moved);
    setBlocks(newBlocks);
  };

  const addBlock = (type: 'text' | 'image') => {
    const newBlock: Block = {
      id: Date.now(),
      type,
      content: type === 'text' ? '' : 'https://via.placeholder.com/400x300.png?text=Placeholder' // Using external placeholder
    };
    setBlocks([...blocks, newBlock]);
  };

  const updateBlockContent = (blockId: number, content: string) => {
    setBlocks(blocks.map(block =>
      block.id === blockId ? { ...block, content } : block
    ));
  };

  return (
    <div className="bg-white rounded-xl p-6 shadow-sm">
      <h2 className="text-2xl font-bold mb-6">Website Content</h2>

      <div className="space-y-4">
        {blocks.map((block, index) => (
          <ContentBlock
            key={block.id}
            block={block}
            index={index}
            moveBlock={moveBlock}
            onEdit={updateBlockContent}
          />
        ))}
      </div>

      <div className="mt-6 flex gap-2">
        <button
          onClick={() => addBlock('text')}
          className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
          Add Text Block
        </button>
        <button
          onClick={() => addBlock('image')}
          className="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
        >
          Add Image Block
        </button>
      </div>
    </div>
  );
}
