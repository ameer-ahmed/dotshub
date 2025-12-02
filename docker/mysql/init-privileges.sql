-- Grant all privileges to laravel user for multi-tenancy
GRANT ALL PRIVILEGES ON *.* TO 'laravel'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
