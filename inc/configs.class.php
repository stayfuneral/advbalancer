<?php


class PluginAdvbalancerConfigs extends CommonDBTM
{
    private $db;
    private static $instance = null;

    /**
     * PluginAdvbalancerConfigs constructor.
     *
     * @param DB $DB
     */
    private function __construct($DB)
    {
        $this->db = $DB;
    }

    public static function getInstance()
    {
        global $DB;

        if(self::$instance === null) {
            self::$instance = new self($DB);
        }

        return self::$instance;
    }

    private $limitParamName = 'user_tickets_limit';


    function getUserTicketsLimit()
    {
        $limit = $this->db->request(self::getTable(), ['config_name' => $this->limitParamName]);

        foreach ($limit as $item) {
            if(!empty($item['config_value'])) {
                return (int)$item['config_value'];
            }
        }
    }

    function setUserTicketsLimit($value)
    {
        $table = self::getTable();

        $params = [
            'config_value' => $value
        ];
        $where = [
            'config_name' => $this->limitParamName
        ];

        return $this->db->updateOrInsert($table, $params, $where, $this->db->error());
    }

    function setConfigs($configName, $configValue)
    {
        $where = [
            'config_name' => $configName
        ];

        $params = [
            'config_name' => $configName,
            'config_value' => $configValue
        ];

        return $this->db->updateOrInsert(self::getTable(), $params, $where);
    }

    function getConfigs($configName = null)
    {
        $result = [];
        $params = [];

        if($configName !== null) {
            $params['config_name'] = $configName;
        }

        $configs = $this->db->request(self::getTable(), $params);

        foreach ($configs as $config) {
            $result[$config['config_name']] = $config['config_value'];
        }

        return $result;
    }

}