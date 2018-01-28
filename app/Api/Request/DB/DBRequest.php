<?php

namespace App\Api\Request\DB;

use App\Eloquent\AuthorizationAwareModel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Enables PHP classes to convert API request parameters to their query counterparts. Supports relations.
 */
trait DBRequest
{
    use BasicDBRequest;

    /**
     * @inheritDoc
     */
    protected function _rules()
    {
        $modelClass = $this->modelClass();

        /** @var AuthorizationAwareModel|Model $model */
        $model = new $modelClass;

        return [
                'scope' => [
                    'required',
                    Rule::in($model->getPublicScopes())
                ]
            ] + parent::_rules();
    }

    /**
     * Converts API request parameter to its query counterpart. Supports relations using forward slash notation.
     * @param Builder $query
     * @param string $param The parameter. Forward slash notation defines relations.
     * @param string $operator See {@see Builder::where}
     * @param mixed $value See {@see Builder::where}
     * @param string $boolean See {@see Builder::where}
     */
    protected function addWhere(Builder $query, $param, $operator = null, $value = null, $boolean = 'and')
    {
        if ($value === null) {
            return;
        }

        $parts = explode('/', $param, 2);

        if (!isset($parts[1])) {
            $query->where($parts[0], $operator, $value, $boolean);
            return;
        }

        $query->whereHas($parts[0], function (Builder $query) use ($parts, $param, $operator, $value, $boolean) {
            $query->where($parts[1], $operator, $value, $boolean);
        });
    }

    /**
     * Used to add additional parameters to the query
     * @param Builder|\Laravel\Scout\Builder $query
     * @param Collection $parameters
     * @return Builder|\Laravel\Scout\Builder
     */
    protected function additionalQuery($query, Collection $parameters)
    {
        foreach ($this->getDBParameters($parameters) as $key => $value) {
            $this->addWhere($query, $key, '=', $value);
        }

        return $query;
    }

    /**
     * Builds the query from parameters
     * @param Collection $parameters
     * @return Builder
     * @throws AuthorizationException
     */
    protected function buildQuery(Collection $parameters)
    {
        $scope = $parameters['scope'];

        $modelClass = $this->modelClass();

        /** @var AuthorizationAwareModel|Model $model */
        $model = new $modelClass;

        if (!$model->canUsePublicScope($scope, \Auth::user())) {
            $this->authorizationError();
        }

        if (!$model->validatePublicScopeParams($scope, $this->getDBParameters($parameters)->keys())) {
            $this->authorizationError();
        }

        /** @var Builder $query */
        $query = $model->newQuery();

        // limit the query to a scope
        $query->scopes([$scope]);

        // add the additional query
        $query = $this->additionalQuery($query, $parameters);

        return $query;
    }
}