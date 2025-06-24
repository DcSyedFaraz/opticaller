# OptiCaller

**OptiCaller** is a Laravel-based web application designed to streamline and automate outbound calling campaigns. It integrates AI-driven features to enhance call scheduling, contact management, and campaign analytics, making it an ideal solution for businesses aiming to optimize their telecommunication strategies.

## 🚀 Features

* **Campaign Management**: Create, monitor, and analyze outbound calling campaigns with ease.
* **Contact Management**: Efficiently manage contact lists and segment audiences for targeted outreach.
* **AI-Powered Scheduling**: Leverage AI to determine optimal calling times, increasing engagement rates.
* **Real-Time Analytics**: Access comprehensive dashboards to monitor campaign performance and agent productivity.
* **User Roles & Permissions**: Define roles to manage access levels and maintain data security.

## 🛠️ Tech Stack

* **Frontend**: Vue.js, Tailwind CSS
* **Backend**: Laravel (PHP)
* **Database**: MySQL
* **Build Tools**: Vite

## 📦 Installation

1. **Clone the Repository**:

   ```bash
   git clone https://github.com/DcSyedFaraz/opticaller.git
   cd opticaller
   ```
2. **Install Dependencies**:

   ```bash
   composer install
   npm install
   ```
3. **Environment Setup**:

   * Duplicate `.env.example` and rename it to `.env`.
   * Configure your database and other environment variables in the `.env` file.
4. **Generate Application Key**:

   ```bash
   php artisan key:generate
   ```
5. **Run Migrations**:

   ```bash
   php artisan migrate
   ```
6. **Start the Development Server**:

   ```bash
   php artisan serve
   npm run dev
   ```

## 📂 Project Structure

```plaintext
opticaller/
├── app/               # Application logic
├── bootstrap/         # Bootstrap files
├── config/            # Configuration files
├── database/          # Migrations and seeders
├── public/            # Publicly accessible files
├── resources/         # Views and assets
├── routes/            # Route definitions
├── storage/           # Compiled views, logs, etc.
├── tests/             # Automated tests
├── .env.example       # Environment variable example
├── artisan            # Artisan CLI
├── composer.json      # PHP dependencies
├── package.json       # Node.js dependencies
└── vite.config.js     # Vite configuration
```

## 🧪 Testing

* **Run Tests**:

  ```bash
  php artisan test
  ```

## 🤝 Contributing

Contributions are welcome! Please fork the repository and submit a pull request for any enhancements or bug fixes.

## 📄 License

This project is licensed under the [MIT License](LICENSE).
