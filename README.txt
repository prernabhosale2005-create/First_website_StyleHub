STYLE HUB - ENHANCED PHP + MYSQL PROJECT

FEATURES
- Men, Women, Kids and Accessories categories
- Functional database-backed clothing search
- Expanded product catalogue
- Shopping cart and quantity management
- Checkout and delivery details
- Dummy payment options: Cash on Delivery, UPI, Card, Net Banking, Digital Wallet
- Order and order-item storage
- Delivery-person and delivery tables
- Wishlist/address tables for future expansion
- Persistent login using a secure remember token cookie
- Existing login and registration fields/flow retained
- Product admin upload with category and stock

DATABASE TABLES
users
categories
products
addresses
cart_items
orders
order_items
payments
delivery_persons
deliveries
wishlist
remember_tokens

INSTALLATION
1. Copy the project folder into C:\xampp\htdocs\
2. Start Apache and MySQL in XAMPP.
3. Open http://localhost/phpmyadmin
4. Import database.sql.
5. Open http://localhost/style_hub/home.php

LOGIN MEMORY
After a successful login/registration, Style Hub creates a hashed remember token in the remember_tokens table and a secure cookie. This restores the login after a page refresh or browser restart for up to 30 days. Logout revokes the token.

PAYMENTS
All payment options are dummy/simulated for academic demonstration. No real financial transaction is processed.

ADMIN
Open http://localhost/style_hub/admin.php after login. The current project protects the admin page by login status only; production deployment should add a dedicated admin role.
