<?php


class PluginAdvbalancerEvent extends CommonDBTM
{

    const GROUP_L1_USER_SUPPORT_ID = 29;

    /**
     * Обрабатывает изменение заявки.
     *
     * Если назначена группа, распределяет заявку между пользователями в зависимости от лимита
     *
     * @param Ticket $item
     *
     * @return bool|mysqli_result|void
     */
    static function handleTicketAfterUpdate(Ticket $item)
    {
        $ticketId = intval($item->getID());
        $fields = $item->fields;
        $status = intval($fields['status']);

        $balancer = PluginAdvbalancerBalancer::getInstance();

        $ticketGroup = $balancer->getGroupByTicket($ticketId);

        if($ticketGroup == self::GROUP_L1_USER_SUPPORT_ID) {

            $ticketGroupUsers = $balancer->getGroupUsers($ticketGroup);

            switch ($status) {

                case PluginAdvbalancerBalancer::TICKET_CLOSED_STATUS:
                    
                    $balancer->deleteTicketFromBalancer($ticketId);

                    $responsibleId = $balancer->getTicketResponsible($ticketId);
                    $suspendedTicketId = $balancer->getFirstSuspendedTicketByGroup($ticketGroupUsers);

                    if($suspendedTicketId) {

                        $balancer->setTicketResponsible($suspendedTicketId, $responsibleId);
                        $balancer->setUserToTicketInBalancer($suspendedTicketId, $responsibleId);

                        return;
                    }
                    


                    break;

                default:

                    $ticketResponsible = $balancer->getTicketResponsible($ticketId);

                    if(is_null($ticketResponsible)) {

                        $userId = $balancer->getAvailableUser($ticketGroupUsers);

                        if(!$userId) {

                            if($ticketGroup === null) {
                                continue;
                            }
                            return $balancer->addTicketInBalancer($ticketId, $ticketGroup);

                        } else {

                            $balancer->setTicketResponsible($ticketId, $userId);
                            $balancer->addTicketInBalancer($ticketId, $ticketGroup, $userId);

                            return;

                        }

                    }
                    

                    
                    break;
            }

        }

    }

}