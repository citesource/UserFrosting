<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Tests;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;

/**
 * Helper trait to pose as user when running an integration test
 * @author Louis Charette
 */
trait withTestUser
{
    /**
     * @param User $user
     */
    protected function setCurrentUser(User $user)
    {
        $this->ci->currentUser = $user;
        $this->ci->authenticator->login($user);
    }

    /**
     * Logout
     */
    protected function logoutCurrentUser()
    {
        $this->ci->authenticator->logout();
    }

    /**
     * Create a test user with no settings/permissions for a controller test
     * @param bool $isAdmin Does this user have root access? Will bypass all permissions
     * @param bool $login Login this user, setting him as the currentUser
     * @return User
     */
    protected function createTestUser($isAdmin = false, $login = false)
    {
        if ($isAdmin) {
            $user_id = $this->ci->config['reserved_user_ids.master'];
        } else {
            $user_id = rand(0, 1222);
        }

        $fm = $this->ci->factory;
        $user = $fm->create(User::class, ["id" => $user_id]);

        if ($login) {
            $this->setCurrentUser($user);
        }

        return $user;
    }

    /**
     * Gives a user a new test permission
     * @param  User $user
     * @param  string $slug
     * @param  string $conditions
     * @return Permission
     */
    protected function giveUserTestPermission(User $user, $slug, $conditions = "always()")
    {
        /** @var \League\FactoryMuffin\FactoryMuffin $fm **/
        $fm = $this->ci->factory;

        $permission = $fm->create(Permission::class, [
            'slug' => $slug,
            'conditions' => $conditions
        ]);

        // Add the permission to the user
        $this->giveUserPermission($user, $permission);

        return $permission;
    }

    /**
     * Add the test permission to a Role, then the role to the user
     * @param  User       $user
     * @param  Permission $permission
     * @return Role       The intermidiate role
     */
    protected function giveUserPermission(User $user, Permission $permission)
    {
        /** @var \League\FactoryMuffin\FactoryMuffin $fm **/
        $fm = $this->ci->factory;

        $role = $fm->create(Role::class);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        return $role;
    }
}