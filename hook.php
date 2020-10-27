<?php
/*
 -------------------------------------------------------------------------
 AdvBalancer plugin for GLPI
 Copyright (C) 2020 by the AdvBalancer Development Team.

 https://github.com/pluginsGLPI/advbalancer
 -------------------------------------------------------------------------

 LICENSE

 This file is part of AdvBalancer.

 AdvBalancer is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 AdvBalancer is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with AdvBalancer. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_advbalancer_install() {

   require 'inc/setup.class.php';

   $setup = PluginAdvbalancerSetup::getInstance();

   $setup->installDbTables();
   $setup->setDefaultUserTicketsLimit();

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_advbalancer_uninstall() {

   require 'inc/setup.class.php';
   PluginAdvbalancerSetup::getInstance()->uninstallDbTables();

   return true;
}

function plugin_advbalancer_item_add_ticket(Ticket $ticket) {
    return PluginAdvbalancerEvent::handleTicketAfterUpdate($ticket);
}
