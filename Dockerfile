# Stage 1: Build the frontend assets with Node
FROM node:20 AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Serve the application with PHP & Apache
FROM php:8.2-apache
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    dos2unix

# Clear cache to keep image small
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install native PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the entire Laravel application
COPY . /var/www/html/

# Copy the built Vite assets from the Node stage
COPY --from=frontend /app/public/build /var/www/html/public/build

# Set correct DocumentRoot for Apache (Laravel needs it pointing to /public)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer dependencies (exclude dev dependencies for production)
RUN composer install --no-dev --optimize-autoloader

# Set permissions for the web server to write to storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port (Render defaults to 80 for Web Services)
EXPOSE 80

# Make the start script executable
RUN dos2unix /var/www/html/start.sh
RUN chmod +x /var/www/html/start.sh

# Run the startup script (migrates and boots apache)
CMD ["/var/www/html/start.sh"]
