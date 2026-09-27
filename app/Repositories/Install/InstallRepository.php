<?php

namespace App\Repositories\Install;

use App\Interfaces\Install\InstallRepositoryInterface;
use App\Models\Blogs\Blog;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class InstallRepository implements InstallRepositoryInterface
{
    public function systemCheck()
    {
        // Basic system checks
        $checks = [
            'php_version' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'extensions'  => [
                'OpenSSL'   => extension_loaded('openssl'),
                'PDO'       => extension_loaded('pdo'),
                'Mbstring'  => extension_loaded('mbstring'),
                'Tokenizer' => extension_loaded('tokenizer'),
                'XML'       => extension_loaded('xml'),
            ],
            'writable' => [
                'storage'         => is_writable(storage_path()),
                'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
            ],
        ];

        return $checks;
    }

    public function install(array $data)
    {
        // Write to .env file

        $this->createSiteEnvFile($data);

        try {
            // Run migrations
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('config:cache');
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
            Artisan::call('storage:link');
            // Define the source and destination paths
            $sourcePath = public_path('images');
            $destinationPath = storage_path('app/public/images');
            // Copy the directory
            File::copyDirectory($sourcePath, $destinationPath);
            // Cache Clear
            Artisan::call('optimize:clear');

            return [
                'success' => true,
                'message' => 'Installation record created successfully.',
            ];
        } catch (\Exception $e) {
            Log::error('InstallRepository error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => 'Failed to create installation record.',
            ];
        }
    }

    private function createSiteEnvFile(array $data)
    {
        $env = base_path('.env');
        $content = file_get_contents($env);

        $replacements = [
            'APP_NAME'      => '"'.$data['app_name'].'"',
            'APP_URL'       => $data['app_url'],
            'DB_CONNECTION' => $data['db_connection'],
            'DB_PORT'       => $data['db_port'],
            'DB_HOST'       => $data['db_host'],
            'DB_DATABASE'   => $data['db_name'],
            'DB_USERNAME'   => $data['db_user'],
            'DB_PASSWORD'   => $data['db_password'] ?? '',
        ];

        foreach ($replacements as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $line = "{$key}={$value}";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content .= "\n{$line}";
            }
        }

        file_put_contents($env, $content);
    }

    public function createAdmin(array $data)
    {
        $admin = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $data['name'],
                'password' => Hash::make($data['password']),
            ]
        );

        $admin->assignRole('Admin');

        Blog::where('author_id', 1)->update([
            'author_id' => $admin->id,
        ]);
    }
}
