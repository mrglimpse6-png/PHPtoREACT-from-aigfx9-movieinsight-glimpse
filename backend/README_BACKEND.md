# Adil GFX Backend Documentation

## Overview
Complete PHP backend system with advanced CMS capabilities and admin panel for the Adil GFX design services platform. Built with security, performance, and scalability in mind.

## Features Implemented

### 🔐 Authentication & Security
- JWT-based authentication with secure token handling
- Password hashing using PHP's `password_hash()` with bcrypt
- Role-based access control (User vs Admin)
- CSRF protection on all forms
- Rate limiting to prevent abuse
- SQL injection prevention with prepared statements

### 🎨 Global CMS Control
- **Global Settings Management**: Branding, contact info, social links, feature toggles
- **Dynamic Page Management**: Create, edit, delete, and reorder pages
- **Section-Based Page Builder**: Drag-and-drop section management with predefined types
- **Media Library**: Secure file upload and management system
- **Carousel Management**: Dynamic slider content with reordering capabilities
- **SEO Management**: Per-page meta tags, OG tags, and schema markup
- **Content Scheduling**: Publish/unpublish dates for all content types
- **Version Control**: Draft vs published states with preview capabilities

### 📊 Database Architecture
- **Users**: Authentication, profiles, roles
- **Tokens**: Gamification system with earning/spending history
- **Streaks**: Login streak tracking with milestone rewards
- **Referrals**: Viral growth system with tracking
- **Blogs**: Content management with SEO optimization
- **Portfolio**: Project showcase with results tracking
- **Services**: Dynamic pricing and package management
- **Testimonials**: Client feedback with verification
- **Orders**: Project tracking and status management
- **Notifications**: Real-time user notifications
- **Contact**: Form submissions with auto-replies
- **Settings**: Global site configuration storage
- **Pages**: Dynamic page structure and content
- **Carousel Slides**: Slider content management
- **Media Uploads**: File storage and metadata

### 🚀 Performance Optimization
- File-based caching system for frequently accessed data
- Database indexing for optimal query performance
- Pagination support for large datasets
- Optimized queries with proper JOIN strategies

### 📧 Email Integration
- Contact form auto-replies
- Admin notifications for new submissions
- Newsletter subscription confirmations
- SMTP configuration ready for production

## API Endpoints

### Authentication
```
POST /api/auth.php/register
POST /api/auth.php/login
GET  /api/auth.php/verify
```

### Global Settings
```
GET    /api/settings.php                 # Get all settings
GET    /api/settings.php/category/{cat}  # Get settings by category
GET    /api/settings.php/{key}           # Get single setting
PUT    /api/settings.php/{key}           # Update single setting
PUT    /api/settings.php/bulk            # Bulk update settings
POST   /api/settings.php                 # Create new setting
DELETE /api/settings.php/{key}           # Delete setting
```

### Page Management
```
GET    /api/pages.php                    # Get navigation pages (public) or all pages (admin)
GET    /api/pages.php/{slug}             # Get page by slug
POST   /api/pages.php                    # Create new page (admin)
PUT    /api/pages.php/{id}               # Update page (admin)
DELETE /api/pages.php/{id}               # Delete page (admin)
POST   /api/pages.php/reorder            # Reorder pages (admin)
```

### Carousel Management
```
GET    /api/carousel.php?name={name}     # Get carousel slides
GET    /api/carousel.php                 # Get all carousels (admin)
POST   /api/carousel.php                 # Create new slide (admin)
PUT    /api/carousel.php/{id}            # Update slide (admin)
DELETE /api/carousel.php/{id}            # Delete slide (admin)
POST   /api/carousel.php/reorder         # Reorder slides (admin)
```

### Media Management
```
POST   /api/uploads.php                  # Upload media file
GET    /api/uploads.php                  # Get media library
PUT    /api/uploads.php/{id}             # Update media metadata
DELETE /api/uploads.php/{id}             # Delete media file
```

### Blogs
```
GET    /api/blogs.php                    # Get paginated blogs
GET    /api/blogs.php/{id}               # Get single blog
POST   /api/blogs.php                    # Create blog (admin)
PUT    /api/blogs.php/{id}               # Update blog (admin)
DELETE /api/blogs.php/{id}               # Delete blog (admin)
```

### Portfolio
```
GET    /api/portfolio.php                # Get paginated portfolio
GET    /api/portfolio.php/{id}           # Get single portfolio item
POST   /api/portfolio.php                # Create item (admin)
PUT    /api/portfolio.php/{id}           # Update item (admin)
DELETE /api/portfolio.php/{id}           # Delete item (admin)
```

### Services
```
GET    /api/services.php                 # Get all services
GET    /api/services.php/{id}            # Get single service
POST   /api/services.php                 # Create service (admin)
PUT    /api/services.php/{id}            # Update service (admin)
DELETE /api/services.php/{id}            # Delete service (admin)
```

### Testimonials
```
GET    /api/testimonials.php             # Get all testimonials
GET    /api/testimonials.php/{id}        # Get single testimonial
POST   /api/testimonials.php             # Create testimonial (admin)
PUT    /api/testimonials.php/{id}        # Update testimonial (admin)
DELETE /api/testimonials.php/{id}        # Delete testimonial (admin)
```

### Newsletter
```
POST   /api/newsletter.php/subscribe     # Subscribe to newsletter
```

### User Profile
```
GET    /api/user/profile.php             # Get user dashboard data
PUT    /api/user/profile.php             # Update user profile
```

### Contact & Forms
```
POST   /api/contact.php                  # Submit contact form
POST   /api/newsletter.php/subscribe     # Subscribe to newsletter
```

### Admin Panel
```
GET    /api/admin/stats.php              # Dashboard statistics
GET    /api/admin/activity.php           # Recent activity log
GET    /api/admin/users.php              # User management
POST   /api/admin/notifications.php      # Send notifications
```

## Advanced CMS Features

### Global Settings Categories

**Branding Settings:**
- Site logo and favicon upload
- Primary and secondary color customization
- Typography selection
- Custom CSS injection

**Contact Information:**
- Email, phone, address management
- WhatsApp integration number
- Business hours configuration

**Social Media Links:**
- Facebook, Instagram, LinkedIn, YouTube
- Fiverr profile and other platform links
- Social sharing configuration

**Feature Toggles:**
- Enable/disable referral system
- Toggle login streaks and rewards
- Control popup offers and notifications
- Chatbot activation

**Analytics & Integrations:**
- Google Analytics and Facebook Pixel IDs
- Mailchimp/SendGrid API keys
- Calendly booking URL
- Third-party service configurations

### Page Management System

**Dynamic Page Creation:**
- Create unlimited custom pages
- SEO-optimized with meta tags and schema markup
- Drag-and-drop section ordering
- Navigation menu auto-updates

**Section Types Available:**
- **Hero Section**: Title, subtitle, description, CTA
- **Content Block**: Rich text with WYSIWYG editor
- **Services Overview**: Dynamic service carousel
- **Portfolio Highlights**: Featured project showcase
- **Testimonials**: Client feedback display
- **CTA Section**: Call-to-action with custom styling
- **Custom HTML**: Advanced users can inject custom code

**Content Scheduling:**
- Set publish and unpublish dates
- Draft vs published states
- Preview unpublished changes

### Media Management

**Secure File Uploads:**
- Images: JPG, PNG, GIF, SVG (max 10MB)
- Videos: MP4, WebM (max 50MB)
- Documents: PDF (max 10MB)
- Automatic file optimization and compression

**Media Library Features:**
- Grid view with thumbnails
- Search and filter capabilities
- Alt text and caption management
- Usage tracking across content

## Installation

### 1. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Edit .env with your database credentials
nano .env
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE adilgfx_db;"

# Install schema and sample data
php scripts/install_database.php

# Install CMS extensions
mysql -u root -p adilgfx_db < database/schema_extensions.sql
```

### 4. File Permissions
```bash
# Set proper permissions
chmod 755 backend/
chmod 777 backend/cache/
chmod 777 backend/uploads/
```

### 5. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/$1.php [QSA,L]

# Serve uploaded files
RewriteRule ^uploads/(.*)$ uploads/$1 [L]
```

#### Nginx
```nginx
location /api/ {
    try_files $uri $uri.php $uri/ =404;
    fastcgi_pass php-fpm;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

location /uploads/ {
    try_files $uri =404;
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## Frontend Integration

### 1. Update React Environment
```env
# .env (React project)
VITE_API_BASE_URL=https://your-domain.com/backend
VITE_USE_MOCK_DATA=false
```

### 2. API Calls Work Automatically
The existing `src/utils/api.ts` will automatically switch to live API calls when `VITE_USE_MOCK_DATA=false`.

### 3. Dynamic Content Integration

**Global Settings Usage:**
```typescript
import { useGlobalSettings } from '@/components/global-settings-provider';

function MyComponent() {
  const { settings } = useGlobalSettings();
  
  return (
    <div style={{ color: settings?.branding?.primaryColor }}>
      Contact: {settings?.contact?.email}
    </div>
  );
}
```

**Dynamic Page Rendering:**
```typescript
import { fetchPageBySlug } from '@/utils/api';

// Pages are automatically rendered based on CMS structure
// No hardcoded content - everything comes from the database
```

**Carousel Integration:**
```typescript
import { fetchCarouselSlides } from '@/utils/api';

const slides = await fetchCarouselSlides('hero');
// Renders carousel based on admin-configured slides
```

## Advanced Admin Panel

### Access URLs
- **Main Admin Panel**: `https://your-domain.com/backend/admin/`
- **Advanced CMS Panel**: `https://your-domain.com/backend/admin/cms.php`

### Admin Panel Features

**Dashboard Overview:**
- Real-time statistics and metrics
- User growth charts
- Content performance analytics
- Recent activity feed

**Global Settings Management:**
- Visual branding controls (logo upload, color pickers)
- Contact information forms
- Social media link management
- Feature toggle switches
- Analytics and integration configuration

**Page Management:**
- Create unlimited custom pages
- Drag-and-drop section ordering
- SEO optimization tools
- Content scheduling
- Navigation menu management

**Content Management:**
- WYSIWYG editors for rich text
- Media library integration
- Content scheduling and versioning
- Bulk operations

**User Management:**
- User search and filtering
- Token and streak management
- Referral tracking
- Activity monitoring

## Security Features

### Rate Limiting
- 100 requests per hour per IP address
- Configurable limits in `.env`
- Automatic cleanup of old entries

### CORS Protection
- Whitelist of allowed origins
- Proper preflight handling
- Credential support for authenticated requests

### Input Validation
- Server-side validation for all inputs
- Email format validation
- Length constraints on all fields
- XSS prevention through proper escaping

### Authentication Security
- JWT tokens with expiration
- Secure password hashing (bcrypt cost 12)
- Session invalidation on logout
- Role-based access control

### File Upload Security
- File type validation (whitelist approach)
- MIME type verification
- File size limits
- Secure file naming and storage
- Virus scanning ready (configurable)

## Enhanced Admin Panel Features

### Dashboard Overview
- User statistics and growth metrics
- Blog performance analytics
- Contact form submissions
- Token economy overview

### Content Management
- **Blogs**: Create, edit, delete blog posts with rich text editor
- **Portfolio**: Manage project showcase with before/after images
- **Services**: Dynamic pricing and package management
- **Testimonials**: Client feedback moderation and verification
- **Pages**: Dynamic page creation with section management
- **Carousels**: Slider content with drag-and-drop reordering
- **Media Library**: File upload and organization system

### User Management
- View all registered users
- Reset user tokens and streaks
- Send targeted notifications
- Track user activity and engagement

### Global Configuration
- **Branding Controls**: Logo, colors, fonts, favicon
- **Contact Management**: Email, phone, address, WhatsApp
- **Social Links**: All social media platform links
- **Feature Toggles**: Enable/disable system features
- **Analytics Setup**: GA4, Facebook Pixel, Hotjar integration
- **API Integrations**: Mailchimp, SendGrid, chatbot services

### Analytics & Reporting
- User growth charts
- Popular content metrics
- Contact form conversion rates
- Token economy health

## CMS Usage Examples

### Creating a Custom Page

1. **Access Admin Panel**: Navigate to `/backend/admin/cms.php`
2. **Go to Page Management**: Click "Page Management" in sidebar
3. **Add New Page**: Click "Add New Page" button
4. **Configure Page**:
   ```json
   {
     "title": "Our Process",
     "metaDescription": "Learn about our design process",
     "sections": [
       {
         "type": "hero",
         "title": "Our Design Process",
         "description": "How we create amazing designs"
       },
       {
         "type": "content",
         "title": "Step-by-Step Process",
         "content": "<p>Our process involves...</p>"
       }
     ]
   }
   ```
5. **Publish**: Set status to "published" and save

### Managing Global Settings

1. **Access Settings**: Click "Global Settings" in admin sidebar
2. **Choose Category**: Select from Branding, Contact, Social, etc.
3. **Update Values**: Use form controls to modify settings
4. **Auto-Save**: Changes are automatically applied to frontend

### Carousel Management

1. **Access Carousels**: Click "Carousels" in admin sidebar
2. **Select Carousel**: Choose from Hero, Services, Testimonials, etc.
3. **Add/Edit Slides**: Upload images, set titles, configure CTAs
4. **Reorder**: Drag and drop to change slide order

## Caching Strategy

### What's Cached
- Blog listings (1 hour TTL)
- Service listings (1 hour TTL)
- Portfolio items (1 hour TTL)
- User profile data (30 minutes TTL)
- Global settings (2 hours TTL)
- Page content (1 hour TTL)
- Carousel slides (2 hours TTL)
- Navigation menus (2 hours TTL)

### Cache Management
```php
// Clear specific cache
$cache->delete('blogs_page_1');

// Clear pattern
$cache->clearPattern('blogs_*');

// Clear all cache
$cache->clearAll();
```

## Database Schema Extensions

### New Tables Added

**settings**: Global site configuration
```sql
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type ENUM('text', 'json', 'boolean', 'number', 'file'),
    category VARCHAR(50) NOT NULL,
    description TEXT
);
```

**pages**: Dynamic page management
```sql
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    meta_title VARCHAR(255),
    meta_description TEXT,
    sections JSON,
    status ENUM('draft', 'published', 'archived'),
    sort_order INT DEFAULT 0,
    show_in_nav BOOLEAN DEFAULT TRUE
);
```

**carousel_slides**: Slider content management
```sql
CREATE TABLE carousel_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carousel_name VARCHAR(100) NOT NULL,
    title VARCHAR(255),
    description TEXT,
    image_url VARCHAR(255),
    cta_text VARCHAR(100),
    cta_url VARCHAR(255),
    sort_order INT DEFAULT 0
);
```

**media_uploads**: File storage tracking
```sql
CREATE TABLE media_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    alt_text VARCHAR(255),
    caption TEXT
);
```

## Database Optimization

### Indexes Created
- Email lookups (users table)
- Blog searches (title, content fulltext)
- Portfolio filtering (category, featured)
- Token history (user_id, created_at)
- Rate limiting (ip_address, endpoint)
- Settings lookups (setting_key, category)
- Page routing (slug, status)
- Carousel ordering (carousel_name, sort_order)
- Media searches (mime_type, uploaded_by)

### Query Optimization
- Prepared statements for all queries
- Efficient JOIN operations
- Pagination with LIMIT/OFFSET
- Proper WHERE clause ordering

## Content Management Workflow

### For Non-Technical Users

1. **Login to Admin Panel**: Use provided admin credentials
2. **Global Settings**: Configure branding, contact info, social links
3. **Page Management**: Create and organize site pages
4. **Content Creation**: Add blogs, portfolio items, testimonials
5. **Media Management**: Upload and organize images/videos
6. **Carousel Setup**: Configure homepage and section sliders
7. **Preview Changes**: Use preview mode before publishing
8. **Publish Content**: Make changes live with one click

### For Developers

1. **API Integration**: All endpoints documented with examples
2. **Custom Sections**: Extend section types in `DynamicPageRenderer`
3. **Theme Customization**: Settings automatically apply CSS variables
4. **Webhook Integration**: Ready for CRM and marketing tool connections
5. **Performance Monitoring**: Built-in caching and optimization

## Monitoring & Logging

### Error Logging
- All errors logged to PHP error log
- Custom error handling for API endpoints
- Database connection error handling
- Email delivery failure logging

### Audit Trail
- All admin actions logged with timestamps
- User authentication attempts
- Data modification tracking
- IP address and user agent logging
- Content creation and modification history
- Settings change tracking
- Media upload and deletion logs

## Production Deployment

### Server Requirements
- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.2+
- Composer for dependency management
- SSL certificate for HTTPS
- File upload support (php.ini configuration)
- GD or ImageMagick for image processing

### Performance Tuning
- Enable OPcache for PHP
- Configure MySQL query cache
- Use CDN for static assets
- Implement Redis for session storage (optional)
- Configure file upload limits
- Enable gzip compression for API responses

### Security Checklist
- [ ] Change default JWT secret
- [ ] Set strong database passwords
- [ ] Enable HTTPS only
- [ ] Configure firewall rules
- [ ] Set up automated backups
- [ ] Enable error logging
- [ ] Disable PHP error display in production
- [ ] Configure file upload security
- [ ] Set proper file permissions
- [ ] Enable CSRF protection
- [ ] Configure rate limiting

## API Examples

### Global Settings API

**Get All Settings:**
```bash
curl -X GET "https://your-domain.com/backend/api/settings.php"
```

**Update Single Setting:**
```bash
curl -X PUT "https://your-domain.com/backend/api/settings.php/primary_color" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"value": "#FF0000", "type": "text"}'
```

### Page Management API

**Create New Page:**
```bash
curl -X POST "https://your-domain.com/backend/api/pages.php" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "About Us",
    "metaDescription": "Learn about our company",
    "sections": [
      {
        "type": "hero",
        "title": "About Our Company",
        "description": "We are passionate about design"
      }
    ],
    "status": "published"
  }'
```

### Media Upload API

**Upload File:**
```bash
curl -X POST "https://your-domain.com/backend/api/uploads.php" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@image.jpg" \
  -F "altText=Company logo" \
  -F "caption=Our new brand identity"
```

## Testing

### Run Tests
```bash
composer test
```

### Manual Testing
1. Test user registration and login
2. Verify JWT token authentication
3. Test CRUD operations for all entities
4. Check rate limiting functionality
5. Verify email notifications
6. Test admin panel functionality
7. Test global settings updates
8. Verify dynamic page rendering
9. Test media upload and management
10. Check carousel functionality

## Migration from Mock Data

### Automatic Migration
The system automatically detects when `VITE_USE_MOCK_DATA=false` and switches to live API calls. No code changes needed in React components.

### Data Import
Run the database installation script to import all existing mock data:
```bash
php scripts/install_database.php
```

This imports:
- All blog posts from `src/data/blogs.json`
- Portfolio items from `src/data/portfolio.json`
- Services from `src/data/services.json`
- Testimonials from `src/data/testimonials.json`
- User data and notifications

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Check database credentials in `.env`
- Verify MySQL service is running
- Check firewall settings

**JWT Token Invalid**
- Verify JWT_SECRET is set correctly
- Check token expiration time
- Ensure proper Authorization header format

**Email Not Sending**
- Check SMTP credentials
- Verify firewall allows SMTP ports
- Test with a simple mail() function first

**Cache Not Working**
- Check cache directory permissions (777)
- Verify CACHE_ENABLED in config
- Clear cache manually if needed

**File Upload Issues**
- Check upload directory permissions (777)
- Verify PHP upload limits in php.ini
- Check file type restrictions
- Ensure adequate disk space

**Settings Not Applying**
- Clear browser cache
- Check API response in network tab
- Verify settings cache is cleared
- Check CSS variable application

## Future Enhancements

### Planned Features
- **Multi-language Support**: Content translation management
- **Advanced SEO Tools**: Schema markup generator, sitemap automation
- **A/B Testing**: Content variation testing
- **Advanced Analytics**: Custom event tracking and reporting
- **Workflow Management**: Content approval processes
- **API Webhooks**: Real-time integrations with external services

### Integration Roadmap
- **CRM Integration**: HubSpot, Salesforce, Zoho
- **Email Marketing**: Advanced Mailchimp, SendGrid, Brevo integration
- **E-commerce**: WooCommerce, Shopify integration
- **Social Media**: Auto-posting to social platforms
- **Analytics**: Advanced reporting and insights

## Support

For technical support or questions about this CMS backend implementation:
- Check error logs first: `tail -f /var/log/php_errors.log`
- Review database queries in slow query log
- Test API endpoints with Postman or curl
- Verify environment variables are loaded correctly
- Check file permissions for uploads directory
- Verify cache directory is writable
- Test admin panel functionality in different browsers

## Success Metrics

✅ **Fully Dynamic Content**: Zero hardcoded content, everything manageable via CMS  
✅ **Non-Developer Friendly**: Intuitive admin interface for content management  
✅ **SEO Optimized**: Per-page meta tags and schema markup  
✅ **Performance Optimized**: Comprehensive caching and database optimization  
✅ **Security Hardened**: Multiple layers of security protection  
✅ **Mobile Responsive**: Admin panel works on all devices  
✅ **Future-Proof**: Modular architecture for easy extensions  
✅ **Production Ready**: Comprehensive error handling and logging  

## Hostinger Deployment Guide

### Quick Deployment Checklist

See `DEPLOYMENT_GUIDE.md` for comprehensive deployment instructions.

**Pre-Deployment:**
- [ ] Configure all environment variables in `.env`
- [ ] Test locally with production database
- [ ] Run build: `npm run build`
- [ ] Export database: `php scripts/backup_database.php`
- [ ] Verify all API integrations are configured

**Deployment:**
- [ ] Upload files via FTP/SSH to Hostinger
- [ ] Import database schema and migrations
- [ ] Set file permissions (755 for dirs, 644 for files, 775 for uploads/cache)
- [ ] Configure `.user.ini` for PHP settings
- [ ] Test database connection
- [ ] Verify API endpoints respond correctly

**Post-Deployment:**
- [ ] Configure SMTP (see `SMTP_SETUP_GUIDE.md`)
- [ ] Set up cron jobs for cache clearing and backups
- [ ] Enable Cloudflare CDN
- [ ] Run load tests (`php scripts/load_test.php`)
- [ ] Configure SSL certificate
- [ ] Update DNS records
- [ ] Test contact form and email notifications
- [ ] Verify admin panel access

### Essential Files & Guides

| Guide | Purpose |
|-------|---------|
| `DEPLOYMENT_GUIDE.md` | Complete Hostinger deployment instructions |
| `SMTP_SETUP_GUIDE.md` | Email configuration for multiple providers |
| `CACHING_GUIDE.md` | Caching strategies and CDN integration |
| `MEDIA_OPTIMIZATION_GUIDE.md` | Image/video optimization best practices |
| `LOAD_TESTING_GUIDE.md` | Performance testing and optimization |
| `README_APIS.md` | Complete API documentation and integration guide |

### Automated Scripts

**Maintenance Scripts:**
```bash
# Database backup (run daily via cron)
php scripts/backup_database.php

# Restore from backup
php scripts/restore_database.php /path/to/backup.sql.gz

# Clear expired cache (run hourly via cron)
php scripts/clear_cache.php

# Clean old logs (run weekly via cron)
php scripts/clean_logs.php

# Test SMTP configuration
php scripts/test_smtp.php your-email@example.com

# Run load test
php scripts/load_test.php
```

**Recommended Cron Jobs:**
```cron
# Clear expired cache every hour
0 * * * * php /home/username/public_html/backend/scripts/clear_cache.php

# Daily database backup at 2 AM
0 2 * * * php /home/username/public_html/backend/scripts/backup_database.php

# Clean old logs every Sunday at midnight
0 0 * * 0 php /home/username/public_html/backend/scripts/clean_logs.php
```

### Performance Optimization

**Enabled Optimizations:**
- ✅ OPcache for PHP bytecode caching
- ✅ Database query result caching
- ✅ File-based or database-based cache system
- ✅ Gzip compression for API responses
- ✅ Image optimization on upload
- ✅ Lazy loading for frontend images
- ✅ CDN integration (Cloudflare)
- ✅ Far-future expires headers for static assets

**Performance Targets:**
- API Response Time: < 300ms average
- Database Queries: < 50ms average
- Page Load (FCP): < 2 seconds
- Page Load (LCP): < 3 seconds
- Lighthouse Score: > 90

### Security Features

**Implemented Protections:**
- ✅ JWT authentication with secure tokens
- ✅ Password hashing with bcrypt (cost 12)
- ✅ Rate limiting (100 req/hour per IP)
- ✅ CORS whitelist protection
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (input sanitization)
- ✅ CSRF token validation
- ✅ File upload security (type/size validation)
- ✅ Secure session handling
- ✅ Security headers (X-Frame-Options, X-XSS-Protection, etc.)

**Security Checklist:**
- [ ] Change default JWT secret to strong random string
- [ ] Set strong database passwords
- [ ] Enable HTTPS only (disable HTTP)
- [ ] Configure firewall rules
- [ ] Set up automated backups
- [ ] Review and set file permissions
- [ ] Disable directory listing
- [ ] Protect `.env` file from web access
- [ ] Enable error logging (disable display in production)
- [ ] Regular security audits

### Monitoring & Maintenance

**Health Checks:**
```bash
# Check API health
curl https://yourdomain.com/backend/api/services.php

# Check database connection
mysql -u username -p -h localhost adilgfx_db -e "SELECT 1"

# Check disk space
df -h

# Check PHP error logs
tail -f /home/username/logs/php_errors.log
```

**Regular Maintenance Tasks:**
- Daily: Review error logs, check backup success
- Weekly: Review performance metrics, check disk space
- Monthly: Update dependencies, security audit, test backup restoration
- Quarterly: Review and optimize database, performance testing

### Troubleshooting Quick Reference

| Issue | Solution |
|-------|----------|
| 500 Internal Server Error | Check `.htaccess`, verify PHP version, review error logs |
| Database Connection Failed | Verify credentials in `.env`, check MySQL service |
| API Returns 404 | Verify `.htaccess` mod_rewrite is enabled, check file paths |
| File Upload Fails | Check `uploads/` permissions (775), verify PHP upload limits |
| CORS Issues | Verify domain in `ALLOWED_ORIGINS` in `config/config.php` |
| Slow API Response | Check cache is enabled, optimize slow queries, review indexes |
| Email Not Sending | Test SMTP config with `test_smtp.php`, check credentials |
| Rate Limit Errors | Increase limits in config or whitelist IP |

### Funnel Tester System

### Overview

The Funnel Tester Engine simulates complete user journeys from traffic source to conversion, testing all integrations and identifying bottlenecks.

### Funnel Stages

The tester simulates 6 core stages:

1. **Landing (Stage 1)**: Initial page view with analytics tracking
2. **Signup (Stage 2)**: User registration with token assignment
3. **Engagement (Stage 3)**: Welcome emails, WhatsApp messages, Telegram notifications
4. **Service Selection (Stage 4)**: Add service to cart simulation
5. **Checkout (Stage 5)**: Payment processing (Stripe or Coinbase)
6. **Post-Purchase (Stage 6)**: Order confirmation communications

### Mock User Generation

Each test creates a realistic mock user:

```php
{
  "full_name": "Jane Smith",
  "email": "jane.smith847@testuser.com",
  "phone": "+12345678900",
  "token_balance": 100,
  "role": "user"
}
```

### Running Funnel Tests

**Via Admin Panel:**
1. Navigate to `/backend/admin/`
2. Go to **Funnel Testing** section
3. Select traffic source: `google`, `linkedin`, `email`, `direct`, `social`
4. Select payment method: `stripe` or `coinbase`
5. Click **Run Test**
6. View real-time progress and results

**Via API:**
```bash
curl -X POST https://yourdomain.com/backend/api/funnel/simulate.php \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "traffic_source": "google",
    "payment_method": "stripe"
  }'
```

**Via Command Line:**
```bash
php backend/scripts/test_funnel.php google stripe
```

### Example Test Output

```json
{
  "simulation_id": 42,
  "traffic_source": "google",
  "steps": [
    {
      "step": "landing",
      "status": "success",
      "duration_ms": 1243
    },
    {
      "step": "signup",
      "status": "success",
      "user_id": 156,
      "tokens_assigned": 100,
      "duration_ms": 892
    },
    {
      "step": "engagement",
      "status": "success",
      "apis_triggered": 3,
      "duration_ms": 2456
    },
    {
      "step": "service_selection",
      "status": "success",
      "service": {
        "name": "Premium Logo Design",
        "price": 299.00
      },
      "duration_ms": 1105
    },
    {
      "step": "checkout",
      "status": "success",
      "payment_method": "stripe",
      "amount": 299.00,
      "duration_ms": 1876
    },
    {
      "step": "post_purchase",
      "status": "success",
      "order_number": "ORD-A8F3B2C1",
      "apis_triggered": 3,
      "duration_ms": 2234
    }
  ],
  "completion": {
    "success": true,
    "summary": {
      "total_steps": 6,
      "successful_steps": 6,
      "failed_steps": 0,
      "total_duration_ms": 9806,
      "conversion_value": 299.00
    }
  }
}
```

### Funnel Analytics

**View Simulation History:**
```bash
curl https://yourdomain.com/backend/api/funnel/report.php \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

**Response includes:**
- Total simulations run
- Success rate by traffic source
- Average completion time
- Step-by-step conversion rates
- API integration success rates
- Revenue simulation totals

### Database Tables

**funnel_simulations:**
```sql
CREATE TABLE funnel_simulations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    traffic_source VARCHAR(50) NOT NULL,
    mock_user_id INT,
    status ENUM('pending', 'in_progress', 'completed', 'failed'),
    conversion_value DECIMAL(10,2),
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    total_duration_ms INT
);
```

**funnel_steps:**
```sql
CREATE TABLE funnel_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    simulation_id INT NOT NULL,
    step_name VARCHAR(100) NOT NULL,
    step_order INT NOT NULL,
    status ENUM('pending', 'success', 'failed'),
    api_calls_made JSON,
    response_data JSON,
    error_message TEXT,
    duration_ms INT,
    executed_at TIMESTAMP,
    FOREIGN KEY (simulation_id) REFERENCES funnel_simulations(id)
);
```

### Integration Testing

The funnel tester validates:

- **SendGrid**: Welcome email delivery, order confirmations
- **WhatsApp**: Welcome messages, order notifications
- **Telegram**: Admin alerts for leads and orders
- **Stripe**: Payment intent creation (test mode)
- **Coinbase**: Charge creation (test mode)
- **Database**: User creation, token assignment, order logging

### Monitoring & Alerts

**Telegram Notifications:**
- Test started: "🧪 Funnel Test Started - ID: 42, Source: google"
- Test completed: "✅ Funnel Test Completed - 6/6 steps successful, 9.8s"
- Test failed: "❌ Funnel Test Failed - Step 3 failed: SendGrid API timeout"

### Use Cases

**Pre-Launch Testing:**
- Verify all integrations work correctly
- Identify configuration issues
- Test payment flows without real transactions
- Validate email delivery

**Monitoring:**
- Regular automated tests (cron job)
- Alert if success rate drops below threshold
- Track API uptime and response times

**Optimization:**
- Identify slowest funnel stages
- A/B test different flows
- Measure impact of changes

**Compliance:**
- Demonstrate system functionality
- Provide audit trail
- Document API usage

## Admin Panel Enhancements

### API Management Dashboard

**Location:** `/backend/admin/cms.php → API Integrations`

**Features:**
- Real-time API status indicators (green/red)
- Enable/disable toggle for each integration
- Test connection button with live results
- View recent API calls and response times
- Configure API keys without touching code
- Usage statistics and rate limit monitoring

**Supported Integrations:**
- SendGrid Email
- WhatsApp Business Cloud
- Telegram Bot
- Stripe Payments
- Coinbase Commerce
- Google Search Console
- PageSpeed Insights

### Content Management Features

**Blog Management:**
- Create, edit, delete blog posts
- Rich text WYSIWYG editor
- Featured image upload
- SEO meta tags per post
- Publish/draft status
- Scheduling (future publish dates)
- Category and tag management

**Portfolio Management:**
- Add portfolio projects
- Before/after image sliders
- Client information
- Results metrics (ROI, engagement, etc.)
- Featured projects toggle
- Project categories

**Service Management:**
- Dynamic service creation
- Pricing tiers and packages
- Service descriptions with HTML support
- Icon/image upload
- Featured services
- Service ordering (drag-and-drop)

**Testimonial Management:**
- Add client testimonials
- Client avatar upload
- Star ratings
- Verification status toggle
- Featured testimonials
- Display order management

**Page Management:**
- Create unlimited custom pages
- Section-based page builder
- Drag-and-drop section ordering
- SEO optimization per page
- Navigation menu auto-updates
- Template selection

**Carousel Management:**
- Multiple carousel support (hero, testimonials, portfolio)
- Image upload with captions
- CTA button configuration
- Link URLs
- Slide ordering (drag-and-drop)
- Auto-play settings

**Media Library:**
- Upload images, videos, documents
- Grid view with thumbnails
- Search and filter
- Alt text and captions
- File size and type info
- Usage tracking (where file is used)
- Bulk operations

### Funnel Testing Interface

**Location:** `/backend/admin/cms.php → Funnel Testing`

**Run New Test:**
1. Select traffic source dropdown
2. Select payment method dropdown
3. Click "Run Funnel Test"
4. Watch real-time progress bar
5. View detailed results

**Test History:**
- List of all previous tests
- Filter by date range
- Filter by traffic source
- View detailed step-by-step breakdown
- Export reports to CSV

**Analytics Dashboard:**
- Success rate by traffic source
- Average completion time
- Most common failure points
- API integration health scores
- Conversion value totals

### User Management

**Features:**
- View all registered users
- Search by email, name, or ID
- Filter by role (admin/user)
- User activity log
- Token balance management
- Login streak tracking
- Referral statistics
- Send targeted notifications
- Account status (active/suspended)

**User Actions:**
- Edit user details
- Reset password
- Adjust token balance
- Grant/revoke admin privileges
- View user orders and projects
- Send direct notifications

### Activity Audit Log

**Location:** `/backend/api/admin/activity.php`

**Logged Actions:**
- Admin logins and logouts
- Content creation/editing/deletion
- User management actions
- Settings changes
- API key updates
- Funnel tests run
- Database backups

**Log Entry Example:**
```json
{
  "id": 1234,
  "admin_id": 1,
  "admin_email": "admin@adilgfx.com",
  "action": "blog_post_created",
  "entity_type": "blog",
  "entity_id": 42,
  "changes": {
    "title": "New Design Trends 2025",
    "status": "published"
  },
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "timestamp": "2025-01-01 14:30:00"
}
```

### Dashboard Statistics

**Real-time Metrics:**
- Total users registered
- New users (last 7 days)
- Active users (logged in last 30 days)
- Total blog posts
- Total portfolio items
- Total testimonials
- Total services
- Contact form submissions (pending)
- Newsletter subscribers
- Total revenue (from orders)
- Conversion rate
- Average order value

**Growth Charts:**
- User registration over time
- Revenue over time
- Page views over time
- Popular content
- Traffic sources

### Security Features

**Access Control:**
- Role-based permissions (admin vs user)
- JWT token authentication
- Session management
- IP whitelisting (optional)
- Failed login tracking
- Account lockout after failed attempts

**Data Protection:**
- All passwords hashed with bcrypt
- SQL injection prevention
- XSS protection
- CSRF token validation
- File upload validation
- Secure cookie handling

**Admin Notifications:**
- Alert on multiple failed logins
- Notify on admin account creation
- Alert on critical errors
- Notify on high API usage

## Integration Status

**API Integrations (Part 2):**
- ✅ Stripe Payment Processing
- ✅ SendGrid Email Service
- ✅ WhatsApp Business API
- ✅ Telegram Bot Notifications
- ✅ Coinbase Commerce (Crypto Payments)
- ✅ Google Search Console
- ✅ PageSpeed Insights API

**Funnel Tester Engine:**
- ✅ Visitor flow simulation (6 stages)
- ✅ Mock user generation
- ✅ API integration testing
- ✅ Conversion tracking
- ✅ Funnel analytics and reporting
- ✅ A/B testing foundation
- ✅ Session duration tracking
- ✅ Drop-off point identification
- ✅ Real-time Telegram notifications
- ✅ Automated testing capability

**Admin Panel Features:**
- ✅ API management dashboard
- ✅ Content CRUD operations (blogs, portfolio, services)
- ✅ Media library with upload
- ✅ User management
- ✅ Activity audit logs
- ✅ Real-time statistics
- ✅ Funnel testing interface
- ✅ Settings management
- ✅ Security hardening

See `README_APIS.md` for complete API integration documentation.
See `DEPLOYMENT_HOSTINGER.md` for deployment instructions.

## License
MIT License - See LICENSE file for details