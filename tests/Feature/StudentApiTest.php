<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_students(): void
    {
        $student = Student::create($this->studentData());

        $this->getJson('/api/students')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $student->id]);
    }

    public function test_it_creates_a_student(): void
    {
        $this->postJson('/api/students', $this->studentData())
            ->assertCreated()
            ->assertJsonPath('message', 'Student created successfully.')
            ->assertJsonPath('data.student_number', '2026-0001');

        $this->assertDatabaseHas('students', [
            'student_number' => '2026-0001',
            'email' => 'juan@example.com',
        ]);
    }

    public function test_it_returns_a_student_or_a_not_found_response(): void
    {
        $student = Student::create($this->studentData());

        $this->getJson("/api/students/{$student->id}")
            ->assertOk()
            ->assertJsonPath('student_number', '2026-0001');

        $this->getJson('/api/students/999')
            ->assertNotFound()
            ->assertJson(['message' => 'Student not found.']);
    }

    public function test_it_updates_a_student(): void
    {
        $student = Student::create($this->studentData());

        $this->putJson("/api/students/{$student->id}", [
            ...$this->studentData(),
            'name' => 'Juan Miguel Dela Cruz',
            'course' => 'BS Computer Science',
            'age' => 21,
            'email' => 'juanmiguel@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Student updated successfully.')
            ->assertJsonPath('data.name', 'Juan Miguel Dela Cruz');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'email' => 'juanmiguel@example.com',
        ]);
    }

    public function test_it_deletes_a_student(): void
    {
        $student = Student::create($this->studentData());

        $this->deleteJson("/api/students/{$student->id}")
            ->assertOk()
            ->assertJson(['message' => 'Student deleted successfully.']);

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_it_validates_student_creation(): void
    {
        $this->postJson('/api/students', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'student_number',
                'name',
                'course',
                'age',
                'email',
            ]);
    }

    /**
     * @return array{student_number: string, name: string, course: string, age: int, email: string}
     */
    private function studentData(): array
    {
        return [
            'student_number' => '2026-0001',
            'name' => 'Juan Dela Cruz',
            'course' => 'BS Information Technology',
            'age' => 20,
            'email' => 'juan@example.com',
        ];
    }
}
