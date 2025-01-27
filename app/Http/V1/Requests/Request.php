<?php

namespace App\Http\V1\Requests;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request as BaseRequest;
use Illuminate\Support\Carbon;

class Request extends BaseRequest
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected Container $container;

    /**
     * Get the transformed request data.
     *
     * @return array
     */
    public function transform(): array
    {
        return $this->all();
    }

    /**
     * Get the data for the controller.
     */
    public function getValidated(): array
    {
        return array_intersect_key($this->transform(), $this->all()) + $this->defaults();
    }

    /**
     * Get the default request data.
     *
     * @return array
     */
    public function defaults(): array
    {
        return [];
    }

    /**
     * @param $date
     *
     * @return Carbon
     */
    protected function parseDate($date)
    {
        if ($date !== null) {
            return Carbon::parse($date)->setTimezone('UTC');
        }
    }

    /**
     * @param $time
     *
     * @throws \Exception
     *
     * @return Carbon
     */
    protected function parseTime($time)
    {
        if ($time !== null) {
            return Carbon::parse($time);
        }
    }

    /**
     * @param string $phone
     *
     * @return string
     */
    protected function parseE164(string $phone): string
    {
        return $this->phoneFormatter()->e164Phone($phone);
    }

    /**
     * @param Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getValidatorInstance()
    {
        /** @var ValidationFactory $factory */
        $factory = app(ValidationFactory::class);

        if (method_exists($this, 'validator')) {
            return app()->call([$this, 'validator'], compact('factory'));
        }

        $validator = $factory->make(
            $this->all(),
            app()->call([$this, 'rules']),
            $this->messages(),
            $this->attributes()
        );

        if (method_exists($this, 'extendValidator')) {
            app()->call([$this, 'extendValidator'], compact('validator'));
        }

        return $validator;
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }
}
