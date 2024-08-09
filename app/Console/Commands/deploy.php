<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'deployer l\'application sur les branches locales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Deploying application...');
        $this->line('');

//        $this->alert('Vider le cache');
//        $this->call('optimize:clear');
//        $this->line('');

        if (file_exists(storage_path('app/profilmanager.cache'))) {
            $this->alert('Deleting profilmanager.cache');
            unlink(storage_path('app/profilmanager.cache'));
        }

        $this->alert('Migrations');
        $this->call('migrate', ['--force' => true, '--vvv']);

        $this->alert('Mise à jour des dépendances');
        shell_exec("composer install");
        $this->line("");

        $this->alert("Mise à jour des rôles et des permissions");
        $this->call("roles-and-permissions-updated");
        $this->line("");
    }
}
