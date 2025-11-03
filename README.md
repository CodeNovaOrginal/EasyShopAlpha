# EasyShop CMS

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%207.0-777BB4.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7-orange.svg)](https://www.mysql.com/)
[![YouTube](https://img.shields.io/badge/Youtube-@CodeNovaOrginal-FF0000.svg)](https://www.youtube.com/@CodeNovaOrginal)

EasyShop is a simple, powerful, and customizable e-commerce Content Management System (CMS) designed for ease of use and flexibility. It provides all the essential tools to run an online store without the complexity of larger platforms.


## ✨ Features

-   🚀 **Web-based Installer:** Get your store running in minutes with a simple, guided setup process.
-   👤 **User Management:** Secure customer registration, login, and account management.
-   📦 **Full Product CRUD:** Easily Create, Read, Update, and Delete products and categories.
-   🎨 **Dynamic Theming:** A powerful theming system for both the admin panel and the public storefront.
-   🛒 **Shopping Cart:** A fully functional session-based shopping cart.
-   📄 **Custom Error Pages:** Clean, professional 404 and 500 error pages.
-   📱 **Responsive Design:** A modern, mobile-friendly storefront for your customers.
-   🔒 **Secure:** Built with security best practices, including prepared statements and password hashing.

## 🛠️ Tech Stack

-   **Backend:** PHP 8.x
-   **Database:** MySQL / MariaDB
-   **Frontend:** Vanilla HTML, CSS, and JavaScript
-   **Server:** Apache (with `.htaccess` for routing)

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

-   A web server like Apache or Nginx.
-   PHP 8.0 or greater.
-   MySQL or MariaDB.
-   A web browser.

## 🚀 Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/CodeNovaOrginal/EasyShopAlpha.git
    cd easyshop
    ```

2.  **Set up the Database:**
    -   Create a new database and a database user for EasyShop in your MySQL/MariaDB server.
    -   Note down the database name, username, and password.

3.  **Configure the Web Server:**
    -   Point your web server's document root to the `easyshop` directory.
    -   Ensure the `uploads` directory has write permissions for the web server.

4.  **Run the Installer:**
    -   Navigate to `http://your-domain.com/install.php` in your web browser.
    -   Follow the on-screen instructions to enter your database details and create your admin account.

5.  **You're Done!**
    -   You will be redirected to the admin login page. Log in and start building your store!

## 📖 Usage

### For the Store Admin

1.  **Log in** to the admin panel at `/admin`.
2.  **Add Products:** Go to `Products -> Add New Product` to list your items.
3.  **Customize Your Store:** Go to `Settings -> Personalize` to change themes and `Settings -> Store Info` to update your store name and slogan.
4.  **Manage Orders:** View and manage incoming customer orders.

### For the Customer

1.  **Browse Products:** Customers can visit your homepage to view all available products.
2.  **View Product Details:** Click on a product to see more information.
3.  **Add to Cart:** Add items to their shopping cart and adjust quantities.
4.  **Checkout:** Proceed through the checkout process to place an order (checkout flow to be implemented).
5.  **Manage Account:** Customers can register, log in, and view their order history.

## 🤝 Contributing

Contributions are what make the open-source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement". Don't forget to give the project a star! Thanks again!

1.  Fork the Project
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the Branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request

## 📄 License

This project is licensed under the Apache License 2.0. See the `LICENSE` file for details.

Copyright 2025 CodeNova

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

## 🙏 Acknowledgments

-   A huge thank you to everyone who contributes to making open-source a great place to be.
-   Inspired by the need for a truly simple and extensible e-commerce solution.
