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
        $post = new BlogPost();
        $post->title = 'New Title';
        $post->content = 'Content of the BlogPost';
        $post->save();

        // Act
        $response = $this->get('/posts');
        
        //Assert
        $response->assertSeeText('New Title');
        
        $this->assertDatabaseHas('blog_posts', [
            'title' => 'New Title',
            'content' => 'Content of the BlogPost'
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
}









GIT TEST