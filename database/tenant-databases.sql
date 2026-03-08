-- إنشاء قواعد بيانات الـ tenants للـ Multi-Tenancy
-- شغّل هذا الملف في MySQL: mysql -u root -p < database/tenant-databases.sql

CREATE DATABASE IF NOT EXISTS alasadiya_db;
CREATE DATABASE IF NOT EXISTS abuhamad_db;
CREATE DATABASE IF NOT EXISTS demo_db;
