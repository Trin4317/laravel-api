<?php

namespace App\Services\V1;

class CustomerQuery
{
    protected $safeParams = [
        'name'       => ['eq'],
        'type'       => ['eq'],
        'email'      => ['eq'],
        'address'    => ['eq'],
        'city'       => ['eq'],
        'state'      => ['eq'],
        'postalCode' => ['eq', 'gt', 'lt']
    ];

    protected $columnMap = [
        'postalCode' => 'postal_code'
    ];

    protected $operatorMap = [
        'eq' => '=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<='
    ];

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
