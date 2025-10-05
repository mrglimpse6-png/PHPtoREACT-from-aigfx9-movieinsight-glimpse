/**
 * Admin API Service Layer
 * Handles all admin-specific API calls with authentication
 */

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/backend';

interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}

class AdminApiError extends Error {
  constructor(
    message: string,
    public statusCode?: number,
    public response?: any
  ) {
    super(message);
    this.name = 'AdminApiError';
  }
}

function getAuthToken(): string {
  return localStorage.getItem('admin_token') || '';
}

async function request<T = any>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const token = getAuthToken();

  const config: RequestInit = {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      ...options.headers,
    },
  };

  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, config);

    if (response.status === 401) {
      localStorage.removeItem('admin_token');
      window.location.href = '/admin/login';
      throw new AdminApiError('Unauthorized', 401);
    }

    const data = await response.json();

    if (!response.ok) {
      throw new AdminApiError(
        data.error || data.message || 'Request failed',
        response.status,
        data
      );
    }

    return data;
  } catch (error) {
    if (error instanceof AdminApiError) {
      throw error;
    }
    throw new AdminApiError(
      error instanceof Error ? error.message : 'Network error',
      0
    );
  }
}

export interface Blog {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  content: string;
  category: string;
  author_id: number;
  author?: {
    id: number;
    name: string;
    email: string;
    avatar?: string;
  };
  featured_image: string;
  tags: string[];
  featured: boolean;
  published: boolean;
  status: 'draft' | 'published' | 'archived';
  views: number;
  likes: number;
  created_at: string;
  updated_at: string;
  published_at?: string;
}

export interface BlogFormData {
  title: string;
  excerpt: string;
  content: string;
  category: string;
  featured_image: string;
  tags: string[];
  featured: boolean;
  published: boolean;
  status: 'draft' | 'published' | 'archived';
}

export interface DashboardStats {
  total_users: number;
  total_blogs: number;
  total_contacts: number;
  total_tokens: number;
  new_users_month: number;
  popular_blogs: Array<{ id: number; title: string; views: number }>;
}

export interface ActivityItem {
  id: number;
  type: string;
  title: string;
  description: string;
  timestamp: string;
  user?: string;
}

export interface Service {
  id: number;
  title: string;
  slug: string;
  description: string;
  price: number;
  icon: string;
  features: string[];
  gallery: string[];
  published: boolean;
  display_order: number;
  created_at: string;
  updated_at: string;
}

export interface ServiceFormData {
  title: string;
  description: string;
  price: number;
  icon: string;
  features: string[];
  gallery: string[];
  published: boolean;
  display_order: number;
}

export interface Portfolio {
  id: number;
  title: string;
  slug: string;
  description: string;
  client_name: string;
  project_type: string;
  images: string[];
  featured: boolean;
  category: string;
  created_at: string;
  updated_at: string;
}

export interface PortfolioFormData {
  title: string;
  description: string;
  client_name: string;
  project_type: string;
  images: string[];
  featured: boolean;
  category: string;
}

export interface Testimonial {
  id: number;
  client_name: string;
  client_company: string;
  rating: 1 | 2 | 3 | 4 | 5;
  content: string;
  avatar: string;
  published: boolean;
  display_order: number;
  created_at: string;
  updated_at: string;
}

export interface TestimonialFormData {
  client_name: string;
  client_company: string;
  rating: 1 | 2 | 3 | 4 | 5;
  content: string;
  avatar: string;
  published: boolean;
  display_order: number;
}

export const adminApi = {
  auth: {
    login: async (email: string, password: string) => {
      return request<ApiResponse<{ token: string; user: any }>>('/api/auth.php/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
      });
    },

    verify: async () => {
      return request<ApiResponse<{ user: any }>>('/api/auth.php/verify');
    },

    logout: () => {
      localStorage.removeItem('admin_token');
      window.location.href = '/admin/login';
    },
  },

  stats: {
    getDashboard: async () => {
      return request<ApiResponse<DashboardStats>>('/api/admin/stats.php');
    },
  },

  activity: {
    getRecent: async (limit: number = 10) => {
      return request<ApiResponse<{ activities: ActivityItem[] }>>(
        `/api/admin/activity.php?limit=${limit}`
      );
    },
  },

  blogs: {
    getAll: async () => {
      return request<ApiResponse<{ blogs: Blog[] }>>('/api/admin/blogs.php');
    },

    getById: async (id: number) => {
      return request<ApiResponse<{ blog: Blog }>>(`/api/blogs.php/${id}`);
    },

    create: async (data: BlogFormData) => {
      return request<ApiResponse<{ id: number }>>('/api/admin/blogs.php', {
        method: 'POST',
        body: JSON.stringify(data),
      });
    },

    update: async (id: number, data: BlogFormData) => {
      return request<ApiResponse>(`/api/admin/blogs.php/${id}`, {
        method: 'PUT',
        body: JSON.stringify(data),
      });
    },

    delete: async (id: number) => {
      return request<ApiResponse>(`/api/admin/blogs.php/${id}`, {
        method: 'DELETE',
      });
    },

    uploadImage: async (file: File) => {
      const token = getAuthToken();
      const formData = new FormData();
      formData.append('file', file);

      const response = await fetch(`${API_BASE_URL}/api/uploads.php`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
        body: formData,
      });

      if (!response.ok) {
        const error = await response.json();
        throw new AdminApiError(error.error || 'Upload failed', response.status);
      }

      return response.json();
    },
  },

  services: {
    getAll: async () => {
      return request<ApiResponse<{ services: Service[] }>>('/api/services.php');
    },

    getById: async (id: number) => {
      return request<ApiResponse<{ service: Service }>>(`/api/services.php/${id}`);
    },

    create: async (data: ServiceFormData) => {
      return request<ApiResponse<{ id: number }>>('/api/services.php', {
        method: 'POST',
        body: JSON.stringify(data),
      });
    },

    update: async (id: number, data: ServiceFormData) => {
      return request<ApiResponse>(`/api/services.php/${id}`, {
        method: 'PUT',
        body: JSON.stringify(data),
      });
    },

    delete: async (id: number) => {
      return request<ApiResponse>(`/api/services.php/${id}`, {
        method: 'DELETE',
      });
    },

    updateOrder: async (services: Array<{ id: number; display_order: number }>) => {
      return request<ApiResponse>('/api/services.php/reorder', {
        method: 'PUT',
        body: JSON.stringify({ services }),
      });
    },
  },

  portfolio: {
    getAll: async () => {
      return request<ApiResponse<{ portfolio: Portfolio[] }>>('/api/portfolio.php');
    },

    getById: async (id: number) => {
      return request<ApiResponse<{ project: Portfolio }>>(`/api/portfolio.php/${id}`);
    },

    create: async (data: PortfolioFormData) => {
      return request<ApiResponse<{ id: number }>>('/api/portfolio.php', {
        method: 'POST',
        body: JSON.stringify(data),
      });
    },

    update: async (id: number, data: PortfolioFormData) => {
      return request<ApiResponse>(`/api/portfolio.php/${id}`, {
        method: 'PUT',
        body: JSON.stringify(data),
      });
    },

    delete: async (id: number) => {
      return request<ApiResponse>(`/api/portfolio.php/${id}`, {
        method: 'DELETE',
      });
    },

    uploadImages: async (files: File[]) => {
      const token = getAuthToken();
      const formData = new FormData();
      files.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
      });

      const response = await fetch(`${API_BASE_URL}/api/uploads.php`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
        body: formData,
      });

      if (!response.ok) {
        const error = await response.json();
        throw new AdminApiError(error.error || 'Upload failed', response.status);
      }

      return response.json();
    },
  },

  testimonials: {
    getAll: async () => {
      return request<ApiResponse<{ testimonials: Testimonial[] }>>('/api/testimonials.php');
    },

    getById: async (id: number) => {
      return request<ApiResponse<{ testimonial: Testimonial }>>(`/api/testimonials.php/${id}`);
    },

    create: async (data: TestimonialFormData) => {
      return request<ApiResponse<{ id: number }>>('/api/testimonials.php', {
        method: 'POST',
        body: JSON.stringify(data),
      });
    },

    update: async (id: number, data: TestimonialFormData) => {
      return request<ApiResponse>(`/api/testimonials.php/${id}`, {
        method: 'PUT',
        body: JSON.stringify(data),
      });
    },

    delete: async (id: number) => {
      return request<ApiResponse>(`/api/testimonials.php/${id}`, {
        method: 'DELETE',
      });
    },

    updateOrder: async (testimonials: Array<{ id: number; display_order: number }>) => {
      return request<ApiResponse>('/api/testimonials.php/reorder', {
        method: 'PUT',
        body: JSON.stringify({ testimonials }),
      });
    },
  },
};

export { AdminApiError };
