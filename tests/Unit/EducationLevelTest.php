<?php

namespace Tests\Unit;

use App\Services\Education\QuestionEligibility;
use App\Support\EducationLevel;
use PHPUnit\Framework\TestCase;

class EducationLevelTest extends TestCase
{
    public function test_valid_levels(): void
    {
        $this->assertTrue(EducationLevel::isValid('medium'));
        $this->assertTrue(EducationLevel::isValid('higher'));
        $this->assertFalse(EducationLevel::isValid(null));
        $this->assertFalse(EducationLevel::isValid('unknown'));
    }

    public function test_medium_accepts_only_medium_questions(): void
    {
        $eligibility = new QuestionEligibility();
        $this->assertTrue($eligibility->levelAllows('medium', 'medium'));
        $this->assertFalse($eligibility->levelAllows('medium', 'higher'));
        $this->assertFalse($eligibility->levelAllows('medium', null));
    }

    public function test_higher_accepts_both_classified_levels(): void
    {
        $eligibility = new QuestionEligibility();
        $this->assertTrue($eligibility->levelAllows('higher', 'medium'));
        $this->assertTrue($eligibility->levelAllows('higher', 'higher'));
        $this->assertFalse($eligibility->levelAllows('higher', null));
    }

    public function test_unclassified_target_rejects_all_questions(): void
    {
        $eligibility = new QuestionEligibility();
        $this->assertFalse($eligibility->levelAllows(null, 'medium'));
        $this->assertFalse($eligibility->levelAllows(null, 'higher'));
    }
}
