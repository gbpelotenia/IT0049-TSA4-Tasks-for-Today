<?php

namespace Tests\Feature;

use App\Mail\TaskCompleted;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $overrides = []): array
    {
        return array_merge(['title' => 'Prepare report', 'description' => 'Review all project requirements.', 'due_date' => '2026-12-01', 'priority' => 'high', 'status' => 'pending'], $overrides);
    }

    public function test_required_pages_and_task_details_are_public(): void
    {
        $task = Task::factory()->create();
        foreach (['/', '/tasks', '/profile', '/about', "/tasks/$task->id"] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/tasks')->assertDontSee('>Archive<', false);
        $this->get('/profile')->assertSee('Project profile');
        $this->get('/account')->assertRedirect(route('login'));
    }

    public function test_guests_cannot_reach_any_management_action(): void
    {
        $task = Task::factory()->create();
        foreach ([['GET', '/tasks/create'], ['POST', '/tasks'], ['GET', "/tasks/$task->id/edit"], ['PUT', "/tasks/$task->id"], ['PATCH', "/tasks/$task->id"], ['DELETE', "/tasks/$task->id"]] as [$method, $url]) {
            $this->call($method, $url, $this->data())->assertRedirect(route('login'));
        }
        $this->assertDatabaseCount('tasks', 1);
        $this->assertFalse($task->fresh()->is_archived);
    }

    public function test_signed_in_user_can_create_view_edit_and_update(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create());
        $this->get('/tasks/create')->assertOk();
        $this->post('/tasks', $this->data(['is_archived' => true]))->assertSessionHasNoErrors();
        $task = Task::firstOrFail();
        $this->assertFalse($task->is_archived);
        $this->get("/tasks/$task->id")->assertOk()->assertSee('Prepare report');
        $this->get("/tasks/$task->id/edit")->assertOk();
        $this->put("/tasks/$task->id", $this->data(['title' => 'Updated report', 'status' => 'in_progress']))->assertRedirect(route('tasks.show', $task));
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated report', 'status' => 'in_progress']);
        Mail::assertNothingSent();
    }

    public function test_archiving_preserves_record_and_excludes_it_from_pages(): void
    {
        $task = Task::factory()->create(['title' => 'Archived test title']);
        $this->actingAs(User::factory()->create())->delete("/tasks/$task->id")->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'is_archived' => true]);
        $this->get('/tasks')->assertDontSee('Archived test title');
        $this->get("/tasks/$task->id")->assertNotFound();
        $this->get("/tasks/$task->id/edit")->assertNotFound();
        $this->put("/tasks/$task->id", $this->data())->assertNotFound();
        $this->delete("/tasks/$task->id")->assertNotFound();
    }

    public function test_validation_applies_to_create_and_update(): void
    {
        $this->actingAs(User::factory()->create());
        $fields = ['title', 'description', 'due_date', 'priority', 'status'];
        $this->post('/tasks', [])->assertSessionHasErrors($fields);
        $this->post('/tasks', $this->data(['title' => '   ', 'description' => '  ', 'due_date' => 'bad-date', 'priority' => 'urgent', 'status' => 'complete']))->assertSessionHasErrors($fields);
        $this->post('/tasks', $this->data(['title' => str_repeat('x', 151), 'description' => str_repeat('x', 5001)]))->assertSessionHasErrors(['title', 'description']);
        $this->assertDatabaseCount('tasks', 0);
        $task = Task::factory()->create();
        $this->put("/tasks/$task->id", [])->assertSessionHasErrors($fields);
    }

    public function test_transition_to_done_sends_once_to_configured_recipient(): void
    {
        Mail::fake();
        config(['tasks.notification_recipient' => 'teacher@example.com']);
        $task = Task::factory()->create();
        $this->actingAs(User::factory()->create())->put("/tasks/$task->id", $this->data(['status' => 'done']))->assertSessionHasNoErrors()->assertSessionHas('status', 'Task completed. A completion email was generated.');
        $this->assertSame('done', $task->fresh()->status);
        Mail::assertSent(TaskCompleted::class, fn ($mail) => $mail->hasTo('teacher@example.com') && $mail->task->id === $task->id);
        Mail::assertSentCount(1);
        $this->put("/tasks/$task->id", $this->data(['status' => 'done', 'title' => 'Already finished']))->assertSessionHasNoErrors();
        Mail::assertSentCount(1);
    }

    public function test_editing_already_done_task_sends_no_email(): void
    {
        Mail::fake();
        $task = Task::factory()->create(['status' => 'done']);
        $this->actingAs(User::factory()->create())->put("/tasks/$task->id", $this->data(['status' => 'done']))->assertSessionHasNoErrors();
        Mail::assertNothingSent();
    }

    public function test_reopening_then_completing_again_is_a_new_transition(): void
    {
        Mail::fake();
        $task = Task::factory()->create(['status' => 'done']);
        $this->actingAs(User::factory()->create());
        $this->put("/tasks/$task->id", $this->data(['status' => 'pending']))->assertSessionHasNoErrors();
        Mail::assertNothingSent();
        $this->put("/tasks/$task->id", $this->data(['status' => 'done']))->assertSessionHasNoErrors();
        Mail::assertSentCount(1);
    }

    public function test_invalid_update_sends_no_mail_and_preserves_status(): void
    {
        Mail::fake();
        $task = Task::factory()->create();
        $this->actingAs(User::factory()->create())->put("/tasks/$task->id", $this->data(['title' => '', 'status' => 'done']))->assertSessionHasErrors('title');
        $this->assertSame('pending', $task->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_invalid_recipient_preserves_task_status(): void
    {
        Mail::fake();
        config(['tasks.notification_recipient' => 'invalid']);
        $task = Task::factory()->create();
        $this->actingAs(User::factory()->create())->put("/tasks/$task->id", $this->data(['status' => 'done']))->assertSessionHasErrors('mail');
        $this->assertSame('pending', $task->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_creation_does_not_send_update_notification(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create())->post('/tasks', $this->data(['status' => 'done']))->assertSessionHasNoErrors();
        Mail::assertNothingSent();
    }

    public function test_email_template_and_public_pages_escape_html(): void
    {
        $task = Task::factory()->create(['title' => '<script>unsafe()</script>', 'status' => 'done']);
        $email = new TaskCompleted($task);
        $email->assertSeeInHtml('Task completed');
        $email->assertSeeInHtml('&lt;script&gt;unsafe()&lt;/script&gt;', false);
        $email->assertDontSeeInHtml('<script>unsafe()</script>', false);
        $email->assertSeeInHtml(route('tasks.show', $task));
        $this->get("/tasks/$task->id")->assertOk()->assertSee('&lt;script&gt;unsafe()&lt;/script&gt;', false)->assertDontSee('<script>unsafe()</script>', false);
    }

    public function test_search_and_filter_hide_unrelated_and_archived_tasks(): void
    {
        Task::factory()->create(['title' => 'Report Alpha', 'status' => 'pending']);
        Task::factory()->create(['title' => 'Report Beta', 'status' => 'done']);
        Task::factory()->create(['title' => 'Report Archived', 'status' => 'pending', 'is_archived' => true]);
        $this->get('/tasks?q=Report&status=pending')->assertOk()->assertSee('Report Alpha')->assertDontSee('Report Beta')->assertDontSee('Report Archived');
    }
}
