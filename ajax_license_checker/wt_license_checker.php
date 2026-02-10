<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

class PlgAjaxWt_license_checker extends CMSPlugin
{
    public function onAjaxWt_license_checker()
    { 
        Factory::getApplication()->setHeader('Content-Type', 'application/json', true);

        // Get license key and domain from the request
        $input = Factory::getApplication()->input;
        $licenseKey = $input->getString('key', '');
        $domain = $input->getString('domain', '');

        // Validate input
        if (empty($licenseKey) || empty($domain)) {
            echo json_encode(['error' => 'Missing license key or domain']);
            return;
        }

        // Query license key from the database
        $db = Factory::getContainer()->get(DatabaseDriver::class);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__swjprojects_keys'))
            ->where($db->quoteName('key') . ' = ' . $db->quote($licenseKey));
        $db->setQuery($query);
        $result = $db->loadAssoc();

        // If license key not found
        if (!$result) {
            echo json_encode(['error' => 'Invalid license key']);
            return;
        }

        $dateEnd = $result['date_end'];
        $allowedPlugins = isset($result['allowed_plugins']) ? $result['allowed_plugins'] : '*';
        $registeredDomain = $result['domain'];

        // Validate domain
        $isDomainValid = $this->isAllowedDomain($registeredDomain, $domain);

        // Check if license is still valid
        $isValid = $isDomainValid && ($dateEnd == '0000-00-00 00:00:00' || strtotime($dateEnd) > time());

        // Prepare response (fix it as you want)
        $data = [
            'domain'           => $registeredDomain,
            'date_start'       => $result['date_start'],
            'date_end'         => $result['date_end'],
            'project_id'       => $result['project_id'],
            'state'            => $result['state'],
            'license_name'     => $result['license_name'] ?? 'Standard',
            'expires'          => ($isValid ? 'never' : $result['date_end']),
            'key_status'       => ($isValid ? 'Active' : 'Expired'),
            'owner'            => $result['owner'] ?? 'N/A',
            'license_valid'    => ($isValid ? '1' : '0'),
            'allows_plugins'   => ($isValid ? '1' : '0'),
            'is_trial_license' => $result['is_trial_license'] ?? '0',
            'allowed_plugins'  => ($isValid ? $allowedPlugins : '')
        ];

        echo json_encode($data);
        return;
    }

    /**
     * Check if current domain is allowed (supports subdomains)
     */
    private function isAllowedDomain($registeredDomain, $currentDomain)
    {
        if (empty($registeredDomain)) {
            return false;
        }

        $registeredDomain = parse_url('http://' . $registeredDomain, PHP_URL_HOST);
        $currentDomain = parse_url('http://' . $currentDomain, PHP_URL_HOST);

        if ($registeredDomain === $currentDomain) {
            return true;
        }

        if (str_ends_with($currentDomain, '.' . $registeredDomain)) {
            return true;
        }

        return false;
    }
}
