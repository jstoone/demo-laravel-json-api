<?php

namespace App\JsonApi\Posts;

use App\Post;
use CloudCreativity\JsonApi\Contracts\Validators\RelationshipsValidatorInterface;
use CloudCreativity\LaravelJsonApi\Validators\AbstractValidatorProvider;

class Validators extends AbstractValidatorProvider
{

    /**
     * @inheritdoc
     */
    protected function attributeRules($resourceType, $record = null)
    {
        /** @var Post $record */

        // The JSON API spec says the client does not have to send all attributes for an update request, so
        // if the record already exists we need to include a 'sometimes' before required.
        $required = $record ? 'sometimes|required' : 'required';
        $slugUnique = 'unique:posts,slug';

        if ($record) {
            $slugUnique .= ',' . $record->getKey();
        }

        return [
            'title' => "$required|string|between:1,255",
            'content' => "$required|string|min:1",
            'slug' => "$required|alpha_dash|$slugUnique",
        ];
    }

    /**
     * @inheritdoc
     */
    protected function relationshipRules(RelationshipsValidatorInterface $relationships, $resourceType, $record = null)
    {
        $relationships->hasOne('author', 'people', is_null($record), false);
    }

    /**
     * @inheritdoc
     */
    protected function filterRules($resourceType)
    {
        return [
            'title' => 'string|min:1',
            'slug' => 'sometimes|required|alpha_dash',
        ];
    }

}
