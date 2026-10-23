<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits()
    {
        $connection = DB::connection();

        if (
            ! app()->environment('testing')
            || $connection->getDriverName() !== 'pgsql'
            || $connection->getDatabaseName() !== 'task_manager_test'
            || $connection->selectOne('SELECT current_database() AS name')->name !== 'task_manager_test'
        ) {
            throw new RuntimeException(
                'Tests must run against the task_manager_test postgresql db'
            );
        }

        return parent::setUpTraits();
    }
}
