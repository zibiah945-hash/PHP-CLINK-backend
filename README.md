# 🏥 CLINIK Backend API - Deployment Ready

Production-ready backend API for the CLINIK Patient Record Management System. Built with PHP and MySQL, featuring secure authentication, RESTful endpoints, and comprehensive patient management.

---

## 📁 Project Structure

```
backend_structured_deployment/
├── config/
│   └── database.php          # Database connection with PDO
├── includes/
│   ├── auth.php              # Authentication helper functions
│   ├── cors.php              # CORS configuration
│   └── functions.php         # Common utility functions
├── api/
│   ├── auth/
│   │   ├── login.php         # User login endpoint
│   │   ├── register.php      # User registration endpoint
│   │   ├── logout.php        # User logout endpoint
│   │   └── check-session.php # Session validation endpoint
│   ├── patients/
│   │   ├── create.php        # Create new patient
│   │   ├── read.php          # Get patient details
│   │   ├── list.php          # List all patients
│   │   ├── update.php        # Update patient
│   │   └── search.php        # Search patients
│   ├── appointments/
│   │   ├── create.php        # Schedule appointment
│   │   ├── list.php          # List appointments
│   │   └── update.php        # Update appointment
│   └── visits/
│       ├── create.php        # Record visit
│       └── history.php       # Get visit history
├── logs/                     # Application logs (auto-created)
├── .env.example              # Environment variables template
├── .htaccess                 # Apache configuration
├── index.php                 # API documentation endpoint
└── README.md                 # This file
```

---

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_rewrite enabled
- Composer (optional, for dependencies)

### Installation Steps

#### 1. Clone or Copy Files

```bash
# Copy the backend_structured_deployment folder to your web server
cp -r backend_structured_deployment /path/to/xampp/htdocs/clinik-backend
```

#### 2. Configure Environment

```bash
# Copy the example environment file
cp .env.example .env

# Edit .env with your database credentials
nano .env
```

Example `.env` configuration:
```env
DB_HOST=localhost
DB_NAME=clinic_db
DB_USER=root
DB_PASS=your_password
DB_PORT=3306

ALLOWED_ORIGINS=http://localhost:3000,http://localhost
APP_ENV=production
```

#### 3. Create Database

```sql
-- Run this in MySQL/phpMyAdmin
CREATE DATABASE clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import your database schema
SOURCE /path/to/clinic_database.sql;
```

#### 4. Set Permissions

```bash
# Make logs directory writable
chmod 755 logs/
chmod 644 logs/*.log

# Protect sensitive files
chmod 600 .env
```

#### 5. Test the API

Visit: `http://localhost/clinik-backend/`

You should see the API documentation JSON response.

---

## 📡 API Endpoints

### Authentication

#### Login
```http
POST /api/auth/login.php
Content-Type: application/json

{
  "username": "admin",
  "password": "your_password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "user_id": 1,
      "username": "admin",
      "email": "admin@clinic.com",
      "full_name": "Administrator",
      "role": "admin"
    }
  }
}
```

#### Register
```http
POST /api/auth/register.php
Content-Type: application/json

{
  "username": "newuser",
  "password": "password123",
  "email": "user@clinic.com",
  "full_name": "John Doe",
  "role": "staff"
}
```

#### Logout
```http
POST /api/auth/logout.php
```

#### Check Session
```http
GET /api/auth/check-session.php
```

---

### Patients

#### Create Patient
```http
POST /api/patients/create.php
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "age": 35,
  "gender": "Male",
  "contact_number": "+1234567890",
  "address": "123 Main St",
  "emergency_contact": "Jane Doe",
  "emergency_contact_phone": "+0987654321",
  "date_of_birth": "1988-05-15",
  "blood_type": "O+",
  "allergies": "None",
  "medical_history": "No significant history",
  "diagnosis": "Common cold",
  "prescription": "Rest and fluids"
}
```

#### List Patients
```http
GET /api/patients/list.php?page=1&limit=50
```

#### Get Patient Details
```http
GET /api/patients/read.php?id=1
```

#### Update Patient
```http
PUT /api/patients/update.php
Content-Type: application/json

{
  "patient_id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "age": 36,
  "gender": "Male",
  ...
}
```

#### Search Patients
```http
GET /api/patients/search.php?q=John
```

---

### Visits

#### Create Visit
```http
POST /api/visits/create.php
Content-Type: application/json

{
  "patient_id": 1,
  "diagnosis": "Hypertension",
  "prescription": "Medication XYZ",
  "notes": "Follow-up in 2 weeks",
  "visit_date": "2024-11-24 10:30:00"
}
```

#### Get Visit History
```http
GET /api/visits/history.php?patient_id=1
```

---

### Appointments

#### Create Appointment
```http
POST /api/appointments/create.php
Content-Type: application/json

{
  "patient_id": 1,
  "appointment_date": "2024-11-30 14:00:00",
  "appointment_type": "Follow-up",
  "status": "scheduled",
  "notes": "Bring previous test results"
}
```

#### List Appointments
```http
GET /api/appointments/list.php?status=scheduled&date_from=2024-11-01
```

#### Update Appointment
```http
PUT /api/appointments/update.php
Content-Type: application/json

{
  "appointment_id": 1,
  "status": "completed",
  "notes": "Patient arrived on time"
}
```

---

## 🔒 Security Features

### Implemented Security Measures

1. **Authentication & Authorization**
   - Session-based authentication
   - Role-based access control (Admin/Staff)
   - Session timeout (24 hours)
   - Secure session cookies

2. **Input Validation**
   - Required field validation
   - Data type validation
   - Email and phone validation
   - SQL injection prevention (PDO prepared statements)

3. **CORS Protection**
   - Configurable allowed origins
   - Preflight request handling
   - Credentials support

4. **Security Headers**
   - X-Content-Type-Options: nosniff
   - X-Frame-Options: DENY
   - X-XSS-Protection: 1; mode=block
   - Referrer-Policy: strict-origin-when-cross-origin

5. **File Protection**
   - .htaccess rules to protect sensitive files
   - .env file protection
   - Log file protection
   - Directory browsing disabled

6. **Logging**
   - All authentication attempts logged
   - User actions logged
   - Error logging
   - Audit trail

---

## 🔧 Configuration

### Database Configuration

Edit `config/database.php` or use environment variables:

```php
DB_HOST=localhost
DB_NAME=clinic_db
DB_USER=root
DB_PASS=your_password
DB_PORT=3306
```

### CORS Configuration

Edit `includes/cors.php` or set in `.env`:

```env
ALLOWED_ORIGINS=http://localhost:3000,http://localhost,https://yourdomain.com
```

### Frontend Configuration

Update your frontend `config.js`:

```javascript
const API_CONFIG = {
    BASE_URL: 'http://localhost/clinik-backend/api',
    ENDPOINTS: {
        AUTH: {
            LOGIN: '/auth/login.php',
            REGISTER: '/auth/register.php',
            LOGOUT: '/auth/logout.php',
            CHECK_SESSION: '/auth/check-session.php'
        },
        PATIENTS: {
            CREATE: '/patients/create.php',
            LIST: '/patients/list.php',
            READ: '/patients/read.php',
            UPDATE: '/patients/update.php',
            SEARCH: '/patients/search.php'
        },
        VISITS: {
            CREATE: '/visits/create.php',
            HISTORY: '/visits/history.php'
        },
        APPOINTMENTS: {
            CREATE: '/appointments/create.php',
            LIST: '/appointments/list.php',
            UPDATE: '/appointments/update.php'
        }
    }
};
```

---

## 🧪 Testing

### Test API Connection

```bash
curl http://localhost/clinik-backend/
```

### Test Login

```bash
curl -X POST http://localhost/clinik-backend/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"1234"}'
```

### Test Patient Creation

```bash
curl -X POST http://localhost/clinik-backend/api/patients/create.php \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{
    "first_name":"Test",
    "last_name":"Patient",
    "age":30,
    "gender":"Male"
  }'
```

---

## 📝 Logging

Logs are stored in the `logs/` directory:

- `logs/app.log` - Application logs

Log format:
```
[2024-11-24 10:30:45] [INFO] User logged in: admin
[2024-11-24 10:31:12] [ERROR] Database connection failed
```

---

## 🚀 Deployment to Production

### 1. Update Environment

```env
APP_ENV=production
ENABLE_HTTPS=true
```

### 2. Enable HTTPS

Update `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Secure Database

- Change default passwords
- Use strong database passwords
- Restrict database access to localhost only

### 4. Update CORS

```env
ALLOWED_ORIGINS=https://yourdomain.com,https://www.yourdomain.com
```

### 5. Set Proper Permissions

```bash
chmod 755 /path/to/clinik-backend
chmod 644 /path/to/clinik-backend/.htaccess
chmod 600 /path/to/clinik-backend/.env
chmod 755 /path/to/clinik-backend/logs
```

### 6. Disable Error Display

In `config/database.php`:
```php
if (getenv('APP_ENV') === 'production') {
    ini_set('display_errors', 0);
    error_reporting(0);
}
```

---

## 🐛 Troubleshooting

### Common Issues

**1. CORS Errors**
- Check `ALLOWED_ORIGINS` in `.env`
- Verify frontend URL matches allowed origins
- Check browser console for specific CORS error

**2. Database Connection Failed**
- Verify database credentials in `.env`
- Check if MySQL service is running
- Verify database exists

**3. Session Not Working**
- Check PHP session configuration
- Verify cookies are enabled in browser
- Check session cookie settings in `includes/auth.php`

**4. 404 Errors**
- Verify mod_rewrite is enabled
- Check `.htaccess` file exists
- Verify file paths are correct

**5. Permission Denied**
- Check file permissions (755 for directories, 644 for files)
- Verify web server user has access
- Check logs directory is writable

---

## 📚 Additional Resources

- [PHP PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [RESTful API Best Practices](https://restfulapi.net/)
- [OWASP Security Guidelines](https://owasp.org/)

---

## 📄 License

MIT License - See LICENSE file for details

---

## 👥 Support

For issues or questions:
- Check the troubleshooting section
- Review logs in `logs/app.log`
- Contact: support@clinik.com

---

**Built with ❤️ for healthcare professionals**
