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

### Integration Status

**API Integrations (Part 2):**
- ✅ Stripe Payment Processing
- ✅ SendGrid Email Service
- ✅ WhatsApp Business API
- ✅ Telegram Bot Notifications
- ✅ Coinbase Commerce (Crypto Payments)
- ✅ Google Search Console
- ✅ PageSpeed Insights API

**Funnel Tester Engine:**
- ✅ Visitor flow simulation (4 scenarios)
- ✅ Conversion tracking
- ✅ Funnel analytics and reporting
- ✅ A/B testing foundation
- ✅ Session duration tracking
- ✅ Drop-off point identification

See `README_APIS.md` for complete API integration documentation.

## License
MIT License - See LICENSE file for details