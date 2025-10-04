/**
 * BlogForm Component
 * Create/Edit blog post form with validation
 */

import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Loader2, Upload, X } from 'lucide-react';
import { Blog } from '../../utils/api';
import { blogSchema, BlogFormValues } from '../../utils/validation';
import { useCreateBlog, useUpdateBlog, useUploadImage } from '../../hooks/useBlogs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { toast } from 'sonner';

interface BlogFormProps {
  blog: Blog | null;
  onSuccess: () => void;
  onCancel: () => void;
}

const CATEGORIES = [
  'Design',
  'Development',
  'Marketing',
  'Business',
  'Tutorial',
  'Case Study',
  'News',
  'Tips & Tricks',
];

export function BlogForm({ blog, onSuccess, onCancel }: BlogFormProps) {
  const [tagInput, setTagInput] = useState('');
  const [imagePreview, setImagePreview] = useState<string>(blog?.featured_image || '');
  const [isUploading, setIsUploading] = useState(false);

  const createBlog = useCreateBlog();
  const updateBlog = useUpdateBlog();
  const uploadImage = useUploadImage();

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
    setValue,
    watch,
    reset,
  } = useForm<BlogFormValues>({
    resolver: zodResolver(blogSchema),
    defaultValues: blog
      ? {
          title: blog.title,
          excerpt: blog.excerpt,
          content: blog.content,
          category: blog.category,
          featured_image: blog.featured_image,
          tags: blog.tags,
          featured: blog.featured,
          published: blog.published,
          status: blog.status,
        }
      : {
          title: '',
          excerpt: '',
          content: '',
          category: '',
          featured_image: '',
          tags: [],
          featured: false,
          published: false,
          status: 'draft',
        },
  });

  const tags = watch('tags') || [];
  const featured = watch('featured');
  const published = watch('published');
  const status = watch('status');

  useEffect(() => {
    if (blog) {
      reset({
        title: blog.title,
        excerpt: blog.excerpt,
        content: blog.content,
        category: blog.category,
        featured_image: blog.featured_image,
        tags: blog.tags,
        featured: blog.featured,
        published: blog.published,
        status: blog.status,
      });
      setImagePreview(blog.featured_image);
    }
  }, [blog, reset]);

  const handleAddTag = () => {
    if (tagInput.trim() && !tags.includes(tagInput.trim())) {
      setValue('tags', [...tags, tagInput.trim()]);
      setTagInput('');
    }
  };

  const handleRemoveTag = (tagToRemove: string) => {
    setValue(
      'tags',
      tags.filter((tag) => tag !== tagToRemove)
    );
  };

  const handleImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      toast.error('Please select an image file');
      return;
    }

    if (file.size > 10 * 1024 * 1024) {
      toast.error('Image must be less than 10MB');
      return;
    }

    setIsUploading(true);
    try {
      const result = await uploadImage.mutateAsync(file);
      if (result.success && result.file) {
        const imageUrl = result.file.url;
        setValue('featured_image', imageUrl);
        setImagePreview(imageUrl);
        toast.success('Image uploaded successfully');
      }
    } catch (error) {
      toast.error('Failed to upload image');
    } finally {
      setIsUploading(false);
    }
  };

  const onSubmit = async (data: BlogFormValues) => {
    try {
      if (blog) {
        await updateBlog.mutateAsync({ id: blog.id, data });
        toast.success('Blog updated successfully');
      } else {
        await createBlog.mutateAsync(data);
        toast.success('Blog created successfully');
      }
      onSuccess();
    } catch (error: any) {
      toast.error(error.message || 'Failed to save blog');
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
      <div className="space-y-2">
        <Label htmlFor="title">Title *</Label>
        <Input
          id="title"
          {...register('title')}
          placeholder="Enter blog title"
        />
        {errors.title && (
          <p className="text-sm text-red-500">{errors.title.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label htmlFor="category">Category *</Label>
        <Select
          value={watch('category')}
          onValueChange={(value) => setValue('category', value)}
        >
          <SelectTrigger>
            <SelectValue placeholder="Select a category" />
          </SelectTrigger>
          <SelectContent>
            {CATEGORIES.map((cat) => (
              <SelectItem key={cat} value={cat}>
                {cat}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
        {errors.category && (
          <p className="text-sm text-red-500">{errors.category.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label htmlFor="excerpt">Excerpt *</Label>
        <Textarea
          id="excerpt"
          {...register('excerpt')}
          placeholder="Brief description of the blog post"
          rows={3}
        />
        {errors.excerpt && (
          <p className="text-sm text-red-500">{errors.excerpt.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label htmlFor="content">Content *</Label>
        <Textarea
          id="content"
          {...register('content')}
          placeholder="Write your blog content here..."
          rows={12}
        />
        {errors.content && (
          <p className="text-sm text-red-500">{errors.content.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label>Featured Image</Label>
        <div className="flex items-center gap-4">
          <div className="flex-1">
            <Input
              type="file"
              accept="image/*"
              onChange={handleImageUpload}
              disabled={isUploading}
            />
          </div>
          {isUploading && <Loader2 className="h-5 w-5 animate-spin" />}
        </div>
        {imagePreview && (
          <div className="mt-2">
            <img
              src={imagePreview}
              alt="Preview"
              className="w-full h-48 object-cover rounded-lg"
            />
          </div>
        )}
        {errors.featured_image && (
          <p className="text-sm text-red-500">{errors.featured_image.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label>Tags *</Label>
        <div className="flex gap-2">
          <Input
            value={tagInput}
            onChange={(e) => setTagInput(e.target.value)}
            onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), handleAddTag())}
            placeholder="Add tags (press Enter)"
          />
          <Button type="button" onClick={handleAddTag}>
            Add
          </Button>
        </div>
        <div className="flex flex-wrap gap-2 mt-2">
          {tags.map((tag) => (
            <div
              key={tag}
              className="bg-gray-100 px-3 py-1 rounded-full flex items-center gap-2"
            >
              <span className="text-sm">{tag}</span>
              <button
                type="button"
                onClick={() => handleRemoveTag(tag)}
                className="text-gray-500 hover:text-gray-700"
              >
                <X className="h-3 w-3" />
              </button>
            </div>
          ))}
        </div>
        {errors.tags && (
          <p className="text-sm text-red-500">{errors.tags.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <Label htmlFor="status">Status</Label>
        <Select
          value={status}
          onValueChange={(value) => setValue('status', value as 'draft' | 'published' | 'archived')}
        >
          <SelectTrigger>
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="draft">Draft</SelectItem>
            <SelectItem value="published">Published</SelectItem>
            <SelectItem value="archived">Archived</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div className="flex items-center gap-2">
          <Switch
            checked={featured}
            onCheckedChange={(checked) => setValue('featured', checked)}
          />
          <Label>Featured Post</Label>
        </div>
        <div className="flex items-center gap-2">
          <Switch
            checked={published}
            onCheckedChange={(checked) => setValue('published', checked)}
          />
          <Label>Published</Label>
        </div>
      </div>

      <div className="flex justify-end gap-3 pt-4 border-t">
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button type="submit" disabled={isSubmitting}>
          {isSubmitting && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          {blog ? 'Update Blog' : 'Create Blog'}
        </Button>
      </div>
    </form>
  );
}
