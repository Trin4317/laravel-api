<?php

namespace App\Filters;

class ApiFilter
{
    protected $safeParams = [];

    protected $columnMap = [];

    protected $operatorMap = [];

    public function transform($request)
    {
        $eloQueries = [];

        // Note: By looping against $safeParams instead of query string in request
        // we can limit the amount of loops happens
        foreach ($this->safeParams as $param => $operators) {
            $query = $request->query($param); // ?name[eq]=foo would return ['name' => ['eq' => 'foo']]
            if (!isset($query)) {
                continue;
            }

            $column = $this->columnMap[$param] ?? $param;

            foreach ($operators as $operator) {
                if (isset($query[$operator])) { // ['name']['eq'] = 'foo'
                    $eloQueries[] = [$column, $this->operatorMap[$operator], $query[$operator]]; // ['name', '=', 'foo']
                }
            }
        }

        return $eloQueries;
    }
}
