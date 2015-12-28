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
    public static function currentServerName() {
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
     * Determine the current website ID.
     *
     * Returns null if the web site is not found in the websites table.
     *
     * @return	integer
     */
    public static function currentWebsiteId() {

        static $current_id;

        // Get the current ID from the cache if it is present.
        if (empty($current_id)) {
            $BASE_URL = static::currentServerName();
            if (Cache::has('website-id.' . $BASE_URL)) {
                $current_id = Cache::get('website-id.' . $BASE_URL);
            }
        }

        // If the cache doesn't have it then get it from the database.
        if (empty($current_id)) {

            // Have to do this using a raw query because Laravel doesn't INSTR.
            $result = static::whereRaw("INSTR('" . $BASE_URL . "', `http_host`) > 0")
                ->orderBy(DB::raw('LENGTH(`http_host`) DESC'))
                ->first();
            if (empty($result)) {
                $current_id = null;
            } else {
                $current_id = $result->id;
            }

            Cache::put('website-id.' . $BASE_URL, $current_id, 60);
        }

        return $current_id;
    }
}
