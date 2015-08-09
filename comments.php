<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__.'/../comments/start.php';

// Connection to the old database.
$capsule = new Capsule();
$capsule->addConnection(require __DIR__.'/database.php');
$capsule->setFetchMode(PDO::FETCH_OBJ);
$db = $capsule->getDatabaseManager();

// Get the comment root id for the given parent id.
function get_root_id($id) {
    global $db;

    $comment = $db->table('comments')
                  ->where('id', $id)
                  ->first(['id', 'parent']);

    if (!$comment) {
        return $id;
    }

    if (!empty($comment->parent)) {
        return get_root_id($comment->parent);
    }

    return $comment->id;
}

// Select all comments from old table.
$comments = $db->table('comments')->get();

$status = ['pending', 'approved', 'spam'];

Comment::unguard();

// Insert the old comments into the new table.
foreach ($comments as $comment) {
    if (empty($comment->parent)) {
        $rootId = null;
    } else {
        $rootId = get_root_id($comment->parent);
    }

    Comment::create([
        'page_id'      => $comment->page,
        'user_id'      => $comment->user_id,
        'author_name'  => $comment->author,
        'author_email' => $comment->author_email,
        'author_url'   => $comment->author_url,
        'author_ip'    => $comment->author_ip,
        'content'      => $comment->comment,
        'status'       => $status[$comment->status],
        'user_agent'   => $comment->agent,
        'root_id'      => $rootId,
        'parent_id'    => $comment->parent ?: null,
        'created_at'   => $comment->date,
    ]);
}

echo 'Done!';
