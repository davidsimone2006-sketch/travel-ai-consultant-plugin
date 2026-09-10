# Alex Travel Consultant Plugin

**Premium AI-Powered Travel Consultant for WordPress**

🌍 Transform your travel portal with an intelligent AI advisor that helps customers find the perfect trip while capturing high-quality leads for your business.

## Features

✨ **Intelligent AI Conversations**
- Powered by OpenAI GPT technology
- Natural, engaging dialogue
- Learns customer preferences
- Multi-topic expertise (flights, hotels, cruises, packages, Amtrak)

🎨 **Luxury Premium Interface**
- 3D immersive experience
- Gold & dark luxury theme
- Beautiful animations
- Mobile-responsive design

💼 **Lead Generation System**
- Automatic lead capture
- Email notifications to you
- Full conversation context
- Admin dashboard tracking

🔒 **Secure & Compliant**
- Data encryption
- GDPR compliant
- Privacy-first approach
- Secure API integration

## Quick Start

### Installation

1. Download and extract plugin
2. Upload to `/wp-content/plugins/`
3. Activate in WordPress
4. Go to Alex Consultant → Settings
5. Add OpenAI API key
6. Add shortcode `[alex_travel_consultant]` to your page

### Getting Started

1. Get OpenAI API key: https://platform.openai.com/api-keys
2. Add to plugin settings
3. Configure your email
4. Customize consultant name
5. Add to website pages
6. Start receiving leads!

## Usage

```php
// Display on any page or post
[alex_travel_consultant]
```

## File Structure

```
travel-ai-consultant-plugin/
├── travel-ai-consultant.php      # Main plugin file
├── uninstall.php                 # Uninstall handler
├── readme.txt                    # Plugin readme
├── INSTALLATION.md               # Setup guide
├── README.md                     # This file
├── includes/
│   ├── class-alex-admin.php      # Admin dashboard
│   ├── class-alex-frontend.php   # Frontend UI
│   ├── class-alex-api.php        # AI & API handler
│   ├── class-alex-database.php   # Database operations
│   ├── class-alex-settings.php   # Settings management
│   └── class-alex-email.php      # Email notifications
├── templates/
│   └── shortcode.php             # Shortcode template
├── assets/
│   ├── css/
│   │   ├── alex-consultant.css   # Frontend styles
│   │   └── alex-admin.css        # Admin styles
│   └── js/
│       ├── three.min.js          # 3D library
│       ├── alex-consultant.js    # Frontend logic
│       └── alex-admin.js         # Admin logic
└── languages/                    # Translations
```

## Requirements

- WordPress 6.0+
- PHP 7.4+
- OpenAI API key
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Configuration

### Settings Page

1. **Consultant Name** - Display name (e.g., "Alex")
2. **Consultant Title** - Professional title (e.g., "Senior Travel Advisor")
3. **Admin Email** - Where to receive leads
4. **OpenAI API Key** - Your API credentials
5. **Color Theme** - Customize primary/secondary colors

## Customization

### CSS Variables

```css
:root {
    --alex-gold: #d4af37;      /* Primary color */
    --alex-dark: #1a1a1a;      /* Secondary color */
    --alex-light: #f5f5f5;     /* Background */
    --alex-white: #ffffff;     /* Text */
}
```

### JavaScript Hooks

Extend functionality through JavaScript events and API.

## API Integration

The plugin can be extended to integrate with:
- Amadeus Travel API
- Sabre GDS
- Booking.com API
- Expedia API
- Flight comparison APIs
- Hotel booking platforms

## Database Schema

### alex_leads
```sql
- id (INT PRIMARY KEY)
- name (VARCHAR 255)
- email (VARCHAR 255)
- phone (VARCHAR 20)
- destination (VARCHAR 255)
- travel_date (VARCHAR 50)
- budget (VARCHAR 100)
- service_type (VARCHAR 100)
- travelers (VARCHAR 50)
- preferences (LONGTEXT)
- conversation_summary (LONGTEXT)
- status (VARCHAR 50) [new, contacted, converted, lost]
- created_at (DATETIME)
- updated_at (DATETIME)
```

### alex_conversations
```sql
- id (INT PRIMARY KEY)
- session_id (VARCHAR 255)
- user_message (LONGTEXT)
- ai_response (LONGTEXT)
- message_type (VARCHAR 50)
- created_at (DATETIME)
```

### alex_sessions
```sql
- id (INT PRIMARY KEY)
- session_id (VARCHAR 255 UNIQUE)
- user_email (VARCHAR 255)
- ip_address (VARCHAR 100)
- conversation_data (LONGTEXT)
- created_at (DATETIME)
- updated_at (DATETIME)
```

## Troubleshooting

### Common Issues

**Plugin not showing:**
- Verify WordPress version 6.0+
- Check PHP version 7.4+
- Clear cache
- Try different browser

**AI not responding:**
- Validate OpenAI API key
- Check API quota
- Verify internet connection
- Check browser console for errors

**Emails not sent:**
- Confirm email address in settings
- Check WordPress mail configuration
- Test with SMTP plugin
- Check spam folder

## Security

✅ Secure API communication
✅ Data encryption at rest
✅ NONCE verification
✅ SQL injection prevention
✅ XSS protection
✅ CSRF protection

## Performance

- Lightweight (~2MB)
- Async AI calls
- Efficient database queries
- Lazy loading for 3D effects
- Browser caching support

## Browser Support

✅ Chrome 90+
✅ Firefox 88+
✅ Safari 14+
✅ Edge 90+
✅ Mobile browsers (iOS Safari, Chrome Mobile)

## License

MIT License - Free for personal and commercial use

## Support

- 📧 Email: support@mytripfares.com
- 🌐 Website: https://mytripfares.com
- 📚 Docs: https://mytripfares.com/docs
- 🐛 Issues: GitHub Issues

## Changelog

### v1.0.0 (September 2024)
- Initial release
- Full AI conversation engine
- Lead capture system
- Admin dashboard
- Email notifications
- 3D premium interface
- Mobile responsive
- Security features

## Credits

Developed by MyTripFares Team
Powered by OpenAI GPT Technology
Built for WordPress

---

**Ready to revolutionize your travel business? Get started today! 🚀**
