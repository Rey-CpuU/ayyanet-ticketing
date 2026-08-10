<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * Postgres connector that forwards the URL query string "options" parameter
 * into the PDO DSN.
 *
 * Neon (neon.tech/sni) requires clients without SNI support (such as the
 * vercel-php runtime) to pass the endpoint ID as a connection option:
 *   ?options=endpoint%3D<endpoint-id>
 * Laravel parses that query parameter into $config['options'] as a string,
 * which PDO would reject as its attribute array - so we move it into the DSN.
 */
class NeonPostgresConnector extends PostgresConnector
{
    /**
     * Create a DSN string from a configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        if (isset($config['options']) && is_string($config['options'])) {
            $options = str_replace("'", "\\'", $config['options']);
            $dsn .= ";options='{$options}'";
        }

        return $dsn;
    }

    /**
     * Get the PDO options based on the configuration.
     *
     * @param  array  $config
     * @return array
     */
    public function getOptions(array $config)
    {
        if (isset($config['options']) && is_string($config['options'])) {
            unset($config['options']);
        }

        return parent::getOptions($config);
    }
}
