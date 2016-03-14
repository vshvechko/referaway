<?php

namespace App\Logger;


use Doctrine\DBAL\Logging\SQLLogger;

class Logger extends \Monolog\Logger implements SQLLogger
{

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->debug($sql);

        if ($params) {
            $this->debug(var_export($params, true));
        }

        if ($types) {
            $this->debug(var_export($types, true));
        }
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        // TODO: Implement stopQuery() method.
    }
}