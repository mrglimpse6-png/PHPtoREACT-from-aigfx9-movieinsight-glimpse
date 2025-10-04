/**
 * BlogModal Component
 * Modal wrapper for BlogForm
 */

import { Blog } from '../../utils/api';
import { BlogForm } from './BlogForm';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

interface BlogModalProps {
  open: boolean;
  onClose: () => void;
  blog: Blog | null;
}

export function BlogModal({ open, onClose, blog }: BlogModalProps) {
  return (
    <Dialog open={open} onOpenChange={onClose}>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>
            {blog ? 'Edit Blog Post' : 'Create New Blog Post'}
          </DialogTitle>
        </DialogHeader>
        <BlogForm blog={blog} onSuccess={onClose} onCancel={onClose} />
      </DialogContent>
    </Dialog>
  );
}
