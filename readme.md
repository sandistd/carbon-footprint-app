# Carbon Footprint App

Carbon Footprint App adalah aplikasi yang membantu pengguna menghitung dan memantau jejak karbon dari aktivitas sehari-hari. Dengan fitur pelaporan dan analisis, aplikasi ini mendorong gaya hidup ramah lingkungan serta memberikan rekomendasi untuk mengurangi emisi karbon.

## Instalasi

1. **Clone repository**
    ```bash
    git clone https://github.com/sandistd/carbon-footprint-app.git
    cd carbon-footprint-app
    ```

2. **Install dependencies**
    ```bash
    composer install
    npm install
    ```

3. **Copy environment file**
    ```bash
    cp .env.example .env
    ```

4. **Generate application key**
    ```bash
    php artisan key:generate
    ```

5. **Set up database**
    - Edit file `.env` dan sesuaikan konfigurasi database.

6. **Run migrations**
    ```bash
    php artisan migrate:fresh --seed
    ```

7. **Start development server**
    ```bash
    composer run dev
    ```
