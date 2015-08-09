<?php

use ACS\User;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__.'/../comments/start.php';

// Connection to the old database.
$capsule = new Capsule();
$capsule->addConnection(require __DIR__.'/database.php');
$capsule->setFetchMode(PDO::FETCH_OBJ);
$db = $capsule->getDatabaseManager();

// Select all users from old table.
$users = $db->table('users')->get();

// Insert the old users into the new table.
foreach ($users as $user) {
    User::create([
        'name'     => $user->name,
        'email'    => $user->email,
        'password' => $user->password,
    ]);
}

echo 'Done!';
