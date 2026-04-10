<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * When testing with the SQLite driver (DB_CONNECTION=sqlite), all three
     * database connections ('sqlite', 'mysql', 'tenant') share the same physical
     * file.  We include 'mysql' and 'tenant' in the list of connections to wrap
     * in transactions so that factory-created records on those connections are
     * rolled back between test methods, preventing unique-constraint violations.
     *
     * @var string[]
     */
    protected array $connectionsToTransact = [null, 'mysql', 'tenant'];
}
