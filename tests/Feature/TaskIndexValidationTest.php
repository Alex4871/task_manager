<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaskIndexValidationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidQueryParametersProvider')]
    public function test_it_rejects_invalid_query_parameters(
        string $parameter,
        string $value
    ): void {
        $this->getJson('/api/tasks?'.http_build_query([
            $parameter => $value,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$parameter]);
    }

    public static function invalidQueryParametersProvider(): array
    {
        return [
            'invalid sort field' => ['sort', 'title'],
            'unsupported created_at sort' => ['sort', 'created_at'],
            'invalid sort direction' => ['order', 'random'],
            'uppercase sort direction' => ['order', 'DESC'],
            'zero page' => ['page', '0'],
            'negative page' => ['page', '-1'],
            'non numeric page' => ['page', 'abc'],
            'decimal page' => ['page', '1.5'],
            'zero per page' => ['per_page', '0'],
            'negative per page' => ['per_page', '-1'],
            'per page over limit' => ['per_page', '101'],
            'non numeric per page' => ['per_page', 'abc'],
            'search over limit' => ['search', str_repeat('A', 256)],
        ];
    }

    public function test_it_accepts_maximum_per_page_value(): void
    {
        $this->getJson('/api/tasks?per_page=100')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100);
    }

    public function test_it_accepts_empty_search(): void
    {
        $this->createTask('Первая задача');

        $this->getJson('/api/tasks?search=')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_sorts_tasks_by_create_date(): void
    {
        $this->createTask(
            'Создана позже',
            '2026-10-20 10:00:00'
        );

        $this->createTask(
            'Создана раньше',
            '2026-10-10 10:00:00'
        );

        $this->getJson('/api/tasks?sort=create_date&order=asc')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Создана раньше')
            ->assertJsonPath('data.1.title', 'Создана позже');
    }

    public function test_it_combines_search_sorting_and_pagination(): void
    {
        $this->createTask(
            'Купить билеты',
            '2026-10-10 10:00:00'
        );

        $this->createTask(
            'Купить продукты',
            '2026-10-20 10:00:00'
        );

        $this->createTask(
            'Позвонить маме',
            '2026-10-30 10:00:00'
        );

        $this->getJson('/api/tasks?'.http_build_query([
            'search' => 'Купить',
            'sort' => 'create_date',
            'order' => 'desc',
            'per_page' => 1,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Купить билеты')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_it_preserves_query_parameters_in_pagination_links(): void
    {
        $this->createTask('Купить билеты');
        $this->createTask('Купить продукты');

        $response = $this->getJson('/api/tasks?'.http_build_query([
            'search' => 'Купить',
            'sort' => 'create_date',
            'order' => 'asc',
            'per_page' => 1,
        ]))
            ->assertOk();

        $nextPageUrl = $response->json('links.next');

        $this->assertNotNull($nextPageUrl);

        $query = parse_url($nextPageUrl, PHP_URL_QUERY);

        $this->assertIsString($query);

        parse_str($query, $parameters);

        $this->assertSame('Купить', $parameters['search']);
        $this->assertSame('create_date', $parameters['sort']);
        $this->assertSame('asc', $parameters['order']);
        $this->assertSame('1', $parameters['per_page']);
        $this->assertSame('2', $parameters['page']);
    }

    private function createTask(
        string $title,
        ?string $createDate = null
    ): Task {
        $data = [
            'title' => $title,
            'due_date' => '2026-11-01 12:00:00',
        ];

        if ($createDate !== null) {
            $data['create_date'] = $createDate;
        }

        return Task::create($data);
    }
}
