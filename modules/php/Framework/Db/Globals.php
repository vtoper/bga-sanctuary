<?php

namespace Bga\Games\sanctuary\Framework\Db;

/*
 * Globals
 */

abstract class Globals extends DB_Manager
{
    protected static bool $initialized = false;
    protected static array $data = [];
    protected static array $variables = [];

    protected static string $table = 'global_variables';
    protected static string $primary = 'name';
    protected static function cast(array $row): mixed
    {
        $val = json_decode(\stripslashes($row['value']), true);
        if ($val == null) {
            //when encoding and decoding qualified names there is some
            //issue with decoding so we need to try to decode without stripslashes
            $val = json_decode($row['value'], true);
        }

        if (!array_key_exists($row['name'], static::$variables)) {
            return null;
        }

        return static::$variables[$row['name']] == 'int' ? ((int) $val) : $val;
    }

    /*
   * Fetch all existings variables from DB
   */
    public static function fetch()
    {
        static::$data = [];

        foreach (
            self::DB()
                ->select(['value', 'name'])
                ->get(false)
            as $name => $variable
        ) {
            if (\array_key_exists($name, static::$variables)) {
                static::$data[$name] = $variable;
            }
        }
        static::$initialized = true;
    }

    /*
   * Create and store a global variable declared in this file but not present in DB yet
   *  (only happens when adding globals while a game is running)
   */
    public static function create($name)
    {
        if (!\array_key_exists($name, static::$variables)) {
            return;
        }

        $default = [
            'int' => 0,
            'obj' => [],
            'bool' => false,
            'str' => '',
        ];
        $val = $default[static::$variables[$name]];
        self::DB()->insert(
            [
                'name' => $name,
                'value' => \json_encode($val),
            ],
            true
        );
        static::$data[$name] = $val;
    }

    /*
   * Magic method that intercept not defined static method and do the appropriate stuff
   */
    public static function __callStatic($method, $args)
    {
        if (!static::$initialized) {
            self::fetch();
        }

        if (preg_match('/^([gs]et|inc|is)([A-Z])(.*)$/', $method, $match)) {
            // Sanity check : does the name correspond to a declared variable ?
            $name = mb_strtolower($match[2]) . $match[3];
            if (!\array_key_exists($name, static::$variables) && $name !== "all") {
                throw new \InvalidArgumentException("Property {$name} doesn't exist");
            }

            // Create in DB if don't exist yet
            if (!\array_key_exists($name, static::$data)) {
                self::create($name);
            }

            if ($match[1] == 'get') {
                if ($name === "all") {
                    return static::$data;
                }

                // Basic getters
                return static::$data[$name];
            } elseif ($match[1] == 'is') {
                // Boolean getter
                if (static::$variables[$name] != 'bool') {
                    throw new \InvalidArgumentException("Property {$name} is not of type bool");
                }
                return (bool) static::$data[$name];
            } elseif ($match[1] == 'set') {
                // Setters in DB and update cache
                $value = $args[0];
                if (static::$variables[$name] == 'int') {
                    $value = (int) $value;
                }
                if (static::$variables[$name] == 'bool') {
                    $value = (bool) $value;
                }

                static::$data[$name] = $value;

                self::DB()->update(['value' => \addslashes(\json_encode($value))], $name);
                return $value;
            } elseif ($match[1] == 'inc') {
                if (static::$variables[$name] != 'int') {
                    throw new \InvalidArgumentException("Trying to increase {$name} which is not an int");
                }

                $getter = 'get' . $match[2] . $match[3];
                $setter = 'set' . $match[2] . $match[3];
                return self::$setter(self::$getter() + (empty($args) ? 1 : $args[0]));
            }
        }
        throw new \feException('Undefined method ' . $method);
        return null;
    }

    const PREFERENCE_CONFIRM = 103;
    const CONFIRM_DISABLED = 0;
    const CONFIRM_ENABLED = 1;
}
