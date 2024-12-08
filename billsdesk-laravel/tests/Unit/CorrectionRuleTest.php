<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CorrectionRule;
use App\Models\InvoiceTemplate;
use Illuminate\Support\Facades\Config;

class CorrectionRuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'mongodb');
    }

    public function testCanCreateCorrectionRule(): void
    {
        $rule = CorrectionRule::create([
            'company_id' => '63e5d5b555f8d2006f9c8397',
            'rule_name' => 'Simple Correction Rule',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                ['field' => 'weight', 'action' => 'update', 'value' => 5],
            ],
            'template_id' => '63e5d5b555f8d2006f9c8398',
        ]);

        $this->assertNotNull($rule->_id);
        $this->assertEquals('Simple Correction Rule', $rule->rule_name);
        $this->assertEquals([
            ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
        ], $rule->conditions);
        $this->assertEquals([
            ['field' => 'weight', 'action' => 'update', 'value' => 5],
        ], $rule->corrections);
    }

    public function testCorrectionRuleWithComplexCorrections(): void
    {
        $rule = CorrectionRule::create([
            'company_id' => '63e5d5b555f8d2006f9c8397',
            'rule_name' => 'Complex Correction Rule',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                [
                    'field' => 'weight',
                    'action' => 'update',
                    'value' => [
                        ['min' => 1, 'max' => 2, 'value' => 4],
                        ['min' => 2, 'max' => 3, 'value' => 4.5],
                        ['min' => 3, 'max' => 5, 'value' => 5],
                    ],
                ],
            ],
            'template_id' => '63e5d5b555f8d2006f9c8398',
        ]);

        $this->assertEquals([
            [
                'field' => 'weight',
                'action' => 'update',
                'value' => [
                    ['min' => 1, 'max' => 2, 'value' => 4],
                    ['min' => 2, 'max' => 3, 'value' => 4.5],
                    ['min' => 3, 'max' => 5, 'value' => 5],
                ],
            ],
        ], $rule->corrections);
    }

    public function testCorrectionRuleBelongsToTemplate(): void
    {
        $template = InvoiceTemplate::factory()->create();

        $rule = CorrectionRule::create([
            'company_id' => '63e5d5b555f8d2006f9c8397',
            'rule_name' => 'Rule with Template',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                ['field' => 'weight', 'action' => 'update', 'value' => 5],
            ],
            'template_id' => (string) $template->_id,
        ]);

        $this->assertInstanceOf(InvoiceTemplate::class, $rule->template);
        $this->assertEquals((string) $template->_id, (string) $rule->template->_id);
    }

    public function testCorrectionRuleHandlesMixedValueTypes(): void
    {
        $rule = CorrectionRule::create([
            'company_id' => '63e5d5b555f8d2006f9c8397',
            'rule_name' => 'Mixed Values Rule',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                [
                    'field' => 'weight',
                    'action' => 'update',
                    'value' => [
                        ['min' => 1, 'max' => 2, 'value' => 4],
                        ['min' => 2, 'max' => 3, 'value' => 4.5],
                        5, // Valor exacto
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            [
                'field' => 'weight',
                'action' => 'update',
                'value' => [
                    ['min' => 1, 'max' => 2, 'value' => 4],
                    ['min' => 2, 'max' => 3, 'value' => 4.5],
                    5,
                ],
            ],
        ], $rule->corrections);
    }
}
