<?php




define('GLPI_ROOT', $_SERVER['DOCUMENT_ROOT']);

include GLPI_ROOT . '/inc/includes.php';

Html::header('ADV Balancer', $_SERVER['PHP_SELF']);


?>

<div id="plugin-advbalancer-app">
    <template>
        <h1>{{title}}</h1>
        <div class="form-element">
            <p>Максимальное количество открытых заявок</p>
            <input v-model="limit" class="input-limit form-control" type="number" :value="limit">
            <button @click="setUserTicketsLimit" class="set-limit btn btn-success" type="submit">Задать лимит</button>
        </div>
    </template>
</div>

<?php Html::footer();