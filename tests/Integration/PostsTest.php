<?php

namespace App\Tests\Integration;

use App\Post;
use App\Tag;

class PostsTest extends TestCase
{

    /**
     * Test the search route
     */
    public function testSearch()
    {
        // ensure there is at least one model in the database
        $this->model();

        $this->doSearch()
            ->assertSearchResponse();
    }

    /**
     * Test that we can search posts for specific ids
     */
    public function testSearchById()
    {
        $models = factory(Post::class, 2)->create();
        // this model should not be in the search results
        $this->model();

        $this->doSearchById($models)
            ->assertSearchByIdResponse($models);
    }

    /**
     * Test the create resource route.
     */
    public function testCreate()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();
        $model = $this->model(false);

        $data = [
            'type' => 'posts',
            'attributes' => [
                'title' => $model->title,
                'slug' => $model->slug,
                'content' => $model->content,
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'people',
                        'id' => $model->author_id,
                    ],
                ],
                'tags' => [
                    'data' => [
                        ['type' => 'tags', 'id' => $tag->getKey()],
                    ],
                ],
            ],
        ];

        $id = $this
            ->doCreate($data)
            ->assertCreateResponse($data);

        $this->assertModelCreated($model, $id, ['title', 'slug', 'content', 'author_id']);
    }

    /**
     * Test the read resource route.
     */
    public function testRead()
    {
        $model = $this->model();
        /** @var Tag $tag */
        $tag = $model->tags()->create(['name' => 'Important']);

        $data = [
            'type' => 'posts',
            'id' => $model->getKey(),
            'attributes' => [
                'title' => $model->title,
                'slug' => $model->slug,
                'content' => $model->content,
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'people',
                        'id' => $model->author_id,
                    ],
                ],
                'tags' => [
                    'data' => [
                        ['type' => 'tags', 'id' => $tag->getKey()],
                    ],
                ],
            ],
        ];

        $this->doRead($model)
            ->assertReadResponse($data);
    }

    /**
     * Test the update resource route.
     */
    public function testUpdate()
    {
        $model = $this->model();

        $data = [
            'type' => 'posts',
            'id' => $model->getKey(),
            'attributes' => [
                'slug' => 'posts-test',
                'title' => 'Foo Bar Baz Bat',
            ],
        ];

        $this->doUpdate($data)
            ->assertUpdateResponse($data)
            ->assertModelPatched($model, $data['attributes'], ['content']);
    }

    /**
     * Test the delete resource route.
     */
    public function testDelete()
    {
        $model = $this->model();

        $this->doDelete($model)
            ->assertDeleteResponse()
            ->assertModelDeleted($model);
    }

    /**
     * @inheritdoc
     */
    protected function getResourceType()
    {
        return 'posts';
    }

    /**
     * Just a helper method so that we get a type-hinted model back...
     *
     * @param bool $create
     * @return Post
     */
    private function model($create = true)
    {
        $builder = factory(Post::class);

        return $create ? $builder->create() : $builder->make();
    }
}
