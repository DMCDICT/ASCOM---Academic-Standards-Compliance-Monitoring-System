# Docker Migration Instructions

Based on your `docker-compose.yml`, here are your credentials:
- **Database**: `ascom_db`
- **User**: `ascom_user`
- **Password**: `ascom_password_secure_123`
- **Root Password**: `root_password_secure_456`
- **PHPMyAdmin**: http://localhost:8081

---

## Option 1: Using docker exec (Easiest)

Run this in your terminal:

```bash
docker exec -i ascom_db mysql -uascom_user -pascom_password_secure_123 ascom_db < database/migration_add_syllabus_system.sql
```

Or with root access:

```bash
docker exec -i ascom_db mysql -uroot -proot_password_secure_456 ascom_db < database/migration_add_syllabus_system.sql
```

---

## Option 2: Copy to init folder (Automatic on restart)

```bash
# Copy migration to MySQL init folder
cp database/migration_add_syllabus_system.sql docker/mysql/init/02-syllabus-migration.sql

# Restart containers
docker-compose down && docker-compose up -d
```

---

## Option 3: Using PHPMyAdmin (No command line)

1. Open **http://localhost:8081** in your browser
2. Login with:
   - **Server**: `db`
   - **Username**: `ascom_user` (or `root`)
   - **Password**: `ascom_password_secure_123` (or `root_password_secure_456`)
3. Select database **`ascom_db`** from the left panel
4. Click **"Import"** tab at the top
5. Choose file: `database/migration_add_syllabus_system.sql`
6. Click **"Go"** at the bottom

---

## Option 4: Using docker-compose exec

```bash
docker-compose exec db mysql -uascom_user -pascom_password_secure_123 ascom_db < database/migration_add_syllabus_system.sql
```

---

## Verify It Worked

After running, check the table exists:

```bash
docker exec -i ascom_db mysql -uascom_user -pascom_password_secure_123 ascom_db -e "SHOW TABLES LIKE 'course_syllabi';"
```

You should see `course_syllabi` in the output.

---

## Container Names Reference

| Service | Container Name | Port |
|---------|----------------|------|
| App | ascom_app | 8080 |
| Database | ascom_db | 3307 |
| PHPMyAdmin | ascom_phpmyadmin | 8081 |
| Redis | ascom_redis | 6379 |