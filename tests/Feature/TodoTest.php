<?php

use App\Models\Todo;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('ToDo一覧ページが表示される', function () {
    $response = $this->get(route('todos.index'));

    $response->assertStatus(200);
    $response->assertSee('ToDoリスト');
});

test('ToDo作成ページが表示される', function () {
    $response = $this->get(route('todos.create'));

    $response->assertStatus(200);
    $response->assertSee('新しいToDo');
});

test('ToDoを作成できる', function () {
    $todoData = [
        'title' => 'テストToDo',
        'description' => 'テスト用の説明',
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'priority' => 'high',
    ];

    $response = $this->post(route('todos.store'), $todoData);

    $response->assertRedirect(route('todos.index'));

    $this->assertDatabaseHas('todos', [
        'title' => 'テストToDo',
        'description' => 'テスト用の説明',
        'priority' => 'high',
        'user_id' => $this->user->id,
    ]);
});

test('ToDo編集ページが表示される', function () {
    $todo = Todo::factory()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('todos.edit', $todo));

    $response->assertStatus(200);
    $response->assertSee('ToDoを編集');
});

test('ToDoを更新できる', function () {
    $todo = Todo::factory()->create(['user_id' => $this->user->id]);

    $updateData = [
        'title' => '更新されたToDo',
        'description' => '更新された説明',
        'due_date' => now()->addDays(14)->format('Y-m-d'),
        'priority' => 'medium',
        'is_completed' => true,
    ];

    $response = $this->put(route('todos.update', $todo), $updateData);

    $response->assertRedirect(route('todos.index'));

    $this->assertDatabaseHas('todos', [
        'id' => $todo->id,
        'title' => '更新されたToDo',
        'description' => '更新された説明',
        'priority' => 'medium',
        'is_completed' => true,
    ]);
});

test('ToDoを削除できる', function () {
    $todo = Todo::factory()->create(['user_id' => $this->user->id]);

    $response = $this->delete(route('todos.destroy', $todo));

    $response->assertRedirect(route('todos.index'));

    $this->assertDatabaseMissing('todos', [
        'id' => $todo->id,
    ]);
});

test('他のユーザーのToDoは編集できない', function () {
    $otherUser = User::factory()->create();
    $todo = Todo::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('todos.edit', $todo));

    $response->assertStatus(403);
});

test('ToDoの完了状態を切り替えられる', function () {
    $todo = Todo::factory()->create([
        'user_id' => $this->user->id,
        'is_completed' => false,
    ]);

    $response = $this->patch(route('todos.toggle', $todo));

    $response->assertRedirect(route('todos.index'));

    $this->assertDatabaseHas('todos', [
        'id' => $todo->id,
        'is_completed' => true,
    ]);
});

test('未認証ユーザーはToDoページにアクセスできない', function () {
    auth()->logout();

    $response = $this->get(route('todos.index'));

    $response->assertRedirect(route('login'));
});
