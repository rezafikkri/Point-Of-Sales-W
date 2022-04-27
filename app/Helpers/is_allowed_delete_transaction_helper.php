<?php

/**
 * This helper for check, is allowed to delete transaction.
 * 
 * If edited at smaller than timestamp five hours ago, this is mean transaction is over five hours ago,
 * so transaction is allowed to delete, if the opposite, so transaction is not allowed to delete.
 */
function is_allowed_delete_transaction(string $editedAt): bool
{
    $timestampFiveHoursAgo = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')) - (60 * 60 * 5));
    return $editedAt < $timestampFiveHoursAgo;
}
