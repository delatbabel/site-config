<?php
/**
 * Website model
 */
namespace Delatbabel\SiteConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Website
 *
 * This model class is for the database backed websites table.
 *
 * ### Example
 *
 * <code>
 * // Example code goes here
 * </code>
 *
 */
class Website extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'http_host', 'environment'];

    protected $dates = ['deleted_at'];

    /**
     * 1:Many relationship with Config model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function configs()
    {
        return $this->hasMany('Delatbabel\SiteConfig\Models\Config');
    }

    /**
     * Determine the current website server name.
     *
     * This doesn't contain any URL identifiers such as 'http://', just
     * the host name (e.g. www.myserver.com).
     *
     * @return	string
     */
    public static function currentServerName()
    {
        if (! empty(env('SERVER_NAME'))) {
            $BASE_URL = env('SERVER_NAME');
        } elseif (! empty($_SERVER['SERVER_NAME'])) {
            $BASE_URL = $_SERVER['SERVER_NAME'];
        } elseif (! empty($_SERVER['HTTP_HOST'])) {
            $BASE_URL = $_SERVER['HTTP_HOST'];
        } else {
            $BASE_URL = 'empty';
        }
        return $BASE_URL;
    }

    /**
     * Determine the current website data.
     *
     * Returns null if the web site is not found in the websites table.
     *
     * @return	array
     */
    public static function currentWebsiteData()
    {
        static $current_data;
        $BASE_URL = static::currentServerName();
        $cache_key = 'website-data.' . $BASE_URL;

        // Get the current ID from the cache if it is present.
        if (empty($current_data)) {
            if (Cache::has($cache_key)) {
                return Cache::get($cache_key);
            }
        }

        // If the cache doesn't have it then get it from the database.
        if (empty($current_data)) {

            // Have to do this using a raw query because Laravel doesn't INSTR.
            /** @var Website $result */
            $result = static::whereRaw("INSTR('" . $BASE_URL . "', `http_host`) > 0")
                ->orderBy(DB::raw('LENGTH(`http_host`)'), 'desc')
                ->first();
            if (empty($result)) {
                $current_data = null;
            } else {
                $current_data = $result->toArray();
            }

            Cache::put($cache_key, $current_data, 60);
        }

        return $current_data;
    }

    /**
     * Determine the current website ID.
     *
     * Returns null if the web site is not found in the websites table.
     *
     * @return	integer
     */
    public static function currentWebsiteId()
    {
        $data = static::currentWebsiteData();
        if (empty($data)) {
            return null;
        }
        return $data['id'];
    }
}
