<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;
    public function test_noBlogPostsWhenNothingInDatabase()
    {
        $response = $this->get('/posts');

        $response->assertSeeText('No posts found!');
    }

    public function test_see1BlogPostWhenThereIs1()
    {
        // Arrange
        $post = $this->createDummyBlogPost();

        // Act
        $response = $this->get('/posts');
        
        //Assert
        $response->assertSeeText('New Title');
        
        $this->assertDatabaseHas('blog_posts', [
            'title' => 'NEWER New Title',
            'content' => 'NEWER Content of the BlogPost'
        ]); 
    }

    public function test_storeValid()
    {
        $params = [
            'title' => 'Valid title',
            'content' => 'At least 10 characters'
        ];

        $this->post('/posts', $params)
            ->assertStatus(302)
            ->assertSessionHas('status');

        $this->assertEquals(session('status'), 'The blog post was created!');
    }

    public function test_storeFail()
    {
        $params = [
            'title' => 'x',
            'content' => 'x'
        ];

        $this->post('/posts', $params)
            ->assertStatus(302)
            ->assertSessionHas('errors');
        
        $messages = session('errors')->getMessages();

        $this->assertEquals($messages['title'][0], 'The title must be at least 5 characters.');
        $this->assertEquals($messages['content'][0], 'The content must be at least 10 characters.');
    }

    public function testUpdateValid()
    {
        $post = $this->createDummyBlogPost();

        // Works in Laravel 8
        // $this->assertDatabaseHas('blog_posts', [
        //     'title' => 'NEW New Title',
        //     'content' => 'NEW Content of the BlogPost'
        // ]); 
            
        // Another way for Laravel 7+ versions
        $this->assertDatabaseHas('blog_posts', $post->getAttributes());

        $params = [
            'title' => 'A new named title',
            'content' => 'Content was changed'
        ];

        $this->put("/posts/{$post->id}", $params)
            ->assertStatus(302)
            ->assertSessionHas('status');  
        
        $this->assertEquals(session('status'), 'The blog post was updated!');
        $this->assertDatabaseMissing('blog_posts', $post->getAttributes());
        $this->assertDatabaseHas ('blog_posts', [
            'title' => 'A new named title',
            'content' => 'Content was changed'
        ]);
    }

    public function testDelete()
    {
        $post = $this->createDummyBlogPost();
        $this->assertDatabaseHas('blog_posts', $post->getAttributes());

        $this->delete("/posts/{$post->id}")
            ->assertStatus(302)
            ->assertSessionHas('status');

        $this->assertEquals(session('status'), 'The blog post was deleted!');
        $this->assertDatabaseMissing('blog_posts', $post->getAttributes()); 
        
    }

    private function createDummyBlogPost(): BlogPost
    {
        $post = new BlogPost();
        $post->title = 'NEWER New Title';
        $post->content = 'NEWER Content of the BlogPost';
        $post->save();

        return $post;
    }
}
