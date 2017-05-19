<?php
/**
 * Config model
 */
namespace Delatbabel\SiteConfig\Models;

use Illuminate\Database\Eloquent\Collection;
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
        return $this->belongsTo('\Delatbabel\SiteConfig\Models\Website');
    }

    /**
     * Check to see if a string is JSON.
     *
     * @param $string
     * @return bool
     */
    protected function isJson($string) {
        if (! is_string($string)) {
            return false;
        }
        $result = @json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Check to see if a string is a serialized array or object.
     *
     * @param $string
     * @return bool
     */
    protected function isSerialized($string) {
        $data = @unserialize($string);
        if ($data !== false) {
            return true;
        } else {
            return false;
}
    }

    /**
     * Accessor function
     *
     * @return mixed, but should always return an array for an array type field.
     */
    public function getValueAttribute($value) {
        if ($this->isJson($value)) {
            return json_decode($value);
        }
        if ($this->isSerialized($value)) {
            return unserialize($value);
        }
        return $value;
    }

    /**
     * Mutator function
     *
     * Always stores the serialized version of an array or JSON string.
     *
     * @param $value
     * @return void
     */
    public function setValueAttribute($value) {
        if ($this->isJson($value)) {
            $value = serialize(json_decode($value, true));
        } elseif (is_array($value)) {
            $value = serialize($value);
        }
        $this->attributes['value'] = $value;
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
     * Otherwise the values that are stored where website_id and/or environment
     * act as "wildcards", so that they will match any searched website_id
     * or environment if there is no closer match.
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
     * The data that this function returns is actually a set of key => value
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

        /** @var Collection $collection */
        $collection = $model->get();
        return static::normaliseCollection($collection);
    }

    /**
     * Return the exact configuration data for a specific environment & group.
     *
     * This function returns the exact configuration data for a specific
     * environment and group, ignoring any wildcard (NULL) values.
     *
     * As an example here are some table entries:
     *
     * <code>
     * id     host    env    key    value
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
     * NULL    www    fruit  null
     * junk    junk   fruit  null
     * junk    www    fruit  null
     * prod    test   fruit  null
     * </code>
     *
     * The data that this function returns is actually a set of key => value
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
    public static function fetchExactSettings($environment = null, $website_id = null, $group = 'config')
    {
        $model = static::where('group', '=', $group);

        // Environment can be null, or must match or use the null wildcard.
        $model->where(function ($query) use ($environment) {
            if (empty($environment)) {
                $query->whereNull('environment');
            } else {
                $query->where('environment', '=', $environment);
            }
        });

        // Website can be null, or must match or use the null wildcard.
        $model->where(function ($query) use ($website_id) {
            if (empty($website_id)) {
                $query->whereNull('website_id');
            } else {
                $query->where('website_id', '=', $website_id);
            }
        });

        /*
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Config SQL query: ' . $model->toSql());
        */

        /** @var Collection $collection */
        $collection = $model->get();
        return static::normaliseCollection($collection);
    }

    /**
     * Normalise a Collection (a result from model->get())
     *
     * Normalise a Collection (a result from model->get()) to a key
     * => value array, picking only the first result in the array. The
     * above queries will produce a collection where the most relevant
     * results happen before the least relevant results, so we just pick
     * the first key=>value pair found in the collection.
     *
     * @param Collection $collection
     * @return array
     */
    protected static function normaliseCollection(Collection $collection)
    {
        $result = [];

        /** @var Config $item */
        foreach ($collection as $item) {
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
                        // The accessor function on $item->value will always return an array
                        $result[$item->key] = $item->value;
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

        $result = [];
        try {
            foreach ($model->select('group')->distinct()->get() as $row) {
                $result[] = $row->group;
            }
        } catch (\Exception $e) {
            // Do nothing.
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
     * @param string $type   "array"|"string"|"integer"
     * @return Config
     */
    public static function set($key, $value, $group = 'config', $environment = null, $website_id = null, $type = 'string')
    {
        //Lets check if we are doing special array handling
        $arrayHandling = false;
        $keyExploded   = explode('.', $key);
        if (count($keyExploded) > 1) {
            $arrayHandling = true;
            $key           = array_shift($keyExploded);
            if ($type == 'array') {
                // Use the accessor to ensure we always get an array.
                $value = static::getValueAttribute($value);
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
            if ($arrayHandling) {
                // we are setting a subset of an array
                $array = [];
                self::buildArrayPath($keyExploded, $value, $array);
                $type  = 'array';
            }

            return static::create(
                [
                    'website_id'  => $website_id,
                    'environment' => $environment,
                    'group'       => $group,
                    'key'         => $key,
                    'value'       => $value,
                    'type'        => $type,
                ]);
        }

        //Check if we need to do special array handling
        if ($arrayHandling) {
            // we are setting a subset of an array
            $array = [];
            self::buildArrayPath($keyExploded, $value, $array);

            //do we need to merge?
            if ($model->type == 'array' && ! empty($model->value)) {
                $array = array_replace_recursive($model->value, $array);
            }
            $value = $array;

            $type = 'array';
        }

        $model->value = $value;
        $model->type  = $type;
        $model->save();
        return $model;
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
            $array[$key] = [];
            self::buildArrayPath($map, $value, $array[$key]);
        } else {
            $array[$key] = $value;
        }
    }
}
