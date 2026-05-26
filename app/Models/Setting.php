<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
 
    protected $fillable = ['key', 'value'];
 
    /**
     * Get setting value by key, with auto-decoding of JSON structures.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::find($key);
        if (!$setting) {
            return $default;
        }
 
        $val = $setting->value;
        // Check if value is a JSON string (objects or arrays)
        if ($val !== null && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
 
        // Boolean conversion helper
        if ($val === 'true') return true;
        if ($val === 'false') return false;
 
        return $val;
    }
 
    /**
     * Set setting value by key, auto-encoding arrays or objects into JSON.
     */
    public static function setValue(string $key, $value)
    {
        $valStr = is_array($value) || is_object($value) ? json_encode($value) : (is_bool($value) ? ($value ? 'true' : 'false') : $value);
        return self::updateOrCreate(['key' => $key], ['value' => $valStr]);
    }
}
