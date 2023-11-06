# To Install Passport
composer clear-cache <br>
composer update laravel/passport <br>
php artisan passport:install <br>
php artisan serve <br>

# For Hosting:
.htaccess <br>
<IfModule mod_rewrite.c> <br>
	RewriteEngine on <br>
	RewriteCond %{REQUEST_URI} !^public <br>
	RewriteRule ^(.*)$ public/$1 [L] <br>
</IfModule> <br>

# To Solve DB Errors: <br>
ALTER TABLE product_lists MODIFY star INT NOT NULL DEFAULT 0; <br>
