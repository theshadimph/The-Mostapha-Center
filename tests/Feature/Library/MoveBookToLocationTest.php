<?php

namespace Tests\Feature\Library;

use App\Actions\Library\MoveBookToLocation;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MoveBookToLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_moves_a_book_and_records_the_previous_location(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $fromShelf = Shelf::factory()->create();
        $toShelf = Shelf::factory()->create();
        $book = Book::factory()->onShelf($fromShelf, '10')->create();

        (new MoveBookToLocation)->handle($book, $toShelf, '20', $librarian, 'نقل تجريبي');

        $book->refresh();
        $this->assertSame($toShelf->id, $book->shelf_id);
        $this->assertSame('20', $book->position);

        $movement = $book->movements()->first();
        $this->assertSame($fromShelf->id, $movement->from_shelf_id);
        $this->assertSame('10', $movement->from_position);
        $this->assertSame($toShelf->id, $movement->to_shelf_id);
        $this->assertSame('20', $movement->to_position);
        $this->assertSame($librarian->id, $movement->moved_by);
    }

    public function test_it_prevents_moving_a_book_onto_an_occupied_position(): void
    {
        $shelf = Shelf::factory()->create();
        Book::factory()->onShelf($shelf, '01')->create();
        $mover = Book::factory()->create();

        $this->expectException(ValidationException::class);

        (new MoveBookToLocation)->handle($mover, $shelf, '01');
    }

    public function test_it_can_unshelve_a_book(): void
    {
        $shelf = Shelf::factory()->create();
        $book = Book::factory()->onShelf($shelf, '01')->create();

        (new MoveBookToLocation)->handle($book, null, null);

        $book->refresh();
        $this->assertNull($book->shelf_id);
        $this->assertNull($book->position);
        $this->assertFalse($book->isShelved());
    }

    public function test_location_code_reflects_section_shelf_and_position(): void
    {
        $book = Book::factory()->create();
        $shelf = Shelf::factory()->create(['code' => '02']);
        $shelf->librarySection->update(['code' => 'F03']);

        (new MoveBookToLocation)->handle($book, $shelf->fresh(), '45');

        $this->assertSame('F03-02-45', $book->fresh()->location_code);
    }
}
