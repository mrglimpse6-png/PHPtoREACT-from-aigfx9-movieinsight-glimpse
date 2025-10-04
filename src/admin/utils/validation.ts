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
