import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { adminApi, ServiceFormData } from '../utils/api';

export function useServices() {
  return useQuery({
    queryKey: ['admin', 'services'],
    queryFn: async () => {
      const response = await adminApi.services.getAll();
      return response.data?.services || [];
    },
    staleTime: 30000,
  });
}

export function useService(id: number | null) {
  return useQuery({
    queryKey: ['admin', 'services', id],
    queryFn: async () => {
      if (!id) return null;
      const response = await adminApi.services.getById(id);
      return response.data?.service || null;
    },
    enabled: !!id,
  });
}

export function useCreateService() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (data: ServiceFormData) => {
      return await adminApi.services.create(data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'services'] });
    },
  });
}

export function useUpdateService() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: ServiceFormData }) => {
      return await adminApi.services.update(id, data);
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'services'] });
      queryClient.invalidateQueries({ queryKey: ['admin', 'services', variables.id] });
    },
  });
}

export function useDeleteService() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      return await adminApi.services.delete(id);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'services'] });
    },
  });
}

export function useUpdateServiceOrder() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (services: Array<{ id: number; display_order: number }>) => {
      return await adminApi.services.updateOrder(services);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'services'] });
    },
  });
}
