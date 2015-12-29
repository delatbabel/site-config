<?php
/**
 * Config model
 */
namespace Delatbabel\SiteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// use Log;

/**
 * Class Config
 *
 * This model class is for the database backed configuration table.
 *
 * ### Example
 *
 * <code>
 * // Example code goes here
 * </code>
 *
 * ### TODO
 *
 * * Doesn't yet do anything about websites.
 */
class Config extends Model
{
    protected $fillable = ['website_id', 'environment', 'group', 'key', 'value', 'type'];

    /**
     * 1:Many relationship with Website model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function website()
    {
        return $this->belongsTo('Delatbabel\SiteConfig\Models\Website');
    }

    /**
     * Return the configuration data for a specific environment & group.
     *
     * What this function tries to achieve is to return the configuration
     * for a given environment and group,  The configuration table contains
     * 2 limiting columns, which are website_id and environment.  The way
     * that the limiting columns work is that if there is an entry in that
     * column then that config item applies only for that host or environment.
     * If a config item is sought where the host or environment matches then
     * these limited values take precedence over any values where website_id
     * or environment are NULL.
     *
     * As an example here are some table entries:
     *
     * <code>
     * id     site    env    key    value
     * 1      NULL    NULL   fruit  apple
     * 2      NULL    prod   fruit  banana
     * 3      www     prod   fruit  mango
     * 4      NULL    test   fruit  peach
     * 5      test    NULL   fruit  orange
     * </code>
     *
     * Given that sample data, this function should return the following
     * data for these searches:
     *
     * <code>
     * env     host   key    data returned
     * NULL    NULL   fruit  1, fruit, apple
     * prod    NULL   fruit  2, fruit, banana
     * NULL    www    fruit  3, fruit, mango
     * junk    junk   fruit  1, fruit, apple  // NULL/NULL values are used as a fallback
     * junk    www    fruit  1, fruit, apple  // no match, fallback
     * prod    test   fruit  5, fruit, orange // host=test takes precedence
     * </code>
     *
     * The data that this funciton returns is actually a set of key => value
     * pairs for the configuration found within group $group.
     *
     * To get the full configuration you need to call this function for each
     * group returned by fetchAllGroups().
     *
     * @param string $environment
     * @param integer $website_id
     * @param string $group
     * @return array
     */
    public static function fetchSettings($environment = null, $website_id = null, $group = 'config')
    {
        $model = static::where('group', '=', $group);

        // Environment can be null, or must match or use the null wildcard.
        $model->where(function ($query) use ($environment) {
            if (empty($environment)) {
                $query->whereNull('environment');
            } else {
                $query->where('environment', '=', $environment)
                    ->orWhereNull('environment');
            }
        });

        // Website can be null, or must match or use the null wildcard.
        $model->where(function ($query) use ($website_id) {
            if (empty($website_id)) {
                $query->whereNull('website_id');
            } else {
                $query->where('website_id', '=', $website_id)
                    ->orWhereNull('website_id');
            }
        });

        // Order by relevance.
        $model->orderBy(DB::raw('CASE
            WHEN website_id IS NOT NULL AND environment IS NOT NULL THEN 1
            WHEN website_id IS NOT NULL THEN 2
            WHEN environment IS NOT NULL THEN 3
            ELSE 4
        END'));

        /*
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Config SQL query: ' . $model->toSql());
        */

        // The above query will produce a query where the most relevant results
        // happen before the least relevant results.  So now we have to create a
        // key->value list picking only the most relevant results.
        $result = array();
        foreach ($model->get() as $item) {
            if (empty($result[$item->key])) {
                switch (strtolower($item->type)) {
                    case 'string':
                        $result[$item->key] = (string)$item->value;
                        break;
                    case 'integer':
                        $result[$item->key] = (integer)$item->value;
                        break;
                    case 'double':
                        $result[$item->key] = (double)$item->value;
                        break;
                    case 'boolean':
                        $result[$item->key] = (boolean)$item->value;
                        break;
                    case 'array':
                        $result[$item->key] = unserialize($item->value);
                        break;
                    case 'null':
                        $result[$item->key] = null;
                        break;
                    default:
                        $result[$item->key] = $item->value;
                }
            }
        }

        return $result;
    }

    /**
     * Return an array of all groups.
     *
     * @return array
     */
    public static function fetchAllGroups()
    {
        $model = new self;

        $result = array();
        foreach ($model->select('group')->distinct()->get() as $row) {
            $result[] = $row->group;
        }

        return $result;
    }

    /**
     * Store a group of settings into the database.
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string $environment
     * @param integer $website_id
     * @param string $type   "array"|null
     * @return Config
     */
    public static function set($key, $value, $group='config', $environment = null, $website_id = null, $type = null)
    {
        //Lets check if we are doing special array handling
        $arrayHandling = false;
        $keyExploded   = explode('.', $key);
        if (count($keyExploded) > 1) {
            $arrayHandling = true;
            $key           = array_shift($keyExploded);
            if ($type == 'array' && ! is_array($value)) {
                $value = unserialize($value);
            }
        }

        // First let's try to fetch the model, if it exists then we need to do an
        // Update not an insert
        $model = static::where('key', '=', $key)->where('group', '=', $group);

        // Environment can be null or must match.
        if (empty($environment)) {
            $model->whereNull('environment');
        } else {
            $model->where('environment', '=', $environment);
        }

        // Website can be null or must match.
        if (empty($website_id)) {
            $model->whereNull('website_id');
        } else {
            $model->where('website_id', '=', $website_id);
        }

        $model = $model->first();

        if (empty($model)) {

            //Check if we need to do special array handling
            if ($arrayHandling) { // we are setting a subset of an array
                $array = array();
                self::buildArrayPath($keyExploded, $value, $array);
                $value = serialize($array);
                $type  = 'array';
            }

            static::create(
                array(
                     'environment' => $environment,
                     'group'       => $group,
                     'key'         => $key,
                     'value'       => $value,
                     'type'        => $type,
                ));
        } else {

            //Check if we need to do special array handling
            if ($arrayHandling) { // we are setting a subset of an array
                $array = array();
                self::buildArrayPath($keyExploded, $value, $array);

                //do we need to merge?
                if ($model->type == 'array') {
                    $array = array_replace_recursive(unserialize($model->value), $array);
                }
                $value = serialize($array);

                $type = 'array';
            }

            $model->value = $value;
            $model->type  = $type;
            $model->save();
        }
    }

    /**
     * This inserts a value into an array at a point in the array path.
     *
     * ### Example
     *
     * <code>
     * $map = [1, 2];
     * $value = 'hello';
     * $array = [];
     *
     * buildArrayPath($map, $value, $array);
     * // $array is now [1 => [2 => 'hello']]
     * </code>
     *
     * @param array $map
     * @param mixed $value
     * @param $array
     * @return void
     */
    protected static function buildArrayPath($map, $value, &$array)
    {
        $key = array_shift($map);
        if (count($map) !== 0) {
            $array[$key] = array();
            self::buildArrayPath($map, $value, $array[$key]);
        } else {
            $array[$key] = $value;
        }
    }
}
