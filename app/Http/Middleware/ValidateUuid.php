<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidateUuid
{
    /**
     * @param Request  $request
     * @param \Closure $next
     * @param array    ...$params
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, ...$params)
    {
        $values = $this->getParamValues($request, $params);
        $rules = $this->buildRules($values);

        $validator = Validator::make($values, $rules);

        $validator->validate();

        return $next($request);
    }

    /**
     * @param Request $request
     * @param array   $params
     *
     * @return array
     */
    protected function getParamValues(Request $request, array $params): array
    {
        $values = [];

        foreach ($params as $param) {
            $values[$param] = $request->route($param);
        }

        return $values;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    protected function buildRules(array $values): array
    {
        $rules = [];

        foreach ($values as $param => $value) {
            $rules[$param] = 'uuid';
        }

        return $rules;
    }
}
