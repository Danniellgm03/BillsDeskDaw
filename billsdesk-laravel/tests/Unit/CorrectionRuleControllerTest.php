<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\CorrectionRule;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CorrectionRuleController;
use App\Models\InvoiceTemplate;
use Illuminate\Http\Request;

class CorrectionRuleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar usuario autenticado y empresa
        $this->company = Company::factory()->create();
        $this->authUser = User::factory()->create(['company_id' => $this->company->id]);
        Auth::login($this->authUser);

        // Limpiar manualmente la colección de MongoDB antes de cada prueba
        CorrectionRule::truncate();
    }

    public function testIndexReturnsAllRules()
    {
        $rules = CorrectionRule::factory(3)->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $response = $controller->index();

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertCount(3, $responseData);
        $this->assertEquals($rules->toArray(), $responseData);
    }

    public function testIndexReturnsEmptyArrayIfNoRules()
    {
        $controller = new CorrectionRuleController();
        $response = $controller->index();

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEmpty($responseData);
    }

    public function testIndexUnAunthenticatedUser()
    {
        Auth::logout();

        $controller = new CorrectionRuleController();
        $response = $controller->index();

        $this->assertEquals(401, $response->status());
    }

    public function testGetByTemplateIdReturnsRules()
    {
        $template = InvoiceTemplate::factory()->create(['company_id' => $this->company->id]);
        $rules = CorrectionRule::factory(3)->create(['company_id' => $this->company->id, 'template_id' => $template->id]);

        $controller = new CorrectionRuleController();
        $response = $controller->getByTemplateId($template->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertCount(3, $responseData);
        $this->assertEquals($rules->toArray(), $responseData);
    }

    public function testGetByTemplateIdReturnsEmptyArrayIfNoRules()
    {
        $template = InvoiceTemplate::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $response = $controller->getByTemplateId($template->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEmpty($responseData);
    }

    public function testGetByTemplateIdUnAunthenticatedUser()
    {
        Auth::logout();

        $controller = new CorrectionRuleController();
        $response = $controller->getByTemplateId(1);

        $this->assertEquals(401, $response->status());
    }

    public function testShowReturnsRule()
    {
        $rule = CorrectionRule::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $response = $controller->show($rule->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals($rule->toArray(), $responseData);
    }

    public function testCreateRuleWithMultipleRanges()
    {
        $template = InvoiceTemplate::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $request = new Request([
            'company_id' => $this->company->id,
            'rule_name' => 'Corrección de peso',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                [
                    'field' => 'weight',
                    'new_column' => 'real_cost',
                    'action' => 'update',
                    'value' => [
                        ['min' => 1, 'max' => 2, 'value' => 4],
                        ['min' => 2, 'max' => 3, 'value' => 4.5],
                        ['min' => 3, 'max' => 5, 'value' => 5],
                    ],
                ],
            ],
            'template_id' => $template->_id,
        ]);

        $response = $controller->create($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Regla de corrección creada', $responseData['message']);
        $this->assertEquals('Corrección de peso', $responseData['rule']['rule_name']);
    }

    public function testCreateRuleWithSimpleValue()
    {
        $template = InvoiceTemplate::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $request = new Request([
            'company_id' => $this->company->id,
            'rule_name' => 'Corrección de peso simple',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                [
                    'field' => 'weight',
                    'action' => 'update',
                    'value' => 2,
                ],
            ],
            'template_id' => $template->_id,
        ]);

        $response = $controller->create($request);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Regla de corrección creada', $responseData['message']);
        $this->assertEquals('Corrección de peso simple', $responseData['rule']['rule_name']);
    }

    public function testCreateRuleValidationFailsMissingRuleName()
    {
        $template = InvoiceTemplate::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $request = new Request([
            'company_id' => $this->company->id,
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                [
                    'field' => 'weight',
                    'action' => 'update',
                    'value' => 2,
                ],
            ],
            'template_id' => $template->_id,
        ]);

        $response = $controller->create($request);

        $this->assertEquals(400, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('rule_name', $responseData['errors']);
    }

    public function testCreateRuleUnauthenticated()
    {
        Auth::logout();

        $template = InvoiceTemplate::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $request = new Request([
            'company_id' => $this->company->id,
            'rule_name' => 'Corrección sin autenticación',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                [
                    'field' => 'weight',
                    'action' => 'update',
                    'value' => 2,
                ],
            ],
            'template_id' => $template->_id,
        ]);

        $response = $controller->create($request);

        $this->assertEquals(401, $response->status());
        $responseData = $response->getData(true);
    }

    public function testCreateRuleFailsTemplateNotBelongingToCompany()
    {
        $otherCompany = Company::factory()->create();
        $template = InvoiceTemplate::factory()->create(['company_id' => $otherCompany->id]);

        $controller = new CorrectionRuleController();
        $request = new Request([
            'company_id' => $this->company->id,
            'rule_name' => 'Regla inválida',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                [
                    'field' => 'weight',
                    'action' => 'update',
                    'value' => 2,
                ],
            ],
            'template_id' => $template->_id,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->create($request);
    }

    public function testUpdateRule()
    {
        $rule = CorrectionRule::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $request = new Request([
            'rule_name' => 'Regla actualizada',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
            'corrections' => [
                [
                    'field' => 'weight',
                    'action' => 'update',
                    'value' => 2,
                ],
            ],
        ]);

        $response = $controller->update($request, $rule->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Regla actualizada', $responseData['rule']['rule_name']);
    }

    public function testUpdateRuleConditionsIsRequired()
    {
        $rule = CorrectionRule::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $request = new Request([
            'rule_name' => 'Regla actualizada',
            'corrections' => [
                [
                    'field' => 'weight',
                    'action' => 'update',
                    'value' => 2,
                ],
            ],
        ]);

        $response = $controller->update($request, $rule->id);

        $this->assertEquals(400, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('conditions', $responseData['errors']);
    }

    public function testUpdateRuleCorrectionsIsRequired()
    {
        $rule = CorrectionRule::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $request = new Request([
            'rule_name' => 'Regla actualizada',
            'conditions' => [
                ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
            ],
        ]);

        $response = $controller->update($request, $rule->id);

        $this->assertEquals(400, $response->status());
        $responseData = $response->getData(true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('corrections', $responseData['errors']);
    }

    public function testDestroyRule()
    {
        $rule = CorrectionRule::factory()->create(['company_id' => $this->company->id]);

        $controller = new CorrectionRuleController();
        $response = $controller->destroy($rule->id);

        $this->assertEquals(200, $response->status());
        $responseData = $response->getData(true);

        $this->assertEquals('Regla de corrección eliminada', $responseData['message']);
    }

}
