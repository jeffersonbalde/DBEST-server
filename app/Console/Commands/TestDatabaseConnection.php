<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TestDatabaseConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test database connection and diagnose connection issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing database connection...');
        $this->newLine();

        // Display connection details (without password)
        $config = Config::get('database.connections.' . Config::get('database.default'));
        $this->line('Connection: ' . Config::get('database.default'));
        $this->line('Host: ' . ($config['host'] ?? 'N/A'));
        $this->line('Port: ' . ($config['port'] ?? 'N/A'));
        $this->line('Database: ' . ($config['database'] ?? 'N/A'));
        $this->line('Username: ' . ($config['username'] ?? 'N/A'));
        $this->newLine();

        // Test DNS resolution
        $host = $config['host'] ?? '127.0.0.1';
        $this->info('Testing DNS resolution...');
        $ip = gethostbyname($host);
        
        if ($ip === $host) {
            $this->error("❌ DNS resolution failed for: {$host}");
            $this->warn('The hostname could not be resolved. Check:');
            $this->warn('  1. Internet connectivity');
            $this->warn('  2. DNS server configuration');
            $this->warn('  3. Hostname is correct');
            return 1;
        } else {
            $this->info("✅ DNS resolved: {$host} -> {$ip}");
        }
        $this->newLine();

        // Test database connection
        $this->info('Testing database connection...');
        try {
            $pdo = DB::connection()->getPdo();
            $this->info('✅ Database connection successful!');
            $this->newLine();

            // Test a simple query
            $this->info('Testing query execution...');
            $result = DB::select('SELECT 1 as test, DATABASE() as current_db, VERSION() as version');
            $this->info('✅ Query executed successfully!');
            $this->line('Current Database: ' . ($result[0]->current_db ?? 'N/A'));
            $this->line('MySQL Version: ' . ($result[0]->version ?? 'N/A'));
            $this->newLine();

            // Get connection info
            $this->info('Connection Information:');
            $this->line('PDO Driver: ' . $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
            $this->line('Server Info: ' . $pdo->getAttribute(\PDO::ATTR_SERVER_INFO));
            
            return 0;
        } catch (\PDOException $e) {
            $this->error('❌ Database connection failed!');
            $this->newLine();
            $this->error('Error Code: ' . $e->getCode());
            $this->error('Error Message: ' . $e->getMessage());
            $this->newLine();
            
            // Provide helpful suggestions
            $this->warn('Possible solutions:');
            
            if (strpos($e->getMessage(), 'getaddrinfo') !== false || strpos($e->getMessage(), 'No such host') !== false) {
                $this->warn('  1. DNS resolution issue - check hostname');
                $this->warn('  2. Check internet connectivity');
                $this->warn('  3. Verify DB_HOST in .env file');
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
                $this->warn('  1. Database server is not accessible');
                $this->warn('  2. Check firewall rules');
                $this->warn('  3. Verify DB_PORT in .env file');
                $this->warn('  4. Add your IP to database trusted sources');
            } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
                $this->warn('  1. Check DB_USERNAME and DB_PASSWORD');
                $this->warn('  2. Verify user has access to the database');
            } elseif (strpos($e->getMessage(), 'SSL') !== false) {
                $this->warn('  1. SSL configuration issue');
                $this->warn('  2. Try setting MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=false in .env');
            } else {
                $this->warn('  1. Check database credentials');
                $this->warn('  2. Verify database server is running');
                $this->warn('  3. Check network connectivity');
            }
            
            return 1;
        } catch (\Exception $e) {
            $this->error('❌ Unexpected error: ' . $e->getMessage());
            return 1;
        }
    }
}

