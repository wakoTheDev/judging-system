# LAMP Stack Judging System

## Overview
A complete judging system built with LAMP stack (Linux/Apache/MySQL/PHP) featuring admin panel, judge portal, and public scoreboard with real-time updates.

## Database Schema

```sql
-- Create database
CREATE DATABASE judging_system;
USE judging_system;

-- Judges table
CREATE TABLE judges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users/Participants table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Scores table
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judge_id INT NOT NULL,
    user_id INT NOT NULL,
    points INT NOT NULL CHECK (points >= 0 AND points <= 100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (judge_id) REFERENCES judges(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(judge_id, user_id) -- Each judge can only score each user once
);

-- Insert sample data
INSERT INTO judges (username, display_name) VALUES 
('judge1', 'Judge Smith'),
('judge2', 'Judge Johnson'),
('judge3', 'Judge Williams');

INSERT INTO users (username, display_name) VALUES 
('participant1', 'Alice Cooper'),
('participant2', 'Bob Dylan'),
('participant3', 'Charlie Brown'),
('participant4', 'Diana Prince'),
('participant5', 'Eve Adams');

-- Insert sample scores
INSERT INTO scores (judge_id, user_id, points) VALUES 
(1, 1, 85), (1, 2, 92), (1, 3, 78),
(2, 1, 88), (2, 2, 85), (2, 4, 95),
(3, 2, 90), (3, 3, 82), (3, 5, 89);
```

## Project Structure
```
/judging-system/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   └── footer.php
├── css/
│   └── style.css
├── js/
│   └── scoreboard.js
├── admin/
│   ├── index.php
│   └── add_judge.php
├── judge/
│   ├── index.php
│   └── score_user.php
├── api/
│   └── scoreboard.php
├── index.php (Public Scoreboard)
└── README.md
```

## Installation Instructions

### Prerequisites
- XAMPP, WAMP, or Docker with LAMP stack
- PHP 7.4+ with MySQLi extension
- MySQL 5.7+ or MariaDB

### Setup Steps

1. **Clone/Download the project** to your web server directory:
   ```bash
   # For XAMPP
   /xampp/htdocs/judging-system/
   
   # For WAMP
   /wamp64/www/judging-system/
   ```

2. **Database Setup:**
   - Start Apache and MySQL services
   - Access phpMyAdmin (http://localhost/phpmyadmin)
   - Execute the SQL schema provided above
   - Or import the provided `database.sql` file

3. **Configuration:**
   - Update database credentials in `config/database.php`
   - Ensure proper file permissions

4. **Access the Application:**
   - Public Scoreboard: `http://localhost/judging-system/`
   - Admin Panel: `http://localhost/judging-system/admin/`
   - Judge Portal: `http://localhost/judging-system/judge/`

## Core Features

### 1. Admin Panel (`/admin/`)
- **Add New Judges**: Create judges with unique usernames and display names
- **View All Judges**: List of registered judges
- **Basic Management**: Foundation for expanded admin features

### 2. Judge Portal (`/judge/`)
- **Judge Selection**: Judges select themselves from dropdown
- **User List**: Display all participants available for scoring
- **Score Assignment**: Assign points (1-100) to individual users
- **Score Updates**: Modify existing scores

### 3. Public Scoreboard (`/`)
- **Real-time Updates**: Auto-refreshes every 10 seconds
- **Total Points Display**: Shows accumulated points from all judges
- **Sorted Rankings**: Descending order by total points
- **Highlighted Leaders**: Visual emphasis on top performers
- **Responsive Design**: Works on mobile and desktop

## Technical Implementation

### Database Design Decisions
- **Normalized Structure**: Separate tables for judges, users, and scores
- **Foreign Key Constraints**: Ensures data integrity
- **Unique Constraints**: Prevents duplicate judge-user scoring pairs
- **Point Validation**: CHECK constraint for valid point ranges (0-100)

### PHP Architecture
- **Procedural Approach**: Simple, maintainable code structure
- **MySQLi Extension**: Prepared statements for security
- **Error Handling**: Basic validation and error reporting
- **Separation of Concerns**: Clear separation between logic and presentation

### Frontend Features
- **Responsive CSS**: Mobile-friendly design using Flexbox/Grid
- **JavaScript AJAX**: Dynamic scoreboard updates without page refresh
- **Form Validation**: Client-side and server-side validation
- **User Feedback**: Success/error messages for all actions

## Security Considerations

### Current Implementation
- **SQL Injection Prevention**: Prepared statements throughout
- **Input Validation**: Server-side validation for all forms
- **XSS Protection**: HTML escaping for output

### Production Recommendations
- **Authentication System**: Implement secure login for admins and judges
- **Session Management**: Secure session handling
- **HTTPS**: SSL certificate for encrypted communication
- **Access Control**: Role-based permissions
- **CSRF Protection**: Token-based form protection

## API Endpoints

### `/api/scoreboard.php`
- **Method**: GET
- **Response**: JSON array of users with total scores
- **Usage**: AJAX calls for real-time scoreboard updates

```json
[
    {
        "id": "1",
        "username": "participant1",
        "display_name": "Alice Cooper",
        "total_points": "173"
    }
]
```

## Future Enhancements

### High Priority
1. **Authentication System**: Secure login for admins and judges
2. **Score Categories**: Multiple scoring criteria per user
3. **Competition Management**: Multiple concurrent competitions
4. **Detailed Analytics**: Score breakdowns and judge statistics

### Medium Priority
1. **Email Notifications**: Automated updates to participants
2. **Export Functionality**: CSV/PDF reports
3. **Score Comments**: Text feedback alongside numerical scores
4. **User Registration**: Self-registration for participants

### Low Priority
1. **Real-time Notifications**: WebSocket-based live updates
2. **Mobile App**: Native mobile application
3. **Advanced Reporting**: Charts and graphs
4. **Integration APIs**: External system connectivity

## Testing

### Manual Testing Checklist
- [ ] Add new judges via admin panel
- [ ] Score users from judge portal
- [ ] Verify scoreboard updates automatically
- [ ] Test with multiple judges scoring same user
- [ ] Validate point range enforcement (1-100)
- [ ] Check responsive design on mobile

### Sample Test Data
The system includes sample judges, users, and scores for immediate testing.

## Troubleshooting

### Common Issues
1. **Database Connection**: Check credentials in `config/database.php`
2. **File Permissions**: Ensure web server can read files
3. **JavaScript Errors**: Check browser console for AJAX issues
4. **MySQL Version**: Ensure CHECK constraints are supported

### Error Logs
- PHP errors: Check Apache error logs
- Database errors: Enable MySQL query logging
- JavaScript errors: Use browser developer tools

## Performance Considerations

### Current Optimizations
- **Efficient Queries**: JOINs for scoreboard data
- **Minimal JavaScript**: Lightweight AJAX implementation
- **CSS Optimization**: Minimal external dependencies

### Production Recommendations
- **Database Indexing**: Add indexes on frequently queried columns
- **Caching**: Implement Redis/Memcached for scoreboard data
- **CDN**: Use Content Delivery Network for static assets
- **Database Connection Pooling**: Optimize connection management

## Code Quality

### Standards Followed
- **PSR-12**: PHP coding standards compliance
- **Consistent Naming**: Clear variable and function names
- **Comments**: Inline documentation for complex logic
- **Error Handling**: Graceful failure handling

### File Organization
- **Modular Structure**: Clear separation of concerns
- **Reusable Components**: Header/footer includes
- **Configuration Management**: Centralized database config

