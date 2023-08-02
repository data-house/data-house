<?php

namespace App\Exports;

use App\Brand;
use App\Models\Question;
use App\Models\QuestionFeedback;
use App\Sentiment;
use Vitorccs\LaravelCsv\Concerns\Exportable;
use Vitorccs\LaravelCsv\Concerns\FromQuery;
use Vitorccs\LaravelCsv\Concerns\WithHeadings;
use Vitorccs\LaravelCsv\Concerns\WithMapping;

class ExportQuestionFeedback implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct()
    {
    }

    public function headings(): array
    {
        return [
            'question_id',
            'question_text',
            'language',
            'answer',
            'references',
            'question_url',
            'questionable_id',
            'questionable_title',
            'questionable_type',
            'questionable_url',
            'parent_questions',
            'feedback_vote',
            'feedback_reason',
            'feedback_comment',
        ];
    }

    public function map($feedback): array
    {
        return [
            $feedback->question->uuid,
            $feedback->question->question,
            $feedback->question->language,
            str_replace(array("\r", "\n"), '', $feedback->question->answer['text'] ?? ''),
            collect($feedback->question->answer['references'] ?? [])->toJson(),
            $feedback->question->url(),
            $feedback->question->questionable->ulid,
            $feedback->question->questionable->title,
            $feedback->question->questionable_type,
            $feedback->question->questionable->url(),
            $feedback->question->ancestors->pluck('uuid')->join('--'),
            $feedback->vote->name,
            $feedback->reason?->name,
            $feedback->note,
        ];
    }

    public function query()
    {
        return QuestionFeedback::query()
            ->with([
                'question',
                'question.questionable',
                'question.ancestors',
            ])
            ->whereHas('question')
            ->orderBy('question_id', 'DESC')
            ->orderBy('created_at', 'DESC');
    }
}
