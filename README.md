# Article Publisher - DBCLM College

A web-based platform for publishing and managing academic articles within the DBCLM College community. This system allows students and teachers to share knowledge through articles, engage with content through comments and reactions, and provides administrators with tools to manage content quality.

## Project Overview

Article Publisher is designed to be a centralized platform where college members can publish articles, share ideas, and engage in academic discussions. The platform features a user-friendly interface, comprehensive moderation tools, and a full notification system to keep users engaged.

## Project Structure

### User-Facing Pages
- **Newsfeed** - Main content stream showing approved articles
- **Profile** - User profile management and article history
- **About Us** - Information about the platform and its team
- **Contact Us** - Support and contact information
- **Landing Page** - Entry point for non-logged in users

### Administrative Interface
- **Admin Dashboard** - Overview of system activity and article management
- **User Management** - User account administration
- **Announcement System** - Creation and distribution of announcements
- **Settings** - System configuration options

## Key Features

### User Management
- Role-based access control (Students, Teachers, Administrators)
- User profiles with customizable profile pictures
- Email verification with DBCLM domain (@dbclm.com)
- Password security with proper hashing

### Article System
- Article submission with rich text and image support
- Moderation workflow (Pending → Approved/Rejected)
- Filtering by institute/department
- Article interactions (likes, comments)
- Content reporting functionality
- Personal content hiding
- Institute-specific article categorization

### Social Features
- Comment system on articles
- Like/reaction system with multiple reaction types (like, love, haha, wow, sad, angry)
- Content sharing
- Notification system for interactions

### Administrative Tools
- Comprehensive dashboard with statistics
- Content moderation queue
- User management interface
- Announcement creation with audience targeting
- Audit logging for system activities

### Notifications
- Real-time notification for article status updates
- Interaction notifications (comments, likes)
- Optional email notifications
- Announcement notifications

## Database Structure

### Core Tables

#### Users and Authentication
- **`users`**: Stores user accounts with roles (Student/Teacher), credentials, and profile information
  - Notable fields: `role`, `full_name`, `email`, `password`, `institute`, `profile_picture`
  - Has triggers for audit logging and preventing duplicate emails/names

#### Content Management
- **`articles`**: The central content repository
  - Key fields: `user_id`, `title`, `abstract`, `content`, `featured_image`, `status` (PENDING/APPROVED/REJECTED)
  - Additional controls: `allow_comments`, `notify_comments`, `comments_enabled`, `notifications`
  - Has triggers for automatic logging on submission

- **`announcements`**: System-wide announcements from administrators
  - Fields include: `title`, `content`, `publication_date`, `expiry_date`, `audience`, `status`

#### User Engagement
- **`comments`**: User comments on articles with proper relationships
- **`reactions`**: Stores user reactions to articles
- **`hidden_articles`**: Tracks which users have hidden which articles
- **`article_reports`**: Records when users report problematic content

#### Organizational Structure
- **`institutes`**: Academic departments within the college (IC, ITEd, ILEGG, IAAS)
  - Used to categorize articles and users

### Notification System
- **`notifications`**: User-specific notifications
- **`admin_notifications`**: Administrator-targeted notifications

### Audit and Logging
- **`article_logs`**: Detailed tracking of article state changes
- **`audit_log`**: System-wide activity logging
- **`user_logs`**: User-specific activity tracking

### Database Features
- Proper foreign key constraints between related tables
- Cascading deletes to maintain referential integrity
- Triggers for automated logging and data validation
- Appropriate data types and constraints

## Technical Implementation

### Front-End
- HTML5, CSS3 for layout and styling
- Responsive design for multiple device support
- JavaScript for interactive elements

### Back-End
- PHP for server-side processing
- MySQL database for data storage
- Session-based authentication
- File upload handling for images and profile pictures

## Installation

1. Clone this repository to your web server
2. Import the database schema from `dbclm_college.sql`
3. Configure database connection in db_connect.php
4. Ensure the uploads directory has proper write permissions
5. Access the application through your web server

## Usage

### Student/Teacher
1. Register with a valid DBCLM email address
2. Log in to access the newsfeed
3. Create and submit articles for review
4. Engage with content through comments and likes
5. Receive notifications for activity on your content

### Administrator
1. Access the admin dashboard with administrative credentials
2. Review and moderate submitted articles
3. Manage user accounts
4. Create announcements for the community
5. Monitor system statistics and activity

## Security Features

- Password hashing
- Input validation and sanitization
- Domain-restricted registration
- Session management
- CSRF protection
- Content moderation

## Contributors
- Barbie Penafiel
- Donna Meg Eran
- Chenybabes Dalogdog

## License
This project is intended for educational use within DBCLM College.