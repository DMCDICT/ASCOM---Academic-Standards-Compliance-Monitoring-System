#!/bin/bash
# Migration Script for Course Syllabus System
# Run this script to apply all database changes

echo "=========================================="
echo "ASCOM Course Syllabus System Migration"
echo "=========================================="
echo ""

# Check if MySQL is available
if ! command -v mysql &> /dev/null; then
    echo "Error: MySQL client not found"
    echo "Please install MySQL or run the SQL manually"
    exit 1
fi

# Get database credentials
read -p "MySQL Host [localhost]: " MYSQL_HOST
MYSQL_HOST=${MYSQL_HOST:-localhost}

read -p "MySQL User [root]: " MYSQL_USER
MYSQL_USER=${MYSQL_USER:-root}

read -s -p "MySQL Password: " MYSQL_PASSWORD
echo ""

read -p "Database Name: " MYSQL_DATABASE

# Run migration
echo ""
echo "Running migration..."
echo ""

mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < database/migration_add_syllabus_system.sql

if [ $? -eq 0 ]; then
    echo ""
    echo "=========================================="
    echo "✅ Migration completed successfully!"
    echo "=========================================="
    echo ""
    echo "Next steps:"
    echo "1. Log in as a Dean"
    echo "2. Navigate to Syllabus Management"
    echo "3. Create a program and assign a program head"
    echo "4. Create courses and assign teachers"
    echo "5. Have teachers write syllabi"
else
    echo ""
    echo "❌ Migration failed. Please check the error above."
fi