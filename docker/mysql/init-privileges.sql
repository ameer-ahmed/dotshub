-- Grant all privileges to root user for multi-tenancy
GRANT ALL PRIVILEGES ON *.* TO 'dotshub'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
