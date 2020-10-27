<?php


class PluginAdvbalancerBalancer extends CommonDBTM
{
    const TICKET_CLOSED_STATUS = 6;
    const TYPE_RESPONSIBLE = 2;

    private $db;
    private static $instance = null;

    /**
     * @var string $tablePrefix префикс таблиц плагина в БД GLPI
     */
    public static $tablePrefix = 'glpi_plugin_advbalancer_';

    /**
     * @var array $tables таблицы БД GLPI, использующиеся в работе плагина
     *
     */
    private $tables = [
        'groups' => 'glpi_groups',
        'group_tickets' => 'glpi_groups_tickets',
        'group_users' => 'glpi_groups_users',
        'tickets' => 'glpi_tickets',
        'tickets_users' => 'glpi_tickets_users'
    ];

    static function getTable($classname = null)
    {
        return self::$tablePrefix . 'balancer';
    }

    /**
     * PluginAdvbalancerBalancer constructor.
     *
     * @param DB $DB
     */
    private function __construct($DB)
    {
        $this->db = $DB;
    }

    /**
     * @return PluginAdvbalancerBalancer|null
     */
    public static function getInstance()
    {
        global $DB;

        if(self::$instance === null) {
            self::$instance = new self($DB);
        }

        return self::$instance;
    }

    /**
     * Получает последнюю заявку из балансера
     *
     * @return array
     */
    function getLastTicketInBalancer()
    {
        $params = [
            'WHERE' => [
                'users_id' => ['!=', null]
            ],
            'ORDER' => 'date DESC',
            'LIMIT' => 1
        ];

        $ticket = $this->db->request(self::getTable(), $params);

        foreach ($ticket as $id => $item) {
            return $item;
        }
    }

    /**
     * Проверяет наличие заявки в балансере
     *
     * @param int $ticketId ID заявки
     *
     * @return bool
     */
    function checkTicketInBalancer($ticketId)
    {
        $ticket = $this->db->request(self::getTable(), ['tickets_id' => $ticketId]);

        foreach ($ticket as $item) {
            if($item['tickets_id']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Удаляет заявку из балансера
     *
     * @param int $ticketId ID заявки
     *
     * @return bool|mysqli_result
     */
    function deleteTicketFromBalancer($ticketId)
    {
        return $this->db->deleteOrDie(self::getTable(), ['tickets_id' => $ticketId]);
    }



    /**
     * Получает список пользователей группы
     *
     * @param int $groupId - ID группы
     *
     * @return array
     */
    function getGroupUsers($groupId)
    {
        $users = [];

        $groupUsers = $this->db->request($this->tables['group_users'], ['groups_id' => $groupId]);

        foreach ($groupUsers as $data) {
            $users[] = (int)$data['users_id'];
        }

        return $users;
    }

    /**
     * Получает список заявок, назначенных на пользователя
     *
     * @param int $userId ID пользователя
     *
     * @return array
     */
    function getUserTickets($userId)
    {
        $tickets = [];
        $params = [
            'users_id' => $userId,
            'type' => self::TYPE_RESPONSIBLE
        ];

        $userTickets = $this->db->request($this->tables['tickets_users'], $params);

        foreach ($userTickets as $userTicket) {
            $tickets[] = (int) $userTicket['tickets_id'];
        }

        return $tickets;
    }

    /**
     * Получает количество незакрытых заявок пользователя
     *
     * @param int $userId - ID пользователя
     *
     * @return int
     */
    function getTotalOpenTicketsByUser($userId)
    {
        $totalTickets = 0;

        $result = [];
        $ticketIds = $this->getUserTickets($userId);

        if(!empty($ticketIds)) {
            $params = [
                'id' => $ticketIds,
                'status' => ['!=', self::TICKET_CLOSED_STATUS],
                'is_deleted' => (int) false
            ];
            $tickets = $this->db->request(Ticket::getTable(), $params);

            foreach ($tickets as $id => $ticket) {

                if(in_array($id, $ticketIds)) {
                    $result[] = $ticket;
                }

            }

            $totalTickets = count($result);
        }

        return $totalTickets;

    }

    /**
     * Получает из массива первый ID пользователя, у которого не превышено ограничение на количество открытых заявок.
     * Если таковой не находится, метод возвращает false
     *
     * @param array $userIds массив с ID пользователей
     *
     * @return int|bool
     */
    function getAvailableUser(array $userIds)
    {
        foreach ($userIds as $key => $userId) {
            $openTickets = $this->getTotalOpenTicketsByUser($userId);
            $limit = PluginAdvbalancerConfigs::getInstance()->getUserTicketsLimit();

            if($openTickets < $limit) {

                $lastTicketInBalancer = $this->getLastTicketInBalancer();

                if($lastTicketInBalancer['users_id'] !== $userId) {
                    return $userId;
                } else {
                    unset($userIds[$key]);
                    sort($usersIds);

                    return $this->getAvailableUser($userIds);
                }
            }
        }

        return false;
    }


    /**
     * Добавляет ID заявки в балансер
     *
     * @param $ticketId ID заявки
     * @param $groupId ID группы
     * @param int|null $userId ID пользователя
     *
     * @return bool|mysqli_result
     */
    function addTicketInBalancer($ticketId, $groupId, $userId = null)
    {
        $params = [
            'tickets_id' => $ticketId,
            'groups_id' => $groupId,
            'users_id' => $userId
        ];

        return $this->db->insertOrDie(self::getTable(), $params, $this->db->error());
    }

    /**
     * Добавляет пользователя к заявке в балансере
     *
     * @param int $ticketId ID заявки
     * @param int $userId ID пользователя
     *
     * @return bool|mysqli_result
     */
    function setUserToTicketInBalancer($ticketId, $userId)
    {
        $fields = [
            'users_id' => $userId
        ];

        $where = [
            'tickets_id' => $ticketId
        ];

        return $this->db->updateOrDie(self::getTable(), $fields, $where, $this->db->error());
    }

    /**
     * Назначает заявку на пользователя
     *
     * @param int $ticketId - ID заявки
     * @param int $userId - ID пользователя
     *
     * @return bool|mixed
     */
    function setTicketResponsible($ticketId, $userId)
    {
        $params = [
            'tickets_id' => $ticketId,
            'users_id' => $userId,
            'type' => self::TYPE_RESPONSIBLE,
            'use_notification' => 1
        ];

        return $this->db->insertOrDie($this->tables['tickets_users'], $params, $this->db->error());
    }

    /**
     * Получает пользователя, на которого назначена заявка
     *
     * @param int $ticketId ID заявки
     *
     * @return int
     */
    function getTicketResponsible($ticketId)
    {
        $params = [
            'tickets_id' => $ticketId,
            'type' => self::TYPE_RESPONSIBLE
        ];

        $dbRequest = $this->db->request($this->tables['tickets_users'], $params);

        foreach ($dbRequest as $item) {
            return (int)$item['users_id'];
        }

        return null;
    }

    /**
     * Получает группу специалистов, на которую назначена заявка
     *
     * @param int $ticketId ID заявки
     *
     * @return int
     */
    function getGroupByTicket($ticketId)
    {
        $ticket = $this->db->request($this->tables['group_tickets'], ['tickets_id' => $ticketId]);

        foreach ($ticket as $item) {
            return (int)$item['groups_id'];
        }
    }


    /**
     * Получает список зависших заявок по группе из балансера
     *
     * @param int $groupId ID группы
     *
     * @return array
     */
    function getSuspendedTicketsByGroup($groupId)
    {
        $result = [];

        $params = [
            'groups_id' => $groupId,
            'users_id' => null
        ];

        $suspendedTickets = $this->db->request(self::getTable(), $params);

        foreach ($suspendedTickets as $ticket) {
            $result[] = $ticket['tickets_id'];
        }

        return $result;
    }

    /**
     * Получает первую зависшую заявку из балансера
     *
     * @param int $groupId ID группы
     *
     * @return int
     */
    function getFirstSuspendedTicketByGroup($groupId)
    {
        $suspendedTickets = $this->getSuspendedTicketsByGroup($groupId);

        if(!empty($suspendedTickets)) {
            return (int)$suspendedTickets[0];
        } else {
            return false;
        }
    }
}