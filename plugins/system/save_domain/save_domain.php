<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Input\Json;

class PlgSystemSave_Domain extends CMSPlugin
{
    public function onAjaxSave_domain()
    {
        $input = Factory::getApplication()->input;
        $json = json_decode(file_get_contents('php://input'));

        if (!isset($json->key_id) || !isset($json->domain)) {
            return json_encode(['success' => false, 'message' => 'Missing parameters']);
        }

        // Debug log
        file_put_contents(JPATH_SITE . '/logs/save_domain_log.txt', "Key ID: {$json->key_id}, Domain: {$json->domain}\n", FILE_APPEND);

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__swjprojects_keys'))
            ->set($db->quoteName('domain') . ' = ' . $db->quote($json->domain))
            ->where($db->quoteName('id') . ' = ' . (int) $json->key_id);

        $db->setQuery($query);
        
        try {
            $db->execute();
            return json_encode(['success' => true, 'message' => 'Domain saved successfully']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
