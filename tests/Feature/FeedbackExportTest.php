<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionFeedback;
use App\Models\QuestionRelation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FeedbackExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_empty_when_no_feedback(): void
    {
        Storage::fake('local');

        $this->artisan('feedback:export')
            ->assertSuccessful();

        Storage::assertExists('feedbacks.csv');

        $export = Storage::get('feedbacks.csv');
        
        $this->assertNotEmpty($export);

        $this->assertEquals("\u{FEFF}question_id,question_text,language,answer,references,question_url,questionable_id,questionable_title,questionable_type,questionable_url,parent_questions,feedback_vote,feedback_reason,feedback_comment\n", $export);
    }

    public function test_export_contain_feedbacks(): void
    {
        $question = Question::factory()
            ->has(QuestionFeedback::factory()->count(2), 'feedbacks')
            ->answered()
            ->create();

        Storage::fake('local');

        $this->artisan('feedback:export')
            ->assertSuccessful();

        Storage::assertExists('feedbacks.csv');

        $export = Storage::get('feedbacks.csv');

        $this->assertNotEmpty($export);

        $lines = str($export)->split('/\n/');

        $this->assertCount(4, $lines);

        $this->assertEquals("\u{FEFF}question_id,question_text,language,answer,references,question_url,questionable_id,questionable_title,questionable_type,questionable_url,parent_questions,feedback_vote,feedback_reason,feedback_comment", $lines[0]);

        $feedback = QuestionFeedback::first()->load(['question.questionable', 'question']);

        $this->assertEquals([
            $feedback->question->uuid,
            $feedback->question->question,
            $feedback->question->language,
            $feedback->question->answer['text'],
            '[]',
            $feedback->question->url(),
            $feedback->question->questionable->ulid,
            $feedback->question->questionable->title,
            $feedback->question->questionable_type,
            $feedback->question->questionable->url(),
            '',
            $feedback->vote->name,
            $feedback->reason?->name ?? '',
            $feedback->note ?? '',
        ], str_getcsv($lines[1]));
    }

    public function test_export_include_question_ancestors(): void
    {
        $question = Question::factory()
            ->has(QuestionFeedback::factory(), 'feedbacks')
            ->hasAttached(
                Question::factory()->has(QuestionFeedback::factory(), 'feedbacks'),
                ['type' => QuestionRelation::CHILDREN],
                'related'
            )
            ->answered()
            ->multiple()
            ->create([
                'created_at' => now()->subDay(),
            ]);

        Storage::fake('local');

        $this->artisan('feedback:export')
            ->assertSuccessful();

        Storage::assertExists('feedbacks.csv');

        $export = Storage::get('feedbacks.csv');

        $this->assertNotEmpty($export);

        $lines = str($export)->split('/\n/');

        $this->assertCount(4, $lines);

        $this->assertEquals("\u{FEFF}question_id,question_text,language,answer,references,question_url,questionable_id,questionable_title,questionable_type,questionable_url,parent_questions,feedback_vote,feedback_reason,feedback_comment", $lines[0]);

        list($secondFeedback, $firstFeedback) = QuestionFeedback::with(['question.ancestors', 'question.questionable', 'question'])->get();

        $child = $question->children()->first();

        $firstFeedback = QuestionFeedback::first()->load(['question.questionable', 'question']);

        $this->assertEquals([
            $firstFeedback->question->uuid,
            $firstFeedback->question->question,
            $firstFeedback->question->language,
            $firstFeedback->question->answer['text'],
            '[]',
            $firstFeedback->question->url(),
            $firstFeedback->question->questionable->ulid,
            $firstFeedback->question->questionable->title,
            $firstFeedback->question->questionable_type,
            $firstFeedback->question->questionable->url(),
            '',
            $firstFeedback->vote->name,
            $firstFeedback->reason?->name ?? '',
            $firstFeedback->note ?? '',
        ], str_getcsv($lines[2]));

        $this->assertEquals([
            $child->uuid,
            $child->question,
            $child->language,
            $child->answer['text'] ?? '',
            '[]',
            $child->url(),
            $child->questionable->ulid,
            $child->questionable->title,
            $child->questionable_type,
            $child->questionable->url(),
            $question->uuid,
            $secondFeedback->vote->name,
            $secondFeedback->reason?->name ?? '',
            $secondFeedback->note ?? '',
        ], str_getcsv($lines[1]));
    }
}
