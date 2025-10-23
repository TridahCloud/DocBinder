# DocBinder by Tridah

A modern web application for organizing digital documents into binders. Built with HTML, CSS, PHP, and JavaScript.

## Features

- **Digital Binders**: Create and organize documents in digital binders
- **Multiple File Types**: Support for PDF files, images, and text documents
- **Lightweight Text Editor**: Write content directly in the application
- **Public Sharing**: Share binders publicly or keep them private
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Light/Dark Mode**: Toggle between light and dark themes
- **Modern UI**: Clean, modern interface inspired by tech startups

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Styling**: Custom CSS with CSS Variables for theming
- **Icons**: Font Awesome 6.4.0

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/TridahCloud/DocBinder.git
   cd DocBinder
   ```

2. **Set up the database**
   - Create a MySQL database
   - Import the `database_schema.sql` file
   - Copy `config.sample.php` to `config.php` and update with your database credentials

3. **Configure the application**
   - Update `config.php` with your database credentials
   - Ensure the `uploads/` directory is writable
   - Set up a web server (Apache/Nginx) with PHP support

4. **Access the application**
   - Navigate to your web server's document root
   - Visit `index.php` to get started

## Security Notes

- **Never commit `config.php`**: This file contains sensitive database credentials
- **Use `config.sample.php`**: Copy this file to `config.php` and customize it
- **Protect uploads directory**: Ensure proper file permissions and consider adding `.htaccess` rules
- **Regular updates**: Keep PHP and MySQL updated for security patches

## File Structure

```
DocBinder/
├── assets/
│   ├── css/
│   │   ├── style.css          # Main stylesheet
│   │   └── components.css     # Component-specific styles
│   └── js/
│       ├── main.js            # Main JavaScript functionality
│       └── theme.js           # Theme switching
├── api/
│   ├── add-document.php       # Add document endpoint
│   ├── delete-document.php     # Delete document endpoint
│   └── delete-binder.php      # Delete binder endpoint
├── uploads/                   # File upload directory
├── config.php                 # Configuration and database connection
├── header.php                 # Common header
├── footer.php                 # Common footer
├── index.php                  # Homepage
├── register.php               # User registration
├── login.php                  # User login
├── logout.php                 # User logout
├── dashboard.php              # User dashboard
├── create-binder.php           # Create new binder
├── view-binder.php             # View binder (authenticated)
├── edit-binder.php             # Edit binder and documents
├── shared-binder.php            # Public binder viewer
├── database_schema.sql         # Database schema
└── README.md                  # This file
```

## Usage

### For Users

1. **Sign Up**: Create a free account
2. **Create Binders**: Organize your documents into digital binders
3. **Add Documents**: Upload PDFs, images, or write text directly
4. **Share**: Make binders public or share with specific people
5. **View**: Navigate through documents with sidebar and next/previous buttons

### For Developers

The application follows a modular structure:
- PHP includes for header/footer consistency
- CSS variables for easy theming
- JavaScript modules for functionality
- RESTful API endpoints for AJAX operations

## Contributing

This project is developed by Tridah, a non-profit organization. Contributions are welcome!

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## About Tridah

Tridah is a non-profit organization committed to creating free, open-source software that empowers individuals and organizations. Visit us at [tridah.cloud](https://tridah.cloud) or check out our GitHub at [github.com/TridahCloud](https://github.com/TridahCloud).

## Support

For support, please open an issue on GitHub or visit our website at [tridah.cloud](https://tridah.cloud).
