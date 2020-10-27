<?php

foreach (glob('*.class.php') as $class) {
    require $class;
}

class PluginAdvbalancerSetup extends CommonDBTM
{
    /**
     * @var string параметр первичного ключа в таблицах плагина
     */
    const PLUGIN_ADVBALANCER_DB_TABLE_PRIMARY_KEY = 'primary key (id)';

    /**
     * @var string поле дата в таблицах плагина
     */
    const PLUGIN_ADVBALANCER_DB_TABLE_DATE_FIELD = 'date datetime default current_timestamp';

    private $db;

    /**
     * @var null $instance хранилище объекта класса
     */
    private static $instance = null;

    private function getTables()
    {
        return [
            PluginAdvbalancerConfigs::getTable() => [
                'config_name varchar(100) not null unique',
                'config_value varchar(255) not null',
                self::PLUGIN_ADVBALANCER_DB_TABLE_DATE_FIELD
            ],
            PluginAdvbalancerBalancer::getTable() => [
                'id int(11) not null auto_increment',
                'tickets_id int(11) not null',
                'users_id int(11)',
                'groups_id int(11) not null',
                self::PLUGIN_ADVBALANCER_DB_TABLE_DATE_FIELD,
                self::PLUGIN_ADVBALANCER_DB_TABLE_PRIMARY_KEY
            ]
        ];
    }

    /**
     * PluginAdvbalancerSetup constructor.
     *
     * @param DB $DB
     */
    private function __construct($DB)
    {
        $this->db = $DB;
    }

    /**
     * @return PluginAdvbalancerSetup|null
     */
    static function getInstance()
    {
        global $DB;

        if(self::$instance === null) {
            self::$instance = new self($DB);
        }

        return self::$instance;
    }

    /**
     * Создаёт таблицы в БД при установке плагина
     */
    function installDbTables()
    {

        foreach ($this->getTables() as $table => $fields) {


            if(!$this->db->tableExists($table)) {
                $sql = "create table $table (" . implode(', ', $fields) . ")";
                $this->db->queryOrDie($sql, $this->db->error());
            }

        }

    }

    /**
     * Удаляет таблицы из БД при удалении плагина
     */
    function uninstallDbTables()
    {
        foreach (array_keys($this->getTables()) as $table) {

            if($this->db->tableExists($table)) {
                $sql = "drop table $table";
                $this->db->queryOrDie($sql, $this->db->error());
            }
        }
    }

    /**
     * Присваивает дефолтные значения в настройках плагина
     *
     * @return bool
     */
    function setDefaultUserTicketsLimit()
    {
        $params = [
            'config_name' => 'user_tickets_limit',
            'config_value' => rand(5, 15)
        ];

        return $this->db->insertOrDie(PluginAdvbalancerConfigs::getTable(), $params, $this->db->error());
    }
}