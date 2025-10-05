import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { adminApi, PortfolioFormData } from '../utils/api';

export function usePortfolio() {
  return useQuery({
    queryKey: ['admin', 'portfolio'],
    queryFn: async () => {
      const response = await adminApi.portfolio.getAll();
      return response.data?.portfolio || [];
    },
    staleTime: 30000,
  });
}

export function usePortfolioItem(id: number | null) {
  return useQuery({
    queryKey: ['admin', 'portfolio', id],
    queryFn: async () => {
      if (!id) return null;
      const response = await adminApi.portfolio.getById(id);
      return response.data?.project || null;
    },
    enabled: !!id,
  });
}

export function useCreatePortfolioItem() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (data: PortfolioFormData) => {
      return await adminApi.portfolio.create(data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'portfolio'] });
    },
  });
}

export function useUpdatePortfolioItem() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: PortfolioFormData }) => {
      return await adminApi.portfolio.update(id, data);
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'portfolio'] });
      queryClient.invalidateQueries({ queryKey: ['admin', 'portfolio', variables.id] });
    },
  });
}

export function useDeletePortfolioItem() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      return await adminApi.portfolio.delete(id);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'portfolio'] });
    },
  });
}

export function useUploadPortfolioImages() {
  return useMutation({
    mutationFn: async (files: File[]) => {
      return await adminApi.portfolio.uploadImages(files);
    },
  });
}
