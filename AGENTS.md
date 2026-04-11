# ASCOM Academic Standards Compliance Monitoring System

## Key Entry Points
- User portal: `user_login.php` (faculty, deans, librarians, QA)
- Super admin portal: `super_admin_login.php` (system administration)

## Authentication
1. Login: `user_auth.php` or `super_admin_auth.php`
2. Sessions: `session_config.php`
3. Role-based access: Multiple roles per user (legacy roles + user_roles table)
4. Password verification: `ascom_verify_password_with_migration()` includes hash migration

## Database
- Connection: `super_admin-mis/includes/db_connection.php` (MySQLi prepared statements)
- Schema changes: Direct SQL scripts (`*.sql` files)
- Important: Department deans identified by `dean_user_id` in departments table

## Important Directories
- `super_admin-mis/` - Main administrative interface
- `admin-quality_assurance/` - QA-specific functionality  
- `department-dean/` - Dean portal features
- `librarian/` - Librarian portal features
- `teachers/` - Teacher portal features
- `src/` - Static assets (images, fonts, icons)

## Development Notes
- PHP sessions started in `session_config.php` and `user_auth.php`
- No explicit CSRF protection visible
- Password hashing: Custom verification with migration support
- Error handling: try/catch with fallback queries
- File uploads: PHPSpreadsheet for Excel operations (per composer.json)

## Testing & Debugging
- Test files: Prefixed with `test_` or `debug_`
- Database manipulation: Common via `*.sql` and `*_columns.php` scripts
- Session testing: `session_test.php`, `test_session.php`

## Gotchas
- Role assignment complex (legacy roles vs user_roles table)
- Multiple session cleanup points (login pages destroy existing sessions)
- Timezone sensitive: last_activity comparisons