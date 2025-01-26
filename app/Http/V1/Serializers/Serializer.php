<?php

namespace App\Http\V1\Serializers;

use App\Model;
use App\Packages\Utilities\PhoneFormatter;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use libphonenumber\RegionCode;

abstract class Serializer
{
    /**
     * The list of properties that may be expanded.
     *
     * @var array
     */
    protected array $expandable = [];

    /**
     * The list of properties to expand.
     *
     * @var array
     */
    protected array $expand = [];

    /**
     * The list of properties to expand in nested objects.
     *
     * @var array
     */
    protected array $nestedExpand = [];

    /**
     * The serializer instances for expanded properties.
     *
     * @var array
     */
    protected array $expandSerializers = [];

    /**
     * @var \App\Packages\Utilities\PhoneFormatter
     */
    private $phoneFormatter;

    /**
     * Model object cache.
     *
     * @var array
     */
    protected static array $cache = [];

    /**
     * @var bool
     */
    protected bool $cacheEnabled;

    /**
     * Serializer constructor.
     *
     * @param bool $cacheEnabled
     */
    public function __construct(bool $cacheEnabled = false)
    {
        $this->cacheEnabled = $cacheEnabled;
    }

    /**
     * @param Model $model
     *
     * @return array
     */
    abstract public function serialize(Model $model): array;

    /**
     * Format a date.
     *
     * Returns an RFC 3339 formatted date.
     *
     * @param \DateTime|null $date
     *
     * @return string
     */
    protected function formatDate($date)
    {
        return $date?->format(DateTime::RFC3339);
    }

    /**
     * @param \DateTime|null $time
     *
     * @return string
     */
    protected function formatTime($time)
    {
        return $time ? $time->format('H:i:s') : null;
    }

    /**
     * If instantiated with $cacheEnabled this method will handle caching
     * and fetching a fresh copy when necessary.
     *
     * @param string   $id
     * @param string   $class
     * @param \Closure $callback
     *
     * @return array
     */
    protected function cache($id, $class, \Closure $callback)
    {
        if ($this->cacheEnabled) {
            $data = $this->getCached($id, $class);

            if ($data !== false) {
                return $data;
            }
        }

        $data = $callback();

        if ($this->cacheEnabled) {
            $this->setCache($data, $id, $class);
        }

        return $data;
    }

    /**
     * @param string $id
     * @param string $class
     *
     * @return bool|mixed
     */
    protected function getCached($id, $class)
    {
        $key = "{$class}::{$id}";

        return isset(static::$cache[$key]) ? static::$cache[$key] : false;
    }

    /**
     * @param array  $serializedData
     * @param string $id
     * @param string $class
     *
     * @return array
     */
    protected function setCache(array $serializedData, $id, $class)
    {
        return static::$cache["{$class}::{$id}"] = $serializedData;
    }

    /**
     * Format a phone number into a phone object for use on the front-end
     *
     * @param string $phoneNumber
     * @param string $country
     *
     * @return array
     */
    public function formatPhone($phoneNumber, $country = RegionCode::US)
    {
        $phoneFormatter = $this->getPhoneFormatter();

        return [
            'e164' => $phoneFormatter->e164Phone($phoneNumber, $country),
            'national' => $phoneFormatter->nationalPhone($phoneNumber, $country),
            'international' => $phoneFormatter->internationalPhone($phoneNumber, $country),
            'friendly' => $phoneFormatter->phone($phoneNumber, $country),
        ];
    }

    /**
     * Get the serialized data along with expanded properties.
     *
     * @param mixed $abstract
     *
     * @return array
     */
    public function data($abstract)
    {
        $data = $this->serialize($abstract);

        foreach ($this->expand as $key) {
            $method = 'expand'.Str::camel($key);

            $data[$key] = $this->{$method}($abstract, $this->nestedExpand[$key] ?? []);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getExpandable(): array
    {
        return $this->expandable;
    }

    /**
     * Set the properties to expand.
     *
     * @param array $expand
     *
     * @return $this
     */
    public function expand(array $expand)
    {
        $topExpand = [];
        $nestedExpand = [];

        foreach ($expand as $model) {
            $parts = explode('.', $model);

            if (! in_array($parts[0], $this->expandable)) {
                continue;
            }

            if (count($parts) === 2) {
                if (! isset($nestedExpand[$parts[0]])) {
                    $nestedExpand[$parts[0]] = [];
                }

                $nestedExpand[$parts[0]][] = $parts[1];
            } else {
                $topExpand[] = $parts[0];
            }
        }

        $this->expand = $topExpand;
        $this->nestedExpand = $nestedExpand;

        return $this;
    }

    /**
     * Get the serialized data for an expanded property with the give serializer.
     *
     * @param            $abstract
     * @param Serializer $serializer
     * @param array      $expand
     *
     * @return array
     */
    protected function expandWithSerializer($abstract, self $serializer, $expand = [])
    {
        if ($abstract) {
            if (is_string($serializer)) {
                if (! isset($this->expandSerializers[$serializer])) {
                    $this->expandSerializers[$serializer] = new $serializer($this->cacheEnabled);

                    if ($expand) {
                        $this->expandSerializers[$serializer]->expand($expand);
                    }
                }

                $serializer = $this->expandSerializers[$serializer];
            }

            return $serializer->data($abstract);
        }
    }

    /**
     * Get the serialized data for an expanded collection with the give serializer.
     *
     * @param $collection
     * @param $serializer
     *
     * @return mixed
     */
    protected function expandCollectionWithSerializer(Collection $collection, self $serializer): array
    {
        return $collection->map(function ($abstract) use ($serializer) {
            return $this->expandWithSerializer($abstract, $serializer);
        })->toArray();
    }

    /**
     * @return PhoneFormatter
     */
    protected function getPhoneFormatter(): PhoneFormatter
    {
        if ($this->phoneFormatter === null) {
            $this->phoneFormatter = app(PhoneFormatter::class);
        }

        return $this->phoneFormatter;
    }
}
