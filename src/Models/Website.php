<?php
/**
 * Website model
 */
namespace Delatbabel\SiteWebsite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
    protected $fillable = ['name', 'http_host', 'environment'];

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
        if (! empty($_SERVER['BASE_URL'])) {
            $BASE_URL = $_SERVER['BASE_URL'];
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
            $results = DB::query('SELECT `id` FROM `websites` ' .
                "WHERE INSTR('" . $BASE_URL . "', `http_host`) > 0 AND status='active' ORDER BY LENGTH(`http_host`) DESC");
            if (empty($results)) {
                $current_id = null;
            } else {
                $current_id = $results[0]->id;
            }

            Cache::put('website-id.' . $BASE_URL, $current_id, 60);
        }

        return $current_id;
    }
}
