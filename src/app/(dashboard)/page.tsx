'use client';
import { useState } from 'react';
import { DndProvider } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';
import DashboardLayout from '@/components/DashboardLayout';
import MediaUploader from '@/components/MediaUploader';
import ContentBlock from '@/components/ContentBlock';

export default function Dashboard() {
  const [blocks, setBlocks] = useState([
    { id: 1, type: 'text', content: 'Welcome to your CMS!' },
    { id: 2, type: 'image', content: '/placeholder.jpg' }
  ]);

  const moveBlock = (fromIndex: number, toIndex: number) => {
    const newBlocks = [...blocks];
    const [moved] = newBlocks.splice(fromIndex, 1);
    newBlocks.splice(toIndex, 1, moved);
    setBlocks(newBlocks);
  };

  return (
    <DashboardLayout>
      <DndProvider backend={HTML5Backend}>
        <div className="grid grid-cols-4 gap-6 p-6">
          {/* Content Area */}
          <div className="col-span-3">
            <div className="bg-white rounded-xl p-6 shadow-sm">
              <h2 className="text-2xl font-bold mb-6">Website Content</h2>

              <div className="space-y-4">
                {blocks.map((block, index) => (
                  <ContentBlock
                    key={block.id}
                    block={block}
                    index={index}
                    moveBlock={moveBlock}
                  />
                ))}
              </div>
            </div>
          </div>

          {/* Sidebar */}
          <div className="col-span-1 space-y-6">
            <MediaUploader />
            <div className="bg-white rounded-xl p-6 shadow-sm">
              <h3 className="font-semibold mb-4">SEO Settings</h3>
              <input
                type="text"
                placeholder="Meta Title"
                className="w-full p-2 border rounded mb-2"
              />
              <textarea
                placeholder="Meta Description"
                className="w-full p-2 border rounded h-24"
              />
            </div>
          </div>
        </div>
      </DndProvider>
    </DashboardLayout>
  );
}
