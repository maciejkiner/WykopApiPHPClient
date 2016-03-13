<?php
include('../lib/Wapi.php');

$wapiClient = new libs_Wapi('KLUCZ APLKACJI', 'SEKRET APLIKACJI'); // set your API key and secret

echo '<h1>Home page links</h1>';
$result = $wapiClient->doRequest('links/promoted');
if ($wapiClient->isValid()) {
    echo '<ul>';
    foreach ($result as $r) {
        echo '<li>' . $r['title'] . '</li>';
    }
    echo '</ul>';
} else {
    echo $wapiClient->getError();
}

echo '<h1>Entries from microblog with #suchar</h1>';
$result = $wapiClient->doRequest('search/entries', array('q' => '#suchar'));
if ($wapiClient->isValid()) {
    echo '<ul>';
    foreach ($result as $r) {
        echo '<li>' . $r['body'] . '</li>';
    }
    echo '</ul>';
} else {
    echo $wapiClient->getError();
}