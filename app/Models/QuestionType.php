<?php

namespace App\Models;

enum QuestionType: int
{
    /**
     * Free text question.
     * The user can enter the question without guidance on the text or format
     */
    case FREE = 1;

    /**
     * Descriptive question.
     * The user is guided to enter a specific formulation of the question: 
     * What are the main "{{ user_input }}" in the report?
     */
    case DESCRIPTIVE = 10;


    /**
     * Get the copilot service corresponding template names
     */
    public function copilotTemplate(): string
    {
        switch ($this) {
            case self::FREE:
                return '0';
                break;
            case self::DESCRIPTIVE:
                return '1';
                break;
            default:
                return '0';
                break;
        } 
    }

    public function formatQuestion(string $question)
    {
        switch ($this) {
            case self::DESCRIPTIVE:
                return "What are the main **{$question}** in the reports?";
                break;
            default:
                return $question;
                break;
        }     
    }
}
