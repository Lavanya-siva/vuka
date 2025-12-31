<?php 
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Laravel\Passport\ClientRepository;

class CreateOAuthClient extends Command
{
    protected $signature = 'oauth:create-client {userId} {name} {redirectUri}';
    protected $description = 'Create an OAuth client for a user';

    public function handle()
    {
        $userId = $this->argument('userId');
        $name = $this->argument('name');
        $redirectUri = $this->argument('redirectUri');

        $user = User::find($userId);

        if (!$user) {
            $this->error("User not found");
            return;
        }

        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            user: $user,
            name: $name,
            redirectUris: [$redirectUri],
            confidential: false,
            enableDeviceFlow: true
        );

        $this->info("Client created successfully: ID {$client->id}");
    }
}
