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

        // Pairnoume to License Key kai to Domain apo to request
        $input = Factory::getApplication()->input;
        $licenseKey = $input->getString('key', '');
        $domain = $input->getString('domain', '');

        // An den yparxei license key 'h domain, epistrefoume error
        if (empty($licenseKey) || empty($domain)) {
            echo json_encode(['error' => 'Missing license key or domain']);
            return;
        }

        // Sindesi me thn vash dedomenwn
        $db = Factory::getContainer()->get(DatabaseDriver::class);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__swjprojects_keys'))
            ->where($db->quoteName('key') . ' = ' . $db->quote($licenseKey));
        $db->setQuery($query);
        $result = $db->loadAssoc();

        // An den yparxei to kleidi, epistrefoume error
        if (!$result) {
            echo json_encode(['error' => 'Invalid license key']);
            return;
        }

        $dateEnd = $result['date_end'];

        // Epitrepomena plugins
        $allowedPlugins = isset($result['allowed_plugins']) ? $result['allowed_plugins'] : '*';

        // Kataxwrimeno domain sth vash
        $registeredDomain = $result['domain'];

        // Elegxos an to trexon domain einai epitrepto
        $isDomainValid = $this->isAllowedDomain($registeredDomain, $domain);

        // An to domain den tairiazei , h adeia den einai egkyrh
        if (!$isDomainValid) {
            $isValid = false;
        } else {
            // An to domain tairiazei, elegxoume thn hmeromhnia lhkshs ths adeias
            $isValid = ($dateEnd == '0000-00-00 00:00:00' || strtotime($dateEnd) > time());
        }

        // Dhmiourgia apanthshs JSON
        $data = [
            'domain'          => $registeredDomain,
            'date_start'      => $result['date_start'],
            'date_end'        => $result['date_end'],
            'project_id'      => $result['project_id'],
            'state'           => $result['state'],
            'license_name'    => 'Lifetime',
            'expires'         => ($isValid ? 'never' : $result['date_end']),
            'key_status'      => ($isValid ? 'Active' : 'Expired'),
            'owner'           => 'Theodoropoulos',
            'license_valid'   => ($isValid ? '1' : '0'),
            'allows_plugins'  => ($isValid ? '1' : '0'),
            'is_trial_license'=> '0',
            'allowed_plugins' => ($isValid ? $allowedPlugins : '') // An h adeia exei lhksei, kenh lista plugins
        ];

        echo json_encode($data);
        return;
    }

    /**
     * Elegxei an to domain (mazi me subdomains) epitrepetai
     */
    private function isAllowedDomain($registeredDomain, $currentDomain)
    {
        // An den yparxei kataxwrimeno domain, epistrefoume false
        if (empty($registeredDomain)) {
            return false;
        }

        // Metatrepoume to domain se kathari morfh (xwris http/https)
        $registeredDomain = parse_url('http://' . $registeredDomain, PHP_URL_HOST);
        $currentDomain = parse_url('http://' . $currentDomain, PHP_URL_HOST);

        // An einai akrivws to idio, epitrepetai
        if ($registeredDomain === $currentDomain) {
            return true;
        }

        // An to currentDomain einai subdomain tou registeredDomain, epitrepetai
        if (str_ends_with($currentDomain, '.' . $registeredDomain)) {
            return true;
        }

        return false;
    }
}
