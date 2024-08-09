<?php

namespace App\Console\Commands;

use App\Models\Users\Permission;
use App\Models\Users\Role;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolesAndPermissionsUpdated extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles-and-permissions-updated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mettre à jour les roles et permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Désactiver les contraintes de clé étrangère pour éviter les problèmes lors de la troncature des tables
        // Désactiver temporairement la protection contre l'assignation de masse sur le modèle
        Schema::disableForeignKeyConstraints();
        Model::unguard();

        // Tronquer les tables spécifiées pour supprimer tous les enregistrements existants
        $this->truncate("model_has_permissions");
        $this->truncate("permissions");

        // Réactiver la protection contre l'assignation de masse sur le modèle
        // Réactiver les contraintes de clé étrangère après la troncature des tables
        Model::reguard();
        Schema::enableForeignKeyConstraints();

        // Mettre à jour les rôles et les permissions
        $this->updateRoles();
        $this->updatePermissions();

        $this->info('Mise à jour des Liens Roles/Permissions');

        $roles = Role::static_getRoles();
        $bar = $this->output->createProgressBar($roles->count());
        $bar->start();

        foreach ($roles as $name => $description) {
            $this->synchroneRolesPermissions($name);
            $bar->advance();
        }
        $bar->finish();
        $this->info('');
        $this->info('');

        $this->info('Terminé');
    }

    /**
     * @description Met à jour les rôles dans la base de données.
     *
     * Cette méthode récupère les rôles statiques de la classe Role
     * et les compare avec les rôles actuels dans la base de données.
     * Les nouveaux rôles sont ajoutés et les anciens sont mis à jour.
     *
     * @return void
     */
    private function updateRoles(): void
    {

        $this->info('Mise à jour des roles');
        $roles = Role::static_getRoles();
        $bar = $this->output->createProgressBar($roles->count());
        $bar->start();

        foreach ($roles as $name => $description) {
            $role = Role::firstOrNew(
                ['name' => $name],
                ['description' => $description]
            );
            $role->save();
            $bar->advance();
        }
        $bar->finish();
        $this->info('Mise à jour des roles terminée');
        $this->info('');
    }

    /**
     * @description Met à jour les permissions dans la base de données.
     *
     * Cette méthode récupère les permissions statiques de la classe Permission
     * et les compare avec les permissions actuelles dans la base de données.
     * Les nouvelles permissions sont ajoutées et les anciennes sont mises à jour.
     *
     * @return void
     */
    private function updatePermissions(): void
    {
        $this->info('Mise a jour des Permissions');
        $permissions = Permission::static_getPermissions();
        $bar = $this->output->createProgressBar($permissions->count());
        $bar->start();

        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrNew(
                ['name' => $name],
                ['description' => $description]
            );
            $permission->save();
            $bar->advance();
        }
        $bar->finish();
        $this->info('Mis à jour des permissions terminée');
        $this->info('');
    }

    /**
     * @description Synchronise les permissions pour un rôle donné.
     *
     *  Cette méthode révoque toutes les permissions actuelles du rôle spécifié,
     *  puis attribue les nouvelles permissions basées sur la configuration.
     *
     * @param string $roleName
     * @return void
     */
    private function synchroneRolesPermissions(string $roleName): void
    {
        $this->info("Permissions -> Role : $roleName");

        $role = Role::findByName($roleName);
        $role->revokePermissionTo(Permission::all());

        $permissions = Permission::static_getPermissionsByRoleName($roleName);

        if ($permissions) {
            $role->givePermissionTo($permissions);
        } else {
            $this->alert("Non configuré pour ce role : $roleName !");
        }
    }

    /**
     * Truncate a table with foreign key checks disabled and re-enabling them afterwards. This is useful for truncating tables
     * @param $table
     * @return bool
     */
    private function truncate($table): bool
    {
        if (DB::getDefaultConnection() === 'mysql') {
            DB::table($table)->truncate();
            return true;
        }
        return false;
    }
}
