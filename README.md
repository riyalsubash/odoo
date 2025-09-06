♻️ Rebayan - A Sustainable Second-Hand Marketplace
Rebayan is a user-friendly and engaging web application that serves as a central hub for buying and selling second-hand items. It aims to foster a culture of sustainability by extending the lifecycle of products, reducing waste, and promoting a circular economy.

This platform connects buyers and sellers efficiently, allowing users to find unique pre-owned goods and making sustainable choices easier for everyone.

✨ Core Features
👤 User & Account Management
Secure Authentication: Register with Name, Username, Email, and Phone. Login using either email or username.

Email Verification: A robust verification system for new accounts and email changes.

Password Management: Securely hashed passwords with a 'Forgot Password' feature using expiring tokens.

User Dashboard: A central place for users to update their profile, change their password, and track their total earnings from sales.

marketplace & Commerce
Detailed Product Listings: Sellers can upload multiple images and add comprehensive details for each item.

Advanced Search & Filter: Easily find items with keyword search and category filtering.

Seller Profile Pages: View all available listings from a specific seller.

🤝 Order & Notification System
Seller Approval Workflow: Buyers request to purchase, giving sellers control to approve or reject orders.

Direct Communication: Buyers and sellers can contact each other via Email or Phone after a request is made.

Order Tracking: Dashboards for both sellers (Manage Requests) and buyers (My Orders) to track order statuses.

Real-time Alerts: An in-app notification system with a bell icon in the header alerts users to new requests and status updates.

🛠️ Tech Stack
Backend: PHP

Database: MySQL / MariaDB

Frontend: HTML5, CSS3 (with a responsive, mobile-first design), JavaScript

Server: Apache (usually bundled with XAMPP/WAMP)

🚀 Local Setup and Installation
To run this project on your local machine, follow these steps:

1. Prerequisites:

Make sure you have a local server environment like XAMPP or WAMP installed.

2. Database Setup:

Open phpMyAdmin.

Create a new database (e.g., rebayan_db).

Import the provided .sql file to create all the necessary tables.

3. Configure Connection:

Open config/db_connect.php and update your database credentials.

4. Configure Mail Server:

For email features to work, configure php.ini and sendmail.ini with your SMTP server details (e.g., Gmail with an App Password).

5. Run the Project:

Place the project folder in your htdocs directory.

Start Apache and MySQL services.

Navigate to http://localhost/your-project-folder/ in your browser.

🗄️ Database Schema (ER Diagram)
erDiagram
    users {
        int id PK
        varchar name
        varchar username
        varchar email
    }
    products {
        int id PK
        int user_id FK
        varchar title
        decimal price
    }
    orders {
        int id PK
        int product_id FK
        int buyer_id FK
        int seller_id FK
    }
    users ||--o{ products : "lists"
    users ||--o{ orders : "buys/sells"
    products ||--o{ orders : "is_in"

