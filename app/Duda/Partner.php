<?php

namespace App\Duda;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Partner extends Base
{
    /**
     * Class constructor sets the base URI of the partner api services
     */
    public function __construct()
    {
        parent::__construct('/api/');
    }

    /**
     * Creates a user on the duda platform using a generated username, the username is unique because of the complexity
     * of the generated username making a clash very unlikely. Once a positive response has been received from duda the
     * new username is returned as a string.
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @return string
     * @throws Exception
     */
    public function createAccount(string $email, string $first_name, string $last_name): string
    {
        $duda_username = 'avw_' . substr(sha1(microtime(true)), 0, 64);
        $payload = [
            'account_name' => $duda_username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'account_type' => 'CUSTOMER'
        ];
        $response = $this->request('accounts/create', 'POST', null, null, $payload, null);
        if ($response['code'] === 204) {
            return $duda_username;
        }
        throw new Exception("Failed to create the user account", 500);
    }

    public function getAvailableTemplates(): array
    {
        $duda = new Partner();
        $response = $duda->request('sites/multiscreen/templates', 'GET', null, null, null, null);
        if ($response['code'] !== 200) {
            throw new Exception("Failed to get the available templates", 500);
        }
        $template_list = $response['data'] ?? [];
        $output = [];
        foreach ($template_list as $template) {
            if (($template['editor'] ?? '') === 'ADVANCED-2.0') {
                $template_id = $template['template_id'] ?? null;
                if ($template_id !== null && $template_id !== '') {
                    $output[$template_id] = $template;
                }
            }
        }
        return $output;
    }

    public function getWebsiteDetails(string $site_id): array
    {
        $response = $this->request('sites/multiscreen/{site_name}', 'GET', null, ['site_name'=>$site_id], null, null);
        if ($response['code'] !== 200) {
            throw new Exception("Failed to get the website details", 500);
        }
        return $response;

    }

    public function createWebsiteFromTemplate(int $template_id): string
    {
        $duda = new Partner();
        $response = $this->request('sites/multiscreen/create', 'POST', null, null, ['template_id' => $template_id], null);
        if ($response['code'] !== 200) {
            throw new Exception("Failed to create the website template", 500);
        }
        $site_id = strval($response['data']['site_name'] ?? '');
        if ($site_id == '') {
            throw new Exception('Failed to create the website template', 500);
        }
        return $site_id;
    }

    public function grantWebsitePermissions(string $duda_username, string $site_id, array | string $permission_groups): void
    {
        if (is_string($permission_groups)) {
            $permission_groups = explode(',', $permission_groups);
        }
        foreach($permission_groups as $key=>$value) {
            $value = trim((string) $value);
            if ($value === '') {
                unset($permission_groups[$key]);
                continue;
            }
            $permission_groups[$key] = strtolower($value);
        }
        $permissions = [];
        foreach ($permission_groups as $permission_group) {
            $group_permission_flags = config('duda.permissions.' . $permission_group);
            if (!is_array($group_permission_flags)) {
                throw new Exception("Invalid permission group", 500);
            }
            $permissions = array_merge($permissions, $group_permission_flags);
        }
        $permissions = array_unique($permissions);
        $response = $this->request('accounts/{duda_username}/sites/{site_id}/permissions', 'POST', null, ['duda_username'=>$duda_username, 'site_id'=>$site_id], ['permissions' => $permissions], null);
        if ($response['code'] !== 204) {
            throw new Exception("Failed to grant permissions", 500);
        }
    }

    public function getWebsiteEditorLink(string $duda_username, string $site_id): ?string
    {
        $response = $this->request('accounts/sso/{duda_username}/link', 'GET', null, ['duda_username'=>$duda_username], null, ['site_name'=>$site_id, 'target'=>'EDITOR']);
        if ($response['code'] !== 200) {
            return null;
        }
        return $response['data']['url'] ?? null;
    }


}
