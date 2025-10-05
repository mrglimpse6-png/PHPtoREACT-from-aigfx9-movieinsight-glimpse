/**
 * Validation schemas for admin forms using Zod
 */

import { z } from 'zod';

export const blogSchema = z.object({
  title: z
    .string()
    .min(3, 'Title must be at least 3 characters')
    .max(200, 'Title must be less than 200 characters'),

  excerpt: z
    .string()
    .min(10, 'Excerpt must be at least 10 characters')
    .max(500, 'Excerpt must be less than 500 characters'),

  content: z
    .string()
    .min(50, 'Content must be at least 50 characters'),

  category: z
    .string()
    .min(1, 'Category is required'),

  featured_image: z
    .string()
    .url('Must be a valid URL')
    .or(z.string().length(0)),

  tags: z
    .array(z.string())
    .min(1, 'At least one tag is required')
    .max(10, 'Maximum 10 tags allowed'),

  featured: z
    .boolean()
    .default(false),

  published: z
    .boolean()
    .default(false),

  status: z
    .enum(['draft', 'published', 'archived'])
    .default('draft'),
});

export type BlogFormValues = z.infer<typeof blogSchema>;

export const loginSchema = z.object({
  email: z
    .string()
    .email('Invalid email address'),

  password: z
    .string()
    .min(6, 'Password must be at least 6 characters'),
});

export type LoginFormValues = z.infer<typeof loginSchema>;

export const serviceSchema = z.object({
  title: z
    .string()
    .min(3, 'Title must be at least 3 characters')
    .max(200, 'Title must be less than 200 characters'),

  description: z
    .string()
    .min(10, 'Description must be at least 10 characters'),

  price: z
    .number()
    .min(0, 'Price must be positive')
    .or(z.string().transform(val => parseFloat(val))),

  icon: z
    .string()
    .min(1, 'Icon is required'),

  features: z
    .array(z.string())
    .min(1, 'At least one feature is required')
    .max(20, 'Maximum 20 features allowed'),

  gallery: z
    .array(z.string())
    .default([]),

  published: z
    .boolean()
    .default(true),

  display_order: z
    .number()
    .int()
    .min(0)
    .default(0),
});

export type ServiceFormValues = z.infer<typeof serviceSchema>;

export const portfolioSchema = z.object({
  title: z
    .string()
    .min(3, 'Title must be at least 3 characters')
    .max(200, 'Title must be less than 200 characters'),

  description: z
    .string()
    .min(10, 'Description must be at least 10 characters'),

  client_name: z
    .string()
    .min(2, 'Client name must be at least 2 characters')
    .max(100, 'Client name must be less than 100 characters'),

  project_type: z
    .string()
    .min(2, 'Project type is required'),

  images: z
    .array(z.string())
    .min(1, 'At least one image is required')
    .max(10, 'Maximum 10 images allowed'),

  featured: z
    .boolean()
    .default(false),

  category: z
    .string()
    .min(1, 'Category is required'),
});

export type PortfolioFormValues = z.infer<typeof portfolioSchema>;

export const testimonialSchema = z.object({
  client_name: z
    .string()
    .min(2, 'Client name must be at least 2 characters')
    .max(100, 'Client name must be less than 100 characters'),

  client_company: z
    .string()
    .min(2, 'Company name must be at least 2 characters')
    .max(100, 'Company name must be less than 100 characters'),

  rating: z
    .number()
    .int()
    .min(1, 'Rating must be at least 1')
    .max(5, 'Rating must be at most 5')
    .or(z.string().transform(val => parseInt(val, 10))),

  content: z
    .string()
    .min(10, 'Testimonial must be at least 10 characters')
    .max(1000, 'Testimonial must be less than 1000 characters'),

  avatar: z
    .string()
    .url('Must be a valid URL')
    .or(z.string().length(0)),

  published: z
    .boolean()
    .default(true),

  display_order: z
    .number()
    .int()
    .min(0)
    .default(0),
});

export type TestimonialFormValues = z.infer<typeof testimonialSchema>;
