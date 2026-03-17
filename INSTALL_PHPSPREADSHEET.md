# Installing PhpSpreadsheet

## Step 1: Install Composer (if not already installed)

1. Download Composer for Windows from: https://getcomposer.org/download/
2. Run the installer `Composer-Setup.exe`
3. During installation, point it to your PHP executable: `C:\xampp\php\php.exe`
4. Make sure Composer is added to your system PATH

## Step 2: Install PhpSpreadsheet

Open PowerShell or Command Prompt in this project directory and run:

```bash
composer require phpoffice/phpspreadsheet
```

This will:
- Create a `vendor` folder in your project
- Download PhpSpreadsheet and its dependencies
- Create a `composer.json` file

## Step 3: Verify Installation

After installation, you should have:
- A `vendor` folder containing PhpSpreadsheet
- A `composer.json` file
- A `composer.lock` file

The import feature should now work!

## Troubleshooting

If you get errors:
1. Make sure PHP is in your system PATH
2. Make sure Composer is in your system PATH
3. Try running: `php composer.phar require phpoffice/phpspreadsheet` instead
