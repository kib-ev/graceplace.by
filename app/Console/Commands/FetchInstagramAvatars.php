<?php

namespace App\Console\Commands;

use App\Models\Master;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchInstagramAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masters:fetch-avatars {master_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update Instagram avatars for masters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $masterId = $this->argument('master_id');

        $masters = $masterId
            ? Master::whereNotNull('instagram')->where('id', $masterId)->get()
            : Master::whereNotNull('instagram')->get();

        if ($masters->isEmpty()) {
            $this->info('No masters with Instagram profiles found.');
            return 0;
        }

        $this->info("Found {$masters->count()} masters to update.");

        foreach ($masters as $master) {
            $this->line("Processing master: {$master->person->full_name}...");

            $instagramUsernameRaw = $master->instagram;
            $instagramUsername = '';

            if (preg_match('/instagram\.com\/([\w\.]+)/', $instagramUsernameRaw, $matches)) {
                $instagramUsername = $matches[1];
            } else {
                $instagramUsername = explode(' ', $instagramUsernameRaw)[0];
            }
            $instagramUsername = trim($instagramUsername);

            if (empty($instagramUsername)) {
                $this->warn(" > Could not extract a valid username from '{$instagramUsernameRaw}'. Skipping.");
                continue;
            }

            try {
                $sessionId = config('services.instagram.session_id');
                if (empty($sessionId)) {
                    $this->error('Instagram session ID is not configured. Please add INSTAGRAM_SESSION_ID to your .env file.');
                    return 1;
                }

                $response = Http::withOptions(['debug' => $this->option('verbose')])->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'X-IG-App-ID' => '936619743392459'
                ])->withCookies(['sessionid' => $sessionId], 'i.instagram.com')
                ->get("https://i.instagram.com/api/v1/users/web_profile_info/?username={$instagramUsername}");

                if ($response->status() === 404) {
                    $this->warn(" > Profile for @{$instagramUsername} not found (404). Skipping.");
                    continue;
                }
                
                if (!$response->successful()) {
                    $this->error(" > Failed to fetch profile for @{$instagramUsername}. Status: " . $response->status());
                    Storage::disk('local')->put("debug/instagram_{$instagramUsername}.json", $response->body());
                    $this->warn(" > Response body saved to storage/app/debug/instagram_{$instagramUsername}.json");
                    continue;
                }
                
                $jsonData = $response->json();
                $avatarUrl = $jsonData['data']['user']['profile_pic_url_hd'] ?? null;
                
                if (empty($avatarUrl)) {
                    $this->warn(" > Could not find avatar URL for @{$instagramUsername} in the JSON response.");
                    Storage::disk('local')->put("debug/instagram_{$instagramUsername}.json", $response->body());
                    $this->warn(" > JSON response saved to storage/app/debug/instagram_{$instagramUsername}.json for inspection.");
                    continue;
                }

                $imageContents = Http::get($avatarUrl)->body();

                if (empty($imageContents)) {
                    $this->error(" > Failed to download avatar from URL: {$avatarUrl}");
                    continue;
                }
                
                $path = "avatars/{$master->id}.jpg";
                Storage::disk('public')->put($path, $imageContents);

                $master->update(['avatar' => $path]);

                $this->info(" > Successfully updated avatar for @{$instagramUsername}.");

            } catch (\Exception $e) {
                $this->error(" > An error occurred: " . $e->getMessage());
            }
        }
        
        $this->info('Avatar update process finished.');
        return 0;
    }
}
