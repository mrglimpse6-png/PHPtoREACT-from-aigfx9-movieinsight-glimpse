/**
 * React Query hooks for Blog Management
 */

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { adminApi, Blog, BlogFormData } from '../utils/api';

export function useBlogs() {
  return useQuery({
    queryKey: ['admin', 'blogs'],
    queryFn: async () => {
      const response = await adminApi.blogs.getAll();
      return response.data?.blogs || [];
    },
    staleTime: 30000,
  });
}

export function useBlog(id: number | null) {
  return useQuery({
    queryKey: ['admin', 'blogs', id],
    queryFn: async () => {
      if (!id) return null;
      const response = await adminApi.blogs.getById(id);
      return response.data?.blog || null;
    },
    enabled: !!id,
  });
}

export function useCreateBlog() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: BlogFormData) => {
      return await adminApi.blogs.create(data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'blogs'] });
      queryClient.invalidateQueries({ queryKey: ['admin', 'stats'] });
    },
  });
}

export function useUpdateBlog() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: BlogFormData }) => {
      return await adminApi.blogs.update(id, data);
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'blogs'] });
      queryClient.invalidateQueries({ queryKey: ['admin', 'blogs', variables.id] });
      queryClient.invalidateQueries({ queryKey: ['admin', 'stats'] });
    },
  });
}

export function useDeleteBlog() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number) => {
      return await adminApi.blogs.delete(id);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'blogs'] });
      queryClient.invalidateQueries({ queryKey: ['admin', 'stats'] });
    },
  });
}

export function useUploadImage() {
  return useMutation({
    mutationFn: async (file: File) => {
      return await adminApi.blogs.uploadImage(file);
    },
  });
}
